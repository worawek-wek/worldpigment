<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ระบบ Complaint & NCR — ช่องสรุปสาเหตุเลือกจากรายการ A–I หรือพิมพ์เองได้ (27/11/2569)
 * nc.CauseType varchar(5) → varchar(100) · idempotent · down() ไม่หดกลับ (กันข้อความยาวถูกตัด)
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('nc', 'CauseType')) {
            return;
        }

        $len = DB::selectOne(
            'SELECT CHARACTER_MAXIMUM_LENGTH l FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            ['nc', 'CauseType']
        )->l ?? null;

        if ($len !== null && (int) $len < 100) {
            DB::statement('ALTER TABLE `nc` MODIFY `CauseType` VARCHAR(100) NULL DEFAULT NULL');
        }
    }

    public function down()
    {
        // ไม่หดกลับเป็น 5 — ข้อความที่พิมพ์ไว้ยาวกว่าจะถูกตัด
    }
};
