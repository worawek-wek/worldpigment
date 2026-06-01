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
        Schema::table('tb_planning', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_planning_id')->nullable()->after('planning_header_id');
        });
    }

    public function down()
    {
        Schema::table('tb_planning', function (Blueprint $table) {
            $table->dropColumn('parent_planning_id');
        });
    }
};
