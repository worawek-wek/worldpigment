<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ตารางสำเนาของไฟล์ Access `formula_2000.mdb` บน MySQL — 05/08/2569
 *
 * ไฟล์ Access มี 3 ตาราง สร้างคู่กันบน MySQL โดยใช้ชื่อ `access_<ชื่อตารางเดิม>`
 *   Compo   (758 แถว) → access_compo
 *   PdPrice (296 แถว) → access_pdprice
 *   TestMai ( 44 แถว) → access_testmai
 *
 * ชื่อคอลัมน์และขนาดยึดตาม Access ตรง ๆ (อ่านจาก ODBC display size)
 *   - Text(n)          → string(n)
 *   - Currency         → decimal(19,4)  (ข้อมูลจริงมีทศนิยม 4 ตำแหน่งเสมอ)
 *   - Date/Time        → dateTime
 *   - คอลัมน์ธง '0'/'1' → string(1)      (เก็บค่าตามที่ Access ส่งมาตรง ๆ ไม่แปลงเป็น boolean)
 *
 * ชื่อตารางใช้ตัวพิมพ์เล็กทั้งหมด เพราะ MySQL บน Windows กับ Linux ตีความตัวพิมพ์ใหญ่
 * ในชื่อตารางไม่เหมือนกัน — ถ้าใช้ `access_Compo` แล้วย้ายเครื่องจะหาตารางไม่เจอ
 *
 * ⚠ ภาษาไทยในไฟล์ Access อ่านออกมาเป็น `?` อยู่แล้วตั้งแต่ต้นทาง (system codepage)
 *   ตารางนี้เก็บได้แค่สิ่งที่ ODBC ส่งมา จึงยังเป็น `?` เหมือนเดิม
 */
return new class extends Migration
{
    public function up()
    {
        // Compo — ส่วนผสมของสูตร (PdCode = สูตร, PdCodes = ตัวที่ใส่เข้าไป)
        Schema::create('access_compo', function (Blueprint $table) {
            $table->id();

            $table->string('PdCode', 17);
            $table->string('PdCodes', 18);
            $table->decimal('Compp', 19, 4)->nullable();     // % ส่วนผสม
            $table->decimal('CNet', 19, 4)->nullable();      // ราคาต่อหน่วย
            $table->string('TestNo', 20)->nullable();
            $table->string('ChangeNo', 22)->nullable();

            $table->index('PdCode');
            $table->index('PdCodes');
            $table->index('TestNo');
        });

        // PdPrice — ราคาทุนต่อรหัสสินค้า
        Schema::create('access_pdprice', function (Blueprint $table) {
            $table->id();

            $table->string('PdCode', 17);
            $table->decimal('Price', 19, 4)->nullable();

            $table->index('PdCode');
        });

        // TestMai — หัวใบเทียบสี
        Schema::create('access_testmai', function (Blueprint $table) {
            $table->id();

            $table->string('TestNo', 20);
            $table->dateTime('Tdate')->nullable();
            $table->string('Lotno', 15)->nullable();
            $table->string('TDecs', 60)->nullable();          // คำอธิบายสูตร
            $table->string('SPdCode', 2)->nullable();
            $table->string('CCode', 6)->nullable();           // รหัสลูกค้า
            $table->string('CName', 25)->nullable();          // ชื่อลูกค้า (ไทยเป็น ? จากต้นทาง)
            $table->decimal('TQty', 19, 4)->nullable();
            $table->string('Resin', 60)->nullable();
            $table->string('SCode', 2)->nullable();
            $table->decimal('TNet', 19, 4)->nullable();
            $table->string('App', 1)->nullable();             // อนุมัติแล้วหรือยัง ('0'/'1')
            $table->dateTime('AppDate')->nullable();
            $table->string('Matcher', 20)->nullable();        // ผู้เทียบสี
            $table->string('CResin', 50)->nullable();
            $table->decimal('PRICE1', 19, 4)->nullable();

            // ธงมาตรฐาน/ข้อกำหนดสารเคมี — ค่าที่พบมีแค่ '0' กับ '1'
            $table->string('EN71', 1)->nullable();
            $table->string('AP89', 1)->nullable();
            $table->string('EU94', 1)->nullable();
            $table->string('JHOSPA', 1)->nullable();
            $table->string('EU2002', 1)->nullable();
            $table->string('ROH52', 1)->nullable();
            $table->string('MAT10', 1)->nullable();
            $table->string('NA', 1)->nullable();
            $table->string('Othe', 40)->nullable();

            $table->index('TestNo');
            $table->index('CCode');
        });
    }

    public function down()
    {
        Schema::dropIfExists('access_testmai');
        Schema::dropIfExists('access_pdprice');
        Schema::dropIfExists('access_compo');
    }
};
