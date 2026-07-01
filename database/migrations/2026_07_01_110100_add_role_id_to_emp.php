<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('emp', function (Blueprint $table) {
            if (!Schema::hasColumn('emp', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable()->after('department_id'); // อ้าง roles.id
            }
        });
    }

    public function down()
    {
        Schema::table('emp', function (Blueprint $table) {
            if (Schema::hasColumn('emp', 'role_id')) {
                $table->dropColumn('role_id');
            }
        });
    }
};
