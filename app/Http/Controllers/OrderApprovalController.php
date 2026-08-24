<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * อนุมัติใบสั่งซื้อ — แปลงมาจากฟอร์ม Access "morderAPPV"
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

    /**
     * ใบที่ "ต้องผ่านการอนุมัติ" ตามเงื่อนไขบนหัวฟอร์มเดิม
     * (ยังไม่ดูว่าอนุมัติไปหรือยัง — ใช้ทั้งตอนสร้างคิวและตอนตรวจสิทธิ์ก่อนกดอนุมัติ)
     */
    private function approvableQuery()
    {
        return DB::table('morder')
            // ไม่รวมใบจอง R (CR / HR / WR)
            ->whereRaw('SUBSTRING(Orderno, 2, 1) <> ?', ['R'])
            // ไม่รวมใบสั่งทำสต๊อก
            ->where(function ($q) {
                $q->whereRaw('COALESCE(HMStore, 0) = 0')
                    ->whereRaw('COALESCE(SendCust, 0) = 0')
                    ->whereRaw('COALESCE(sendmth, 0) = 0');
            });
    }

    /** คิวรออนุมัติ = ใบที่ต้องอนุมัติ และยังไม่ได้อนุมัติ */
    private function queueQuery()
    {
        return $this->approvableQuery()->whereNull('appv');
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
            // ใบนี้อยู่ในขอบเขตที่ฟอร์มนี้อนุมัติได้ไหม (ไม่ใช่ใบจอง R / ใบสั่งทำสต๊อก)
            'approvable' => $this->approvableQuery()->where('Orderno', $orderno)->exists(),
        ]);
    }

    /**
     * POST — กดอนุมัติ / ยกเลิกการอนุมัติใบสั่งซื้อ 1 ใบ
     *   orderno = เลขที่ใบสั่ง
     *   appv    = 1 อนุมัติ, 0 ยกเลิกการอนุมัติ
     *
     * เขียน `morder.appv` + `morder.appvDT` เท่านั้น
     * — ตาราง `morder` ไม่มีคอลัมน์เก็บ "ใครเป็นคนอนุมัติ" และไม่มีที่เก็บหมายเหตุผู้บริหาร
     *   (ช่องกล่องเขียวบนฟอร์มจึงยังไม่ผูกข้อมูล ดูหมายเหตุใน CLAUDE.md)
     */
    public function approve(Request $request)
    {
        $orderno = trim((string) $request->input('orderno', ''));
        $appv    = $request->boolean('appv');

        if ($orderno === '') {
            return response()->json(['status' => false, 'message' => 'ไม่ได้ระบุเลขที่ใบสั่ง'], 422);
        }

        $order = DB::table('morder')->where('Orderno', $orderno)->first(['Orderno', 'appv']);
        if (!$order) {
            return response()->json(['status' => false, 'message' => 'ไม่พบใบสั่งซื้อ ' . $orderno], 422);
        }

        // ใบจอง R / ใบสั่งทำสต๊อก ไม่ต้องผ่านการอนุมัติ — กันเรียก endpoint ตรง ๆ
        if (!$this->approvableQuery()->where('Orderno', $orderno)->exists()) {
            return response()->json([
                'status'  => false,
                'message' => 'ใบสั่งนี้ไม่ต้องผ่านการอนุมัติ (ใบจอง R หรือใบสั่งทำสต๊อก)',
            ], 422);
        }

        // สถานะตรงกับที่ขอมาอยู่แล้ว = ไม่มีอะไรต้องทำ (เช่นเปิดฟอร์มค้างไว้แล้วมีคนอนุมัติไปก่อน)
        if (self::checked($order->appv) === $appv) {
            return response()->json([
                'status'  => false,
                'message' => $appv ? 'ใบสั่งนี้อนุมัติไปแล้ว' : 'ใบสั่งนี้ยังไม่ได้อนุมัติ',
            ], 422);
        }

        // ข้อมูลจริงมีแค่ 2 สถานะ: -1 = อนุมัติแล้ว (มี appvDT เสมอ) / NULL = รออนุมัติ
        // ไม่มีแถวไหนเก็บ 0 เลย → ยกเลิกอนุมัติจึงคืนเป็น NULL ให้ใบไหลกลับเข้าคิว
        $now = now()->format('Y-m-d H:i:s');

        DB::table('morder')->where('Orderno', $orderno)->update([
            'appv'   => $appv ? -1 : null,
            'appvDT' => $appv ? $now : null,
        ]);

        return response()->json([
            'status'  => true,
            'message' => $appv
                ? 'อนุมัติใบสั่งซื้อ ' . $orderno . ' เรียบร้อย'
                : 'ยกเลิกการอนุมัติใบสั่งซื้อ ' . $orderno . ' เรียบร้อย',
            'appv'    => $appv,
            'appvDT'  => $appv ? $now : null,
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
