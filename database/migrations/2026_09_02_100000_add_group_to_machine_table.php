<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * เพิ่มคอลัมน์ `group` (ประเภท/กลุ่มของเครื่องจักร) ให้ตาราง machine
 * ตามที่ผู้ใช้สั่ง (02/09/2569): ข้อความยาวสูงสุด 50 ตัวอักษร
 *
 * หมายเหตุ: `group` เป็น reserved word ของ MySQL — Blueprint/Eloquent ใส่ backtick
 * ให้อัตโนมัติ ปลอดภัยเมื่อ query ผ่าน query builder; ถ้าเขียน raw SQL ต้องใส่ `` เอง
 *
 * ⚠ ตาราง machine เป็น MyISAM (legacy) — ALTER จะ rebuild ตารางทั้งใบ
 *    สำรองก่อนรันบน production
 */
return new class extends Migration
{
    public function up(): void
    {
        // DB ของลูกค้าเป็น database-first — ต้อง idempotent เสมอ
        if (!Schema::hasTable('machine') || Schema::hasColumn('machine', 'group')) {
            return;
        }

        Schema::table('machine', function (Blueprint $table) {
            $table->string('group', 50)->nullable()->after('speed_rpm');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('machine') && Schema::hasColumn('machine', 'group')) {
            Schema::table('machine', function (Blueprint $table) {
                $table->dropColumn('group');
            });
        }
    }
};
