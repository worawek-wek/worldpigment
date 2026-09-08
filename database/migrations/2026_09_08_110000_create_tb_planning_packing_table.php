<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ตารางลูก: การบรรจุของแต่ละ planning item (1 planning → หลายแถว)
     * มิเรอร์แพทเทิร์นจาก tb_planning_prod_method — ไม่ใส่ FK constraint (โปรเจกต์เป็น database-first) ใช้แค่ index
     *
     * ชนิดคอลัมน์ให้ตรงกับคอลัมน์เดิมบน tb_planning:
     *   packing_datetie datetime | weight_packing decimal(20,6) | pack_remark text  (ทั้งหมด nullable)
     * คอลัมน์วันเวลาในตารางใหม่ตั้งชื่อให้ถูก (packing_datetime) ไม่สืบทอดคำสะกดผิดเดิม
     */
    public function up(): void
    {
        if (!Schema::hasTable('tb_planning_packing')) {
            Schema::create('tb_planning_packing', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('planning_id')->index();  // -> tb_planning.id
                $t->dateTime('packing_datetime')->nullable();
                $t->decimal('weight_packing', 20, 6)->nullable();
                $t->text('pack_remark')->nullable();
                $t->integer('sort')->nullable()->default(0);
                $t->timestamps();
            });
        }

        // ── ย้ายข้อมูลเดิมจากคอลัมน์บน tb_planning เข้าตารางลูก (1 แถวต่อ 1 planning) ──
        // guard: ทำเฉพาะเมื่อตารางลูกยังว่าง (กันคัดลอกซ้ำเมื่อรัน migrate หลายรอบ)
        // เงื่อนไข: มีค่าอย่างน้อย 1 ใน 3 คอลัมน์ (คอลัมน์เดิมยังต้องมีอยู่ตอนรัน migration นี้)
        $alreadyMigrated = DB::table('tb_planning_packing')->exists();
        $hasLegacyCols = Schema::hasColumn('tb_planning', 'packing_datetie');

        if (!$alreadyMigrated && $hasLegacyCols) {
            DB::statement("
                INSERT INTO tb_planning_packing
                    (planning_id, packing_datetime, weight_packing, pack_remark, sort, created_at, updated_at)
                SELECT id, packing_datetie, weight_packing, pack_remark, 0, NOW(), NOW()
                FROM tb_planning
                WHERE packing_datetie IS NOT NULL
                   OR weight_packing IS NOT NULL
                   OR (pack_remark IS NOT NULL AND pack_remark <> '')
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_planning_packing');
    }
};
