<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ฟอร์มแก้ไข Planning Item: เพิ่ม 2 ช่อง (24/11/2569 ตามที่ผู้ใช้สั่ง)
 *  - resin_return = ส่งคืนเม็ด Resin (ข้อความอิสระ)
 *  - delivery_no  = เลขที่ใบส่ง
 * idempotent — DB มักมีคอลัมน์อยู่ก่อนแล้ว
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_planning', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_planning', 'resin_return')) {
                $table->string('resin_return', 255)->nullable()->after('cycles');
            }
            if (!Schema::hasColumn('tb_planning', 'delivery_no')) {
                $table->string('delivery_no', 50)->nullable()->after('resin_return');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tb_planning', function (Blueprint $table) {
            if (Schema::hasColumn('tb_planning', 'delivery_no')) {
                $table->dropColumn('delivery_no');
            }
            if (Schema::hasColumn('tb_planning', 'resin_return')) {
                $table->dropColumn('resin_return');
            }
        });
    }
};
