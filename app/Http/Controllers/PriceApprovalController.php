<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\HolidayService;
use App\Services\ProductPriceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mpdf\Mpdf;

/**
 * ขออนุมัติราคาพิเศษ (MD) — แปลงมาจากฟอร์ม Access "MK ขออนุมัติราคาพิเศษ"
 * เป็นฟอร์มลูกของเมนู O-Order (ดู OrderController)
 *
 * ตารางที่ใช้ (legacy ทั้งหมด อ่านอย่างเดียวในเฟสนี้):
 *   appvreq       ใบขออนุมัติราคา 1 แถว = 1 ครั้งที่ขอ (PK = ReqDate + custno + itemno)
 *                 price1/2/3 = ราคาตามกลุ่มปริมาณสั่ง A/B/C, price = ราคาขายครั้งนี้, Appv = อนุมัติแล้ว
 *   zcustprice    ราคาที่ยืนไว้กับลูกค้า (PK = custno + colorno) → ตารางล่างของฟอร์ม
 *   uprice        ราคาที่ตกลงไว้ล่าสุด (ใช้เป็นรายการเบอร์สินค้าของลูกค้ารายนั้น)
 *   uprice        ราคาที่ตกลงไว้ล่าสุด + ราคาที่ตั้งในเมนู "กำหนดราคา" (ย้ายมาเขียนตารางนี้ 29/08/2569)
 *   cp_itemprice  ประวัติราคาเม็ด CP รายใบสั่ง (ปุ่ม "ประวัติ ราคาเม็ด CP")
 *   customer      ชื่อลูกค้า + รหัสพนักงานขาย (เลข "# 15" ข้างรหัสลูกค้าบนฟอร์ม)
 */
class PriceApprovalController extends Controller
{
    /**
     * กลุ่มปริมาณสั่งซื้อ → ช่องราคาใน appvreq
     * ตามคำอธิบายบนฟอร์ม: กลุ่ม A = 1,000 kg ขึ้นไป / B = 500 kg ขึ้นไป / C = ต่ำกว่า 500 kg
     */
    private const PRICE_GROUPS = [
        ['key' => 'price1', 'group' => 'A', 'label' => 'กลุ่ม A = 1,000 kg. up', 'min' => 1000],
        ['key' => 'price2', 'group' => 'B', 'label' => 'กลุ่ม B = 500 kg. up',   'min' => 500],
        ['key' => 'price3', 'group' => 'C', 'label' => 'กลุ่ม C = under 500 kg.', 'min' => 0],
    ];

    /** session ที่บอกว่า "โหมดอนุมัติ (MD)" ถูกปลดล็อกไว้ถึงเมื่อไหร่ */
    private const MD_SESSION_KEY = 'price_approval_md_until';

    /** โหมดอนุมัติอยู่ได้กี่นาทีหลังปลดล็อก — หมดอายุแล้วต้องกรอกรหัสใหม่ */
    private const MD_UNLOCK_MINUTES = 30;

    /** คอลัมน์ checkbox ของ Access เก็บ -1 = ติ๊ก */
    private static function checked($value): bool
    {
        return (int) $value !== 0 && $value !== null;
    }

    /** กลุ่มราคาที่ตรงกับปริมาณสั่งซื้อ (คืน null ถ้าไม่ได้ระบุปริมาณ) */
    public static function groupOf($weight): ?array
    {
        if ($weight === null || $weight === '' || !is_numeric($weight)) {
            return null;
        }

        foreach (self::PRICE_GROUPS as $g) {
            if ((float) $weight >= $g['min']) {
                return $g;
            }
        }

        return null;
    }

    /**
     * GET — รายการเบอร์สินค้าของลูกค้ารายนี้ (dropdown "รหัสสินค้า")
     *   ?custno=29231
     */
    public function items(Request $request)
    {
        $custno = trim((string) $request->query('custno', ''));
        if ($custno === '') {
            return response()->json(['items' => []]);
        }

        // รวมเบอร์จาก 2 ที่ — บางเบอร์มีอยู่ที่เดียว ถ้าดึงที่เดียวจะหาย
        //   1) uprice      ราคาที่ตกลงไว้ล่าสุด — **เมนู "กำหนดราคา" (/saleinfo) เขียนลงตารางนี้แล้ว**
        //                  (29/08/2569 ย้ายจาก tb_saleinfo) ⇒ เบอร์ที่เพิ่งตั้งราคาโผล่ที่นี่เลย
        //   2) zcustprice  ราคาที่ยืนไว้กับลูกค้า (เขียนตอนอนุมัติในฟอร์มนี้เอง)
        //
        // เดิม (25/08/2569) union `tb_saleinfo` เข้ามาเป็นที่ที่ 3 เพราะตอนนั้น /saleinfo
        // เขียนลงตารางแยก — ถอดออกแล้วเพราะซ้ำกับ uprice และเหลือแต่ข้อมูลทดสอบ
        $items = DB::table('uprice')
            ->where('CustNo', $custno)
            ->whereRaw("TRIM(COALESCE(ITEMNO, '')) <> ''")
            ->selectRaw('TRIM(ITEMNO) as itemno')
            ->union(
                DB::table('zcustprice')
                    ->where('custno', $custno)
                    ->whereRaw("TRIM(COALESCE(colorno, '')) <> ''")
                    ->selectRaw('TRIM(colorno) as itemno')
            )
            ->pluck('itemno')
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return response()->json(['items' => $items]);
    }

