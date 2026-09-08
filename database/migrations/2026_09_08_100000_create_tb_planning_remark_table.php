<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ตาราง tb_planning_remark — หมายเหตุแผนการผลิต (ตัวเลือกสำเร็จรูป) (08/09/2569)
 *
 * database-first: ครอบด้วย Schema::hasTable(...) เพื่อให้ migrate ซ้ำได้
 * แม้ตารางจะถูกสร้างมือใน DB ไปก่อนแล้ว
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_planning_remark')) {
            Schema::create('tb_planning_remark', function (Blueprint $t) {
                $t->id();
                $t->string('name', 255);                        // ข้อความหมายเหตุ
                $t->integer('sort')->default(0);                // ลำดับการแสดง
                $t->char('is_active', 1)->default('Y');         // Y = เปิดใช้งาน, N = ปิด
                $t->timestamps();                               // created_at / updated_at
                $t->unsignedBigInteger('created_by')->nullable();
                $t->unsignedBigInteger('updated_by')->nullable();

                $t->index('sort');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_planning_remark');
    }
};
