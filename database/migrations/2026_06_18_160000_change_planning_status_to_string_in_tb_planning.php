<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * planning_status เดิมเป็น int(11) แต่ฟีเจอร์ Planning Status เก็บเป็น "ชื่อสถานะ"
     * (ข้อความภาษาไทย เช่น "รอวัตถุดิบ") จึงต้องเปลี่ยนชนิดเป็น VARCHAR
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `tb_planning` MODIFY `planning_status` VARCHAR(255) NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `tb_planning` MODIFY `planning_status` INT(11) NULL DEFAULT NULL");
    }
};
