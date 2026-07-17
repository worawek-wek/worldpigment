<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ยี่ห้อของชิ้นงานที่นำไปทำ (คู่กับ column Model ในฟอร์มใบนำส่งเทียบสี)
        Schema::table('testmain', function (Blueprint $table) {
            $table->string('Brand', 50)->nullable()->after('Model');
        });
    }

    public function down()
    {
        Schema::table('testmain', function (Blueprint $table) {
            $table->dropColumn('Brand');
        });
    }
};
