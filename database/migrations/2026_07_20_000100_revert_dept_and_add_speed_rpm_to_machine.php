<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * คืนคอลัมน์ `dept` กลับเป็น char(2) ตามเดิม (ยกเลิกที่เคยขยายเป็น varchar(50))
     * และเพิ่มคอลัมน์ `speed_rpm` varchar(255) ให้ตาราง machine
     * (ยังคงคอลัมน์ `id` primary key ไว้ตามเดิม)
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `machine` MODIFY `dept` CHAR(2) NULL");
        DB::statement("ALTER TABLE `machine` ADD `speed_rpm` VARCHAR(255) NULL AFTER `MBX`");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `machine` DROP COLUMN `speed_rpm`");
        DB::statement("ALTER TABLE `machine` MODIFY `dept` VARCHAR(50) NULL");
    }
};
