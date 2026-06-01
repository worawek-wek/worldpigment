<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tb_planning', function (Blueprint $table) {
            $table->text('semi')->nullable()->after('packing_datetie');
            $table->text('pigment')->nullable()->after('semi');
        });
    }

    public function down()
    {
        Schema::table('tb_planning', function (Blueprint $table) {
            $table->dropColumn(['semi', 'pigment']);
        });
    }
};