    /**
     * GET — ใบขอราคาที่ยังไม่อนุมัติทั้งหมด (12/09/2569)
     *
     * ใช้กับตัวเดินระเบียน (ลูกศรซ้าย/ขวา) บนหัวฟอร์ม — เปิดฟอร์มมาแล้วไล่ดูใบที่รออนุมัติได้เลย
     * โดยไม่ต้องรู้ว่าต้องกรอกลูกค้า/เบอร์ไหน
     *
     * ยังไม่อนุมัติ = `Appv` เป็น NULL หรือ 0 (Access เก็บ -1 = ติ๊กอนุมัติแล้ว)
     */
    public function pending()
    {
        $rows = DB::table('appvreq')
            ->leftJoin('customer as c', 'appvreq.custno', '=', 'c.code')
            ->where(function ($q) {
                $q->whereNull('appvreq.Appv')->orWhere('appvreq.Appv', 0);
            })
            ->orderByDesc('appvreq.ReqDate')
            ->get([
                'appvreq.ReqDate', 'appvreq.custno', 'appvreq.itemno',
                'appvreq.price', 'appvreq.weight',
                DB::raw('c.name as custname'),
            ]);

        return response()->json([
            'count' => $rows->count(),
            'rows'  => $rows,
        ]);
    }

    /**
     * GET — ข้อมูลทั้งฟอร์มของคู่ (ลูกค้า, เบอร์สินค้า)
     *   ?custno=29231&itemno=213E456
     */
    public function data(Request $request)
    {
        $custno = trim((string) $request->query('custno', ''));
        $itemno = trim((string) $request->query('itemno', ''));

        $cust = $custno === '' ? null : DB::table('customer')->where('code', $custno)->first();
        if (!$cust) {
            return response()->json(['found' => false]);
        }

        // ใบขออนุมัติล่าสุดของคู่นี้ (ฟอร์มเปิดมาโชว์ใบล่าสุดเสมอ)
        $req = $itemno === '' ? null : DB::table('appvreq')
            ->where('custno', $custno)
            ->where('itemno', $itemno)
            ->orderByDesc('ReqDate')
            ->first();

        // ราคาที่ตกลงไว้ล่าสุด (ใช้เติมหมายเหตุ/ราคาเมื่อยังไม่เคยมีใบขออนุมัติ)
        $uprice = $itemno === '' ? null : DB::table('uprice')
            ->where('CustNo', $custno)
            ->where('ITEMNO', $itemno)
            ->orderByDesc('DATE')
            ->first(['PRICE', 'DATE', 'REM1', 'REM2']);

        $weight = $req->weight ?? null;
        $group  = self::groupOf($weight);

        return response()->json([
            'found'    => true,
            'customer' => [
                'code' => $cust->code,
                'name' => $cust->name,
                'sale' => $cust->sale,      // เลข "# 15" ข้างรหัสลูกค้า
                'term' => $cust->term,
                'type' => $cust->type,
            ],
            'request'  => $req ? [
                'ReqDate' => $req->ReqDate,
                'itemno'  => $req->itemno,
                'weight'  => $req->weight,
                'price'   => $req->price,
                'price1'  => $req->price1,
                'price2'  => $req->price2,
                'price3'  => $req->price3,
                'remark'  => $req->remark,
                'costup'  => self::checked($req->costup ?? null),
                'Appv'    => self::checked($req->Appv),
            ] : null,
            // ราคาที่ระบบกำหนดราคาคำนวณได้จากรหัสสินค้า — ไว้เทียบ/เติมให้ตอนขึ้นใบใหม่
            'calc'     => $itemno === '' ? null : app(ProductPriceService::class)->lookup($itemno),
            // กลุ่มราคาที่ตรงกับปริมาณสั่งซื้อในใบนี้ (ใช้เน้นช่องราคาที่เกี่ยวข้อง)
            'group'    => $group,
            'groups'   => self::PRICE_GROUPS,
            'uprice'   => $uprice,
            // ตารางล่าง — ราคาที่ยืนไว้ของเบอร์ที่เลือก
            'rows'     => $this->zcustRows($custno, $itemno),
            // ยังอยู่ใน "โหมดอนุมัติ" ไหม — ให้ฟอร์มล็อก/ปลดล็อกช่องอนุมัติตามจริงทุกครั้งที่โหลด
            'md_unlocked' => $this->mdUnlocked(),
            // ค่าเริ่มต้นของ "อนุมัติราคาถึง" = วันทำการถัดไป (ข้ามวันหยุด)
            // คำนวณที่ server ทุกครั้ง เพื่อให้ฟอร์มที่เปิดค้างข้ามวัน/ข้ามวันหยุดยังได้ค่าที่ถูก
            'default_valid_to' => self::defaultValidTo(),
        ]);
    }

