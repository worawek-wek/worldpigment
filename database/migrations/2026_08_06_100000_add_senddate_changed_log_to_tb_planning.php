<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ประวัติ "วันที่+เวลาที่เปลี่ยน" senddate — เก็บคั่นด้วย comma ขนานกับ senddate_log (จับคู่ตาม index)
        // NULL = ยังไม่เคยเปลี่ยน หรือเป็นข้อมูลเก่าก่อนมีคอลัมน์นี้
        Schema::table('tb_planning', function (Blueprint $table) {
            $table->text('senddate_changed_log')->nullable()->after('senddate_log');
        });
    }

    public function down()
    {
        Schema::table('tb_planning', function (Blueprint $table) {
            $table->dropColumn('senddate_changed_log');
        });
    }
};
