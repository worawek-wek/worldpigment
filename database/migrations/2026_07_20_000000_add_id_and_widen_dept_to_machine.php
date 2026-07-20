<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * เพิ่ม primary key `id` (auto_increment) และขยาย `dept` เป็น varchar(50)
     * ให้ตาราง machine เพื่อรองรับการแก้ไข/ลบราย record และเก็บชื่อแผนกจาก dropdown ได้เต็ม
     * ใช้ raw statement เพื่อเลี่ยงการพึ่ง doctrine/dbal ของ ->change()
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `machine` ADD `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
        DB::statement("ALTER TABLE `machine` MODIFY `dept` VARCHAR(50) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `machine` MODIFY `dept` CHAR(2) NULL");
        DB::statement("ALTER TABLE `machine` DROP PRIMARY KEY, DROP COLUMN `id`");
    }
};
