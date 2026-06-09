<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ตารางรวม Semi และ Pigment (แยกประเภทด้วยคอลัมน์ type)
     * สร้างจากหน้า production-planning/planning เพื่อรออนุมัติ
     * ก่อนนำไปสร้างแผนการผลิตจริง
     */
    public function up()
    {
        Schema::create('tb_semi_pigment', function (Blueprint $table) {
            $table->id();

            // อ้างอิงกลับไปยัง planning / order ต้นทาง
            $table->unsignedBigInteger('planning_id')->nullable();          // planning item แม่
            $table->unsignedBigInteger('planning_header_id')->nullable();   // header ของ order แม่
            $table->string('orderno')->nullable();

            // ประเภท: semi | pigment
            $table->string('type', 20)->index();

            // ข้อมูลหลักตามที่ต้องการเก็บ
            $table->string('company')->nullable();          // Company
            $table->date('order_date')->nullable();         // วันที่สั่ง
            $table->date('want_date')->nullable();          // วันที่ต้องการรับ
            $table->string('custno')->nullable();           // รหัสลูกค้า
            $table->string('itemno')->nullable();           // item no
            $table->decimal('quantity', 15, 2)->nullable(); // quantity

            // สถานะการอนุมัติ
            $table->string('status', 30)->default('รออนุมัติ');     // รออนุมัติ | อนุมัติ
            $table->string('approver_code')->nullable();            // รหัสคนอนุมัติ
            $table->dateTime('approve_date')->nullable();           // วันที่อนุมัติ

            // อ้างอิง planning ที่ถูกสร้างหลังอนุมัติ
            $table->unsignedBigInteger('result_planning_id')->nullable();

            $table->timestamps();

            $table->index(['planning_id', 'type', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_semi_pigment');
    }
};
