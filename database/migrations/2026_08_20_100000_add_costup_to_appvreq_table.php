<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ฟอร์ม "MK ขออนุมัติราคาพิเศษ" มี checkbox "ต้นทุนวัตถุดิบปรับขึ้น จึงปรับราคาขาย"
 * แต่ตาราง appvreq เดิมไม่มีคอลัมน์ไหนที่ตรงกับช่องนี้เลย
 *
 * เดาว่าเป็นค่าของใบขอราคาแต่ละใบ จึงเพิ่มคอลัมน์ใหม่ให้ appvreq
 * ใช้รูปแบบเดียวกับ checkbox อื่นในระบบเดิม (Access): -1 = ติ๊ก, 0/NULL = ไม่ติ๊ก
 *
 * ถ้าภายหลังพบว่าค่านี้เก็บอยู่ที่อื่นจริง ๆ ให้ย้ายไปอ่าน/เขียนที่นั่น แล้วลบคอลัมน์นี้ทิ้ง
 */
return new class extends Migration
{
    public function up(): void
    {
        // DB ของลูกค้ามีตาราง/คอลัมน์อยู่ก่อนแล้วหลายจุด — ต้อง idempotent เสมอ
        if (!Schema::hasTable('appvreq') || Schema::hasColumn('appvreq', 'costup')) {
            return;
        }

        // appvreq.ReqDate ตั้ง default ไว้เป็น '0000-00-00 00:00:00' (ของเดิมจาก Access)
        // ซึ่ง sql_mode แบบ strict ไม่ยอมให้ ALTER ผ่าน → ผ่อน sql_mode เฉพาะ session นี้
        $mode = DB::selectOne('SELECT @@SESSION.sql_mode AS m')->m;
        DB::statement("SET SESSION sql_mode=''");

        try {
            Schema::table('appvreq', function (Blueprint $table) {
                $table->tinyInteger('costup')->nullable()->after('remark');
            });
        } finally {
            DB::statement('SET SESSION sql_mode=?', [$mode]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('appvreq') && Schema::hasColumn('appvreq', 'costup')) {
            Schema::table('appvreq', function (Blueprint $table) {
                $table->dropColumn('costup');
            });
        }
    }
};
