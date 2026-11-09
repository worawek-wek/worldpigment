<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

/**
 * ฟอร์ม "ใบทบทวนคำสั่งซื้อ" (03/11/2569) — แปลงจากฟอร์ม Access "OrderPRT"
 * เลือกวันที่ Order + แผนกที่ผลิต → เดินดูทีละรายการ แล้วพิมพ์ใบทบทวนฯ
 *
 * เงื่อนไข: เฉพาะใบที่อนุมัติแล้ว (morder.appv ไม่ใช่ NULL/0 — ตามข้อความบนฟอร์มเดิม
 *           "เพื่อเลือกพิมพ์ใบทบทวนฯ เฉพาะที่อนุมัติแล้ว") และ morder.Mdate ตรงกับวันที่เลือก
 * 1 ระเบียน = 1 รายการ (suborder) เรียงเลขที่ใบ → Runno
 */
class OrderReviewController extends Controller
{
    public function data(Request $request)
    {
        $date = self::parseDate((string) $request->query('date', '')) ?? now()->startOfDay();
        $dept = trim((string) $request->query('dept', ''));   // ว่าง = ทุกแผนก

        $rows = DB::table('suborder as s')
            ->join('morder as m', 'm.Orderno', '=', 's.Orderno')
            ->leftJoin('customer as c', 'c.code', '=', 'm.Custno')
            ->whereBetween('m.Mdate', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->whereNotNull('m.appv')
            ->where('m.appv', '<>', 0)
            ->when($dept !== '', fn ($q) => $q->where('m.Company', $dept))
            ->orderBy('m.Orderno')
            ->orderBy('s.Runno')
            ->get([
                'm.Orderno', 'm.Mdate', 'm.Company', 'm.Custno', 'm.Custname', 'm.Emp', 'm.PO', 'm.supno',
                'm.Send', 'm.RP', 'm.Spec', 'm.Cer', 'm.price',
                's.Runno', 's.Itemno', 's.Lotno', 's.Stock', 's.Production', 's.senddate', 's.Remark',
                'c.name as cust_name',
            ])
            ->map(fn ($r) => [
                'orderno'    => trim((string) $r->Orderno),
                'runno'      => $r->Runno,
                'mdate'      => $r->Mdate ? Carbon::parse($r->Mdate)->format('d/m/y H:i') : '',
                'company'    => trim((string) $r->Company),
                'custno'     => trim((string) $r->Custno),
                'custname'   => trim((string) ($r->cust_name ?: $r->Custname)),
                'emp'        => trim((string) $r->Emp),
                'po'         => trim((string) $r->PO),
                'sale'       => trim((string) $r->supno),
                'send'       => self::checked($r->Send),
                'rp'         => self::checked($r->RP),
                'spec'       => self::checked($r->Spec),
                'cer'        => self::checked($r->Cer),
                'itemno'     => trim((string) $r->Itemno),
                'lotno'      => trim((string) $r->Lotno),
                'stock'      => (float) $r->Stock,
                'production' => (float) $r->Production,
                'price'      => $r->price === null ? null : (float) $r->price,
                'senddate'   => $r->senddate ? Carbon::parse($r->senddate)->format('d/m/y') : '',
                'remark'     => (string) $r->Remark,
            ])
            ->values();

        return response()->json(['date' => $date->format('d/m/Y'), 'dept' => $dept, 'rows' => $rows]);
    }

    /**
     * ชื่อบริษัทที่พิมพ์ในช่อง "ชื่อลูกค้า" + หน้าข้อความ "ขอรับรองรายการ..." ตามตัวอักษรแรกของเลขที่ใบ
     * ⚠ รู้แค่ C จากใบตัวอย่าง CI43493 · ใบ WI25290 ไม่มีชื่อ · H ยังไม่รู้ (ว่าง)
     */
    private const SELLER_NAMES = [
        'C' => 'บริษัท เวิลดีปิกเม้นท์เคมีภัณฑ์ จำกัด',
    ];

    /** blade ผังพิมพ์ใบทบทวนฯ ตาม morder.Company — ไม่อยู่ในรายการ = order.order-review-print (ผังเดิม) */
    private const PRINT_VIEWS = [
        'CP' => 'order.order-review-print-cp',
        'DB' => 'order.order-review-print-db',   // ตำแหน่งเท่าผัง CP เป๊ะ ๆ (09/11/2569 ตามที่ผู้ใช้สั่ง)
        'MB' => 'order.order-review-print-db',   // MB ใช้ผังเดียวกับ DB (09/11/2569 ตามที่ผู้ใช้สั่ง)
    ];

    /**
     * พิมพ์ใบทบทวนคำสั่งซื้อ 1 รายการ (suborder) ลงกระดาษแบบฟอร์ม MK01-FM01.00 — หน้า A4 แนวตั้ง ฟอร์มอยู่ครึ่งบน
     * พิมพ์เฉพาะข้อมูลทับช่องของฟอร์ม — ตำแหน่ง (มม.) อยู่ใน blade order/order-review-print
     */
    public function print(Request $request)
    {
        $orderno = trim((string) $request->query('orderno', ''));
        $runno   = $request->query('runno');

        $r = DB::table('suborder as s')
            ->join('morder as m', 'm.Orderno', '=', 's.Orderno')
            ->leftJoin('customer as c', 'c.code', '=', 'm.Custno')
            ->leftJoin('emp as e', 'e.empno', '=', 'm.Emp')
            ->where('m.Orderno', $orderno)
            ->where('s.Runno', $runno)
            ->first([
                'm.Orderno', 'm.Mdate', 'm.Company', 'm.Custno', 'm.Custname', 'm.Emp', 'm.PO', 'm.supno', 'm.DVpoint',
                'm.Send', 'm.RP', 'm.Spec', 'm.Cer', 'm.price', 'm.appvDT', 'm.itype',
                'm.HMStore', 'm.sendmth', 'm.sendend',
                's.Itemno', 's.nold', 's.prodname', 's.Lotno', 's.Stock', 's.Production', 's.senddate', 's.Remark',
                'c.name as cust_name', 'c.term', 'c.cashdisc',
                'e.empname',
            ]);
        abort_if(!$r, 404, 'ไม่พบรายการ');

        // "ระบุในบิล" = uprice.inBill ของคู่ ลูกค้า+เบอร์ แถว DATE ล่าสุด (⚠ เดาจากชื่อช่อง)
        $inBill = DB::table('uprice')
            ->where('CustNo', $r->Custno)->where('ITEMNO', $r->Itemno)
            ->orderByDesc('DATE')->value('inBill');

        // itype → ป้ายจาก config ตัดวงเล็บท้ายออก (เช่น "เม็ดพลาสติกสี ( เม็ด WPI )" → "เม็ดพลาสติกสี")
        $itype = collect(config('order.itypes'))->firstWhere('key', (string) $r->itype);
        $itypeLabel = $itype ? trim(preg_replace('/\s*\(.*\)\s*$/u', '', $itype['label'])) : '';

        $orderno = trim((string) $r->Orderno);
        $stock   = (float) $r->Stock;
        $prod    = (float) $r->Production;
        $term    = trim((string) $r->term);
        $mdate   = $r->Mdate ? Carbon::parse($r->Mdate) : null;

        $d = [
            'orderno'   => $orderno,
            'company'   => strtoupper(trim((string) $r->Company)),   // ให้ blade ที่ใช้ร่วมหลายแผนก (DB/MB) ปรับเฉพาะแผนกได้
            'day'     => $mdate ? $mdate->format('d') : '',
            'month'     => $mdate ? $mdate->format('m') : '',
            'year'      => $mdate ? $mdate->format('y') : '',
            'in_bill'   => trim((string) $inBill) !== '' ? trim((string) $inBill) : '-',
            'itype'     => $itypeLabel,
            'seller'    => self::SELLER_NAMES[strtoupper(substr($orderno, 0, 1))] ?? '',
            // ตามคำสั่งลูกค้าเพื่อ: รหัสลูกค้า-รหัสผู้ขาย-รหัส O/N + ชื่อลูกค้า (ผู้ใช้ยืนยัน 03/11/2569)
            'for_code'  => implode('-', array_filter([trim((string) $r->Custno), trim((string) $r->supno), trim((string) $r->nold)], 'strlen')),
            'for_name'  => trim((string) ($r->cust_name ?: $r->Custname)),
            'itemno'    => trim((string) $r->Itemno),
            'nold'      => trim((string) $r->nold),
            'prodname'  => trim((string) $r->prodname),
            'lotno'     => trim((string) $r->Lotno),
            'qty'       => number_format($stock + $prod, 2),
            'stock'     => self::qtyText($stock),
            'prod'      => self::qtyText($prod),
            'price'     => $r->price === null ? '' : number_format((float) $r->price, 2),
            'term'      => $term === '' ? '' : '( ' . $term . ($r->cashdisc !== null ? ' - ' . (float) $r->cashdisc . '%' : '') . ' )',
            'dvpoint'   => trim((string) $r->DVpoint),
            'po'        => trim((string) $r->PO),
            'remark'    => trim((string) $r->Remark),
            'hmstore'   => (float) $r->HMStore ? number_format((float) $r->HMStore, 2) : '-',
            'sendmth'   => (float) $r->sendmth ? number_format((float) $r->sendmth, 2) : '-',
            'sendend'   => $r->sendend ? Carbon::parse($r->sendend)->format('d/m/y') : '-',
            'send'      => self::checked($r->Send),
            'rp'        => self::checked($r->RP),
            'spec'      => self::checked($r->Spec),
            'cer'       => self::checked($r->Cer),
            'senddate'  => $r->senddate ? Carbon::parse($r->senddate)->format('d / m / y') : '',
            'taker'     => trim(trim((string) $r->Emp) . ' ' . trim((string) $r->empname)),
            'take_time' => $mdate ? $mdate->format('H:i') : '',
            // ⚠ ชื่อผู้อนุมัติ — morder ยังไม่ได้เก็บว่าใครอนุมัติ (ผู้ใช้กำลังสืบ) จึงเว้นว่าง
            'approver'  => '',
            'appv_time' => $r->appvDT ? Carbon::parse($r->appvDT)->format('H:i') : '',
            'sign'      => trim((string) $r->supno),   // ลงชื่อ = รหัสผู้ขาย (ผู้ใช้ยืนยัน 03/11/2569)
        ];

        // ผังพิมพ์แยกตามแผนก (03/11/2569) — ทำแล้ว: CP · แผนกอื่นยังใช้ผังเดิม (order-review-print)
        $view = self::PRINT_VIEWS[strtoupper(trim((string) $r->Company))] ?? 'order.order-review-print';
        $html = view($view, ['d' => $d])->render();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            // A4 แนวตั้ง — แบบฟอร์ม (210 × 148 มม. = A5 แนวนอน) อยู่ครึ่งบนของแผ่น เพราะตอนสั่งพิมพ์ผู้ใช้เลือกกระดาษ A4 (ผู้ใช้ยืนยัน 03/11/2569)
            'format'        => 'A4',
            'margin_left'   => 0,
            'margin_right'  => 0,
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="order-review-' . $orderno . '.pdf"',
        ]);
    }

    /** น้ำหนัก S / P บนใบ: 0 = ว่าง · จำนวนเต็มไม่มีทศนิยม (ตามใบตัวอย่าง "3", "500") */
    private static function qtyText(float $v): string
    {
        if ($v == 0) {
            return '';
        }

        return floor($v) == $v ? number_format($v) : rtrim(rtrim(number_format($v, 2), '0'), '.');
    }

    /** checkbox แบบ Access: -1 = ติ๊ก, 0/NULL = ไม่ติ๊ก */
    private static function checked($v): bool
    {
        return $v !== null && (int) $v !== 0;
    }

    /** 'd/m/Y' (flatpickr) หรือ 'Y-m-d' → Carbon | null */
    private static function parseDate(string $v): ?Carbon
    {
        $v = trim($v);
        foreach (['d/m/Y', 'Y-m-d'] as $format) {
            $d = \DateTime::createFromFormat('!' . $format, $v);
            if ($d && $d->format($format) === $v) {
                return Carbon::instance($d)->startOfDay();
            }
        }

        return null;
    }
}
