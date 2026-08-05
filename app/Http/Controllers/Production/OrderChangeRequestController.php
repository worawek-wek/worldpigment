<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PlanningHeader;
use Mpdf\Mpdf;

/**
 * ใบขอเปลี่ยนแปลงคำสั่งซื้อจากภายใน
 *
 * ดึง Order (tb_planning_header ระดับบนสุด: parent_planning_id IS NULL) ที่ถูก "ปิดจบงาน"
 * แล้ว (end_close = 'Y') โดยกรองช่วงตาม end_close_date จากนั้นแตกเป็นรายการสินค้า
 * (tb_planning ตรงของ header — ไม่ไล่ semi/pigment) 1 แถวต่อ 1 รายการ แล้วส่งออกเป็น PDF
 *
 * เพิ่ม 2026-08-05
 */
class OrderChangeRequestController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $keyword = $request->input('keyword') ?: null;

        $rows = ($from || $to) ? $this->buildRows($from, $to, $keyword) : collect();

        return view('production-planning.order-change-request.index', [
            'rows'      => $rows,
            'date_from' => $from,
            'date_to'   => $to,
            'keyword'   => $keyword,
            'searched'  => (bool) ($from || $to),
        ]);
    }

    public function pdf(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $keyword = $request->input('keyword') ?: null;

        $rows = $this->buildRows($from, $to, $keyword);

        $html = view('production-planning.order-change-request.pdf', [
            'rows'      => $rows,
            'date_from' => $from,
            'date_to'   => $to,
        ])->render();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L', // แนวนอน (ตารางกว้าง)
            'margin_left'   => 8,
            'margin_right'  => 8,
            'margin_top'    => 8,
            'margin_bottom' => 8,
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="order-change-request.pdf"',
        ]);
    }

    /**
     * แปลง input วันที่ (จาก/ถึง) ให้เป็นคู่ [from, to] รูปแบบ Y-m-d
     * ถ้าไม่ได้ส่งมา ใช้วันที่ปัจจุบันเป็นค่าเริ่มต้น
     */
    private function dateRange(Request $request): array
    {
        $today = now()->toDateString();

        $from = $request->input('date_from') ?: $today;
        $to   = $request->input('date_to') ?: $today;

        return [$from, $to];
    }

    /**
     * สร้างแถวข้อมูลสำหรับตาราง/PDF — 1 แถวต่อ 1 รายการสินค้า (tb_planning)
     *
     * $keyword: ค้นหาแบบคำเดียวข้าม 3 ฟิลด์ — เลขที่ใบทบทวน (orderno) / รหัสสินค้า (itemno) /
     * ชื่อลูกค้า (custname) — กรองที่ระดับแถวสุดท้ายเพราะข้อมูลอยู่คนละระดับ (header/item/morder)
     */
    private function buildRows(?string $from, ?string $to, ?string $keyword = null)
    {
        $headers = PlanningHeader::query()
            ->whereNull('parent_planning_id')
            ->where('end_close', 'Y')
            ->when($from, fn ($q) => $q->whereDate('end_close_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('end_close_date', '<=', $to))
            ->orderBy('end_close_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $rows = collect();

        foreach ($headers as $h) {
            // ชื่อลูกค้า: ดึงจากตาราง morder ตาม orderno (logic เดียวกับ OrderPlanController)
            $custname = DB::table('morder')->where('Orderno', $h->orderno)->value('Custname');

            // รายการสินค้าตรงของ header (ไม่ไล่ sub-header semi/pigment)
            // รวมรายการที่ "รหัสสินค้า (itemno) เดียวกัน" ใน order เดียวกันเป็นแถวเดียว
            // - จาก (weight_from) = netqty ของ header (ค่าเดียว ไม่บวก)
            // - เป็น (weight_to)  = ผลรวม weight_packing ของ item ที่รหัสเดียวกัน
            $items = DB::table('tb_planning')
                ->where('planning_header_id', $h->id)
                ->groupBy('itemno')
                ->orderByRaw('MIN(id) ASC')
                ->get([
                    'itemno',
                    DB::raw('SUM(weight_packing) AS weight_to'),
                ]);

            foreach ($items as $it) {
                $rows->push([
                    'itemno'        => $it->itemno,
                    'custname'      => $custname,
                    'orderno'       => $h->orderno,
                    'due_original'  => '-', // กำหนดเสร็จเดิม (ยังไม่ใช้)
                    'due_postpone'  => '-', // ขอเลื่อนเป็นวันที่ (ยังไม่ใช้)
                    'weight_from'   => $h->netqty,   // จาก: netqty ของ header
                    'weight_to'     => $it->weight_to, // เป็น: ผลรวม weight_packing ของ item รหัสเดียวกัน
                    'reason'        => $h->end_close_remark, // สาเหตุ: หมายเหตุตอนปิดจบงาน
                ]);
            }
        }

        // ค้นหาแบบคำเดียว: เลขที่ใบทบทวน (orderno) / รหัสสินค้า (itemno) / ชื่อลูกค้า (custname)
        if ($keyword) {
            $kw = mb_strtolower($keyword);
            $rows = $rows->filter(function ($r) use ($kw) {
                return mb_strpos(mb_strtolower((string) $r['orderno']), $kw) !== false
                    || mb_strpos(mb_strtolower((string) $r['itemno']), $kw) !== false
                    || mb_strpos(mb_strtolower((string) $r['custname']), $kw) !== false;
            })->values();
        }

        return $rows;
    }
}
