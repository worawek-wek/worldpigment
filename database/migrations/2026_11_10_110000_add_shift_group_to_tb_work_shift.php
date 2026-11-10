<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * กลุ่มของกะการผลิต (10/11/2569 ตามที่ผู้ใช้สั่ง)
 * tb_work_shift.shift_group = 'Normal Working' (เวลาปกติ) / 'Overtime' (ล่วงเวลา)
 * ตั้งค่ากะเดิม: ขึ้นต้น "ปกติ" = Normal Working · ที่เหลือ (OT / วันอาทิตย์) = Overtime
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_work_shift')) {
            return;
        }

        if (!Schema::hasColumn('tb_work_shift', 'shift_group')) {
            Schema::table('tb_work_shift', function (Blueprint $table) {
                $table->string('shift_group', 20)->default('Normal Working')->after('name')->index();
            });

            DB::table('tb_work_shift')->where('name', 'like', 'ปกติ%')->update(['shift_group' => 'Normal Working']);
            DB::table('tb_work_shift')->where('name', 'not like', 'ปกติ%')->update(['shift_group' => 'Overtime']);
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tb_work_shift', 'shift_group')) {
            Schema::table('tb_work_shift', function (Blueprint $table) {
                $table->dropIndex(['shift_group']);
                $table->dropColumn('shift_group');
            });
        }
    }
};
