<?php

namespace App\Http\Controllers;

use App\Models\CreditTerm;
use App\Models\Emp;
use App\Services\AccessControl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ขอเปลี่ยนแปลง ข้อมูล Black List / เปลี่ยนแปลงเครดิต — 02/11/2569 (รวมเครดิต + ย้ายไป appvcredit 03/11/2569)
 *
 * รวม 2 ฟอร์ม Access เดิม:
 *   "Black List 17.05.53"  เขียน customer.Black / BlackDate / Blackrem ตรง ๆ (ไม่มีใบขอ)
 *   "ขออนุมัติเครดิต"       ใบขอเก็บที่ appvcredit (Term / ส่วนลด / ขอเปลี่ยนเป็นปกติ) แล้วผู้บริหารอนุมัติ
 * ทำเป็น 2 ขั้นแบบฟอร์ม "MK ขออนุมัติราคาพิเศษ":
 *   1. ขอ    — เก็บใบขอที่ appvcredit (PK = ReqDate + custno, MyISAM)
 *   2. อนุมัติ — กรอกรหัสเข้าโหมดอนุมัติ (รหัสเดียวกับฟอร์ม MK: config order.md_password)
 *               ติ๊กอนุมัติ → เขียน customer.term / cashdisc / black / blackdate / blackrem
 *
 * คอลัมน์ appvcredit ของเดิม: TermOld/New · CashDiscOld/New · Remark (สาเหตุ, MK) · Appv (-1 = อนุมัติ)
 *   AppvRem (หมายเหตุผู้บริหาร) · black (สถานะเดิมเป็น "ข้อความ" — ดู STATUSES.legacy) · BlackRemOld
 *   BlackNew (-1 = ขอเปลี่ยนเป็นปกติ) · TypeChg (ไม่เคยมีค่า ไม่ใช้)
 * คอลัมน์ที่เพิ่ม (migration add_blacklist_request_columns_to_appvcredit): BlackNewVal / BlackDateNew / BlackRemNew
 *   ReqBy / ReqByName / AppvDT / AppvBy / AppvByName
 *
 * ช่องข้อมูลลูกค้าบนฟอร์ม (ช่องสีขาวของฟอร์มเดิม) แสดงอย่างเดียว
 */
class CustomerStatusController extends Controller
{
    /**
     * ค่าของ customer.black — ผู้ใช้ยืนยันแล้ว (03/11/2569)
     *   legacy = ข้อความที่โปรแกรมเดิมเก็บใน appvcredit.black (ตรงกับข้อมูลจริง 4 ค่า · ปกติ = NULL)
     * ลำดับ = ลำดับบนฟอร์ม Access เดิม · แก้ความหมายที่นี่จุดเดียว
     */
    public const STATUSES = [
        0  => ['label' => 'ปกติ', 'color' => 'success', 'legacy' => null],
        2  => ['label' => 'ยอดค้างเกิน Due > 30 วัน', 'color' => 'info', 'legacy' => 'เกิน Due > 30 วัน'],
        -3 => ['label' => 'หยุดสั่งซื้อระยะหนึ่งแล้ว ให้ทบทวนเครดิต', 'color' => 'warning', 'legacy' => 'ให้ทบทวนเครดิต'],
        -1 => ['label' => 'Black List', 'color' => 'danger', 'legacy' => 'ขึ้นบัญชีดำ'],
        5  => ['label' => 'มีสีค้างส่ง', 'color' => 'secondary', 'legacy' => 'สีค้างส่ง'],
    ];

    public const STATUS_NORMAL = 0;

    /** ค่า "มีสีค้างส่ง" — ฟอร์มเดิมเตือนให้ใส่เบอร์ที่ค้างส่งในช่อง Remark */
    public const STATUS_PENDING_COLOR = 5;

    /** แยก session ของตัวเอง (รหัสเดียวกับฟอร์ม MK แต่ปลดล็อกแยกกัน) */
    private const MD_SESSION_KEY = 'customer_status_md_until';

    private const MD_UNLOCK_MINUTES = 30;

    /** คอลัมน์ลูกค้าที่โชว์บนฟอร์ม (ช่องสีขาว — อ่านอย่างเดียว) */
    private const CUSTOMER_COLS = [
        'code', 'name', 'no', 'road', 'amphur', 'city', 'zip', 'tel', 'fax', 'sale', 'term',
        'remark', 'zone', 'condition', 'cashdisc', 'type', 'flprt', 'black', 'blackdate', 'blackrem',
    ];

