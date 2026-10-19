<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * เพิ่มคอลัมน์ `STDrev` / `inBill` ให้ตาราง `uprice` (19/10/2569)
 *
 * ช่อง "STD Revise" และ "ในบิล" ของฟอร์ม Access "STD ปัจจุบัน" (เมนู "กำหนดราคา")
 * ชนิด/ความยาวตาม `public/sql/uprice.sql` (dump จาก DB ของลูกค้า):
 *   STDrev varchar(50) · inBill varchar(85)
 *
 * ⚠ MyISAM — คำสั่งนี้ rebuild ตารางทั้งใบ สำรองไฟล์ก่อนรันบน production
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('uprice')) {
            return;   // database-first: ไม่มีตารางก็ข้ามไป
        }

        $add = [];
        if (!Schema::hasColumn('uprice', 'STDrev')) {
            $add[] = 'ADD `STDrev` VARCHAR(50) NULL DEFAULT NULL';
        }
        if (!Schema::hasColumn('uprice', 'inBill')) {
            $add[] = 'ADD `inBill` VARCHAR(85) NULL DEFAULT NULL';
        }

        // ALTER ครั้งเดียว = rebuild ตารางครั้งเดียว
        if ($add) {
            DB::statement('ALTER TABLE `uprice` ' . implode(', ', $add));
        }
    }

    public function down()
    {
        if (!Schema::hasTable('uprice')) {
            return;
        }

        $drop = [];
        foreach (['STDrev', 'inBill'] as $col) {
            if (Schema::hasColumn('uprice', $col)) {
                $drop[] = "DROP `{$col}`";
            }
        }

        if ($drop) {
            DB::statement('ALTER TABLE `uprice` ' . implode(', ', $drop));
        }
    }
};
