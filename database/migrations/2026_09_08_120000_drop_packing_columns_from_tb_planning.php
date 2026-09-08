<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ลบ 3 คอลัมน์การบรรจุออกจาก tb_planning หลังย้ายข้อมูลเข้าตารางลูก tb_planning_packing แล้ว
     * (ดู migration create_tb_planning_packing_table ที่คัดลอกข้อมูลให้ก่อน)
     *
     * ⚠ เป็นการลบข้อมูล — ต้องรันหลังยืนยันว่าข้อมูลถูกย้ายครบ และโค้ดทุกจุดเลิกอ้างคอลัมน์เดิมแล้ว
     * idempotent ด้วย Schema::hasColumn ; down() เพิ่มคอลัมน์คืน (ชนิดเดิม) แต่ไม่กู้ข้อมูล
     */
    public function up(): void
    {
        Schema::table('tb_planning', function (Blueprint $table) {
            foreach (['packing_datetie', 'weight_packing', 'pack_remark'] as $col) {
                if (Schema::hasColumn('tb_planning', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('tb_planning', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_planning', 'packing_datetie')) {
                $table->dateTime('packing_datetie')->nullable();
            }
            if (!Schema::hasColumn('tb_planning', 'weight_packing')) {
                $table->decimal('weight_packing', 20, 6)->nullable();
            }
            if (!Schema::hasColumn('tb_planning', 'pack_remark')) {
                $table->text('pack_remark')->nullable();
            }
        });
    }
};
