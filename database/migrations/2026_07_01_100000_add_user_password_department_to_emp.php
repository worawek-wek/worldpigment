<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * เพิ่มคอลัมน์ user / password / department_id / is_active ให้ตาราง emp
     */
    public function up()
    {
        Schema::table('emp', function (Blueprint $table) {
            if (!Schema::hasColumn('emp', 'user')) {
                $table->string('user', 50)->nullable()->after('supno');       // ชื่อผู้ใช้สำหรับ login
            }
            if (!Schema::hasColumn('emp', 'password')) {
                $table->string('password', 255)->nullable()->after('user');   // เก็บเป็น bcrypt hash
            }
            if (!Schema::hasColumn('emp', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('password'); // อ้าง tb_departments.id
            }
            if (!Schema::hasColumn('emp', 'is_active')) {
                $table->enum('is_active', ['Y', 'N'])->default('Y')->after('department_id');
            }
        });
    }

    public function down()
    {
        Schema::table('emp', function (Blueprint $table) {
            foreach (['user', 'password', 'department_id', 'is_active'] as $col) {
                if (Schema::hasColumn('emp', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
