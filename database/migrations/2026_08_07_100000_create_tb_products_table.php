<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ตาราง tb_products — ข้อมูลสินค้า (07/08/2569)
 *
 * database-first: ครอบด้วย Schema::hasTable(...) เพื่อให้ migrate ซ้ำได้
 * แม้ตารางจะถูกสร้างมือใน DB ไปก่อนแล้ว
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_products')) {
            Schema::create('tb_products', function (Blueprint $t) {
                $t->id();
                $t->string('product_code')->nullable();        // รหัสสินค้า
                $t->string('resin')->nullable();               // เรซิน
                $t->unsignedBigInteger('temp_id')->nullable(); // อ้างอิงตาราง temp
                $t->string('code')->nullable();                // รหัส
                $t->string('pack')->nullable();                // ขนาดบรรจุ
                $t->string('sampling')->nullable();            // การชักตัวอย่าง
                $t->timestamps();                              // created_at / updated_at
                $t->unsignedBigInteger('created_by')->nullable();
                $t->unsignedBigInteger('updated_by')->nullable();

                $t->index('product_code');
                $t->index('temp_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_products');
    }
};
