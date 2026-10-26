<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * เพิ่ม index ที่ suborder.Orderno (26/10/2569)
 *
 * PK ของ suborder คือ (Runno, Orderno) — ขึ้นต้นด้วย Runno จึงใช้ค้นด้วย Orderno ไม่ได้
 * ⇒ subquery ต่อแถวในหน้า production-planning/order (stock_total / production_total / itemno_sort)
 *   scan ทั้งตาราง sort คอลัมน์เหล่านั้นใช้เวลา ~30 วินาที
 *
 * ตารางเป็น MyISAM — ALTER จะ rebuild ทั้งตาราง สำรองก่อนรันบน production
 * idempotent: เช็คว่ามี index นี้แล้วหรือยัง
 */
return new class extends Migration
{
    private const INDEX = 'suborder_orderno_index';

    public function up()
    {
        if (!$this->hasIndex()) {
            DB::statement('ALTER TABLE `suborder` ADD INDEX `'.self::INDEX.'` (`Orderno`)');
        }
    }

    public function down()
    {
        if ($this->hasIndex()) {
            DB::statement('ALTER TABLE `suborder` DROP INDEX `'.self::INDEX.'`');
        }
    }

    private function hasIndex(): bool
    {
        return !empty(DB::select('SHOW INDEX FROM `suborder` WHERE Key_name = ?', [self::INDEX]));
    }
};
