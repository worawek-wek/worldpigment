<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tb_planning', function (Blueprint $table) {
            $table->date('mdate')->nullable()->after('packing_datetie');
            $table->date('custwant')->nullable()->after('mdate');
            $table->date('senddate')->nullable()->after('custwant');
        });
    }

    public function down()
    {
        Schema::table('tb_planning', function (Blueprint $table) {
            $table->dropColumn(['mdate', 'custwant', 'senddate']);
        });
    }
};
