<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // เพิ่มคอลัมน์ dept (ชื่อแผนกจาก tb_departments) ให้ตาราง master วิธีการผลิต
    public function up(): void
    {
        Schema::table('tb_prod_method', function (Blueprint $t) {
            $t->string('dept', 50)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('tb_prod_method', function (Blueprint $t) {
            $t->dropColumn('dept');
        });
    }
};
