<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * อนุมัติราคาใบสั่งซื้อ — แปลงมาจากฟอร์ม Access "morderAPPV"
 * (หัวฟอร์มเดิมเขียนกำกับว่า "ไม่รวมทำ STOCK + ไม่รวมใบจอง R" = เงื่อนไขของคิว)
 * เป็นฟอร์มลูกของเมนู O-Order (ดู OrderController)
 *
 * คิวรออนุมัติ = `morder` ที่ยังไม่อนุมัติ (`appv` ว่าง) โดย
 *   - ตัดใบสั่งทำสต๊อกออก  → มีค่าใน HMStore / SendCust / sendmth
 *   - ตัดใบจอง R ออก       → ตัวอักษรที่ 2 ของ Orderno = 'R' (CR / HR / WR)
 * เมื่ออนุมัติ ระบบเดิมจะเขียน `morder.appv` (-1) + `morder.appvDT` (วัน-เวลาอนุมัติ)
 */
class OrderApprovalController extends Controller
{
    /** คอลัมน์ checkbox ของ Access เก็บ -1 = ติ๊ก */
    private static function checked($value): bool
    {
        return (int) $value !== 0 && $value !== null;
    }

    /** คิวรออนุมัติตามเงื่อนไขบนหัวฟอร์มเดิม */
    private function queueQuery()
    {
        return DB::table('morder')
            ->whereNull('appv')
            // ไม่รวมใบจอง R (CR / HR / WR)
            ->whereRaw('SUBSTRING(Orderno, 2, 1) <> ?', ['R'])
            // ไม่รวมใบสั่งทำสต๊อก
            ->where(function ($q) {
                $q->whereRaw('COALESCE(HMStore, 0) = 0')
                    ->whereRaw('COALESCE(SendCust, 0) = 0')
                    ->whereRaw('COALESCE(sendmth, 0) = 0');
            });
    }

    /**
     * GET — รายการใบที่รออนุมัติ (ตัวเดินระเบียน "ระเบียนที่ N จาก M")
     */
    public function queue(Request $request)
    {
        $rows = $this->queueQuery()
            ->leftJoin('customer as c', 'morder.Custno', '=', 'c.code')
            ->orderBy('morder.Mdate')
            ->orderBy('morder.Orderno')
            ->get([
                'morder.Orderno', 'morder.Mdate', 'morder.Company',
                'morder.Custno', 'morder.price',
                DB::raw('COALESCE(c.name, morder.Custname) as custname'),
            ]);

        return response()->json([
            'count' => $rows->count(),
            'rows'  => $rows,
        ]);
    }

    /**
     * GET — ข้อมูลใบเดียวสำหรับหน้าอนุมัติ
     *   ?orderno=HI56681
     */
    public function record(Request $request)
    {
        $orderno = trim((string) $request->query('orderno', ''));
        if ($orderno === '') {
            return response()->json(['found' => false]);
        }

        $order = DB::table('morder')->where('Orderno', $orderno)->first();
        if (!$order) {
            return response()->json(['found' => false]);
        }

        $cust = DB::table('customer')->where('code', $order->Custno)->first();

        $items = DB::table('suborder')
            ->where('Orderno', $orderno)
            ->orderBy('Runno')
            ->get(['Runno', 'Itemno', 'prodname', 'Lotno', 'Stock', 'Production', 'senddate', 'Remark']);

        // ข้อมูลราคาของแต่ละเบอร์ในใบ — ฟอร์มเดิมโชว์ทีละเบอร์ตามแถวที่เลือกในตาราง
        $prices = [];
        foreach ($items->pluck('Itemno')->filter()->unique() as $itemno) {
            $prices[$itemno] = $this->itemPrice($order->Custno, $itemno);
        }

        return response()->json([
            'found' => true,
            'order' => [
                'Orderno'  => $order->Orderno,
                'Mdate'    => $order->Mdate,
                'Company'  => $order->Company,      // แผนกที่ผลิต
                'PO'       => $order->PO,
                'Custno'   => $order->Custno,
                'Custname' => $cust->name ?? $order->Custname,
                'Emp'      => $order->Emp,          // ผู้บันทึก
                'sale'     => $cust->sale ?? null,  // ผู้ขาย
                'HMStore'  => $order->HMStore,      // น.น.Stock คงเหลือปัจจุบัน
                'DVpoint'  => $order->DVpoint,
                'SendCust' => $order->SendCust,     // ส่งลูกค้าภายใน (เดือน)
                'price'    => $order->price,        // ราคาขายครั้งนี้
                'Send'     => self::checked($order->Send),
                'RP'       => self::checked($order->RP),
                'Spec'     => self::checked($order->Spec),
                'Cer'      => self::checked($order->Cer),
                'appv'     => self::checked($order->appv),
                'appvDT'   => $order->appvDT,
                // ท้ายฟอร์มเดิม: "(<เทอม> - <ส่วนลดเงินสด>%)"
                'term'     => $cust->term ?? null,
                'cashdisc' => $cust->cashdisc ?? null,
            ],
            'items'  => $items,
            'prices' => $prices,
        ]);
    }

    /**
     * ราคาอ้างอิงของเบอร์สินค้า 1 เบอร์ (แผงล่างของฟอร์ม)
     *   fixed_price / REM1 / REM2  → uprice  (ราคาที่กำหนดไว้ + หมายเหตุ 2 บรรทัด)
     *   price1/2/3                 → appvreq (ราคาตามกลุ่มปริมาณ A/B/C ของใบขออนุมัติล่าสุด)
     */
    private function itemPrice($custno, $itemno): array
    {
        $uprice = DB::table('uprice')
            ->where('CustNo', $custno)
            ->where('ITEMNO', $itemno)
            ->orderByDesc('DATE')
            ->first(['PRICE', 'REM1', 'REM2']);

        $appv = DB::table('appvreq')
            ->where('custno', $custno)
            ->where('itemno', $itemno)
            ->orderByDesc('ReqDate')
            ->first(['price1', 'price2', 'price3']);

        return [
            'fixed_price' => $uprice->PRICE ?? null,
            'rem1'        => $uprice->REM1 ?? null,
            'rem2'        => $uprice->REM2 ?? null,
            'price1'      => $appv->price1 ?? null,
            'price2'      => $appv->price2 ?? null,
            'price3'      => $appv->price3 ?? null,
        ];
    }
}
