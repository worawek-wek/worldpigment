<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ใบเทียบสี: ช่อง "แก้ไขจาก Lot No." (21/11/2569 ตามที่ผู้ใช้สั่ง) — ยาวเท่า lotno
        if (!Schema::hasColumn('testmain', 'AdjFromLot')) {
            Schema::table('testmain', function (Blueprint $table) {
                $table->string('AdjFromLot', 20)->nullable()->after('Adj');
            });
        }

        // คุณสมบัติ (pop) เลือกได้หลายค่า เก็บรวมคั่นด้วย ", " แบบ ChemSafety → ขยาย 50 → 255 (21/11/2569)
        // raw SQL เพราะไม่มี doctrine/dbal · ขยายอย่างเดียว ข้อมูลเดิมไม่เปลี่ยน
        $col = DB::selectOne("SELECT CHARACTER_MAXIMUM_LENGTH len FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'testmain' AND COLUMN_NAME = 'pop'");
        if ($col && (int) $col->len < 255) {
            DB::statement('ALTER TABLE testmain MODIFY pop VARCHAR(255) NULL');
        }
    }

    public function down()
    {
        if (Schema::hasColumn('testmain', 'AdjFromLot')) {
            Schema::table('testmain', function (Blueprint $table) {
                $table->dropColumn('AdjFromLot');
            });
        }
        // pop ไม่หดกลับ (ค่าหลายรายการที่ยาวเกิน 50 จะถูกตัด)
    }
};
