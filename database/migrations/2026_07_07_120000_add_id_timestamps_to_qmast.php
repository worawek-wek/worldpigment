<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // qmast เดิมไม่มี PRIMARY KEY (Qno เป็น varchar ธรรมดา)
        // เพิ่ม id AUTO_INCREMENT เป็น PK + created_at/updated_at
        DB::statement('ALTER TABLE `qmast`
            ADD COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
            ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT NULL,
            ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE `qmast`
            DROP PRIMARY KEY,
            DROP COLUMN `id`,
            DROP COLUMN `created_at`,
            DROP COLUMN `updated_at`');
    }
};
