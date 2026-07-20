<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * เปลี่ยนชื่อคอลัมน์ emp.department_id → dept และเก็บเป็นชื่อแผนก (varchar) แทน id
     * คอลัมน์เดิมเป็น varchar(50) อยู่แล้ว จึงเปลี่ยนแค่ชื่อ + แปลงค่า id เดิม (ถ้ามี) เป็นชื่อแผนก
     * ใช้ raw statement เพื่อเลี่ยง dependency doctrine/dbal
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `emp` CHANGE `department_id` `dept` VARCHAR(50) NULL");
        DB::statement("UPDATE `emp` e JOIN `tb_departments` d ON d.id = e.dept
                       SET e.dept = d.name WHERE e.dept REGEXP '^[0-9]+$'");
    }

    public function down(): void
    {
        DB::statement("UPDATE `emp` e JOIN `tb_departments` d ON d.name = e.dept SET e.dept = d.id");
        DB::statement("ALTER TABLE `emp` CHANGE `dept` `department_id` VARCHAR(50) NULL");
    }
};