    /**
     * GET — ปุ่ม "ตรวจสอบ เบอร์อื่น ..." → ราคาที่ยืนไว้ทุกเบอร์ของลูกค้ารายนี้
     *   ?custno=29231
     */
    public function otherItems(Request $request)
    {
        $custno = trim((string) $request->query('custno', ''));

        return response()->json([
            'title' => 'ราคาที่ยืนไว้ — ทุกเบอร์ของลูกค้า ' . $custno,
            'rows'  => $this->zcustRows($custno, null),
        ]);
    }

    /**
     * GET — ปุ่ม "ตรวจสอบเฉพาะร้าน ..." → ลูกค้ารายอื่นที่ใช้เบอร์นี้ (เทียบราคาข้ามลูกค้า)
     *   ?itemno=213E456
     */
    public function otherCustomers(Request $request)
    {
        $itemno = trim((string) $request->query('itemno', ''));
        if ($itemno === '') {
            return response()->json(['title' => '', 'rows' => []]);
        }

        $rows = DB::table('zcustprice as z')
            ->leftJoin('customer as c', 'z.custno', '=', 'c.code')
            ->where('z.colorno', $itemno)
            ->orderByDesc('z.enddate')
            ->get(['z.custno', 'c.name as custname', 'z.exprice', 'z.enddate', 'z.remark']);

        return response()->json([
            'title' => 'ลูกค้าที่ใช้เบอร์ ' . $itemno,
            'rows'  => $rows,
        ]);
    }

    /**
     * GET — ปุ่ม "ประวัติของเบอร์นี้" → ใบขออนุมัติราคาทุกครั้งของคู่ (ลูกค้า, เบอร์)
     *   ?custno=29231&itemno=213E456
     */
    public function history(Request $request)
    {
        $custno = trim((string) $request->query('custno', ''));
        $itemno = trim((string) $request->query('itemno', ''));
        if ($custno === '' || $itemno === '') {
            return response()->json(['title' => '', 'rows' => []]);
        }

        $rows = DB::table('appvreq')
            ->where('custno', $custno)
            ->where('itemno', $itemno)
            ->orderByDesc('ReqDate')
            ->get(['ReqDate', 'weight', 'price', 'price1', 'price2', 'price3', 'remark', 'Appv'])
            ->map(function ($r) {
                $r->Appv = self::checked($r->Appv);
                $r->group = optional(self::groupOf($r->weight))['group'];
                return $r;
            });

        return response()->json([
            'title' => 'ประวัติการขออนุมัติราคา — ' . $itemno . ' (ลูกค้า ' . $custno . ')',
            'rows'  => $rows,
        ]);
    }

