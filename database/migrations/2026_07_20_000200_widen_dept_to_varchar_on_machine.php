<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * ขยาย `dept` เป็น varchar(50) เพื่อรองรับ dropdown ชื่อแผนกจาก tb_departments
     * (บางชื่อยาวเกิน 2 ตัว เช่น SPP) — ข้อมูลเดิม (โค้ด 2 ตัว) ยังอยู่ครบ
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `machine` MODIFY `dept` VARCHAR(50) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `machine` MODIFY `dept` CHAR(2) NULL");
    }
};
