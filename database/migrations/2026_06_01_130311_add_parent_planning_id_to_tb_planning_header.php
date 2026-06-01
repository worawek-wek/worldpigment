<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_planning_header', function (Blueprint $table) {
            // link sub-order header กลับหา planning item แม่
            $table->unsignedBigInteger('parent_planning_id')->nullable()->after('plan_type');
        });
    }

    public function down()
    {
        Schema::table('tb_planning_header', function (Blueprint $table) {
            $table->dropColumn('parent_planning_id');
        });
    }
};
