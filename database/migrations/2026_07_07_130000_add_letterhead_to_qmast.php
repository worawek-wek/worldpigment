<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // หัวกระดาษ (หัวเอกสารที่พิมพ์) — WPI / WPC / WH (ตรงกับ prefix ของเลขที่ใบเสนอราคา)
        // database-first: เช็คก่อนกัน error "Duplicate column" ถ้าเพิ่มมือใน DB ไปแล้ว
        if (!Schema::hasColumn('qmast', 'letterhead')) {
            Schema::table('qmast', function (Blueprint $table) {
                $table->string('letterhead', 10)->nullable()->default('WH')->after('PDtype');
            });
        }
    }

    public function down()
    {
        Schema::table('qmast', function (Blueprint $table) {
            $table->dropColumn('letterhead');
        });
    }
};
