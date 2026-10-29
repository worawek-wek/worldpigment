<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Lot No. ของใบส่ง ต.ย. (testmain.lotno) ต้องไม่ซ้ำกัน (29/10/2569 ตามที่ผู้ใช้สั่ง)
 *
 * แก้ข้อมูลเดิมที่ซ้ำ: ในแต่ละกลุ่ม (เทียบแบบ TRIM) แถวที่ id น้อยสุดคงค่าเดิม
 * แถวถัดไปต่อท้ายด้วย "(แก้ซ้ำ1)", "(แก้ซ้ำ2)", … ให้รู้ว่าถูกแก้เพราะซ้ำ
 * ไม่แก้โครงสร้าง (ไม่เพิ่ม unique index) — การกันซ้ำต่อจากนี้อยู่ที่ ColorMatchingController
 *
 * idempotent: รันซ้ำไม่มีอะไรซ้ำให้แก้แล้ว · down() ถอด "(แก้ซ้ำN)" ออก
 */
return new class extends Migration
{
    private const SUFFIX = 'แก้ซ้ำ';
    private const MAX_LEN = 20; // testmain.lotno varchar(20)

    public function up(): void
    {
        $dupLots = DB::table('testmain')
            ->selectRaw('TRIM(lotno) AS l')
            ->whereRaw("TRIM(lotno) <> ''")
            ->groupByRaw('TRIM(lotno)')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('l');

        foreach ($dupLots as $lot) {
            $ids = DB::table('testmain')->whereRaw('TRIM(lotno) = ?', [$lot])->orderBy('id')->pluck('id');

            $n = 0;
            foreach ($ids->slice(1) as $id) {
                // หาเลขที่ยังไม่มีใครใช้ (กันชนกับค่าที่มีอยู่แล้ว)
                do {
                    $n++;
                    $tag = '(' . self::SUFFIX . $n . ')';
                    $new = mb_substr($lot, 0, self::MAX_LEN - mb_strlen($tag)) . $tag;
                } while (DB::table('testmain')->whereRaw('TRIM(lotno) = ?', [$new])->exists());

                DB::table('testmain')->where('id', $id)->update(['lotno' => $new]);
            }
        }
    }

    public function down(): void
    {
        $rows = DB::table('testmain')->where('lotno', 'like', '%(' . self::SUFFIX . '%)')->get(['id', 'lotno']);
        foreach ($rows as $r) {
            $orig = preg_replace('/\(' . self::SUFFIX . '\d+\)$/u', '', $r->lotno);
            DB::table('testmain')->where('id', $r->id)->update(['lotno' => $orig]);
        }
    }
};
