<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ยกเลิกการเก็บ Temp ในสถานะวิธีการผลิต — ย้ายไปจัดการที่ส่วนอื่น
 * ลบเฉพาะคอลัมน์ temp_id ออกจาก tb_planning_prod_method (ตาราง master `temp` ยังคงไว้)
 */
return new class extends Migration
{
    public function up(): void
    {
        // database-first: เช็คก่อนกัน error ถ้าคอลัมน์ถูกลบไปแล้ว
        if (Schema::hasColumn('tb_planning_prod_method', 'temp_id')) {
            Schema::table('tb_planning_prod_method', function (Blueprint $t) {
                $t->dropColumn('temp_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('tb_planning_prod_method', 'temp_id')) {
            Schema::table('tb_planning_prod_method', function (Blueprint $t) {
                $t->unsignedBigInteger('temp_id')->nullable()->after('end_time');
            });
        }
    }
};
