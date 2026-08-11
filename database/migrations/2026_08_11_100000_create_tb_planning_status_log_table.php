<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ประวัติการเปลี่ยนสถานะการผลิตโดยพนักงาน (Worker) — 11/08/2569
 *
 *  เก็บทุกครั้งที่มีการเปลี่ยน tb_planning.planning_status ผ่านหน้า Worker
 *  (ใคร/เมื่อไร/จากสถานะอะไร → เป็นอะไร)
 *
 *  migration idempotent ตามกฎโปรเจกต์ (DB เป็น database-first อาจมีตารางอยู่ก่อน)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_planning_status_log')) {
            return;
        }

        Schema::create('tb_planning_status_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('planning_id')->index();
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->string('changed_by', 50)->nullable()->comment('emp.empno ผู้เปลี่ยนสถานะ');
            $table->dateTime('changed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_planning_status_log');
    }
};
