<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ภาษาที่เลือกให้แสดง label ของ section หมายเหตุ ('th' | 'en')
        // สลับแค่ "คำ" (หัวข้อ) — ข้อมูลค่าต่าง ๆ เก็บชุดเดียว
        Schema::table('qmast', function (Blueprint $table) {
            $table->string('remark_lang', 2)->default('th')->after('other_notes');
        });
    }

    public function down()
    {
        Schema::table('qmast', function (Blueprint $table) {
            $table->dropColumn('remark_lang');
        });
    }
};
