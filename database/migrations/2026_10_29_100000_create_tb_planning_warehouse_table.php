<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ตารางลูก: การเข้าคลังของแต่ละ planning item (1 planning → หลายแถว) (29/10/2569)
     * มิเรอร์จาก tb_planning_packing — ไม่ใส่ FK constraint (โปรเจกต์เป็น database-first) ใช้แค่ index
     */
    public function up(): void
    {
        if (!Schema::hasTable('tb_planning_warehouse')) {
            Schema::create('tb_planning_warehouse', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('planning_id')->index();  // -> tb_planning.id
                $t->dateTime('warehouse_datetime')->nullable();
                $t->decimal('weight_warehouse', 20, 6)->nullable();
                $t->text('warehouse_remark')->nullable();
                $t->integer('sort')->nullable()->default(0);
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_planning_warehouse');
    }
};
