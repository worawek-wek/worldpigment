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
        // แสดง Order (header บนสุด) ที่เข้าเงื่อนไขข้อใดข้อหนึ่ง (OR) โดยใช้ช่วงวันที่เดียวกัน:
        //   (ก) ปิดจบงานในช่วง — ต้อง end_close='Y' และ end_close_date อยู่ในช่วง
        //   (ข) มีรายการที่เปลี่ยน senddate ในช่วง — senddate_changed_at อยู่ในช่วง (ไม่สนสถานะปิดจบงาน)
        $headers = PlanningHeader::query()
            ->whereNull('parent_planning_id')
            ->where(function ($q) use ($from, $to) {
                // (ก) ปิดจบงานในช่วง (เฉพาะเงื่อนไขนี้ที่ต้องปิดจบงานแล้ว)
                $q->where(function ($qq) use ($from, $to) {
                    $qq->where('end_close', 'Y')
                       ->when($from, fn ($x) => $x->whereDate('end_close_date', '>=', $from))
                       ->when($to, fn ($x) => $x->whereDate('end_close_date', '<=', $to));
                })
                // (ข) หรือ มีรายการที่เปลี่ยน senddate ในช่วง (ไม่ต้องปิดจบงาน)
                ->orWhereHas('plannings', function ($qq) use ($from, $to) {
                    $qq->whereNotNull('senddate_changed_at')
                       ->when($from, fn ($x) => $x->whereDate('senddate_changed_at', '>=', $from))
                       ->when($to, fn ($x) => $x->whereDate('senddate_changed_at', '<=', $to));
                });
            })
            ->orderBy('end_close_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $rows = collect();

        foreach ($headers as $h) {
            // ชื่อลูกค้า: ดึงจากตาราง morder ตาม orderno (logic เดียวกับ OrderPlanController)
            $custname = DB::table('morder')->where('Orderno', $h->orderno)->value('Custname');

            // order นี้ "ปิดจบงานในช่วง" หรือไม่ (เข้ามาทางเงื่อนไข ก)
            // ถ้าใช่ → แสดงทุกรายการเหมือนเดิม; ถ้าไม่ใช่ → แสดงเฉพาะรายการที่เปลี่ยน senddate ในช่วง (เงื่อนไข ข)
            $closedInRange = false;
            if ($h->end_close === 'Y' && $h->end_close_date) {
                $d = \Carbon\Carbon::parse($h->end_close_date)->toDateString();
                $closedInRange = (!$from || $d >= $from) && (!$to || $d <= $to);
            }

            // รายการสินค้าตรงของ header (ไม่ไล่ sub-header semi/pigment)
            // ดึงทั้งแถวมา group ในฝั่ง PHP เพื่อเลือก "แถวตัวแทน" ตาม senddate_changed_at ได้ (SQL GROUP BY ทำไม่ได้ตรง ๆ)
            $plannings = DB::table('tb_planning')
                ->where('planning_header_id', $h->id)
                ->orderBy('id', 'asc')
                ->get(['id', 'itemno', 'red_bill_code', 'weight_packing', 'senddate', 'senddate_log', 'senddate_changed_at']);

            // รวมรายการที่ "รหัสสินค้า (itemno) เดียวกัน" เป็นแถวเดียว (คงลำดับตาม id แรกที่พบ)
            foreach ($plannings->groupBy('itemno') as $itemno => $group) {
                // รายการนี้มีการเปลี่ยน senddate ในช่วงที่เลือกหรือไม่
                $changedInRange = $group->contains(function ($r) use ($from, $to) {
                    if (!$r->senddate_changed_at) {
                        return false;
                    }
                    $d = \Carbon\Carbon::parse($r->senddate_changed_at)->toDateString();
                    return (!$from || $d >= $from) && (!$to || $d <= $to);
                });

                // ถ้า order ไม่ได้ปิดจบในช่วง (เข้ามาทางฝั่ง senddate) → ข้ามรายการที่ไม่ได้เปลี่ยน senddate ในช่วง
                if (!$closedInRange && !$changedInRange) {
                    continue;
                }

                // เป็น (weight_to) = ผลรวม weight_packing ของ item รหัสเดียวกัน
                $weight_to = $group->sum('weight_packing');

                // เลือกแถวตัวแทน = แถวที่เปลี่ยน senddate ล่าสุด (senddate_changed_at มากสุด)
                // ถ้าไม่มีแถวไหนเคยเปลี่ยนเลย → ใช้แถวแรก
                $rep = $group->whereNotNull('senddate_changed_at')
                             ->sortByDesc('senddate_changed_at')
                             ->first() ?: $group->first();

                // กำหนดเสร็จเดิม = วันที่ "ล่าสุด" ใน senddate_log ของแถวตัวแทน (ค่าเดิมก่อนเปลี่ยนครั้งล่าสุด)
                $logDates = array_values(array_filter(array_map('trim', explode(',', $rep->senddate_log ?? ''))));
                $due_original = count($logDates) ? end($logDates) : null;

                $rows->push([
                    'itemno'        => $itemno,
                    'custname'      => $custname,
                    'orderno'       => $h->orderno,
                    'red_bill_code' => $rep->red_bill_code, // เลขที่ใบทบทวนคำสั่งซื้อ: จาก tb_planning ของแถวตัวแทน
                    'due_original'  => $due_original,    // กำหนดเสร็จเดิม: วันที่ล่าสุดใน senddate_log (null = ไม่เคยเปลี่ยน)
                    'due_postpone'  => $rep->senddate,   // ขอเลื่อนเป็นวันที่: senddate ปัจจุบันของแถวตัวแทน
                    'weight_from'   => $h->netqty,       // จาก: netqty ของ header
                    'weight_to'     => $weight_to,       // เป็น: ผลรวม weight_packing ของ item รหัสเดียวกัน
                    'reason'        => $h->end_close_remark, // สาเหตุ: หมายเหตุตอนปิดจบงาน
                ]);
            }
        }

        // ค้นหาแบบคำเดียว: เลขที่ใบทบทวน (red_bill_code) / เลขที่ order (orderno) / รหัสสินค้า (itemno) / ชื่อลูกค้า (custname)
        if ($keyword) {
            $kw = mb_strtolower($keyword);
            $rows = $rows->filter(function ($r) use ($kw) {
                return mb_strpos(mb_strtolower((string) $r['red_bill_code']), $kw) !== false
                    || mb_strpos(mb_strtolower((string) $r['orderno']), $kw) !== false
                    || mb_strpos(mb_strtolower((string) $r['itemno']), $kw) !== false
                    || mb_strpos(mb_strtolower((string) $r['custname']), $kw) !== false;
            })->values();
        }

        return $rows;
    }
}
