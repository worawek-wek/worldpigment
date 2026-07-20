<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // ตารางลูก: สถานะวิธีการผลิตของแต่ละ planning item (1 planning → หลายแถว)
    // ไม่ใส่ FK constraint (โปรเจกต์เป็น database-first) ใช้แค่ index
    public function up(): void
    {
        Schema::create('tb_planning_prod_method', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('planning_id')->index();               // -> tb_planning.id
            $t->unsignedBigInteger('prod_method_id')->nullable()->index(); // -> tb_prod_method.id
            $t->date('work_date')->nullable();
            $t->time('start_time')->nullable();
            $t->time('end_time')->nullable();
            $t->integer('sort')->nullable()->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_planning_prod_method');
    }
};
