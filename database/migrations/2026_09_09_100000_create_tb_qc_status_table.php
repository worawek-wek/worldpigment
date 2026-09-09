<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ตาราง tb_qc_status — สถานะ QC (ตัวเลือกสำเร็จรูป, master) (09/09/2569)
 *
 * database-first: ตารางนี้ถูกสร้างมือใน DB ไปก่อนแล้ว จึงครอบด้วย
 * Schema::hasTable(...) ให้ migrate ซ้ำได้ (no-op บนเครื่องที่มีตารางอยู่แล้ว)
 * และไว้สร้างให้ server ที่ยังไม่มีตารางนี้
 *
 * ⚠ โครงตรงกับที่มีอยู่ใน DB: ไม่มีคอลัมน์ created_by / updated_by (ต่างจาก tb_planning_remark)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_qc_status')) {
            Schema::create('tb_qc_status', function (Blueprint $t) {
                $t->id();
                $t->string('name', 255)->nullable();            // ชื่อสถานะ QC
                $t->integer('sort')->nullable();                // ลำดับการแสดง (เลขน้อยแสดงก่อน)
                $t->enum('is_active', ['Y', 'N'])->default('Y'); // Y = เปิดใช้งาน, N = ปิด
                $t->timestamps();                               // created_at / updated_at
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_qc_status');
    }
};
