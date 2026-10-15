<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ประวัติการแก้ไขกลุ่มราคา (zcolorrate) — 15/10/2569
        // 1 แถว = 1 ครั้งที่ เพิ่ม / แก้ไข / ลบ พร้อมค่าก่อน-หลัง และผู้ทำ
        // ไม่ผูก foreign key กับ zcolorrate (MyISAM) — รหัสที่ถูกลบไปแล้วยังต้องดูประวัติได้
        // database-first: เช็คก่อนกัน error ถ้าตารางถูกสร้างมือไปแล้ว
        if (Schema::hasTable('tb_color_rate_history')) {
            return;
        }

        Schema::create('tb_color_rate_history', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('colorno', 12)->index();
            $table->string('action', 10);                 // create / update / delete
            $table->double('old_rate_A')->nullable();
            $table->double('old_rate_B')->nullable();
            $table->double('old_rate_C')->nullable();
            $table->dateTime('old_RDate')->nullable();
            $table->double('new_rate_A')->nullable();
            $table->double('new_rate_B')->nullable();
            $table->double('new_rate_C')->nullable();
            $table->dateTime('new_RDate')->nullable();
            $table->string('changed_by_type', 5)->nullable();   // emp / user
            $table->string('changed_by', 20)->nullable();       // emp.empno หรือ users.id
            $table->string('changed_by_name', 150)->nullable(); // ชื่อ ณ ตอนที่ทำ (snapshot)
            $table->dateTime('changed_at')->index();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_color_rate_history');
    }
};
