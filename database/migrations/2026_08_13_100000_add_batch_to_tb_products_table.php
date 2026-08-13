<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * เพิ่มคอลัมน์ batch ให้ tb_products — ล็อตการผลิต (13/08/2569)
 *
 * database-first: ครอบด้วย Schema::hasColumn(...) เพราะคอลัมน์อาจถูกเพิ่มมือใน DB
 * ไปก่อนแล้ว การรัน migrate ซ้ำจะได้ไม่พัง (Duplicate column)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_products') && !Schema::hasColumn('tb_products', 'batch')) {
            Schema::table('tb_products', function (Blueprint $t) {
                $t->string('batch')->nullable()->after('pack'); // ล็อตการผลิต
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_products') && Schema::hasColumn('tb_products', 'batch')) {
            Schema::table('tb_products', function (Blueprint $t) {
                $t->dropColumn('batch');
            });
        }
    }
};
