<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // แผนก (company) ระดับ planning item — override แผนกของ header ได้เป็นราย item
        // NULL = สืบทอดแผนกจาก tb_planning_header.company (พฤติกรรมเดิม)
        Schema::table('tb_planning', function (Blueprint $table) {
            $table->string('company')->nullable()->after('plan_type');
        });
    }

    public function down()
    {
        Schema::table('tb_planning', function (Blueprint $table) {
            $table->dropColumn('company');
        });
    }
};
