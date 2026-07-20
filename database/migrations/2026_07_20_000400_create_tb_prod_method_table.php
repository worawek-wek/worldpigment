<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // ตาราง master วิธีการผลิต (ใช้เป็นตัวเลือก dropdown ในการ์ด "สถานะวิธีการผลิต")
    public function up(): void
    {
        Schema::create('tb_prod_method', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();               // ชื่อวิธีการผลิต
            $t->integer('sort')->nullable()->default(0);
            $t->enum('is_active', ['Y', 'N'])->default('Y');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_prod_method');
    }
};
