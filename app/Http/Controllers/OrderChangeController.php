<?php

namespace App\Http\Controllers;

use App\Models\Emp;
use App\Models\OrderChangeLine;
use App\Services\AccessControl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * การเปลี่ยนแปลงคำสั่งซื้อ (20/10/2569) — แทนฟอร์ม Access "การเปลี่ยนแปลงคำสั่งซื้อ" ที่เก็บได้แค่ 3 ครั้ง
 *
 * suborder = สถานะปัจจุบัน (senddate = กำหนดส่งปัจจุบัน, Stock/Production = น้ำหนักปัจจุบัน)
 * tb_order_change + tb_order_change_line = ประวัติทุกครั้ง
 *
 * 3 การกระทำ (1 ครั้งที่กดบันทึก = 1 ใบเปลี่ยนแปลง):
 *   postpone  เลื่อนกำหนดส่งของรายการที่เลือก (เลือกได้หลายรายการพร้อมกัน)
 *   split     แยกน้ำหนักบางส่วนของ 1 รายการออกเป็นแถว suborder ใหม่ พร้อมกำหนดส่งของส่วนที่แยก
 *             (แถวใหม่มี parent_runno ชี้กลับแถวต้นทาง — แยกซ้อนได้ไม่จำกัดชั้น)
 *   weight    เปลี่ยนน้ำหนัก S / P ของ 1 รายการ (morder.netqty ปรับตามส่วนต่าง)
 *
 * ไม่มีขั้นอนุมัติ — บันทึกแล้วมีผลทันที
 * ⚠ suborder / morder เป็น MyISAM ⇒ ทรานแซกชันย้อนได้แค่ตาราง tb_order_change* (InnoDB)
 */
class OrderChangeController extends Controller
{
    /** prefix ของเลขที่ใบเมื่อใบสั่งไม่ได้ระบุแผนก */
    private const DEFAULT_PREFIX = 'OC';

    /**
     * Runno ของรายการที่มีประวัติการเปลี่ยนแปลงแล้ว (เป็นต้นทาง / เป็นแถวที่แยกออกมา)
     * — ฟอร์มใบสั่งซื้อห้ามแก้น้ำหนัก/กำหนดส่ง และห้ามลบรายการเหล่านี้ (ต้องทำผ่านฟอร์มนี้)
     */
    public static function lockedRunnos(string $orderno): array
    {
        $fromLines = DB::table('tb_order_change_line')->where('orderno', $orderno)
            ->get(['runno', 'split_runno']);

        $ids = [];
        foreach ($fromLines as $l) {
            $ids[] = (int) $l->runno;
            if ($l->split_runno) {
                $ids[] = (int) $l->split_runno;
            }
        }
        $children = DB::table('suborder')->where('Orderno', $orderno)->whereNotNull('parent_runno')->pluck('Runno');
        foreach ($children as $r) {
            $ids[] = (int) $r;
        }

        return array_values(array_unique($ids));
    }

    // ─────────────────────────────────────────────────────────────
    //  GET — ข้อมูลของใบสั่ง + รายการ + ประวัติ (เติมฟอร์ม)
    // ─────────────────────────────────────────────────────────────
    public function data(Request $request)
    {
        $orderno = trim((string) $request->query('orderno', ''));
        $order = $orderno === '' ? null : DB::table('morder')
            ->leftJoin('customer', 'customer.code', '=', 'morder.Custno')
            ->where('morder.Orderno', $orderno)
            ->first([
                'morder.Orderno', 'morder.Mdate', 'morder.Company', 'morder.PO', 'morder.Custno',
                'customer.name as custname', 'morder.DVpoint', 'morder.Emp', 'morder.supno',
                'morder.Send', 'morder.RP', 'morder.Spec', 'morder.Cer', 'morder.netqty',
            ]);

        if (!$order) {
            return response()->json(['found' => false]);
        }

        $counts = DB::table('tb_order_change_line')->where('orderno', $orderno)
            ->groupBy('runno')->selectRaw('runno, COUNT(*) as n')->pluck('n', 'runno');

        $items = DB::table('suborder')->where('Orderno', $orderno)->orderBy('Runno')
            ->get(['Runno', 'Itemno', 'Lotno', 'Stock', 'Production', 'custwant', 'senddate',
                   'EndP', 'DVDate', 'Remark', 'parent_runno'])
            ->map(function ($r) use ($counts) {
                $r->change_count = (int) ($counts[$r->Runno] ?? 0);

                return $r;
            });

        $flag = fn ($v) => $v !== null && (int) $v !== 0;
        foreach (['Send', 'RP', 'Spec', 'Cer'] as $f) {
            $order->{$f} = $flag($order->{$f});
        }

        return response()->json([
            'found'   => true,
            'order'   => $order,
            'items'   => $this->sortAsTree($items),
            'history' => $this->history($orderno),
            'actions' => OrderChangeLine::ACTIONS,
        ]);
    }

