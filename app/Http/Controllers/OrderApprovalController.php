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
                'morder.Orderno', 'morder.Mdate', 'morder.appvDT', 'morder.Company',
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

        // ราคาเม็ด CP ที่กรอกไว้ตอนบันทึกใบ (cp_itemprice) — ฟอร์มอนุมัติเด้งขึ้นมาให้ดูแบบอ่านอย่างเดียว (19/10/2569)
        //   ตอนบันทึกผูกกับรหัสสินค้าแถวแรกของใบ → หาคู่นั้นก่อน ไม่เจอค่อยเอาแถวใดก็ได้ของใบนี้
        $firstItemno = trim((string) optional($items->first())->Itemno);
        $cp = DB::table('cp_itemprice')->where('Orderno', $orderno)
            ->orderByRaw('itemno = ? DESC', [$firstItemno])
            ->first();

        return response()->json([
            'found' => true,
            'cp'    => $cp,
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
                'MSDS'     => self::checked($order->MSDS),
                // Require Document (19/10/2569 — ให้ครบเหมือนฟอร์มบันทึกใบสั่งซื้อ)
                'ReqDoc'    => self::checked($order->ReqDoc ?? null),
                'ReqDocRem' => $order->ReqDocRem ?? null,
                'appv'   => self::checked($order->appv),
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
     *   price1/2/3                 → appvreq (ราคา 3 ช่องของใบขออนุมัติล่าสุด)
     *   color_rate                 → zcolorrate (ราคากลุ่ม A/B/C — แถวล่างใต้ราคา 3 ช่อง)
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

        // ราคากลุ่ม A/B/C ของรหัสสินค้า (zcolorrate — ตัวเดียวกับเมนู "จัดการกลุ่มราคา") — 12/10/2569
        // ไม่ผูกกับลูกค้า · รหัสไม่มีในตาราง = null (จอโชว์ขีด —)
        $rate = DB::table('zcolorrate')
            ->where('colorno', $itemno)
            ->first(['rate_A', 'rate_B', 'rate_C']);

        // DB 3-4 Kg. / DB 1-2 Kg. — ไม่มีที่เก็บใน appvreq จึงคำนวณสดจากระบบกำหนดราคา
        // (ตัวเดียวกับฟอร์ม "กำหนดราคา" / ฟอร์มขออนุมัติราคาพิเศษ) · คำนวณไม่ได้ = null — 12/10/2569
        $calc = app(\App\Services\ProductPriceService::class)->lookup((string) $itemno);

        return [
            'db_3_4'      => $calc['prices']['db_3_4'] ?? null,
            'db_1_2'      => $calc['prices']['db_1_2'] ?? null,
            'color_rate'  => $rate ? ['A' => $rate->rate_A, 'B' => $rate->rate_B, 'C' => $rate->rate_C] : null,
            'fixed_price' => $uprice->PRICE ?? null,
            'rem1'        => $uprice->REM1 ?? null,
            'rem2'        => $uprice->REM2 ?? null,
            'price1'      => $appv->price1 ?? null,
            'price2'      => $appv->price2 ?? null,
            'price3'      => $appv->price3 ?? null,
        ];
    }

    /**
     * GET — ปุ่ม "พิมพ์รายงานที่ต้องผลิต" บนหน้า /order (06/10/2569, เพิ่มตัวเลือกก่อนพิมพ์ 07/10/2569)
     *   รายงาน PDF "รายงานผลิตภัณฑ์ที่ต้องผลิต (P)" ตามกระดาษของระบบเดิม (MK01-FM04.04)
     *
     *   ตัวเลือก (หน้าต่างก่อนพิมพ์ — ตามฟอร์ม Access เดิม):
     *     ?dept=DB                แผนกที่ผลิต (morder.Company) — ไม่ส่ง = ทุกแผนก แยกหน้าตามแผนก
     *     ?date=02/10/2026        วันที่ (d/m/Y หรือ Y-m-d) — ไม่ส่ง = วันนี้
     *     ?time_from=08:00&time_to=18:00   ช่วงเวลา (ค่าเริ่มต้น 08:00–18:00)
     *     ?type=P                 P = น้ำหนักผลิต (suborder.Production) · S = สต๊อก (suborder.Stock)
     *                             · RP = น้ำหนักรวม S+P เฉพาะใบที่ติ๊ก RP/Spec/Cer/MSDS อย่างน้อย 1 (20/10/2569)
     *
     *   เงื่อนไข: ใบสั่งซื้อที่ **อนุมัติ** ในวัน+ช่วงเวลานั้น (morder.appvDT) เรียงตามเลขที่ใบสั่ง
     *   ไม่รวมใบจอง R (approvableQuery) และ **ไม่รวมใบ HE** (ตามป้าย "ไม่รวม HE" บนฟอร์ม Access เดิม)
     *   1 แถว = 1 ใบ — เอาเฉพาะรายการแรกของใบ (suborder.Runno น้อยสุด) และต้องมีน้ำหนักของชนิดที่เลือก (> 0)
     *   ท้ายแต่ละแผนก: รวมน้ำหนัก · ช่องเซ็น · "รายการสินค้าที่สั่งซ้ำ" (รหัสสินค้าเดียวกันเกิน 1 รายการ)
     */
    public function approvedTodayPdf(Request $request)
    {
        $day      = self::parseReportDate((string) $request->query('date', ''));
        $timeFrom = self::parseReportTime((string) $request->query('time_from', ''), '08:00');
        $timeTo   = self::parseReportTime((string) $request->query('time_to', ''), '18:00');
        $type     = strtoupper((string) $request->query('type', 'P'));
        $type     = in_array($type, ['S', 'RP'], true) ? $type : 'P';
        $dept     = trim((string) $request->query('dept', ''));

        $from = $day->copy()->setTimeFromTimeString($timeFrom . ':00');
        $to   = $day->copy()->setTimeFromTimeString($timeTo . ':59');

        // ชื่อคอลัมน์มาจากค่าคงที่เหล่านี้เท่านั้น (ไม่ได้เอาค่าจาก request ไปต่อ SQL)
        //   RP (20/10/2569 ผู้ใช้สั่ง) = น้ำหนักรวม S + P · เฉพาะใบที่ติ๊ก RP / Spec / Cer / MSDS อย่างน้อย 1 ช่อง
        $weightCol = match ($type) {
            'S'     => 's.Stock',
            'RP'    => '(COALESCE(s.Stock, 0) + COALESCE(s.Production, 0))',
            default => 's.Production',
        };

        $rows = $this->approvableQuery()
            ->join('suborder as s', 's.Orderno', '=', 'morder.Orderno')
            ->leftJoin('customer as c', 'c.code', '=', 'morder.Custno')
            ->whereNotNull('morder.appv')
            ->where('morder.appv', '<>', 0)            // Access เก็บ -1 = อนุมัติแล้ว
            ->whereBetween('morder.appvDT', [$from, $to])
            ->whereRaw('LEFT(morder.Orderno, 2) <> ?', ['HE'])   // "ไม่รวม HE" ตามฟอร์มเดิม
            ->when($dept !== '', fn ($q) => $q->where('morder.Company', $dept))
            // 1 ใบ = 1 แถว: เอาเฉพาะ "รายการแรก" ของใบ (Runno น้อยสุด) แม้ใบจะมีหลายรายการ (07/10/2569 ผู้ใช้สั่ง)
            ->whereRaw('s.Runno = (SELECT MIN(s0.Runno) FROM suborder s0 WHERE s0.Orderno = morder.Orderno)')
            ->whereRaw($weightCol . ' > 0')
            // RP: ติ๊กอย่างน้อย 1 ใน RP / Spec / Cer / MSDS (checkbox แบบ Access: ไม่ใช่ 0/NULL = ติ๊ก)
            ->when($type === 'RP', fn ($q) => $q->where(function ($w) {
                foreach (['RP', 'Spec', 'Cer', 'MSDS'] as $f) {
                    $w->orWhere(fn ($x) => $x->whereNotNull('morder.' . $f)->where('morder.' . $f, '<>', 0));
                }
            }))
            ->orderBy('morder.Orderno')
            ->orderBy('s.Runno')
            ->get([
                'morder.Orderno', 'morder.Mdate', 'morder.appvDT', 'morder.Company', 'morder.Custno', 'morder.supno',
                'morder.Send', 'morder.RP', 'morder.Spec', 'morder.Cer', 'morder.MSDS',
                'c.name as custname',
                's.Itemno', 's.Lotno', DB::raw($weightCol . ' as weight'), 's.senddate', 's.custwant', 's.Remark',
            ]);

        $alist = self::alistCustomers($rows->pluck('Custno')->unique()->all());

        foreach ($rows as $r) {
            // "ซื้อครั้งก่อน" = Lot ของครั้งล่าสุดที่ **ลูกค้ารายนี้** สั่งสินค้านี้ก่อนใบนี้ (07/10/2569 ผู้ใช้ยืนยัน)
            //   ไม่เคยสั่ง = NEW · ครั้งล่าสุดห่างจากใบนี้เกิน 3 ปี = "เกิน 3 ปี"
            $prev = DB::table('suborder as s2')
                ->join('morder as m2', 'm2.Orderno', '=', 's2.Orderno')
                ->where('s2.Itemno', $r->Itemno)
                ->where('m2.Custno', $r->Custno)
                ->where('m2.Orderno', '<>', $r->Orderno)
                ->where('m2.Mdate', '<', $r->Mdate)
                ->orderByDesc('m2.Mdate')
                ->first(['m2.Mdate', 's2.Lotno']);

            if (!$prev) {
                $r->last_label = 'NEW';
            } elseif (Carbon::parse($prev->Mdate)->lt(Carbon::parse($r->Mdate)->subYears(3))) {
                $r->last_label = 'เกิน 3 ปี';
            } else {
                $r->last_label = trim((string) $prev->Lotno);
            }

            // กำหนดผลิตเสร็จ = วันที่กำหนดส่งทบทวน (senddate) - 1 วัน (07/10/2569 ผู้ใช้ยืนยัน)
            //   ส่วน "กำหนดลูกค้าต้องการใช้" ในรายงาน = senddate ตรง ๆ
            $r->finish_date = $r->senddate ? Carbon::parse($r->senddate)->subDay() : null;

            // กล่องหลังรหัสลูกค้า — ลูกค้าที่อยู่ในตาราง Alist ขึ้น "A"
            $r->is_alist = isset($alist[trim((string) $r->Custno)]);

            // checkbox แบบ Access: -1 = ติ๊ก
            foreach (['Send', 'RP', 'Spec', 'Cer', 'MSDS'] as $f) {
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
                        'weight' => $g->sum(fn ($r) => (float) $r->weight),
                    ])
                    ->sortBy('itemno')
                    ->values();

                return (object) [
                    'dept'    => $dept,
                    'items'   => $items->values(),
                    'total'   => $items->sum(fn ($r) => (float) $r->weight),
                    'repeats' => $repeats,
                ];
            })
            ->values();

        // ไม่มีข้อมูล → คง 1 section ว่างไว้พิมพ์หัวรายงานของแผนกที่เลือก
        if ($sections->isEmpty()) {
            $sections = collect([(object) [
                'dept' => $dept, 'items' => collect(), 'total' => 0, 'repeats' => collect(),
            ]]);
        }

        $html = view('order.order-approved-today-pdf', [
            'sections'  => $sections,
            'day'       => $day,
            'time_from' => $timeFrom,
            'time_to'   => $timeTo,
            'type'      => $type,
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

    /** วันที่ของรายงาน — รับ d/m/Y (จากช่อง flatpickr) หรือ Y-m-d · อ่านไม่ได้/ไม่ส่ง = วันนี้ */
    private static function parseReportDate(string $value): Carbon
    {
        $value = trim($value);
        foreach (['d/m/Y', 'Y-m-d'] as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $value);
                if ($d && $d->format($fmt) === $value) {
                    return $d->startOfDay();
                }
            } catch (\Exception $e) {
                // ลองรูปแบบถัดไป
            }
        }

        return now()->startOfDay();
    }

    /** เวลาของรายงาน — รับเฉพาะ HH:MM (24 ชม.) ไม่ตรงรูปแบบ = ค่าเริ่มต้น */
    private static function parseReportTime(string $value, string $default): string
    {
        $value = trim($value);

        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) ? $value : $default;
    }

    /**
     * รหัสลูกค้าที่อยู่ในตาราง Alist (คืนเป็น map รหัส => true) — ใช้ขึ้น "A" ในรายงานที่ต้องผลิต
     *
     * ⚠ 07/10/2569: ตาราง Alist **ยังไม่มีใน DB เครื่อง dev** (และไม่มีในไฟล์ dump ที่ public/sql)
     *   จึงตรวจก่อนว่ามีตาราง + หาคอลัมน์รหัสลูกค้าจากชื่อที่เป็นไปได้ — ไม่มีตาราง/ไม่เจอคอลัมน์ = ไม่ขึ้น A
     *   (ไม่ throw ให้รายงานพัง) · ได้โครงตารางจริงแล้วให้แก้รายชื่อคอลัมน์ที่นี่ที่เดียว
     */
    private static function alistCustomers(array $custnos): array
    {
        $custnos = array_values(array_filter(array_map(fn ($c) => trim((string) $c), $custnos), 'strlen'));
        if (!$custnos) {
            return [];
        }

        foreach (['Alist', 'alist', 'AList'] as $table) {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                continue;
            }
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            foreach (['custno', 'code', 'custid', 'cust_no', 'customer'] as $want) {
                foreach ($columns as $col) {
                    if (strtolower($col) === $want) {
                        return DB::table($table)->whereIn($col, $custnos)->pluck($col)
                            ->mapWithKeys(fn ($c) => [trim((string) $c) => true])->all();
                    }
                }
            }

            return [];
        }

        return [];
    }
}
