<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * เพิ่มคอลัมน์ `id` ให้ตาราง `uprice` (29/08/2569)
 *
 * `uprice` เป็นตาราง legacy ที่ยกมาจากระบบเก่า — เดิม **ไม่มี primary key
 * ไม่มี index สักตัว** จึงระบุแถวใดแถวหนึ่งเพื่อแก้ไข/ลบไม่ได้
 *
 * เมนู "กำหนดราคา" (/saleinfo) ย้ายมาเขียนลงตารางนี้แทน `tb_saleinfo`
 * ตามที่ผู้ใช้ยืนยัน จึงต้องมีคีย์ให้ edit/update/delete อ้างอิงได้
 *
 * ปลอดภัยกับโค้ดเดิม: ทุก query ในระบบ select ชื่อคอลัมน์ตรง ๆ
 * (ไม่มีที่ไหนพึ่งลำดับคอลัมน์หรือ `SELECT *` แบบ positional)
 *
 * ⚠ MyISAM — คำสั่งนี้ rebuild ตารางทั้ง 60,603 แถว สำรองไฟล์ก่อนรันบน production
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('uprice')) {
            return;   // database-first: ไม่มีตารางก็ข้ามไป
        }

        if (Schema::hasColumn('uprice', 'id')) {
            return;   // เพิ่มไปแล้ว (หรือถูกเพิ่มมือใน DB)
        }

        // ใช้ SQL ตรง ๆ เพราะ Blueprint สร้าง AUTO_INCREMENT + PRIMARY KEY
        // ในคำสั่ง ALTER เดียวกันไม่ได้ (MySQL บังคับให้ auto_increment ต้องเป็น key ทันที)
        DB::statement('ALTER TABLE `uprice` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
    }

    public function down()
    {
        if (Schema::hasTable('uprice') && Schema::hasColumn('uprice', 'id')) {
            DB::statement('ALTER TABLE `uprice` DROP PRIMARY KEY, DROP COLUMN `id`');
        }
    }
};
