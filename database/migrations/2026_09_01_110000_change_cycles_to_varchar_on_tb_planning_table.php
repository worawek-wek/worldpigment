<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * เปลี่ยนคอลัมน์ tb_planning.cycles จาก int(11) → varchar(25)
     * เพื่อให้ช่อง "รอบการผลิต (Cycles)" กรอกเป็นข้อความได้ (ยาวสูงสุด 25)
     *
     * ใช้ raw SQL MODIFY (โปรเจกต์ไม่มี doctrine/dbal) + idempotent:
     * ตรวจชนิดคอลัมน์ปัจจุบันก่อน จะ ALTER เฉพาะเมื่อยังไม่ใช่ varchar
     * ค่าเดิมที่เป็นตัวเลข (1/2/3) จะถูกแปลงเป็น string ครบถ้วน (widening) ไม่มีข้อมูลหาย
     */
    public function up(): void
    {
        if (! Schema::hasColumn('tb_planning', 'cycles')) {
            return;
        }

        $type = $this->currentType('cycles');
        if ($type !== null && ! str_starts_with($type, 'varchar')) {
            DB::statement('ALTER TABLE `tb_planning` MODIFY `cycles` VARCHAR(25) NULL');
        }
    }

    /**
     * ถอดกลับเป็น int(11) — แปลงกลับได้เฉพาะกรณีค่าเป็นตัวเลขล้วน
     * ถ้ามีข้อความปนอยู่ MySQL จะแปลงค่านั้นเป็น 0 ตอน cast
     */
    public function down(): void
    {
        if (! Schema::hasColumn('tb_planning', 'cycles')) {
            return;
        }

        $type = $this->currentType('cycles');
        if ($type !== null && str_starts_with($type, 'varchar')) {
            DB::statement('ALTER TABLE `tb_planning` MODIFY `cycles` INT(11) NULL');
        }
    }

    private function currentType(string $column): ?string
    {
        $col = DB::selectOne(
            'SHOW COLUMNS FROM `tb_planning` WHERE Field = ?',
            [$column]
        );

        return $col ? strtolower($col->Type) : null;
    }
};
