<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

/**
 * รายงาน "Order ค้างส่งลูกค้า" ตามรหัสผู้ขาย (26/10/2569) — ผังตามกระดาษของระบบเดิม
 * ปุ่มบนหัวหน้า /order → หน้าต่างกรอกรหัสผู้ขาย (#outstandingModal) → PDF A4 แนวนอน
 *
 * ค้างส่ง = รายการ (suborder) ที่ยังไม่มีวันที่ลูกค้าได้รับ (DVDate ว่าง) ของใบที่ morder.supno = รหัสผู้ขาย
 * จัดกลุ่มตามลูกค้า (เรียงรหัสลูกค้า) · ในกลุ่มเรียงตาม "กำหนดแรก" → เลขที่ใบ → Runno
 *
 * กำหนดแรก / เลื่อน 1-3 มาจากประวัติใน tb_order_change_line (suborder.senddate = กำหนดปัจจุบัน)
 * แถวที่แยกส่งออกมา (parent_runno) ได้ประวัติของแถวต้นทางก่อนแยก + วันที่ของส่วนที่แยก
 */
class OrderOutstandingController extends Controller
{
    /** จำนวนช่อง "เลื่อน" บนกระดาษเดิม */
    public const SHIFT_COLS = 3;

    public function pdf(Request $request)
    {
        $sale = trim((string) $request->query('sale', ''));
        if ($sale === '') {
            abort(422, 'กรุณาระบุรหัสผู้ขาย');
        }

        $rows = DB::table('suborder as s')
            ->join('morder as m', 'm.Orderno', '=', 's.Orderno')
            ->leftJoin('customer as c', 'c.code', '=', 'm.Custno')
            ->where('m.supno', $sale)
            ->whereNull('s.DVDate')
            // เรียง supno → custno → senddate → itemno (26/10/2569 ตามที่ผู้ใช้สั่ง) · senddate ว่างไปท้ายกลุ่ม
            ->orderBy('m.supno')
            ->orderBy('m.Custno')
            ->orderByRaw('s.senddate IS NULL, s.senddate')
            ->orderBy('s.Itemno')
            ->orderBy('s.Runno')
            ->get([
                'm.supno', 'm.Orderno', 'm.Mdate', 'm.Custno', 'm.Custname', 'm.PO', 'm.Send',
                's.Runno', 's.parent_runno', 's.Itemno', 's.Lotno', 's.Stock', 's.Production',
                's.custwant', 's.senddate', 's.EndP',
                'c.name as cust_name',
            ]);

        $history = $this->dueHistory($rows->pluck('Orderno')->unique()->values()->all());
        $today   = now()->startOfDay();

        foreach ($rows as $r) {
            $h = $history($r);
            $r->first_due = $h['first'] ?? self::ymd($r->senddate);
            // มีเลื่อนเกิน 3 ครั้ง → แสดง 3 ครั้งล่าสุด
            $r->shifts = array_slice($h['shifts'], -self::SHIFT_COLS);
            // เลย Due = กำหนดปัจจุบันถึงวันนี้แล้ว แต่ยังไม่ได้ส่ง
            $r->overdue = $r->senddate && Carbon::parse($r->senddate)->startOfDay()->lte($today);
        }

        // ลำดับมาจาก SQL แล้ว — groupBy คงลำดับเดิมไว้
        $groups = $rows
            ->groupBy(fn ($r) => trim((string) $r->Custno))
            ->map(fn ($items, $custno) => [
                'custno' => $custno,
                'name'   => trim((string) ($items->first()->cust_name ?: $items->first()->Custname)),
                'items'  => $items->values(),
                'stock'  => $items->sum(fn ($r) => (float) $r->Stock),
                'prod'   => $items->sum(fn ($r) => (float) $r->Production),
            ])
            ->values();

        $html = view('order.outstanding-pdf', [
            'groups' => $groups,
            'sale'   => $sale,
            'shiftCols' => self::SHIFT_COLS,
        ])->render();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L',
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 10,
            'margin_bottom' => 12,
        ]);
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->SetHTMLFooter(
            '<table width="100%" style="font-family: sarabun; font-size: 10px;"><tr>'
            . '<td>' . now()->format('d/m/Y H:i') . '</td>'
            . '<td style="text-align: right;">Page {PAGENO} of {nbpg}</td>'
            . '</tr></table>'
        );
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="outstanding-' . preg_replace('/[^A-Za-z0-9_-]/', '', $sale) . '-' . now()->format('Ymd') . '.pdf"',
        ]);
    }

    /**
     * คืนฟังก์ชันหาประวัติกำหนดส่งของแต่ละแถว → ['first' => Y-m-d|null, 'shifts' => [Y-m-d, ...]]
     * อ่าน tb_order_change_line ของใบในรายงานครั้งเดียว
     */
    private function dueHistory(array $ordernos): \Closure
    {
        $lines = $ordernos
            ? DB::table('tb_order_change_line')
                ->whereIn('orderno', $ordernos)
                ->whereIn('action', ['postpone', 'split'])
                ->orderBy('id')
                ->get(['id', 'runno', 'action', 'old_senddate', 'new_senddate', 'split_runno'])
            : collect();

        $postpones = $lines->where('action', 'postpone')->groupBy('runno');
        $splitOf   = $lines->where('action', 'split')->keyBy('split_runno');   // แถวลูก → บรรทัดที่แยกมันออกมา

        // $beforeId = นับเฉพาะบรรทัดก่อนจุดแยก (ใช้ตอนไล่ขึ้นไปหาแถวต้นทาง)
        $resolve = function (int $runno, ?int $beforeId = null) use (&$resolve, $postpones, $splitOf) {
            $own = collect($postpones->get($runno, []))
                ->when($beforeId, fn ($c) => $c->filter(fn ($l) => $l->id < $beforeId));

            $split = $splitOf->get($runno);
            if ($split) {
                $base   = $resolve((int) $split->runno, (int) $split->id);
                $first  = $base['first'] ?? self::ymd($split->old_senddate);
                $shifts = array_merge($base['shifts'], [self::ymd($split->new_senddate)]);
            } else {
                $first  = self::ymd(optional($own->first())->old_senddate);
                $shifts = [];
            }

            foreach ($own as $l) {
                $shifts[] = self::ymd($l->new_senddate);
            }

            return ['first' => $first, 'shifts' => array_values(array_filter($shifts))];
        };

        return fn ($row) => $resolve((int) $row->Runno);
    }

    private static function ymd($v): ?string
    {
        return $v ? Carbon::parse($v)->format('Y-m-d') : null;
    }
}
