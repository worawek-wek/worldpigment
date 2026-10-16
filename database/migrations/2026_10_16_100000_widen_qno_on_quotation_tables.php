<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ขยายเลขที่ใบเสนอราคา (Qno) varchar(10) → varchar(20) — 16/10/2569 ตามที่ผู้ใช้สั่ง
 * ทั้ง 3 ตารางที่เก็บเลขที่ใบ: qmast (หัว) · qdetail (รายการ) · qdetail_ext (ราคาแยกย่อย)
 * raw SQL เพราะโปรเจกต์ไม่มี doctrine/dbal · idempotent — ขยายเฉพาะตารางที่ยังแคบกว่า 20
 * ⚠ ตาราง legacy เป็น MyISAM — ALTER จะ rebuild ตาราง (ข้อมูลเดิมไม่หาย)
 */
return new class extends Migration
{
    private const TABLES = ['qmast', 'qdetail', 'qdetail_ext'];

    public function up()
    {
        foreach (self::TABLES as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'Qno')) {
                continue;
            }
            if ($this->length($table) < 20) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `Qno` VARCHAR(20) NULL");
            }
        }
    }

    public function down()
    {
        // ย่อกลับได้เฉพาะเมื่อยังไม่มีเลขที่ยาวเกิน 10 ตัว (ไม่งั้นข้อมูลถูกตัด/ALTER ล้มเหลว)
        foreach (self::TABLES as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'Qno')) {
                continue;
            }
            $tooLong = DB::table($table)->whereRaw('CHAR_LENGTH(`Qno`) > 10')->exists();
            if (!$tooLong && $this->length($table) > 10) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `Qno` VARCHAR(10) NULL");
            }
        }
    }

    private function length(string $table): int
    {
        $row = DB::selectOne(
            'SELECT CHARACTER_MAXIMUM_LENGTH AS len FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, 'Qno']
        );
        return (int) ($row->len ?? 0);
    }
};
