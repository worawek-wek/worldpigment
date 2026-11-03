<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ใบนำส่งเทียบสี: เม็ดพลาสติกที่ใช้เทียบสี (02/11/2569)
        // ResinSource = 'WPI' ใช้เม็ดของ WPI · 'CUST' ใช้เม็ดของลูกค้า · NULL ไม่ระบุ
        if (!Schema::hasColumn('testmain', 'ResinSource')) {
            Schema::table('testmain', function (Blueprint $table) {
                $table->string('ResinSource', 10)->nullable()->after('CustPigment');
            });
        }

        // จำนวนเม็ดของลูกค้าที่มาพร้อมชิ้นงาน (กก.)
        if (!Schema::hasColumn('testmain', 'CustResinKg')) {
            Schema::table('testmain', function (Blueprint $table) {
                $table->decimal('CustResinKg', 10, 2)->nullable()->after('ResinSource');
            });
        }
    }

    public function down()
    {
        foreach (['CustResinKg', 'ResinSource'] as $column) {
            if (Schema::hasColumn('testmain', $column)) {
                Schema::table('testmain', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
