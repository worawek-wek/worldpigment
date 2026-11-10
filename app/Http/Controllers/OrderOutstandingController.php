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

    /**
     * ชนิดรายงาน (26/10/2569) — ทุกชนิดเป็นรายการที่ยังไม่มีวันที่ลูกค้าได้รับ (DVDate ว่าง)
     *   sale    ตามรหัสผู้ขายที่กรอก
     *   done    เฉพาะผลิตเสร็จ    (EndP มีค่า) — ผู้ขายทุกคน
     *   undone  เฉพาะผลิตไม่เสร็จ (EndP ว่าง)  — ผู้ขายทุกคน
     */
    public const TYPES = [
        'sale'   => '',
        'done'   => '(เฉพาะผลิตเสร็จ)',
        'undone' => '(เฉพาะผลิตไม่เสร็จ)',
    ];

    /**
     * ประเภทใบสั่ง (2 ตัวหน้าเลขที่ใบ) ที่ดึงมาในรายงาน sale / done (27/10/2569 ตามที่ผู้ใช้สั่ง)
     * ⇒ ตัดใบสั่งทำสต๊อก (CM/HM/WM) และใบจอง (CR/HR/WR) ออก · แบบ undone ไม่กรอง (ดึงทุกประเภทเหมือนเดิม)
     * ⚠ ไม่มีผู้เรียกแล้ว (09/11/2569) — ทั้ง 3 แบบเปลี่ยนไปใช้เงื่อนไขตาม query Access เดิม (sale/undone/doneConditions)
     */
    public const ORDER_TYPES_FILTERED = ['HI', 'WI', 'CI', 'HE', 'WE', 'CE'];

    public function pdf(Request $request)
    {
        $type = (string) $request->query('type', 'sale');
        if (!isset(self::TYPES[$type])) {
            $type = 'sale';
        }
        $sale = trim((string) $request->query('sale', ''));
        if ($type === 'sale' && $sale === '') {
            abort(422, 'กรุณาระบุรหัสผู้ขาย');
        }

        $rows = DB::table('suborder as s')
            ->join('morder as m', 'm.Orderno', '=', 's.Orderno')
            ->leftJoin('customer as c', 'c.code', '=', 'm.Custno')
            // แบบ sale = เงื่อนไขตาม query Access เดิมที่ผู้ใช้ส่งมา (09/11/2569) — ดู saleConditions()
            ->when($type === 'sale', fn ($q) => $this->saleConditions($q, $sale))
            // แบบ undone = เงื่อนไขตาม query Access เดิม (09/11/2569) — ดู undoneConditions()
            ->when($type === 'undone', fn ($q) => $this->undoneConditions($q))
            // แบบ done = เงื่อนไขตาม query Access เดิม (09/11/2569) — ดู doneConditions()
            ->when($type === 'done', fn ($q) => $this->doneConditions($q))
            // เรียง supno → custno → senddate → itemno (26/10/2569 ตามที่ผู้ใช้สั่ง) · senddate ว่างไปท้ายกลุ่ม
            ->orderBy('m.supno')
            ->orderBy('m.Custno')
            ->orderByRaw('s.senddate IS NULL, s.senddate')
            ->orderBy('s.Itemno')
            ->orderBy('s.Runno')
            ->get([
                'm.supno', 'm.Orderno', 'm.Mdate', 'm.Custno', 'm.Custname', 'm.PO', 'm.Send', 'm.price',
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

        // Baht = (Stock + Production) × @ — สูตรเดียวกับคอลัมน์ Baht ใน blade
        $stock = fn ($c) => $c->sum(fn ($r) => (float) $r->Stock);
        $prod  = fn ($c) => $c->sum(fn ($r) => (float) $r->Production);
        $bath  = fn ($c) => $c->sum(fn ($r) => ((float) $r->Stock + (float) $r->Production) * (float) $r->price);

        // ลำดับมาจาก SQL แล้ว — groupBy คงลำดับเดิมไว้
        // 1 section = 1 ผู้ขาย (หัวรายงาน + กรอบ # รหัส + ตารางของตัวเอง ต่อกันลงไป) → กลุ่มลูกค้า
        // stock/prod/bath ของ section = แถว "Sale Total" ท้ายผู้ขาย (27/10/2569)
        $sections = $rows
            ->groupBy(fn ($r) => trim((string) $r->supno))
            ->map(fn ($saleRows, $supno) => [
                'sale'   => (string) $supno,
                'stock'  => $stock($saleRows),
                'prod'   => $prod($saleRows),
                'bath'   => $bath($saleRows),
                'groups' => $saleRows
                    ->groupBy(fn ($r) => trim((string) $r->Custno))
                    ->map(fn ($items, $custno) => [
                        'custno' => (string) $custno,
                        'name'   => trim((string) ($items->first()->cust_name ?: $items->first()->Custname)),
                        'items'  => $items->values(),
                        'stock'  => $items->sum(fn ($r) => (float) $r->Stock),
                        'prod'   => $items->sum(fn ($r) => (float) $r->Production),
                    ])
                    ->values(),
            ])
            ->values();

        // ไม่มีข้อมูล → คง 1 section ว่างไว้พิมพ์หัวรายงาน + "ไม่พบ"
        if ($sections->isEmpty()) {
            $sections = collect([['sale' => $type === 'sale' ? $sale : '', 'groups' => collect(), 'stock' => 0, 'prod' => 0, 'bath' => 0]]);
        }

        // ยอดรวมทุก Sale — แถวสุดท้ายของรายงาน (27/10/2569)
        $grand = ['count' => $rows->count(), 'stock' => $stock($rows), 'prod' => $prod($rows), 'bath' => $bath($rows)];

        $html = view('order.outstanding-pdf', [
            'sections' => $sections,
            'grand'    => $grand,
            'subtitle' => self::TYPES[$type],
            'type'     => $type,
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
            'Content-Disposition' => 'inline; filename="outstanding-' . ($type === 'sale' ? preg_replace('/[^A-Za-z0-9_-]/', '', $sale) : $type) . '-' . now()->format('Ymd') . '.pdf"',
        ]);
    }

    /**
     * เงื่อนไขแบบ "ตามรหัสผู้ขาย" — แปลงจาก query Access เดิม (09/11/2569 ตามที่ผู้ใช้สั่ง)
     *   - supno = รหัสที่กรอก · ต้องมีรหัสลูกค้าในตาราง customer (INNER JOIN เดิม)
     *   - senddate > 1/1/2005 (กำหนดส่งว่าง = ไม่ออก) · Stock > 0 หรือ Production > 0
     *   - ตัวที่ 2 ของเลขที่ใบ = I/E → ยังไม่ส่ง (DVDate ว่าง หรือเป็นวันในอนาคต)
     *   - ตัวที่ 2 ของเลขที่ใบ = M/R → ยังผลิตไม่เสร็จ (EndP ว่าง หรือเป็นวันในอนาคต)
     * ⚠ query เดิมเขียน Like "%I*" — ตีความว่า "ตัวที่ 2 = I" (ยังไม่ได้ยืนยัน)
     */
    private function saleConditions($q, string $sale)
    {
        $today = now()->toDateString();

        return $q->where('m.supno', $sale)
            ->whereNotNull('c.code')
            ->where('s.senddate', '>', '2005-01-01')
            ->where(fn ($w) => $w->where('s.Stock', '>', 0)->orWhere('s.Production', '>', 0))
            ->where(fn ($w) => $w
                ->where(fn ($ie) => $ie
                    ->whereIn(DB::raw('UPPER(SUBSTRING(s.Orderno, 2, 1))'), ['I', 'E'])
                    ->where(fn ($d) => $d->whereNull('s.DVDate')->orWhere('s.DVDate', '>', $today)))
                ->orWhere(fn ($mr) => $mr
                    ->whereIn(DB::raw('UPPER(SUBSTRING(s.Orderno, 2, 1))'), ['M', 'R'])
                    ->where(fn ($d) => $d->whereNull('s.EndP')->orWhere('s.EndP', '>', $today))));
    }

    /**
     * เงื่อนไขแบบ "เฉพาะผลิตไม่เสร็จ" (ทุก Sale) — แปลงจาก query Access เดิม (09/11/2569 ตามที่ผู้ใช้สั่ง)
     *   - ต้องมีรหัสลูกค้าในตาราง customer · senddate > 1/1/2005 · Production > 0 · EndP ว่าง
     *   - ยังไม่ส่ง: DVDate ว่าง หรือเป็นวันในอนาคต
     *   - ตัวที่ 2 ของเลขที่ใบ = I / E / M / R
     * ⚠ query เดิม GROUP BY แล้วกรอง Sum(Production) > 0 — ที่นี่กรองทีละแถว (ต่างกันเฉพาะแถวที่ซ้ำกันทุกคีย์ ซึ่งแทบไม่มี)
     */
    private function undoneConditions($q)
    {
        $today = now()->toDateString();

        return $q->whereNotNull('c.code')
            ->where('s.senddate', '>', '2005-01-01')
            ->where('s.Production', '>', 0)
            ->whereNull('s.EndP')
            ->where(fn ($d) => $d->whereNull('s.DVDate')->orWhere('s.DVDate', '>', $today))
            ->whereIn(DB::raw('UPPER(SUBSTRING(s.Orderno, 2, 1))'), ['I', 'E', 'M', 'R']);
    }

    /**
     * เงื่อนไขแบบ "เฉพาะผลิตเสร็จ" (ทุก Sale) — แปลงจาก query Access เดิม (09/11/2569 ตามที่ผู้ใช้สั่ง)
     *   - ต้องมีรหัสลูกค้าในตาราง customer · senddate > 1/1/2005 · Stock > 0 หรือ Production > 0
     *   - ผลิตเสร็จแล้ว (EndP มีค่า) · ยังไม่ส่ง: DVDate ว่าง หรือเป็นวันในอนาคต
     *   - ตัวที่ 2 ของเลขที่ใบ = I / E
     */
    private function doneConditions($q)
    {
        $today = now()->toDateString();

        return $q->whereNotNull('c.code')
            ->where('s.senddate', '>', '2005-01-01')
            ->where(fn ($w) => $w->where('s.Stock', '>', 0)->orWhere('s.Production', '>', 0))
            ->whereNotNull('s.EndP')
            ->where(fn ($d) => $d->whereNull('s.DVDate')->orWhere('s.DVDate', '>', $today))
            ->whereIn(DB::raw('UPPER(SUBSTRING(s.Orderno, 2, 1))'), ['I', 'E']);
    }

    /**
     * คืนฟังก์ชันหาประวัติกำหนดส่งของแต่ละแถว → ['first' => Y-m-d|null, 'shifts' => [Y-m-d, ...]]
     * อ่าน tb_order_change_line ของใบในรายงานครั้งเดียว
     * public — รายงาน Order Daily (price) ใช้หา "กำหนดส่งแรก" (02/11/2569)
     */
    public function dueHistory(array $ordernos): \Closure
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
