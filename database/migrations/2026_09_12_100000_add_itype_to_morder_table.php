<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * เพิ่มคอลัมน์ morder.itype — ประเภทสินค้าที่สั่ง (ช่อง itype บนฟอร์มใบสั่งซื้อ, 12/09/2569)
 * เก็บ key จาก config/order.php → itypes ('1'..'6') · เดิมไม่มีที่เก็บ ค่าที่เลือกจึงไม่ถูกบันทึก
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('morder', 'itype')) {
            Schema::table('morder', function (Blueprint $table) {
                $table->string('itype', 5)->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('morder', 'itype')) {
            Schema::table('morder', function (Blueprint $table) {
                $table->dropColumn('itype');
            });
        }
    }
};
