<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ย้ายประวัติการเปลี่ยนแปลงคำสั่งซื้อจากระบบ Access เดิมเข้าตารางใหม่ (20/10/2569)
 *
 *   เลื่อนครั้งที่ 1-3  suborder.Cdate1-3 (กำหนดใหม่) · Cno1-3 (เลขที่ใบ) · Change1-3 (วันที่ใบ)
 *   เปลี่ยนน้ำหนัก     suborder.Oldweight (น้ำหนักเดิม) · Weightno · WeightDate
 *
 * แล้วตั้ง suborder.senddate = กำหนดส่งปัจจุบัน (วันที่เลื่อนครั้งล่าสุด)
 * — ระบบเดิมเก็บ senddate เป็น "กำหนดแรก" ไม่ถูกแก้ตอนเลื่อน ระบบใหม่ให้ senddate = ปัจจุบัน
 *   กำหนดแรกยังอยู่ใน old_senddate ของบรรทัดเลื่อนครั้งแรก → down() คืนค่าได้
 *
 * คอลัมน์ Cdate/Cno/Change/Oldweight เดิมไม่ถูกลบหรือแก้
 * ⚠ suborder เป็น MyISAM — ถ้าพังกลางทาง ข้อมูลที่เขียนไปแล้วไม่ถูกย้อน (รัน down() แล้วรันใหม่ได้)
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_order_change_line')) {
            return;
        }
        // รันซ้ำไม่ได้ — ถ้าย้ายไปแล้วข้าม
        if (DB::table('tb_order_change')->where('source', 'legacy')->exists()) {
            return;
        }

        $rows = DB::table('suborder')
            ->where(function ($q) {
                $q->whereNotNull('Cdate1')->orWhere('Oldweight', '<>', 0);
            })
            ->orderBy('Runno')
            ->get();

        $now = now();
        $headerIds = [];

        // หัวใบใช้ร่วมกันได้ (1 เลขที่ใบครอบหลายรายการ / หลายใบสั่ง) — วันที่ใบ = วันแรกที่เจอ
        $header = function (?string $no, ?string $date, string $fallback, string $orderno) use (&$headerIds, $now) {
            $no = trim((string) $no);
            $no = $no !== '' ? $no : $fallback;
            if (!isset($headerIds[$no])) {
                $headerIds[$no] = DB::table('tb_order_change')->insertGetId([
                    'change_no'   => $no,
                    'change_date' => $date,
                    'orderno'     => $orderno,
                    'source'      => 'legacy',
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            } elseif ($date) {
                DB::table('tb_order_change')->where('id', $headerIds[$no])
                    ->where(function ($q) use ($date) {
                        $q->whereNull('change_date')->orWhere('change_date', '>', $date);
                    })
                    ->update(['change_date' => $date]);
            }

            return $headerIds[$no];
        };

        $day = fn ($v) => $v ? substr((string) $v, 0, 10) : null;

        foreach ($rows as $r) {
            $prev = $r->senddate;
            $last = null;

            for ($k = 1; $k <= 3; $k++) {
                $new = $r->{'Cdate' . $k};
                if (!$new) {
                    break;
                }
                DB::table('tb_order_change_line')->insert([
                    'change_id'    => $header($r->{'Cno' . $k}, $r->{'Change' . $k}, 'L' . $r->Runno . '-' . $k, $r->Orderno),
                    'orderno'      => $r->Orderno,
                    'runno'        => $r->Runno,
                    'action'       => 'postpone',
                    'old_senddate' => $day($prev),
                    'new_senddate' => $day($new),
                    'remark'       => 'ย้ายจากระบบเดิม (เลื่อนครั้งที่ ' . $k . ')',
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
                $prev = $last = $new;
            }

            if ((float) $r->Oldweight != 0) {
                // ระบบเดิมเก็บน้ำหนักเดิมเป็นยอดรวมก้อนเดียว — ลงช่อง S หรือ P ตามที่แถวนั้นใช้อยู่
                $useStock = (float) $r->Production == 0 && (float) $r->Stock != 0;
                DB::table('tb_order_change_line')->insert([
                    'change_id'  => $header($r->Weightno, $r->WeightDate, 'LW' . $r->Runno, $r->Orderno),
                    'orderno'    => $r->Orderno,
                    'runno'      => $r->Runno,
                    'action'     => 'weight',
                    'old_stock'  => $useStock ? (float) $r->Oldweight : 0,
                    'old_prod'   => $useStock ? 0 : (float) $r->Oldweight,
                    'new_stock'  => (float) $r->Stock,
                    'new_prod'   => (float) $r->Production,
                    'remark'     => 'ย้ายจากระบบเดิม (เปลี่ยนน้ำหนัก)',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if ($last) {
                DB::table('suborder')->where('Orderno', $r->Orderno)->where('Runno', $r->Runno)
                    ->update(['senddate' => $last]);
            }
        }
    }

    public function down()
    {
        if (!Schema::hasTable('tb_order_change_line')) {
            return;
        }

        // คืน senddate = กำหนดแรก (old_senddate ของบรรทัดเลื่อนครั้งแรกที่ย้ายมา)
        $firsts = DB::table('tb_order_change_line as l')
            ->join('tb_order_change as h', 'h.id', '=', 'l.change_id')
            ->where('h.source', 'legacy')->where('l.action', 'postpone')
            ->orderBy('l.id')
            ->get(['l.orderno', 'l.runno', 'l.old_senddate'])
            ->unique(fn ($l) => $l->orderno . '|' . $l->runno);

        foreach ($firsts as $l) {
            DB::table('suborder')->where('Orderno', $l->orderno)->where('Runno', $l->runno)
                ->update(['senddate' => $l->old_senddate]);
        }

        DB::table('tb_order_change')->where('source', 'legacy')->delete();   // line ลบตาม (cascade)
    }
};
