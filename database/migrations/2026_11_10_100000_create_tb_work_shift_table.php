<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * กะการผลิต (master) — tb_work_shift (10/11/2569 ตามที่ผู้ใช้สั่ง)
 * 1) สร้างตาราง + ใส่ 10 ตัวเลือกตั้งต้น (ปกติ A/B/C · OT A/B 1-3 ชม · วันอาทิตย์)
 * 2) tb_planning.work_shift: ENUM('A','B','C') → VARCHAR(50) (เก็บ "ชื่อกะ" เป็นข้อความ)
 *    และแปลงค่าเดิม A/B/C → "ปกติ A/B/C"
 * idempotent: เช็คตาราง/ชนิดคอลัมน์ก่อนทุกขั้น
 */
return new class extends Migration
{
    private const SEED = [
        'ปกติ A', 'ปกติ B', 'ปกติ C',
        'OT A 1 ชม', 'OT A 2 ชม', 'OT A 3 ชม',
        'OT B 1 ชม', 'OT B 2 ชม', 'OT B 3 ชม',
        'วันอาทิตย์',
    ];

    public function up()
    {
        if (!Schema::hasTable('tb_work_shift')) {
            Schema::create('tb_work_shift', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50);
                $table->integer('sort')->default(0)->index();
                $table->char('is_active', 1)->default('Y');
                $table->timestamps();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
            });

            $now = now();
            foreach (self::SEED as $i => $name) {
                DB::table('tb_work_shift')->insert([
                    'name' => $name, 'sort' => $i + 1, 'is_active' => 'Y',
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        if (Schema::hasTable('tb_planning') && Schema::hasColumn('tb_planning', 'work_shift')) {
            $col = DB::selectOne("SHOW COLUMNS FROM tb_planning LIKE 'work_shift'");
            if ($col && stripos($col->Type, 'enum') === 0) {
                DB::statement('ALTER TABLE tb_planning MODIFY work_shift VARCHAR(50) NULL');
            }
            foreach (['A', 'B', 'C'] as $s) {
                DB::table('tb_planning')->where('work_shift', $s)->update(['work_shift' => 'ปกติ '.$s]);
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('tb_planning') && Schema::hasColumn('tb_planning', 'work_shift')) {
            foreach (['A', 'B', 'C'] as $s) {
                DB::table('tb_planning')->where('work_shift', 'ปกติ '.$s)->update(['work_shift' => $s]);
            }
            // ค่าอื่นที่ไม่ใช่ A/B/C (เช่น OT) เข้า enum ไม่ได้ → ล้างเป็น NULL
            DB::table('tb_planning')->whereNotNull('work_shift')
                ->whereNotIn('work_shift', ['A', 'B', 'C'])->update(['work_shift' => null]);
            DB::statement("ALTER TABLE tb_planning MODIFY work_shift ENUM('A','B','C') NULL");
        }

        Schema::dropIfExists('tb_work_shift');
    }
};
