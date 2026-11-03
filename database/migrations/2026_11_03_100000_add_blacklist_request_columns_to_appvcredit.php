<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ฟอร์ม "ขอเปลี่ยนแปลง ข้อมูล Black List / เปลี่ยนแปลงเครดิต" — 03/11/2569
 *
 * เก็บใบขอที่ appvcredit (ตาราง legacy ของโปรแกรมเดิม) แต่ของเดิมขอได้แค่ "เปลี่ยนเป็นปกติ" (BlackNew = -1)
 * จึงเพิ่มคอลัมน์ให้ขอเปลี่ยนเป็นสถานะใดก็ได้ + เก็บผู้ขอ/ผู้อนุมัติ:
 *   BlackNewVal   สถานะที่ขอ (ค่าเดียวกับ customer.black) — NULL = ไม่ได้ขอเปลี่ยนสถานะ
 *   BlackDateNew  Black List Date ที่ขอ
 *   BlackRemNew   Black Remark ที่ขอ (50 = ความยาว customer.blackrem)
 *   ReqBy / ReqByName    ผู้ขอ (empno หรือ U<users.id>)
 *   AppvDT / AppvBy / AppvByName   เวลาที่อนุมัติ + บัญชีที่ล็อกอินตอนอนุมัติ
 * และขยาย TermOld / TermNew จาก varchar(15) → varchar(100) ให้เท่า customer.term
 */
return new class extends Migration
{
    private const ADD = [
        'BlackNewVal'  => 'TINYINT NULL',
        'BlackDateNew' => 'DATE NULL',
        'BlackRemNew'  => 'VARCHAR(50) NULL',
        'ReqBy'        => 'VARCHAR(20) NULL',
        'ReqByName'    => 'VARCHAR(100) NULL',
        'AppvDT'       => 'DATETIME NULL',
        'AppvBy'       => 'VARCHAR(20) NULL',
        'AppvByName'   => 'VARCHAR(100) NULL',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('appvcredit')) {
            return;
        }

        // ReqDate มี default '0000-00-00 00:00:00' — strict sql_mode ไม่ยอมให้ ALTER → ผ่อนเฉพาะ session นี้
        $mode = DB::selectOne('SELECT @@SESSION.sql_mode AS m')->m;
        DB::statement("SET SESSION sql_mode=''");

        try {
            DB::statement('ALTER TABLE `appvcredit` MODIFY `TermOld` VARCHAR(100) NULL, MODIFY `TermNew` VARCHAR(100) NULL');

            foreach (self::ADD as $col => $def) {
                if (!Schema::hasColumn('appvcredit', $col)) {
                    DB::statement("ALTER TABLE `appvcredit` ADD `$col` $def");
                }
            }
        } finally {
            DB::statement('SET SESSION sql_mode=?', [$mode]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('appvcredit')) {
            return;
        }

        $mode = DB::selectOne('SELECT @@SESSION.sql_mode AS m')->m;
        DB::statement("SET SESSION sql_mode=''");

        try {
            foreach (array_keys(self::ADD) as $col) {
                if (Schema::hasColumn('appvcredit', $col)) {
                    DB::statement("ALTER TABLE `appvcredit` DROP `$col`");
                }
            }
        } finally {
            DB::statement('SET SESSION sql_mode=?', [$mode]);
        }
    }
};
