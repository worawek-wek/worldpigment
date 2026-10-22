<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// เปิดสถานะบรรจุ (is_packing) ของสถานะ Planning — Y = เปิด, N = ปิด (22/10/2569)
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('tb_planning_status', 'is_packing')) {
            Schema::table('tb_planning_status', function (Blueprint $table) {
                $table->enum('is_packing', ['Y', 'N'])->default('N')->after('sort');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tb_planning_status', 'is_packing')) {
            Schema::table('tb_planning_status', function (Blueprint $table) {
                $table->dropColumn('is_packing');
            });
        }
    }
};
