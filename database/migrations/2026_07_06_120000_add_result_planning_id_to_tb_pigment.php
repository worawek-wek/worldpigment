<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * เพิ่มคอลัมน์ result_planning_id เพื่ออ้างอิงแผนการผลิตที่สร้างจาก Pigment หลังอนุมัติ
     * (mirror ของ tb_semi_pigment.result_planning_id)
     */
    public function up(): void
    {
        Schema::table('tb_pigment', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_pigment', 'result_planning_id')) {
                $table->integer('result_planning_id')->nullable()->after('approve_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tb_pigment', function (Blueprint $table) {
            if (Schema::hasColumn('tb_pigment', 'result_planning_id')) {
                $table->dropColumn('result_planning_id');
            }
        });
    }
};
