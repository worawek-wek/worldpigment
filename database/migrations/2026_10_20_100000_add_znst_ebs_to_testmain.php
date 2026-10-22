<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ช่อง "คำนวณ Znst+EBS ร่วมในชิ้นงาน =" ในฟอร์มเทียบสี (ถัดจาก Chemical Safety) — 20/10/2569
        // เก็บเป็นข้อความ เพราะผู้ใช้อาจกรอกหน่วย/เปอร์เซ็นต์ปนมา
        if (!Schema::hasColumn('testmain', 'ZnstEBS')) {
            Schema::table('testmain', function (Blueprint $table) {
                $table->string('ZnstEBS', 50)->nullable()->after('ChemSafety');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('testmain', 'ZnstEBS')) {
            Schema::table('testmain', function (Blueprint $table) {
                $table->dropColumn('ZnstEBS');
            });
        }
    }
};
