<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

/**
 * อนุมัติใบสั่งซื้อ — แปลงมาจากฟอร์ม Access "morderAPPV"
 * (หัวฟอร์มเดิมเขียนกำกับว่า "ไม่รวมทำ STOCK + ไม่รวมใบจอง R" = เงื่อนไขของคิว)
 * เป็นฟอร์มลูกของเมนู O-Order (ดู OrderController)
 *
 * คิวรออนุมัติ = `morder` ที่ยังไม่อนุมัติ (`appv` ว่าง) โดย
 *   - ตัดใบจอง R ออก → ตัวอักษรที่ 2 ของ Orderno = 'R' (CR / HR / WR)
 *   - ⚠ เงื่อนไข "ไม่รวมใบสั่งทำสต๊อก" **ปิดไว้ชั่วคราว** (25/08/2569) — ดู approvableQuery()
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
     * ตรวจ "รหัสพนักงาน" ก่อนอนุมัติ (16/09/2569 ตามที่ผู้ใช้สั่ง)
     *
     * เดิม (12/09/2569) ให้กรอก **รหัสผ่าน** — ตอนแรกเทียบกับบัญชีที่ล็อกอินอยู่
     * แล้วเปลี่ยนเป็น "รหัสผ่านของใครก็ได้" (16/09/2569) · ตอนนี้เปลี่ยนเป็นกรอก
     * **รหัสพนักงาน (`emp.empno`)** แทน ไม่ใช่รหัสผ่านแล้ว
     *
     * ผ่านเมื่อรหัสที่กรอกตรงกับพนักงานที่ยังใช้งานอยู่ (`is_active = 'Y'`) — ข้อมูลจริง
     * ตอนนี้มีพนักงาน 33 คน ทุกคน active และ `empno` ไม่ซ้ำ/ไม่ว่าง (varchar(4))
     *
     * ⚠ รหัสพนักงานเป็นข้อมูลที่เดาง่ายกว่ารหัสผ่านมาก — ด่านนี้จึงเป็นแค่การ
     *   "ยืนยันตัวผู้กด" ตามที่ผู้ใช้ต้องการ ไม่ใช่การพิสูจน์ตัวตนจริง
     * ⚠ ระบบยัง **ไม่บันทึกว่าใครอนุมัติ** — `morder` ไม่มีคอลัมน์ผู้อนุมัติ
     *   (ถ้าจะเก็บ ต้องเพิ่มคอลัมน์ก่อน แล้วเขียนค่า empno ที่กรอกลงไปใน approve())
     *
     * @return string|null  ข้อความผิดพลาด (null = ผ่าน)
     */
    private function approveEmpnoError($input): ?string
    {
        $empno = trim((string) $input);
        if ($empno === '') {
            return 'กรุณากรอกรหัสพนักงาน';
        }

        $exists = DB::table('emp')
            ->where('empno', $empno)
            ->where('is_active', 'Y')
            ->exists();

        return $exists ? null : 'รหัสพนักงานไม่ถูกต้อง';
    }

    /**
     * ใบที่ "ต้องผ่านการอนุมัติ"
     * (ยังไม่ดูว่าอนุมัติไปหรือยัง — ใช้ทั้งตอนสร้างคิวและตอนตรวจสิทธิ์ก่อนกดอนุมัติ)
     *
     * ⚠ **ปิดเงื่อนไข "ไม่รวมใบสั่งทำสต๊อก" ไว้ชั่วคราว** (25/08/2569 ตามที่ผู้ใช้สั่ง
     *   "ยังไม่ต้องสนเรื่องสต็อก") — ใบสั่งทำสต๊อกจึงเข้าคิวอนุมัติเหมือนใบปกติ
     *
     *   หัวฟอร์ม Access เดิมเขียนกำกับว่า "ไม่รวมทำ STOCK + ไม่รวมใบจอง R" แต่ข้อมูลจริง
     *   สวนทาง: ใบสั่งทำสต๊อก 98 ใบ **อนุมัติไปแล้ว 96 ใบ** ⇒ ของเดิมก็อนุมัติใบสต๊อกเหมือนกัน
     *   (แค่คงไม่ได้ทำผ่านฟอร์มนี้) — รอลูกค้ายืนยันว่าสรุปแล้วใบสต๊อกต้องอนุมัติที่ไหน
     *
     *   จะเปิดคืน: ย้าย `;` ออกจากบรรทัด whereRaw แล้วปลดคอมเมนต์บล็อก where ด้านล่าง
     */
    private function approvableQuery()
    {
        return DB::table('morder')
            // ไม่รวมใบจอง R (CR / HR / WR)
            // qualify ชื่อตาราง — approvedTodayPdf() เอา query นี้ไป join suborder ซึ่งมี Orderno เหมือนกัน (06/10/2569)
            ->whereRaw('SUBSTRING(morder.Orderno, 2, 1) <> ?', ['R']);

        // ── ปิดไว้ชั่วคราว: ไม่รวมใบสั่งทำสต๊อก ──
        // ->where(function ($q) {
        //     $q->whereRaw('COALESCE(HMStore, 0) = 0')
        //         ->whereRaw('COALESCE(SendCust, 0) = 0')
        //         ->whereRaw('COALESCE(sendmth, 0) = 0');
        // });
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

        // ต้องกรอกรหัสพนักงานทุกครั้งที่เปลี่ยนสถานะอนุมัติ (รวมขา "ยกเลิกอนุมัติ" ด้วย
        // เพราะเป็นการแก้สถานะเอกสารเหมือนกัน แม้ตอนนี้ UI จะยังเข้าไม่ถึงขานั้น)
        $empError = $this->approveEmpnoError($request->input('empno'));
        if ($empError !== null) {
            return response()->json([
                'status'    => false,
                'bad_empno' => true,
                'message'   => $empError,
            ], 422);
        }

        $order = DB::table('morder')->where('Orderno', $orderno)->first(['Orderno', 'appv']);
        if (!$order) {
            return response()->json(['status' => false, 'message' => 'ไม่พบใบสั่งซื้อ ' . $orderno], 422);
        }

        // ใบจอง R ไม่ต้องผ่านการอนุมัติ — กันเรียก endpoint ตรง ๆ
        if (!$this->approvableQuery()->where('Orderno', $orderno)->exists()) {
            return response()->json([
                'status'  => false,
                'message' => 'ใบสั่งนี้ไม่ต้องผ่านการอนุมัติ (ใบจอง R)',
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

    /**
     * GET — ปุ่ม "พิมพ์รายการที่อนุมัติวันนี้" บนหน้า /order (06/10/2569)
     *   รายงาน PDF "รายงานผลิตภัณฑ์ที่ต้องผลิต (P)" ตามกระดาษของระบบเดิม (MK01-FM04.04)
     *
     *   เงื่อนไข: ใบสั่งซื้อที่ **อนุมัติวันนี้** (morder.appvDT อยู่ในวันนี้) เรียงตามเลขที่ใบสั่ง
     *   1 แถว = 1 รายการใน suborder ที่มีน้ำหนักผลิต (Production > 0) — รายงานนี้เป็นของ "ที่ต้องผลิต (P)"
     *   แยกหน้าตามแผนกที่ผลิต (morder.Company) — กระดาษเดิมออกทีละแผนก หัวรายงานเขียน "แผนกผลิต DB"
     *   ท้ายแต่ละแผนก: รวมน้ำหนัก P · ช่องเซ็น · "รายการสินค้าที่สั่งซ้ำ" (รหัสสินค้าเดียวกันเกิน 1 รายการ)
     *
     *   ?date=Y-m-d  พิมพ์ย้อนหลังของวันอื่นได้ (ปุ่มบนจอไม่ส่ง = วันนี้)
     */
    public function approvedTodayPdf(Request $request)
    {
        try {
            $day = $request->filled('date')
                ? Carbon::createFromFormat('Y-m-d', (string) $request->query('date'))
                : now();
        } catch (\Exception $e) {
            $day = now();
        }
        $from = $day->copy()->startOfDay();
        $to   = $day->copy()->endOfDay();

        $rows = $this->approvableQuery()
            ->join('suborder as s', 's.Orderno', '=', 'morder.Orderno')
            ->leftJoin('customer as c', 'c.code', '=', 'morder.Custno')
            ->whereNotNull('morder.appv')
            ->where('morder.appv', '<>', 0)            // Access เก็บ -1 = อนุมัติแล้ว
            ->whereBetween('morder.appvDT', [$from, $to])
            ->where('s.Production', '>', 0)
            ->orderBy('morder.Orderno')
            ->orderBy('s.Runno')
            ->get([
                'morder.Orderno', 'morder.Mdate', 'morder.Company', 'morder.Custno', 'morder.supno',
                'morder.Send', 'morder.RP', 'morder.Spec', 'morder.Cer',
                'c.name as custname',
                's.Itemno', 's.Lotno', 's.Production', 's.senddate', 's.custwant', 's.Remark',
            ]);

        foreach ($rows as $r) {
            // "ซื้อครั้งก่อน" = วันที่สั่งครั้งล่าสุดของรหัสสินค้านี้ก่อนใบนี้ (ไม่แยกลูกค้า) · ไม่เคยสั่ง = NEW
            $r->last_order = DB::table('suborder as s2')
                ->join('morder as m2', 'm2.Orderno', '=', 's2.Orderno')
                ->where('s2.Itemno', $r->Itemno)
                ->where('m2.Orderno', '<>', $r->Orderno)
                ->where('m2.Mdate', '<', $r->Mdate)
                ->max('m2.Mdate');

            // checkbox แบบ Access: -1 = ติ๊ก
            foreach (['Send', 'RP', 'Spec', 'Cer'] as $f) {
                $r->{$f} = (int) $r->{$f} !== 0;
            }
        }

        // 1 แผนก = 1 section (ขึ้นหน้าใหม่) — แผนกว่างไปท้ายสุด
        $sections = $rows->groupBy(fn ($r) => trim((string) $r->Company))
            ->sortBy(fn ($g, $dept) => $dept === '' ? 'zzzz' : $dept)
            ->map(function ($items, $dept) {
                // รายการสินค้าที่สั่งซ้ำ — รหัสสินค้า + ประเภทใบ (2 ตัวหน้าเลขที่ใบ) ที่มีมากกว่า 1 รายการ
                $repeats = $items
                    ->groupBy(fn ($r) => $r->Itemno . '|' . substr((string) $r->Orderno, 0, 2))
                    ->filter(fn ($g) => $g->count() > 1)
                    ->map(fn ($g) => (object) [
                        'itemno' => $g->first()->Itemno,
                        'type'   => substr((string) $g->first()->Orderno, 0, 2),
                        'count'  => $g->count(),
                        'weight' => $g->sum(fn ($r) => (float) $r->Production),
                    ])
                    ->sortBy('itemno')
                    ->values();

                return (object) [
                    'dept'    => $dept,
                    'items'   => $items->values(),
                    'total'   => $items->sum(fn ($r) => (float) $r->Production),
                    'repeats' => $repeats,
                ];
            })
            ->values();

        $html = view('order.order-approved-today-pdf', [
            'sections' => $sections,
            'day'      => $day,
        ])->render();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 16,
            'margin_bottom' => 10,
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->SetHTMLHeader(
            '<div style="font-family: sarabun; font-size: 10px;">Page {PAGENO} of {nbpg} &nbsp; ' . now()->format('d/m/y H:i') . '</div>'
        );
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="order-approved-' . $day->format('Ymd') . '.pdf"',
        ]);
    }
}
