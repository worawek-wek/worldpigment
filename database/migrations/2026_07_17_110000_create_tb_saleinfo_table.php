<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ระบบกำหนดราคา (ราคาสินค้าต่อลูกค้า)
 *
 * ชื่อคอลัมน์ยึดตามตาราง `uprice` ของเดิม เพื่อให้ฟอร์มที่ทำไว้แล้วใช้ได้ทันที
 * และ copy ข้อมูลจาก uprice มาได้ตรงๆ — uprice ใช้อ่านอย่างเดียว ไม่เขียนทับ
 *
 * ยังไม่รวมกล่อง "ราคา 1/2/3 (DB tier)" และ ค่าสี/%สี — รอสรุปกับลูกค้าก่อน
 */
return new class extends Migration
{
    public function up()
    {
        // database-first: ข้ามถ้าตารางถูกสร้างมือใน DB ไปแล้ว
        if (Schema::hasTable('tb_saleinfo')) {
            return;
        }

        Schema::create('tb_saleinfo', function (Blueprint $table) {
            $table->id();

            // ลูกค้า และสินค้า
            $table->string('CustNo', 6)->nullable();      // customer.code
            $table->string('st_code', 17)->nullable();    // ชื่อสินค้า
            $table->string('ITEMNO', 17)->nullable();     // รหัสสินค้า

            // ราคา
            $table->dateTime('DATE')->nullable();         // วันที่เริ่มซื้อ
            $table->double('PRICE')->nullable();
            $table->boolean('NoAcp')->default(0);         // ไม่รับ Order เบอร์นี้
            $table->string('REM1', 100)->nullable();      // หมายเหตุ
            $table->string('REM2', 100)->nullable();      // หมายเหตุเพิ่มเติม / ประวัติการปรับราคา

            // บรรจุภัณฑ์ และผู้อนุมัติ
            $table->string('PackRem', 85)->nullable();    // ระบุข้างบรรจุภัณฑ์
            $table->string('Label', 85)->nullable();      // Label DB
            $table->string('Author', 50)->nullable();     // ผู้บริหาร (ผู้อนุมัติราคา)
            $table->dateTime('AuthDate')->nullable();
            $table->integer('Black')->nullable();

            $table->timestamps();

            $table->index(['CustNo', 'ITEMNO']);
            $table->index('st_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_saleinfo');
    }
};
