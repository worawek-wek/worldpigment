<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ผู้สร้างใบเสนอราคา (12/10/2569) — ใช้ตรวจรหัสผ่านของผู้สร้างตอนแก้ไขใบ
        // ระบบมีบัญชี 2 แบบ จึงต้องเก็บคู่กัน: created_by_type = 'emp' (พนักงาน, created_by = emp.empno)
        //                                    หรือ 'user' (admin, created_by = users.id)
        // database-first: เช็คก่อนกัน error "Duplicate column" ถ้าเพิ่มมือใน DB ไปแล้ว
        if (!Schema::hasColumn('qmast', 'created_by_type')) {
            Schema::table('qmast', function (Blueprint $table) {
                $table->string('created_by_type', 5)->nullable()->after('col_config');
            });
        }
        if (!Schema::hasColumn('qmast', 'created_by')) {
            Schema::table('qmast', function (Blueprint $table) {
                $table->string('created_by', 20)->nullable()->after('created_by_type');
            });
        }
    }

    public function down()
    {
        foreach (['created_by', 'created_by_type'] as $col) {
            if (Schema::hasColumn('qmast', $col)) {
                Schema::table('qmast', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }
};