    /** เรียงแถวแบบต้นไม้ (แถวที่แยกออกมาอยู่ใต้แถวต้นทาง) + ใส่ depth ให้ฝั่งจอเยื้อง */
    private function sortAsTree($items): array
    {
        $byParent = [];
        $runnos = $items->pluck('Runno')->map(fn ($v) => (int) $v)->all();
        foreach ($items as $r) {
            // parent ที่ไม่อยู่ในใบนี้แล้ว (ถูกลบ) → ถือเป็นแถวบนสุด
            $p = ($r->parent_runno && in_array((int) $r->parent_runno, $runnos, true)) ? (int) $r->parent_runno : 0;
            $byParent[$p][] = $r;
        }

        $out = [];
        $walk = function ($parent, $depth) use (&$walk, &$out, $byParent) {
            foreach ($byParent[$parent] ?? [] as $r) {
                $r->depth = $depth;
                $out[] = $r;
                $walk((int) $r->Runno, $depth + 1);
            }
        };
        $walk(0, 0);

        return $out;
    }

    /** ประวัติของใบสั่งนี้ — ใบใหม่→เก่า แต่ละใบพร้อมบรรทัด */
    private function history(string $orderno): array
    {
        $lines = DB::table('tb_order_change_line as l')
            ->join('tb_order_change as h', 'h.id', '=', 'l.change_id')
            ->leftJoin('suborder as s', function ($j) {
                $j->on('s.Runno', '=', 'l.runno')->on('s.Orderno', '=', 'l.orderno');
            })
            ->where('l.orderno', $orderno)
            ->orderByRaw('COALESCE(h.change_date, h.created_at) DESC')
            ->orderByDesc('h.id')->orderBy('l.id')
            ->get([
                'h.id as change_id', 'h.change_no', 'h.change_date', 'h.reason', 'h.source',
                'h.created_by_name', 'h.created_at',
                'l.runno', 'l.action', 'l.old_senddate', 'l.new_senddate',
                'l.old_stock', 'l.old_prod', 'l.new_stock', 'l.new_prod',
                'l.split_runno', 'l.split_stock', 'l.split_prod', 'l.remark',
                's.Itemno', 's.Lotno',
            ]);

        $docs = [];
        foreach ($lines as $l) {
            if (!isset($docs[$l->change_id])) {
                $docs[$l->change_id] = [
                    'change_no'   => $l->change_no,
                    'change_date' => $l->change_date,
                    'reason'      => $l->reason,
                    'source'      => $l->source,
                    'by'          => $l->created_by_name,
                    'created_at'  => $l->created_at,
                    'lines'       => [],
                ];
            }
            $docs[$l->change_id]['lines'][] = $l;
        }

        return array_values($docs);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST — บันทึกใบเปลี่ยนแปลง
    // ─────────────────────────────────────────────────────────────
    public function save(Request $request)
    {
        $orderno = trim((string) $request->input('orderno', ''));
        $action  = (string) $request->input('action', '');

        $order = $orderno === '' ? null : DB::table('morder')->where('Orderno', $orderno)->first(['Orderno', 'Company', 'netqty']);
        if (!$order) {
            return $this->fail('ไม่พบใบสั่งซื้อ ' . $orderno);
        }
        if (!isset(OrderChangeLine::ACTIONS[$action])) {
            return $this->fail('ไม่รู้จักการเปลี่ยนแปลงที่เลือก');
        }

        $changeDate = $this->parseDate($request->input('change_date')) ?? now()->toDateString();
        $reason     = trim((string) $request->input('reason', ''));

        $runnos = array_values(array_filter(array_map('intval', (array) $request->input('runnos', []))));
        if (!$runnos) {
            return $this->fail('กรุณาเลือกรายการ');
        }
        $rows = DB::table('suborder')->where('Orderno', $orderno)->whereIn('Runno', $runnos)->get()->keyBy('Runno');
        if ($rows->count() !== count(array_unique($runnos))) {
            return $this->fail('รายการที่เลือกไม่อยู่ในใบสั่งซื้อนี้ — กรุณาโหลดใบใหม่');
        }
        if ($action !== 'postpone' && count($runnos) !== 1) {
            return $this->fail('แยกส่ง / เปลี่ยนน้ำหนัก ทำได้ทีละ 1 รายการ');
        }

        // ── ตรวจค่าของแต่ละการกระทำ (ก่อนแตะ DB) ──
        if ($action === 'postpone') {
            $newDate = $this->parseDate($request->input('new_senddate'));
            if (!$newDate) {
                return $this->fail('กรุณาระบุกำหนดส่งใหม่');
            }
            $targets = $rows->filter(fn ($r) => $this->ymd($r->senddate) !== $newDate);
            if ($targets->isEmpty()) {
                return $this->fail('รายการที่เลือกมีกำหนดส่งเป็นวันนี้อยู่แล้ว');
            }
        } elseif ($action === 'split') {
            $src = $rows->first();
            $sS = $this->num($request->input('split_stock'));
            $sP = $this->num($request->input('split_prod'));
            $splitDate = $this->parseDate($request->input('split_senddate'));
            if ($sS < 0 || $sP < 0 || $sS + $sP <= 0) {
                return $this->fail('กรุณาระบุน้ำหนักที่จะแยก (S หรือ P) มากกว่า 0');
            }
            if ($sS > (float) $src->Stock + 1e-9 || $sP > (float) $src->Production + 1e-9) {
                return $this->fail('น้ำหนักที่แยกเกินน้ำหนักของรายการ (S ' . $this->fmt($src->Stock) . ' / P ' . $this->fmt($src->Production) . ')');
            }
            if (((float) $src->Stock - $sS) + ((float) $src->Production - $sP) <= 0) {
                return $this->fail('แยกทั้งหมดไม่ได้ — ถ้าจะเปลี่ยนวันส่งทั้งรายการ ให้ใช้ "เลื่อนกำหนดส่ง"');
            }
            if (!$splitDate) {
                return $this->fail('กรุณาระบุกำหนดส่งของส่วนที่แยก');
            }
        } else {
            $src = $rows->first();
            $nS = $this->num($request->input('new_stock'));
            $nP = $this->num($request->input('new_prod'));
            if ($nS < 0 || $nP < 0 || $nS + $nP <= 0) {
                return $this->fail('น้ำหนักใหม่ต้องมากกว่า 0');
            }
            if (abs($nS - (float) $src->Stock) < 1e-9 && abs($nP - (float) $src->Production) < 1e-9) {
                return $this->fail('น้ำหนักใหม่เท่ากับน้ำหนักเดิม');
            }
        }

        try {
            $changeNo = DB::transaction(function () use ($request, $order, $orderno, $action, $changeDate, $reason, $rows) {
                $changeNo = $this->allocateChangeNo($order->Company);
                $account  = AccessControl::currentAccount();
                $now      = now();

                $changeId = DB::table('tb_order_change')->insertGetId([
                    'change_no'       => $changeNo,
                    'change_date'     => $changeDate,
                    'orderno'         => $orderno,
                    'reason'          => $reason !== '' ? $reason : null,
                    'source'          => 'web',
                    'created_by_type' => $account ? ($account instanceof Emp ? 'emp' : 'user') : null,
                    'created_by'      => $account ? (string) $account->getKey() : null,
                    'created_by_name' => $account ? $this->accountName($account) : null,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);

                $line = fn (array $d) => DB::table('tb_order_change_line')->insert($d + [
                    'change_id' => $changeId, 'orderno' => $orderno, 'action' => $action,
                    'created_at' => $now, 'updated_at' => $now,
                ]);

                if ($action === 'postpone') {
                    $newDate = $this->parseDate($request->input('new_senddate'));
                    foreach ($rows as $r) {
                        if ($this->ymd($r->senddate) === $newDate) {
                            continue;
                        }
                        $line(['runno' => $r->Runno, 'old_senddate' => $this->ymd($r->senddate), 'new_senddate' => $newDate]);
                        DB::table('suborder')->where('Orderno', $orderno)->where('Runno', $r->Runno)
                            ->update(['senddate' => $newDate . ' 00:00:00']);
                    }
                } elseif ($action === 'split') {
                    $src = $rows->first();
                    $sS = $this->num($request->input('split_stock'));
                    $sP = $this->num($request->input('split_prod'));
                    $splitDate = $this->parseDate($request->input('split_senddate'));
                    $keepS = round((float) $src->Stock - $sS, 6);
                    $keepP = round((float) $src->Production - $sP, 6);

                    // แถวใหม่ = สำเนาข้อมูลสินค้าของแถวต้นทาง (ไม่คัดลอกวันผลิตเสร็จ/วันส่งจริง/เลขที่ใบส่ง)
                    $newRunno = DB::table('suborder')->insertGetId([
                        'Orderno'      => $orderno,
                        'Itemno'       => $src->Itemno,
                        'Lotno'        => $src->Lotno,
                        'nold'         => $src->nold,
                        'prodname'     => $src->prodname,
                        'custwant'     => $src->custwant,
                        'Remark'       => $src->Remark,
                        'Stock'        => $sS,
                        'Production'   => $sP,
                        'senddate'     => $splitDate . ' 00:00:00',
                        'parent_runno' => $src->Runno,
                    ]);
                    DB::table('suborder')->where('Orderno', $orderno)->where('Runno', $src->Runno)
                        ->update(['Stock' => $keepS, 'Production' => $keepP]);

                    $line([
                        'runno'        => $src->Runno,
                        'old_senddate' => $this->ymd($src->senddate),
                        'new_senddate' => $splitDate,
                        'old_stock'    => (float) $src->Stock,
                        'old_prod'     => (float) $src->Production,
                        'new_stock'    => $keepS,
                        'new_prod'     => $keepP,
                        'split_runno'  => $newRunno,
                        'split_stock'  => $sS,
                        'split_prod'   => $sP,
                    ]);
                } else {
                    $src = $rows->first();
                    $nS = $this->num($request->input('new_stock'));
                    $nP = $this->num($request->input('new_prod'));
                    DB::table('suborder')->where('Orderno', $orderno)->where('Runno', $src->Runno)
                        ->update(['Stock' => $nS, 'Production' => $nP]);
                    $line([
                        'runno'     => $src->Runno,
                        'old_stock' => (float) $src->Stock,
                        'old_prod'  => (float) $src->Production,
                        'new_stock' => $nS,
                        'new_prod'  => $nP,
                    ]);

                    // น้ำหนักรวมของใบ (ใช้หากลุ่มราคา) ปรับตามส่วนต่าง — ใบที่ไม่ได้กรอกน้ำหนักรวมไว้ ไม่แตะ
                    if ($order->netqty !== null) {
                        $delta = ($nS + $nP) - ((float) $src->Stock + (float) $src->Production);
                        DB::table('morder')->where('Orderno', $orderno)
                            ->update(['netqty' => round((float) $order->netqty + $delta, 6)]);
                    }
                }

                return $changeNo;
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'บันทึกไม่สำเร็จ: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'success'   => true,
            'change_no' => $changeNo,
            'message'   => 'บันทึกใบเปลี่ยนแปลงเลขที่ ' . $changeNo . ' เรียบร้อย',
        ]);
    }

    /**
     * จองเลขที่ใบเปลี่ยนแปลง = <prefix แผนก><เลข 6 หลัก> เช่น CP000122 (ต่อจากเลขเดิมของระบบ Access)
     * tb_order_change_run เป็น InnoDB ⇒ lockForUpdate กันสองคนกดพร้อมกันได้จริง — ต้องเรียกใน transaction
     */
    private function allocateChangeNo(?string $company): string
    {
        $prefix = strtoupper(trim((string) $company));
        if (!preg_match('/^[A-Z]{1,4}$/', $prefix)) {
            $prefix = self::DEFAULT_PREFIX;
        }

        DB::table('tb_order_change_run')->insertOrIgnore(['prefix' => $prefix, 'last_no' => 0]);
        $next = (int) DB::table('tb_order_change_run')->where('prefix', $prefix)->lockForUpdate()->value('last_no');

        do {
            $next++;
            $no = $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
        } while (DB::table('tb_order_change')->where('change_no', $no)->exists());

        DB::table('tb_order_change_run')->where('prefix', $prefix)->update(['last_no' => $next]);

        return $no;
    }

    private function accountName($account): string
    {
        if ($account instanceof Emp) {
            $name = trim(($account->empname ?? '') . ' ' . ($account->empsur ?? ''));

            return $name !== '' ? $name : (string) $account->empno;
        }

        return trim((string) ($account->name ?: $account->username));
    }

    private function fail(string $message)
    {
        return response()->json(['success' => false, 'message' => $message], 422);
    }

    /** d/m/Y หรือ Y-m-d → Y-m-d (ผิดรูปแบบ / วันที่ไม่มีจริง = null) */
    private function parseDate($value): ?string
    {
        $value = trim((string) $value);
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $value, $m)) {
            [$d, $mo, $y] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        } elseif (preg_match('#^(\d{4})-(\d{2})-(\d{2})#', $value, $m)) {
            [$y, $mo, $d] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        } else {
            return null;
        }

        return checkdate($mo, $d, $y) ? sprintf('%04d-%02d-%02d', $y, $mo, $d) : null;
    }

    private function ymd($value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    private function num($value): float
    {
        $value = str_replace(',', '', trim((string) $value));

        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function fmt($value): string
    {
        return number_format((float) $value, 2);
    }
}
