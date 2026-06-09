<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * เปลี่ยน status ของ tb_semi_pigment เป็น ENUM: request | approved | reject
     */
    public function up()
    {
        // แปลงค่าข้อมูลเดิม (ภาษาไทย) -> ค่าใหม่ ก่อนเปลี่ยนชนิดคอลัมน์
        DB::table('tb_semi_pigment')->where('status', 'รออนุมัติ')->update(['status' => 'request']);
        DB::table('tb_semi_pigment')->where('status', 'อนุมัติ')->update(['status' => 'approved']);

        DB::statement("ALTER TABLE tb_semi_pigment
            MODIFY status ENUM('request','approved','reject') NOT NULL DEFAULT 'request'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE tb_semi_pigment
            MODIFY status VARCHAR(30) NOT NULL DEFAULT 'request'");
    }
};
