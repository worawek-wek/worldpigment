<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ตาราง tb_holiday — วันหยุดนักขัตฤกษ์ / วันหยุดชดเชย / วันหยุดบริษัท (01/09/2569)
 *
 * database-first: ครอบด้วย Schema::hasTable(...) เพื่อให้ migrate ซ้ำได้
 * แม้ตารางจะถูกสร้างมือใน DB ไปก่อนแล้ว
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_holiday')) {
            Schema::create('tb_holiday', function (Blueprint $t) {
                $t->id();
                $t->date('holiday_date');                       // วันที่หยุด (ค.ศ. — แสดงผลเป็น พ.ศ.)
                $t->string('name', 255);                        // ชื่อวันหยุด
                $t->string('type', 20)->default('public');      // public = นักขัตฤกษ์, substitute = ชดเชย, company = วันหยุดบริษัท
                $t->string('remark', 255)->nullable();          // หมายเหตุ
                $t->char('is_active', 1)->default('Y');         // Y = เปิดใช้งาน, N = ปิด (เก็บไว้แต่ไม่นับเป็นวันหยุด)
                $t->timestamps();

                // 1 วัน = 1 แถว (กันบันทึกวันเดียวกันซ้ำ)
                $t->unique('holiday_date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_holiday');
    }
};
