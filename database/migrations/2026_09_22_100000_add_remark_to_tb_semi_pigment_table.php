<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * เพิ่มคอลัมน์ remark (หมายเหตุ) ให้ตาราง tb_semi_pigment
     * idempotent — เช็คก่อนว่ายังไม่มีคอลัมน์นี้
     */
    public function up()
    {
        if (!Schema::hasColumn('tb_semi_pigment', 'remark')) {
            Schema::table('tb_semi_pigment', function (Blueprint $table) {
                $table->text('remark')->nullable()->after('red_bill_code'); // หมายเหตุ
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tb_semi_pigment', 'remark')) {
            Schema::table('tb_semi_pigment', function (Blueprint $table) {
                $table->dropColumn('remark');
            });
        }
    }
};
