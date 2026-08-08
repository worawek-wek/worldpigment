<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // ลำดับคิวการผลิตต่อเครื่องจักร+ต่อวัน (ใช้จัดคิว/แทรกคิวในหน้ารายงานผลิตตามเครื่องจักร) — 2026-08-08
    // NULL = ยังไม่จัดคิว → ตกไปเรียงตามเวลา (job_key) ตามเดิม
    public function up()
    {
        if (! Schema::hasColumn('tb_planning', 'queue_sort')) {
            Schema::table('tb_planning', function (Blueprint $table) {
                $table->integer('queue_sort')->nullable()->after('inplan');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tb_planning', 'queue_sort')) {
            Schema::table('tb_planning', function (Blueprint $table) {
                $table->dropColumn('queue_sort');
            });
        }
    }
};
