<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ProductPriceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
        $validTo = $this->parseDate($request->input('valid_to'));

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