    public static function statusLabel($value): string
    {
        if ($value === null || $value === '') {
            return 'ไม่ระบุ';
        }

        return self::STATUSES[(int) $value]['label'] ?? ('ค่า ' . $value);
    }

    /** ใบที่ยังไม่อนุมัติ (ตาม query ของโปรแกรมเดิม: Appv <> -1 Or Appv Is Null) */
    private static function wherePending($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('Appv')->orWhere('Appv', '<>', -1);
        });
    }

    // ─────────────────────────────────────────────────────────────
    //  โหมดอนุมัติ
    // ─────────────────────────────────────────────────────────────

    private function checkMdPassword($input): bool
    {
        $expected = (string) config('order.md_password', '');

        return $expected !== '' && hash_equals($expected, (string) $input);
    }

    private function mdUnlocked(): bool
    {
        $until = session(self::MD_SESSION_KEY);

        return $until && Carbon::parse($until)->isFuture();
    }

    public function mdState()
    {
        return response()->json(['unlocked' => $this->mdUnlocked()]);
    }

    public function unlock(Request $request)
    {
        if (!$this->checkMdPassword($request->input('md_password'))) {
            return response()->json(['status' => false, 'message' => 'รหัสผ่านไม่ถูกต้อง'], 422);
        }

        session([self::MD_SESSION_KEY => now()->addMinutes(self::MD_UNLOCK_MINUTES)->format('Y-m-d H:i:s')]);

        return response()->json([
            'status'  => true,
            'message' => 'เข้าสู่โหมดอนุมัติแล้ว (' . self::MD_UNLOCK_MINUTES . ' นาที)',
        ]);
    }

    public function lock()
    {
        session()->forget(self::MD_SESSION_KEY);

        return response()->json(['status' => true, 'message' => 'ออกจากโหมดอนุมัติแล้ว']);
    }

    // ─────────────────────────────────────────────────────────────
    //  ข้อมูล
    // ─────────────────────────────────────────────────────────────

    /** GET — คิวใบที่รออนุมัติ (ตัวเดินระเบียน + badge บนปุ่ม) เรียง ReqDate ตามโปรแกรมเดิม */
    public function pending()
    {
        $rows = self::wherePending(DB::table('appvcredit as a'))
            ->leftJoin('customer as c', 'c.code', '=', 'a.custno')
            ->orderBy('a.ReqDate')
            ->get(['a.ReqDate', 'a.custno', 'c.name']);

        return response()->json(['rows' => $rows, 'md_unlocked' => $this->mdUnlocked()]);
    }

    /** GET ?custno= — ข้อมูลลูกค้า + ใบขอที่ค้างอยู่ (ถ้ามี) + ประวัติ */
    public function data(Request $request)
    {
        $custno = trim((string) $request->input('custno'));

        $customer = $custno === ''
            ? null
            : DB::table('customer')->where('code', $custno)->first(self::CUSTOMER_COLS);

        if (!$customer) {
            return response()->json(['customer' => null, 'md_unlocked' => $this->mdUnlocked()]);
        }

        $customer->status_label = self::statusLabel($customer->black);
        $customer->blackdate    = $customer->blackdate ? Carbon::parse($customer->blackdate)->format('d/m/Y') : null;
        $customer->type_name    = DB::table('c_type')->where('type', $customer->type)->value('t_namee');

        // ระบุ req_date = เปิดใบนั้น (ลูกค้า 1 รายมีใบรออนุมัติได้หลายใบ) · ไม่ระบุ/ไม่พบ = ใบล่าสุด
        $req = $this->pendingRow($custno, $request->input('req_date'))
            ?? $this->pendingRow($custno);
        $pendingCount = self::wherePending(DB::table('appvcredit')->where('custno', $custno))->count();
        if ($req) {
            // ใบเก่าของโปรแกรมเดิม: ไม่มี BlackNewVal แต่ติ๊ก "ขอเปลี่ยนเป็นปกติ" (BlackNew = -1)
            if ($req->BlackNewVal === null && CustomerController::checked($req->BlackNew)) {
                $req->BlackNewVal = self::STATUS_NORMAL;
            }
            $req->BlackDateNew = $req->BlackDateNew ? Carbon::parse($req->BlackDateNew)->format('d/m/Y') : null;
            $req->ReqDateText  = Carbon::parse($req->ReqDate)->format('d/m/Y H:i');
        }

        $history = DB::table('appvcredit')
            ->where('custno', $custno)
            ->orderByDesc('ReqDate')
            ->limit(30)
            ->get()
            ->map(function ($h) {
                $newVal = $h->BlackNewVal;
                if ($newVal === null && CustomerController::checked($h->BlackNew)) {
                    $newVal = self::STATUS_NORMAL;
                }

                return [
                    'req_date'   => Carbon::parse($h->ReqDate)->format('d/m/y H:i'),
                    'req_by'     => $h->ReqByName,
                    'term'       => $this->changeText($h->TermOld, $h->TermNew),
                    'cashdisc'   => $this->changeText($h->CashDiscOld, $h->CashDiscNew),
                    'status'     => $newVal === null ? '' : (($h->black ?: 'ปกติ') . ' → ' . self::statusLabel($newVal)),
                    'remark'     => $h->Remark,
                    'appv_rem'   => $h->AppvRem,
                    'approved'   => CustomerController::checked($h->Appv),
                    'appv_at'    => $h->AppvDT ? Carbon::parse($h->AppvDT)->format('d/m/y H:i') : '',
                    'appv_by'    => $h->AppvByName,
                ];
            });

        return response()->json([
            'customer'    => $customer,
            'request'     => $req,
            'pending_count' => $pendingCount,
            'history'     => $history,
            'md_unlocked' => $this->mdUnlocked(),
        ]);
    }

    /** ตัวเลือกช่อง Term (ใหม่) — ส่งให้ blade */
    public static function termOptions()
    {
        return CreditTerm::activeList();
    }

    /**
     * POST — บันทึกใบขอ / อนุมัติ
     *   new=1 (ปุ่ม "สร้างใบขอใหม่", 03/11/2569) = ขึ้นใบใหม่เสมอ — ลูกค้า 1 รายมีใบรออนุมัติได้หลายใบ
     *   ไม่ใช่ new: req_date = แก้ใบนั้น · ไม่ส่ง = แก้ใบรออนุมัติล่าสุด (ไม่มี = ขึ้นใบใหม่, ReqDate = ตอนนี้)
     *   appv=1 → ต้องอยู่ในโหมดอนุมัติ (ตรวจ session) แล้วเขียนลง customer
     *   AppvRem (หมายเหตุผู้บริหาร) เขียนได้เฉพาะโหมดอนุมัติ
     */
    public function save(Request $request)
    {
        $custno = trim((string) $request->input('custno'));

        $customer = $custno === ''
            ? null
            : DB::table('customer')->where('code', $custno)->first(['code', 'term', 'cashdisc', 'black', 'blackdate', 'blackrem']);

        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'ไม่พบรหัสลูกค้า ' . $custno], 422);
        }

        $mdMode = $this->mdUnlocked();
        $appv   = filter_var($request->input('appv'), FILTER_VALIDATE_BOOLEAN);

        // ด่านจริงอยู่ที่ session — จอปลดล็อก checkbox เองก็อนุมัติไม่ได้
        if ($appv && !$mdMode) {
            return response()->json([
                'status'    => false,
                'md_locked' => true,
                'message'   => 'โหมดอนุมัติหมดอายุแล้ว กรุณากรอกรหัสเพื่อเข้าสู่โหมดอนุมัติใหม่',
            ], 422);
        }

        // ── เครดิต ──
        $termNew = trim((string) $request->input('term_new'));
        $termNew = $termNew === '' ? null : mb_substr($termNew, 0, 100);

        $discRaw = str_replace(',', '', trim((string) $request->input('cashdisc_new')));
        if ($discRaw !== '' && (!is_numeric($discRaw) || $discRaw < 0 || $discRaw > 100)) {
            return response()->json(['status' => false, 'message' => 'ส่วนลด (ใหม่) ต้องเป็นตัวเลข 0 - 100'], 422);
        }
        $discNew = $discRaw === '' ? null : (float) $discRaw;

        // ── สถานะ Black List ──
        $oldBlack = $customer->black === null ? null : (int) $customer->black;
        $oldDate  = $customer->blackdate ? Carbon::parse($customer->blackdate)->format('Y-m-d') : null;

        // ไม่เลือกสถานะ = คงเดิม (ลูกค้าที่ black เป็น NULL ยังขอเปลี่ยนแค่เครดิตได้)
        $newBlack = $request->input('new_black');
        if ($newBlack === null || $newBlack === '') {
            $newBlack = $oldBlack;
        } elseif (!isset(self::STATUSES[(int) $newBlack])) {
            return response()->json(['status' => false, 'message' => 'สถานะไม่ถูกต้อง'], 422);
        } else {
            $newBlack = (int) $newBlack;
        }

        $rem = trim((string) $request->input('new_blackrem'));
        $rem = $rem === '' ? null : mb_substr($rem, 0, 50);

        // ไม่กรอกวันที่: สถานะเดิม = คงวันที่เดิม · เปลี่ยนสถานะ = วันนี้
        $date = $this->parseDate($request->input('new_blackdate'))
            ?? ($newBlack === $oldBlack ? $oldDate : now()->format('Y-m-d'));

        $blackChange = $newBlack !== null && (
            $newBlack !== $oldBlack
            || (string) $rem !== trim((string) $customer->blackrem)
            || $date !== $oldDate
        );

        if ($blackChange && $newBlack === self::STATUS_PENDING_COLOR && $rem === null) {
            return response()->json(['status' => false, 'message' => 'กรุณาใส่เบอร์ที่ค้างส่งที่ช่อง Black Remark ด้วย'], 422);
        }

        $termChange = $termNew !== null && $termNew !== trim((string) $customer->term);
        $discChange = $discNew !== null && (float) $customer->cashdisc !== $discNew;

        if (!$blackChange && !$termChange && !$discChange) {
            return response()->json(['status' => false, 'message' => 'ยังไม่ได้แก้ไขอะไร — Term / ส่วนลด / สถานะ ตรงกับข้อมูลลูกค้าปัจจุบัน'], 422);
        }

        $remark = trim((string) $request->input('remark'));
        $remark = $remark === '' ? null : mb_substr($remark, 0, 100);

        $data = [
            'TermNew'      => $termNew,
            'CashDiscNew'  => $discNew,
            'Remark'       => $remark,
            'BlackNewVal'  => $blackChange ? $newBlack : null,
            'BlackDateNew' => $blackChange ? $date : null,
            'BlackRemNew'  => $blackChange ? $rem : null,
            // ให้ข้อมูลตรงกับโปรแกรมเดิม: -1 = ขอเปลี่ยนเป็นปกติ
            'BlackNew'     => ($blackChange && $newBlack === self::STATUS_NORMAL && $oldBlack !== self::STATUS_NORMAL) ? '-1' : null,
        ];

        if ($mdMode) {
            $appvRem = trim((string) $request->input('appv_rem'));
            $data['AppvRem'] = $appvRem === '' ? null : mb_substr($appvRem, 0, 100);
        }

        [$by, $byName] = $this->currentAccountInfo();

        // new=1 (ปุ่ม "สร้างใบขอใหม่") = ขึ้นใบใหม่เสมอ · ไม่งั้นแก้ใบที่ระบุด้วย req_date (ไม่ระบุ = ใบรออนุมัติล่าสุด)
        $isNew    = filter_var($request->input('new'), FILTER_VALIDATE_BOOLEAN);
        $existing = $isNew ? null : $this->pendingRow($custno, $request->input('req_date'));

        if (!$isNew && $request->filled('req_date') && !$existing) {
            return response()->json(['status' => false, 'message' => 'ไม่พบใบขอนี้ (อาจถูกอนุมัติหรือลบไปแล้ว)'], 422);
        }

        if ($existing) {
            $reqDate = $existing->ReqDate;
            DB::table('appvcredit')->where('ReqDate', $reqDate)->where('custno', $custno)->update($data);
        } else {
            // PK = ReqDate + custno (ละเอียดแค่วินาที) — กดซ้ำในวินาทีเดียวกันให้เลื่อนไปวินาทีถัดไป
            $at = now();
            while (DB::table('appvcredit')->where('ReqDate', $at->format('Y-m-d H:i:s'))->where('custno', $custno)->exists()) {
                $at->addSecond();
            }
            $reqDate = $at->format('Y-m-d H:i:s');
            DB::table('appvcredit')->insert($data + [
                'ReqDate'     => $reqDate,
                'custno'      => $custno,
                'TermOld'     => $customer->term,
                'CashDiscOld' => $customer->cashdisc,
                'black'       => $oldBlack === null ? null : (self::STATUSES[$oldBlack]['legacy'] ?? (string) $oldBlack),
                'BlackRemOld' => $customer->blackrem,
                'ReqBy'       => $by,
                'ReqByName'   => $byName,
            ]);
        }

        if (!$appv) {
            return response()->json([
                'status'  => true,
                'message'  => $existing ? 'แก้ไขใบขอเรียบร้อย (รออนุมัติ)' : 'เพิ่มใบขอเรียบร้อย (รออนุมัติ)',
                'req_date' => $reqDate,
            ]);
        }

        // อนุมัติ — MyISAM ไม่มี transaction: เขียน customer ก่อน แล้วค่อยปิดใบ
        $custData = [];
        if ($termNew !== null) {
            $custData['term'] = $termNew;
        }
        if ($discNew !== null) {
            $custData['cashdisc'] = (int) round($discNew);   // customer.cashdisc เป็น tinyint
        }
        if ($blackChange) {
            $custData += ['black' => $newBlack, 'blackdate' => $date, 'blackrem' => $rem];
        }
        if ($custData) {
            DB::table('customer')->where('code', $custno)->update($custData);
        }

        DB::table('appvcredit')->where('ReqDate', $reqDate)->where('custno', $custno)->update([
            'Appv'       => -1,
            'AppvDT'     => now(),
            'AppvBy'     => $by,
            'AppvByName' => $byName,
        ]);

        return response()->json(['status' => true, 'message' => 'อนุมัติแล้ว — ปรับข้อมูลลูกค้าเรียบร้อย', 'req_date' => $reqDate]);
    }

    /** POST — ลบใบขอที่ยังไม่อนุมัติ (ใบที่อนุมัติแล้วเป็นประวัติ ลบไม่ได้) */
    public function destroy(Request $request)
    {
        $custno = trim((string) $request->input('custno'));
        $row    = $this->pendingRow($custno, $request->input('req_date'));

        if (!$row) {
            return response()->json(['status' => false, 'message' => 'ไม่พบใบขอที่รออนุมัติ'], 422);
        }

        DB::table('appvcredit')->where('ReqDate', $row->ReqDate)->where('custno', $custno)->delete();

        return response()->json(['status' => true, 'message' => 'ลบใบขอเรียบร้อย']);
    }

    // ─────────────────────────────────────────────────────────────

    /** ใบรออนุมัติของลูกค้า — ระบุ ReqDate = ใบนั้น · ไม่ระบุ = ใบล่าสุด */
    private function pendingRow(string $custno, $reqDate = null)
    {
        if ($custno === '') {
            return null;
        }

        $query = self::wherePending(DB::table('appvcredit')->where('custno', $custno));

        $reqDate = trim((string) $reqDate);
        if ($reqDate !== '') {
            $query->where('ReqDate', $reqDate);
        }

        return $query->orderByDesc('ReqDate')->first();
    }

    private function changeText($old, $new): string
    {
        $old = trim((string) $old);
        $new = trim((string) $new);
        if ($new === '' || $new === $old) {
            return '';
        }

        return ($old === '' ? '-' : $old) . ' → ' . $new;
    }

    /** d/m/Y หรือ Y-m-d → Y-m-d (ผิดรูปแบบ = null) */
    private function parseDate($value): ?string
    {
        $value = trim((string) $value);
        foreach (['d/m/Y', 'Y-m-d'] as $fmt) {
            $d = \DateTime::createFromFormat('!' . $fmt, $value);
            if ($d && $d->format($fmt) === $value) {
                return $d->format('Y-m-d');
            }
        }

        return null;
    }

    /** ผู้ทำรายการ → [รหัส, ชื่อ] · พนักงาน = empno · admin = U<users.id> */
    private function currentAccountInfo(): array
    {
        $account = AccessControl::currentAccount();

        if ($account instanceof Emp) {
            return [$account->empno, trim($account->empname . ' ' . $account->empsur) ?: $account->empno];
        }
        if ($account) {
            return ['U' . $account->id, $account->name];
        }

        return [null, null];
    }
}
