<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ระบบ Complaint & NCR — 26/11/2569
 * ตาราง nc (legacy) ไม่มี primary key — NCRNo ว่าง/เป็น "--"/ซ้ำ จึงระบุแถวเพื่อแก้ไข/ลบไม่ได้
 * เพิ่ม id AUTO_INCREMENT PRIMARY KEY ไว้คอลัมน์แรก (แบบเดียวกับ uprice) — idempotent
 */
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('nc') && !Schema::hasColumn('nc', 'id')) {
            DB::statement('ALTER TABLE `nc` ADD COLUMN `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
        }
    }

    public function down()
    {
        if (Schema::hasTable('nc') && Schema::hasColumn('nc', 'id')) {
            DB::statement('ALTER TABLE `nc` DROP COLUMN `id`');
        }
    }
};
