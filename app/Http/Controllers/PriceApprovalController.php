<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ขออนุมัติราคาพิเศษ (MD) — แปลงมาจากฟอร์ม Access "MK ขออนุมัติราคาพิเศษ"
 * เป็นฟอร์มลูกของเมนู O-Order (ดู OrderController)
 *
 * ตารางที่ใช้ (legacy ทั้งหมด อ่านอย่างเดียวในเฟสนี้):
 *   appvreq       ใบขออนุมัติราคา 1 แถว = 1 ครั้งที่ขอ (PK = ReqDate + custno + itemno)
 *                 price1/2/3 = ราคาตามกลุ่มปริมาณสั่ง A/B/C, price = ราคาขายครั้งนี้, Appv = อนุมัติแล้ว
 *   zcustprice    ราคาที่ยืนไว้กับลูกค้า (PK = custno + colorno) → ตารางล่างของฟอร์ม
 *   uprice        ราคาที่ตกลงไว้ล่าสุด (ใช้เป็นรายการเบอร์สินค้าของลูกค้ารายนั้น)
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

        // รวมเบอร์จากทั้ง uprice (ราคาที่ตกลงไว้) และ zcustprice (ราคาที่ยืนไว้)
        // — บางเบอร์มีอยู่ในตารางเดียว ถ้าดึงตารางเดียวจะหาย
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
                'Appv'    => self::checked($req->Appv),
            ] : null,
            // กลุ่มราคาที่ตรงกับปริมาณสั่งซื้อในใบนี้ (ใช้เน้นช่องราคาที่เกี่ยวข้อง)
            'group'    => $group,
            'groups'   => self::PRICE_GROUPS,
            'uprice'   => $uprice,
            // ตารางล่าง — ราคาที่ยืนไว้ของเบอร์ที่เลือก
            'rows'     => $this->zcustRows($custno, $itemno),
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
