<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * เพิ่มช่อง "Require Document" ในใบสั่งซื้อ (15/10/2569 ตามที่ผู้ใช้สั่ง)
 *   morder.ReqDoc     — ติ๊ก/ไม่ติ๊ก แบบ Access (-1 = ติ๊ก, 0 = ไม่ติ๊ก) เหมือน MSDS
 *   morder.ReqDocRem  — ข้อความเอกสารที่ต้องใช้ (กรอกได้เมื่อติ๊ก)
 *
 * ⚠ ไฟล์ public/sql/morder.sql ถูกเพิ่ม 2 คอลัมน์นี้ใน CREATE TABLE แล้ว — import ทับไม่หาย
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('morder', 'ReqDoc')) {
            Schema::table('morder', function (Blueprint $table) {
                $table->tinyInteger('ReqDoc')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('morder', 'ReqDocRem')) {
            Schema::table('morder', function (Blueprint $table) {
                $table->string('ReqDocRem', 255)->nullable();
            });
        }
    }

    public function down()
    {
        foreach (['ReqDocRem', 'ReqDoc'] as $col) {
            if (Schema::hasColumn('morder', $col)) {
                Schema::table('morder', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }
};
