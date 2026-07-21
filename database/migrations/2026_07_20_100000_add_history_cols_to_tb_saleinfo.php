<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * เพิ่มคอลัมน์สำหรับ "ประวัติการปรับราคา" ให้ตาราง tb_saleinfo
 *
 * ตามฟอร์มของลูกค้า (จอ Excel) แต่ละครั้งที่ปรับราคา = 1 แถว โดยเก็บ:
 *   - NotifyDate = วันที่แจ้งปรับ (คนละวันกับวันที่เริ่มราคาใหม่ = คอลัมน์ DATE เดิม)
 *   - MOQ        = ปริมาณสั่งซื้อขั้นต่ำ (kg)
 *
 * คอลัมน์อื่นในฟอร์มใช้ของเดิม:
 *   DATE = วันที่เริ่มราคาใหม่, ITEMNO = รหัสสินค้า,
 *   PRICE = ราคา หรือ ค่าแรง+ค่าสี, REM1 = หมายเหตุ
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('tb_saleinfo', function (Blueprint $table) {
            $table->dateTime('NotifyDate')->nullable()->after('DATE');   // วันที่แจ้งปรับ
            $table->double('MOQ')->nullable()->after('NotifyDate');      // MOQ (kg)
        });
    }

    public function down()
    {
        Schema::table('tb_saleinfo', function (Blueprint $table) {
            $table->dropColumn(['NotifyDate', 'MOQ']);
        });
    }
};
