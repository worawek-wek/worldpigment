<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // เพิ่ม flag "role เริ่มต้นของพนักงานใหม่" (Y = เป็นค่าเริ่มต้น) — idempotent กันรันซ้ำ
        if (Schema::hasTable('roles') && !Schema::hasColumn('roles', 'is_default')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->enum('is_default', ['Y', 'N'])->default('N')->after('is_active');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'is_default')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('is_default');
            });
        }
    }
};
