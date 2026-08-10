<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ค่าตัวคูณ/หาร/บวก ของตารางเงื่อนไขราคา ที่ผู้ใช้แก้เองได้จากหน้าจอ (10/08/2569)
 *
 * เก็บเฉพาะ "ค่าที่ถูกแก้" — โครงเงื่อนไข (label / prefix / suffix / suffix_pos)
 * ยังอยู่ใน config/product_price.php แถวไหนไม่มีในตารางนี้ = ใช้ค่าตั้งต้นจาก config
 * ผูกกันด้วย rule_key ซึ่งตรงกับคีย์ 'key' ของแต่ละแถวใน config
 */
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tb_price_rule')) {
            return;
        }

        Schema::create('tb_price_rule', function (Blueprint $table) {
            $table->id();
            $table->string('rule_key', 50)->unique();   // ตรงกับ config('product_price.rules.*.key')
            $table->decimal('mul', 12, 4);              // ×คูณ
            $table->decimal('div', 12, 4);              // /หาร  (ห้ามเป็น 0 — controller กันไว้)
            $table->decimal('add', 12, 4);              // บวก+
            $table->string('updated_by', 100)->nullable(); // ใครแก้ล่าสุด (ราคาขายเป็นข้อมูลอ่อนไหว)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_price_rule');
    }
};
