<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ระบบ Complaint & NCR — คอลัมน์ตามสเปกฟอร์มใหม่ (26/11/2569) — idempotent
 *
 * nc:    CauseTypeRem (14 ข้อความเพิ่มเติมเมื่อสรุปสาเหตุเป็น I) · CauseDate (16 วันที่สรุปสาเหตุ)
 *        FollowUp (21 การติดตามผล) · ProdAlert (23 แจ้งเตือนผลิตระวัง) · CloseDate (24 วันที่ปิดประเด็น)
 * treat: TreatDetail (27 รายละเอียดการแก้ไข) · ค่า material/process/QC/packing/เคลม int → decimal(12,2)
 */
return new class extends Migration
{
    private const NC_COLUMNS = [
        'CauseTypeRem' => 'VARCHAR(100) NULL',
        'CauseDate'    => 'DATE NULL',
        'FollowUp'     => 'VARCHAR(200) NULL',
        'ProdAlert'    => 'TINYINT(1) NOT NULL DEFAULT 0',
        'CloseDate'    => 'DATE NULL',
    ];

    private const TREAT_MONEY = ['TreatRaw', 'TreatProcess', 'TreatQC', 'TreatPack', 'claim'];

    public function up()
    {
        foreach (self::NC_COLUMNS as $col => $def) {
            if (!Schema::hasColumn('nc', $col)) {
                DB::statement("ALTER TABLE `nc` ADD COLUMN `$col` $def");
            }
        }

        if (!Schema::hasColumn('treat', 'TreatDetail')) {
            DB::statement('ALTER TABLE `treat` ADD COLUMN `TreatDetail` VARCHAR(30) NULL AFTER `TreatNo`');
        }

        foreach (self::TREAT_MONEY as $col) {
            $type = DB::selectOne(
                'SELECT DATA_TYPE t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                ['treat', $col]
            )->t ?? null;

            if ($type && $type !== 'decimal') {
                DB::statement("ALTER TABLE `treat` MODIFY `$col` DECIMAL(12,2) NULL DEFAULT NULL");
            }
        }
    }

    public function down()
    {
        foreach (array_keys(self::NC_COLUMNS) as $col) {
            if (Schema::hasColumn('nc', $col)) {
                DB::statement("ALTER TABLE `nc` DROP COLUMN `$col`");
            }
        }
        if (Schema::hasColumn('treat', 'TreatDetail')) {
            DB::statement('ALTER TABLE `treat` DROP COLUMN `TreatDetail`');
        }
        foreach (self::TREAT_MONEY as $col) {
            DB::statement("ALTER TABLE `treat` MODIFY `$col` INT(11) NULL DEFAULT NULL");
        }
    }
};