    /**
     * GET — ปุ่มพิมพ์ประวัติของเบอร์นี้เป็น PDF (12/09/2569)
     *   ?custno=41008&itemno=CP8F247B
     *
     * ผังตามรายงานกระดาษของระบบเดิม — ข้อมูลชุดเดียวกับ history() (คู่ ลูกค้า+เบอร์ ที่เลือกอยู่)
     * เรียงใบล่าสุด → เก่าสุด ("เรียงจากปัจจุบัน ==> อดีต" ตามหัวรายงาน)
     */
    public function historyPdf(Request $request)
    {
        $custno = trim((string) $request->query('custno', ''));
        $itemno = trim((string) $request->query('itemno', ''));

        $rows = ($custno === '' || $itemno === '') ? collect() : DB::table('appvreq')
            ->where('custno', $custno)
            ->where('itemno', $itemno)
            ->orderByDesc('ReqDate')
            ->get(['ReqDate', 'custno', 'itemno', 'weight', 'price', 'price1', 'price2', 'price3', 'remark', 'Appv'])
            ->map(function ($r) {
                $r->Appv = self::checked($r->Appv);

                return $r;
            });

        $html = view('order.price-approval-history-pdf', [
            'custno' => $custno,
            'itemno' => $itemno,
            'rows'   => $rows,
        ])->render();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',   // แนวตั้งตามที่ผู้ใช้สั่ง (12/09/2569 — ให้ตรงกับปุ่ม "ตรวจสอบ เบอร์อื่น ...")
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="price-approval-history.pdf"',
        ]);
    }

    /**
     * GET — ปุ่ม "ตรวจสอบ เบอร์อื่น ..." → รายงาน PDF (12/09/2569)
     *   ?itemno=CP8F247B
     *
     * **เฉพาะเบอร์ที่กรอก แต่ของลูกค้าทุกคน ทั้งใบที่อนุมัติแล้วและยังไม่อนุมัติ**
     * ต่างจาก historyPdf() ที่กรองคู่ (ลูกค้า, เบอร์) ของใบที่เปิดอยู่ — ตรงนี้ไม่กรอง custno
     * ผังรายงานใช้ blade ตัวเดียวกับ "ประวัติของเบอร์นี้" (มีคอลัมน์รหัสลูกค้าอยู่แล้ว)
     */
    public function otherItemsPdf(Request $request)
    {
        $itemno = trim((string) $request->query('itemno', ''));

        $rows = $itemno === '' ? collect() : DB::table('appvreq')
            ->where('itemno', $itemno)
            ->orderByDesc('ReqDate')
            ->orderBy('custno')
            ->get(['ReqDate', 'custno', 'itemno', 'weight', 'price', 'price1', 'price2', 'price3', 'remark', 'Appv'])
            ->map(function ($r) {
                $r->Appv = self::checked($r->Appv);

                return $r;
            });

        $html = view('order.price-approval-history-pdf', [
            'custno'  => '',
            'itemno'  => $itemno,
            'rows'    => $rows,
        ])->render();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',   // แนวตั้งตามที่ผู้ใช้สั่ง (12/09/2569)
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="price-approval-item-history.pdf"',
        ]);
    }

    /**
     * GET — ปุ่ม "ตรวจสอบเฉพาะร้าน ..." → รายงาน PDF (12/09/2569)
     *   ?custno=30034
     *
     * **เฉพาะลูกค้ารายนี้ แต่ทุกเบอร์ ทั้งใบที่อนุมัติแล้วและยังไม่อนุมัติ**
     * = คู่ตรงข้ามของ otherItemsPdf() (เบอร์เดียว ทุกลูกค้า) — ใช้ผัง blade ตัวเดียวกัน
     */
    public function customerHistoryPdf(Request $request)
    {
        $custno = trim((string) $request->query('custno', ''));

        $rows = $custno === '' ? collect() : DB::table('appvreq')
            ->where('custno', $custno)
            ->orderByDesc('ReqDate')
            ->orderBy('itemno')
            ->get(['ReqDate', 'custno', 'itemno', 'weight', 'price', 'price1', 'price2', 'price3', 'remark', 'Appv'])
            ->map(function ($r) {
                $r->Appv = self::checked($r->Appv);

                return $r;
            });

        $html = view('order.price-approval-history-pdf', [
            'custno'      => $custno,
            'itemno'      => '',
            // หัวรายงานใบนี้กรองด้วยรหัสลูกค้า จึงโชว์รหัสลูกค้าแทนรหัสสินค้า
            'doc_subject' => $custno,
            'rows'        => $rows,
        ])->render();

        // 🔴 ลูกค้ารายใหญ่มีประวัติหลักพันแถว (สูงสุดตอนนี้ 1,896 แถว = HTML ~1.4 MB)
        //    mPDF จะโยน MpdfException ทันทีถ้า HTML ยาวเกิน pcre.backtrack_limit (ค่า default 1,000,000)
        //    จึงต้องขยายก่อนเรียก WriteHTML — รายงานอีก 2 ใบไม่เจอเพราะแถวน้อยกว่ามาก
        if ((int) ini_get('pcre.backtrack_limit') < 10000000) {
            ini_set('pcre.backtrack_limit', '10000000');
        }

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',   // แนวตั้ง ให้ตรงกับรายงานอีก 2 ใบที่ใช้ผังเดียวกัน
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="price-approval-customer-history.pdf"',
        ]);
    }

    /**
     * GET — ปุ่ม "พิมพ์" ท้ายฟอร์ม → รายการที่ **อนุมัติราคาแล้วใน 3 วันล่าสุด** (12/09/2569)
     *
     * ผังตามกระดาษของระบบเดิม (1 ระเบียน = 1 บล็อก) ดู blade order/price-approval-approved-pdf
     *
     * ⚠ "3 วันล่าสุด" = **3 วันปฏิทินย้อนหลัง** (วันนี้ + 2 วันก่อนหน้า) ตามที่ผู้ใช้เลือก
     *   ⇒ ช่วงไหนไม่มีใครอนุมัติ รายงานจะว่าง (ถูกต้องตามนิยาม)
     * ⚠ "เวลาอนุมัติ" ใช้ค่าเดียวกับ "วันที่ขอ" (`ReqDate`) เพราะ **`appvreq` ไม่มีคอลัมน์เวลาที่กดอนุมัติ**
     *   (ผู้ใช้เลือกแนวทางนี้ 12/09/2569 แทนการเพิ่มคอลัมน์ appvDT) ⇒ การกรอง 3 วันก็นับจาก ReqDate
     */
    public const PRINT_RECENT_DAYS = 3;

    public function approvedRecentPdf(Request $request)
    {
        $since = now()->startOfDay()->subDays(self::PRINT_RECENT_DAYS - 1);

        $rows = DB::table('appvreq as a')
            ->leftJoin('customer as c', 'a.custno', '=', 'c.code')
            ->where('a.Appv', '<>', 0)
            ->whereNotNull('a.Appv')          // Access เก็บ -1 = อนุมัติแล้ว
            ->where('a.ReqDate', '>=', $since)
            ->orderByDesc('a.ReqDate')
            ->get([
                'a.ReqDate', 'a.custno', 'a.itemno', 'a.weight', 'a.price', 'a.remark',
                'c.name as custname',
            ])
            ->map(function ($r) {
                // ไม่มีคอลัมน์เวลาอนุมัติจริง — ใช้วันที่ขอไปก่อน (ดูหมายเหตุหัวเมธอด)
                $r->appv_at = $r->ReqDate;

                return $r;
            });

        $html = view('order.price-approval-approved-pdf', [
            'rows'  => $rows,
            'since' => $since,
        ])->render();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => 12,
            'margin_right'  => 12,
            'margin_top'    => 12,
            'margin_bottom' => 12,
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="price-approval-approved.pdf"',
        ]);
    }

    /**
     * GET — ปุ่ม "ประวัติ ราคาเม็ด CP" → ราคาเม็ด/ค่าแรงรายใบสั่งของเบอร์นี้
     *   ?itemno=CP8E152B
     */
    public function resinHistory(Request $request)
    {
        $itemno = trim((string) $request->query('itemno', ''));
        if ($itemno === '') {
            return response()->json(['title' => '', 'rows' => []]);
        }

        $rows = DB::table('cp_itemprice')
            ->where('itemno', $itemno)
            ->orderByDesc('Qdate')
            ->limit(50)
            ->get(['Orderno', 'Qdate', 'OrderPrice', 'wage', 'Resin1Code', 'Resin1Price', 'Resin1Per', 'Diff', 'status']);

        return response()->json([
            'title' => 'ประวัติราคาเม็ด CP — ' . $itemno,
            'rows'  => $rows,
        ]);
    }

    /**
     * GET — ปุ่ม "ประวัติ ราคาเม็ด CP" → รายงาน PDF (12/09/2569)
     *   ?itemno=CP8F247B
     *
     * **เฉพาะเบอร์นี้ ของลูกค้าทุกคน** — `cp_itemprice` ผูกกับเลขที่ใบสั่ง ไม่มีคอลัมน์ลูกค้า
     * จึงไม่ต้องกรอง custno · พิมพ์ทุกแถวของเบอร์นั้น (ต่างจากตารางบนจอเดิมที่จำกัด 50 แถว)
     *
     * ผังรายงาน = 1 ระเบียน 1 บล็อก (ป้าย: ค่า) ตามกระดาษเดิม — ต้องใช้ `Mdate` กับ `Custno`
     * ซึ่ง **ไม่มีใน `cp_itemprice`** จึง `leftJoin morder` ด้วยเลขที่ใบสั่ง
     * ⚠ ใบเก่าหลายใบไม่มีอยู่ใน `morder` (247 จาก 482 แถว) → 2 ช่องนั้นเว้นว่าง และการเรียง
     *   ต้องถอยไปใช้ `Qdate` ไม่งั้นแถวที่ไม่มีใบจะหล่นไปท้ายสุดผิดลำดับเวลา
     */
    public function resinHistoryPdf(Request $request)
    {
        $itemno = trim((string) $request->query('itemno', ''));

        $rows = $itemno === '' ? collect() : DB::table('cp_itemprice as c')
            ->leftJoin('morder as m', 'm.Orderno', '=', 'c.Orderno')
            ->where('c.itemno', $itemno)
            ->orderByRaw('COALESCE(m.Mdate, c.Qdate) DESC')
            ->orderByDesc('c.Orderno')
            ->get([
                'c.Orderno', 'c.itemno', 'c.OrderPrice', 'c.Qdate', 'c.wage', 'c.ResinFrom',
                'c.Resin1Code', 'c.Resin1Price', 'c.Resin1Per',
                'c.Resin2Code', 'c.Resin2Price', 'c.Resin2Per',
                'c.wageCal', 'c.Diff', 'c.status',
                'm.Mdate', 'm.Custno',
            ]);

        $html = view('order.price-approval-resin-history-pdf', [
            'itemno' => $itemno,
            'rows'   => $rows,
        ])->render();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L',
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="cp-resin-history.pdf"',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  โหมดอนุมัติ (MD) — ปลดล็อกด้วยรหัสผ่าน
    //
    //  ฟอร์มนี้ทำงาน 2 ขั้นตอน:
    //    1) โหมดขอราคา (MK)  — ค่าเริ่มต้น กรอกใบขอราคาได้ แต่ช่อง "อนุมัติ" ถูกล็อก
    //    2) โหมดอนุมัติ (MD) — กรอกรหัสผ่าน MD แล้วกดปุ่มปลดล็อก จึงจะติ๊กอนุมัติได้
    //
    //  การปลดล็อกเก็บที่ session ฝั่ง server (ไม่ใช่แค่ซ่อน/โชว์ปุ่มฝั่ง JS)
    //  → save() ตรวจจาก session เสมอ เปิด devtools ปลดปุ่มเองก็อนุมัติไม่ได้
    // ─────────────────────────────────────────────────────────────

    /**
     * ตรวจรหัสผ่าน MD
     *
     * ⚠ ยังไม่พบว่าระบบเดิมเก็บรหัสนี้ไว้ที่ไหน — เดาว่าเป็นรหัสเดียวใช้ร่วมกัน
     * เก็บไว้ที่ `config/order.php` (ตั้งทับได้ด้วย ORDER_MD_PASSWORD ใน .env)
     * ได้ที่เก็บจริงเมื่อไหร่ แก้เมธอดนี้ที่เดียวพอ
     */
    private function checkMdPassword($input): bool
    {
        $expected = (string) config('order.md_password', '');

        return $expected !== '' && hash_equals($expected, (string) $input);
    }

    /** ตอนนี้อยู่ในโหมดอนุมัติอยู่ไหม (ปลดล็อกไว้แล้วและยังไม่หมดอายุ) */
    private function mdUnlocked(): bool
    {
        $until = session(self::MD_SESSION_KEY);

        return $until && Carbon::parse($until)->isFuture();
    }

    /** GET — ฟอร์มถามตอนเปิดว่าตอนนี้ปลดล็อกโหมดอนุมัติค้างไว้อยู่ไหม */
    public function mdState()
    {
        return response()->json(['unlocked' => $this->mdUnlocked()]);
    }

    /** POST — กรอกรหัสผ่าน MD เพื่อเข้าสู่โหมดอนุมัติ */
    public function unlock(Request $request)
    {
        if (!$this->checkMdPassword($request->input('md_password'))) {
            return response()->json(['status' => false, 'message' => 'รหัสผ่านไม่ถูกต้อง'], 422);
        }

        $until = now()->addMinutes(self::MD_UNLOCK_MINUTES);
        session([self::MD_SESSION_KEY => $until->format('Y-m-d H:i:s')]);

        return response()->json([
            'status'  => true,
            'message' => 'เข้าสู่โหมดอนุมัติแล้ว (' . self::MD_UNLOCK_MINUTES . ' นาที)',
            'minutes' => self::MD_UNLOCK_MINUTES,
        ]);
    }

    /** POST — ออกจากโหมดอนุมัติ (ปุ่มบนฟอร์ม / ทำงานให้เสร็จแล้วล็อกกลับ) */
    public function lock()
    {
        session()->forget(self::MD_SESSION_KEY);

        return response()->json(['status' => true, 'message' => 'ออกจากโหมดอนุมัติแล้ว']);
    }

    // ─────────────────────────────────────────────────────────────
    //  บันทึก / ลบ ใบขออนุมัติราคา
    // ─────────────────────────────────────────────────────────────

    /**
     * POST — เพิ่ม / บันทึกใบขออนุมัติราคา
     *
     * เขียนลง `appvreq` (PK = ReqDate + custno + itemno) — **1 คู่ (ลูกค้า, เบอร์) = 1 ใบที่แก้ได้**
     *   - คู่ที่ยังไม่เคยขอ  → ขึ้นใบใหม่ด้วยเวลาปัจจุบัน
     *   - คู่ที่เคยขอแล้ว    → แก้ทับ "ใบล่าสุด" เสมอ (หา ReqDate ของใบล่าสุดที่ server เอง
     *                          ไม่เชื่อค่าที่ client ส่งมา กันเผลอสร้างใบซ้ำ/ใบผิดวัน)
     *   - ติ๊ก "อนุมัติ" ต้องอยู่ในโหมดอนุมัติ (ปลดล็อกด้วยรหัสผ่าน MD แล้ว) — ดู unlock()
     *   - การอนุมัติจะเขียนราคาที่ยืนไว้ลง `zcustprice` ด้วย (ราคาขายครั้งนี้ + อนุมัติราคาถึง)
     *     ตามที่ฟอร์มเดิมโชว์ทั้งสองที่คู่กัน
     */
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'custno' => 'required|string|max:10',
            'itemno' => 'required|string|max:20',
        ], [
            'custno.required' => 'ต้องระบุรหัสลูกค้า',
            'itemno.required' => 'ต้องเลือกรหัสสินค้า',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        // ราคาขายครั้งนี้ — ตรวจหลังถอดคอมมาแล้ว (rule numeric ของ Laravel ไม่รับ "1,234.50")
        $price = $this->numOrNull($request->input('price'));
        if ($price === null) {
            return response()->json(['status' => false, 'message' => 'ต้องกรอกราคาขายครั้งนี้เป็นตัวเลข'], 422);
        }

        $custno = trim((string) $request->input('custno'));
        $itemno = trim((string) $request->input('itemno'));
        $appv   = $request->boolean('Appv');

        if (!DB::table('customer')->where('code', $custno)->exists()) {
            return response()->json(['status' => false, 'message' => 'ไม่พบรหัสลูกค้า ' . $custno], 422);
        }

        // ใบล่าสุดของคู่นี้ — มีอยู่ = แก้ทับใบเดิม, ไม่มี = ขึ้นใบใหม่
        $existing = DB::table('appvreq')
            ->where('custno', $custno)
            ->where('itemno', $itemno)
            ->orderByDesc('ReqDate')
            ->first(['ReqDate', 'Appv']);

        $alreadyApproved = $existing ? self::checked($existing->Appv) : false;
        $mdMode          = $this->mdUnlocked();

        // ติ๊กอนุมัติ = ต้องอยู่ในโหมดอนุมัติ
        // ยกเว้นใบที่ "อนุมัติไปแล้ว" — ฝั่ง MK แก้หมายเหตุ/ราคาต่อได้โดยไม่ต้องปลดล็อก
        // (ช่องอนุมัติถูก disabled อยู่ ค่าที่ส่งมาจึงเป็นสถานะเดิม ไม่ใช่การอนุมัติใหม่)
        if ($appv && !$alreadyApproved && !$mdMode) {
            return response()->json([
                'status'    => false,
                'md_locked' => true,
                'message'   => 'ต้องเข้าสู่โหมดอนุมัติก่อน (กรอกรหัสแล้วกดปุ่มปลดล็อก) — หรือโหมดอนุมัติหมดอายุแล้ว',
            ], 422);
        }

        $reqDate = $existing->ReqDate ?? now()->format('Y-m-d H:i:s');
        // ช่อง "อนุมัติราคาถึง" ว่าง (เผลอลบวันที่ทิ้ง) → ยืนราคาถึงพรุ่งนี้ ไม่ปล่อยให้ enddate ว่าง
        $validTo = $this->parseDate($request->input('valid_to')) ?? self::defaultValidTo();

        // เขียน zcustprice เฉพาะตอนที่เป็น "การอนุมัติจริง ๆ" (อยู่ในโหมด MD)
        // — ไม่งั้น MK ที่แก้ใบซึ่งอนุมัติไปแล้วจะเผลอทับ enddate ด้วยช่องที่ถูกล็อกไว้ (ค่าว่าง)
        $writeZcust = $appv && $mdMode;

        $row = [
            'weight'  => $this->numOrNull($request->input('weight')),
            'price'   => $price,
            'price1'  => $this->numOrNull($request->input('price1')),
            'price2'  => $this->numOrNull($request->input('price2')),
            'price3'  => $this->numOrNull($request->input('price3')),
            'remark'  => $this->nullIfBlank($request->input('remark')),
            'costup'  => $request->boolean('costup') ? -1 : 0,
            'Appv'    => $appv ? -1 : 0,
        ];

        try {
            DB::transaction(function () use ($custno, $itemno, $reqDate, $row, $writeZcust, $validTo, $request) {
                $key = ['ReqDate' => $reqDate, 'custno' => $custno, 'itemno' => $itemno];

                if (DB::table('appvreq')->where($key)->exists()) {
                    DB::table('appvreq')->where($key)->update($row);
                } else {
                    DB::table('appvreq')->insert($key + $row);
                }

                // อนุมัติแล้ว → บันทึกราคาที่ยืนไว้ให้ลูกค้ารายนี้ (ตารางล่างของฟอร์ม)
                if ($writeZcust) {
                    $zkey = ['custno' => $custno, 'colorno' => $itemno];
                    $zrow = [
                        'exprice' => $this->numOrNull($request->input('price')),
                        'enddate' => $validTo,
                        'remark'  => $this->nullIfBlank($request->input('remark')),
                    ];

                    if (DB::table('zcustprice')->where($zkey)->exists()) {
                        DB::table('zcustprice')->where($zkey)->update($zrow);
                    } else {
                        DB::table('zcustprice')->insert($zkey + $zrow);
                    }
                }
            });
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => 'บันทึกไม่สำเร็จ: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => $writeZcust
                ? 'บันทึกและอนุมัติราคาเรียบร้อย'
                : ($existing ? 'แก้ไขใบขออนุมัติราคาเรียบร้อย' : 'สร้างใบขออนุมัติราคาเรียบร้อย'),
            'ReqDate' => $reqDate,
        ]);
    }

    /**
     * POST — ลบใบขออนุมัติราคา 1 ใบ (ปุ่ม "ลบ รายการ")
     * ลบเฉพาะแถวใน appvreq — ไม่แตะราคาที่ยืนไว้ใน zcustprice
     */
    public function destroy(Request $request)
    {
        $custno  = trim((string) $request->input('custno'));
        $itemno  = trim((string) $request->input('itemno'));
        $reqDate = $this->parseDateTime($request->input('ReqDate'));

        if ($custno === '' || $itemno === '' || !$reqDate) {
            return response()->json(['status' => false, 'message' => 'ไม่พบใบขออนุมัติราคาที่จะลบ'], 422);
        }

        $deleted = DB::table('appvreq')
            ->where(['ReqDate' => $reqDate, 'custno' => $custno, 'itemno' => $itemno])
            ->delete();

        if (!$deleted) {
            return response()->json(['status' => false, 'message' => 'ไม่พบใบขออนุมัติราคาที่จะลบ'], 422);
        }

        return response()->json(['status' => true, 'message' => 'ลบใบขออนุมัติราคาเรียบร้อย']);
    }

    /** ค่าว่าง → null */
    private function nullIfBlank($value)
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /** ตัวเลขจากฟอร์ม (ผ่าน stripCommaFields มาแล้ว) — ว่าง/ไม่ใช่ตัวเลข = null */
    private function numOrNull($value)
    {
        $value = str_replace(',', '', trim((string) $value));

        return ($value === '' || !is_numeric($value)) ? null : (float) $value;
    }

    /**
     * ค่าเริ่มต้นของช่อง "อนุมัติราคาถึง" (zcustprice.enddate) = **วันทำการถัดไป**
     *
     * เดิม (29/08/2569) คือ "พรุ่งนี้" ตรง ๆ — เปลี่ยนเป็นข้ามวันหยุด 01/09/2569 ตามที่ผู้ใช้สั่ง:
     * วันนี้ที่ 1 · วันที่ 2 เป็นวันหยุด → ได้วันที่ 3
     * (วันหยุด = วันอาทิตย์ + tb_holiday ที่เปิดใช้งาน — ดู App\Services\HolidayService)
     *
     * ใช้ 2 ที่ให้ตรงกัน: ฟอร์มเติมให้ตอนเปิด (ค่ามาจาก server ผ่าน default_valid_to
     * ใน data() และตัวแปร APPROVAL_DEFAULT_VALID_TO ที่ blade ฝังไว้)
     * และ save() เติมให้เองเมื่อช่องถูกส่งมาว่าง (เผลอลบวันที่ทิ้ง)
     *
     * ⚠ ห้ามปล่อย enddate เป็น null — activeApprovedPrice() ถือว่า "ว่าง = ไม่กำหนดวันหมดอายุ"
     *   ราคาพิเศษใบนั้นจะปลดล็อกด่านราคาใน OrderController::checkPriceFloor() ได้ตลอดไป
     */
    public static function defaultValidTo(): string
    {
        return HolidayService::nextWorkingDay();
    }

    /** d/m/Y → Y-m-d (ว่าง/รูปแบบผิด = null) */
    private function parseDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $value, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        if (preg_match('#^\d{4}-\d{2}-\d{2}#', $value)) {
            return substr($value, 0, 10);
        }

        return null;
    }

    /** d/m/Y H:i หรือ Y-m-d H:i:s → Y-m-d H:i:s (ว่าง/รูปแบบผิด = null) */
    private function parseDateTime($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})(?:[ T](\d{1,2}):(\d{2})(?::(\d{2}))?)?$#', $value, $m)) {
            return sprintf('%04d-%02d-%02d %02d:%02d:%02d', $m[3], $m[2], $m[1], $m[4] ?? 0, $m[5] ?? 0, $m[6] ?? 0);
        }
        if (preg_match('#^\d{4}-\d{2}-\d{2}#', $value)) {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        }

        return null;
    }

    /** ตารางราคาที่ยืนไว้ (zcustprice) — ระบุ itemno = เฉพาะเบอร์นั้น, null = ทุกเบอร์ของลูกค้า */
    private function zcustRows($custno, $itemno): array
    {
        $custno = trim((string) $custno);
        if ($custno === '') {
            return [];
        }

        $query = DB::table('zcustprice')->where('custno', $custno);

        if ($itemno !== null && trim((string) $itemno) !== '') {
            $query->where('colorno', trim((string) $itemno));
        }

        return $query->orderBy('colorno')
            ->get(['colorno', 'exprice', 'enddate', 'remark', 'mk'])
            ->all();
    }
}
