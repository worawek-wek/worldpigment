<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tb_planning', function (Blueprint $table) {
            // เวลาที่เปลี่ยน senddate "ล่าสุด" ค่าเดียว (เขียนทับทุกครั้ง) — ใช้สำหรับค้นหาว่าวันไหนมีการเปลี่ยน
            // มี index เพื่อให้ whereDate('senddate_changed_at', ...) ใช้ index ได้
            $table->dateTime('senddate_changed_at')->nullable()->after('senddate_log');
            $table->index('senddate_changed_at');
        });

        // เลิกใช้ประวัติเวลาแบบ log (comma-separated) — แทนที่ด้วย senddate_changed_at ด้านบน
        if (Schema::hasColumn('tb_planning', 'senddate_changed_log')) {
            Schema::table('tb_planning', function (Blueprint $table) {
                $table->dropColumn('senddate_changed_log');
            });
        }
    }

    public function down()
    {
        Schema::table('tb_planning', function (Blueprint $table) {
            $table->dropIndex(['senddate_changed_at']);
            $table->dropColumn('senddate_changed_at');
            $table->text('senddate_changed_log')->nullable()->after('senddate_log');
        });
    }
};
