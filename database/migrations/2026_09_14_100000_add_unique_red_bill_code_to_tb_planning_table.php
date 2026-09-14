<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * บังคับให้เลขที่ใบเบิกออกใบแดง (tb_planning.red_bill_code) ไม่ซ้ำกันทุก item — 14/09/2569
 *
 * เดิมช่องนี้ไม่มีการเช็คซ้ำเลย (validation แค่ nullable|string|max:255) และ flow แปลง
 * Order → แผน (Production\OrderController::convertplanning) stamp red_bill_code = orderno
 * ให้ทุก item ⇒ ออเดอร์ที่มีหลาย suborder จะได้เลขซ้ำกัน
 *
 * ข้อมูลจริงใช้ convention "/R1", "/R2", "/R3" ต่อท้ายเลขใบเบิกอยู่แล้ว (เช่น HE1576/R1..R3)
 * — migration นี้แก้ข้อมูลเดิมที่ยังซ้ำ (ไม่มี suffix) ให้เป็น /R1, /R2 ตาม convention
 * แล้วค่อยเพิ่ม unique index เป็นด่านสุดท้าย (คู่กับด่านฝั่ง app ที่ ProductionPlanController::saveItem)
 *
 * MySQL: unique index ยอมให้ NULL ซ้ำได้หลายค่า แต่ string ว่าง ('') ซ้ำไม่ได้
 * ⇒ normalize '' → NULL ก่อน (ปัจจุบันไม่มีแถว '' แต่กันไว้)
 *
 * DB ของลูกค้าเป็น database-first — เช็คทุกอย่างจาก information_schema จริง (idempotent)
 */
return new class extends Migration
{
    private string $table   = 'tb_planning';
    private string $column  = 'red_bill_code';
    private string $uniqIdx = 'tb_planning_red_bill_code_unique';

    public function up(): void
    {
        if (!Schema::hasTable($this->table) || !Schema::hasColumn($this->table, $this->column)) {
            return;
        }

        // มี unique index ครอบคอลัมน์นี้อยู่แล้ว → ไม่ต้องทำอะไร (idempotent)
        if ($this->hasUniqueOnColumn()) {
            return;
        }

        // 1) normalize string ว่าง → NULL (unique index ยอม NULL ซ้ำได้ แต่ '' ซ้ำไม่ได้)
        DB::table($this->table)->where($this->column, '')->update([$this->column => null]);

        // 2) แก้ข้อมูลเดิมที่ยังซ้ำ ให้ต่อท้าย /R1, /R2, ... (เรียงตาม id) ตาม convention เลขใบเบิกจริง
        $dupCodes = DB::table($this->table)
            ->select($this->column)
            ->whereNotNull($this->column)
            ->groupBy($this->column)
            ->havingRaw('COUNT(*) > 1')
            ->pluck($this->column);

        foreach ($dupCodes as $code) {
            $ids = DB::table($this->table)
                ->where($this->column, $code)
                ->orderBy('id')
                ->pluck('id');

            $seq = 1;
            foreach ($ids as $id) {
                // หาเลขต่อท้ายที่ยังว่าง (กันชนกับค่าที่มีอยู่แล้ว เช่น code/R1 อาจถูกใช้ที่อื่น)
                do {
                    $newCode = $code . '/R' . $seq;
                    $seq++;
                    $taken = DB::table($this->table)
                        ->where($this->column, $newCode)
                        ->where('id', '<>', $id)
                        ->exists();
                } while ($taken);

                DB::table($this->table)->where('id', $id)->update([$this->column => $newCode]);
            }
        }

        // 3) กันพลาด — ถ้ายังเหลือค่าซ้ำ (ไม่น่าเกิด) ให้หยุดพร้อมบอกสาเหตุ ก่อนสร้าง unique
        $stillDup = DB::table($this->table)
            ->select($this->column)
            ->whereNotNull($this->column)
            ->groupBy($this->column)
            ->havingRaw('COUNT(*) > 1')
            ->pluck($this->column)
            ->all();

        if (!empty($stillDup)) {
            throw new \RuntimeException(
                'ยังมี red_bill_code ซ้ำใน tb_planning จึงเพิ่ม unique index ไม่ได้: '
                . implode(', ', $stillDup)
            );
        }

        // 4) เพิ่ม unique index (คอลัมน์เดียว) — index composite เดิม
        //    (tb_planning_senddate_changed_at_index บน senddate_changed_at, red_bill_code) คงไว้
        Schema::table($this->table, function (Blueprint $t) {
            $t->unique($this->column, $this->uniqIdx);
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        // ถอดเฉพาะ unique index ที่ migration นี้สร้าง (ไม่คืนค่าที่ต่อ /R ให้ — เป็นข้อมูลที่ถูกต้องตาม convention)
        if ($this->indexExists($this->uniqIdx)) {
            Schema::table($this->table, function (Blueprint $t) {
                $t->dropUnique($this->uniqIdx);
            });
        }
    }

    /** มี index ชื่อนี้อยู่บนตารางไหม (จาก information_schema) */
    private function indexExists(string $name): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $this->table)
            ->where('INDEX_NAME', $name)
            ->exists();
    }

    /** มี unique index (index ใดก็ได้) ครอบคอลัมน์ red_bill_code อยู่แล้วไหม */
    private function hasUniqueOnColumn(): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $this->table)
            ->where('COLUMN_NAME', $this->column)
            ->where('NON_UNIQUE', 0)
            ->exists();
    }
};
