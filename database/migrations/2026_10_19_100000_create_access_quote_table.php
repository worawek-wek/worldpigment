<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ตารางสำเนาของไฟล์ Access `public/sql/Quote.mdb` บน MySQL — 19/10/2569
 *
 * ไฟล์มีตารางเดียว `Quote` (9,374 แถว) → access_quote (ตั้งชื่อตามแพทเทิร์น access_compo / access_testmai)
 *   TestNo Text(15) — PK ใน Access (รูปแบบเดียวกับ access_testmai.TestNo เช่น 26/0055/1)
 *   Remark Text(50) — หมายเหตุ/ราคาที่เสนอ (เช่น "p.8.59.- 1/11/56")
 *
 * ข้อมูลนำเข้าด้วย ACE OLEDB ผ่าน PowerShell (ห้ามใช้ ODBC ของ PHP — ภาษาไทยจะกลายเป็น ?)
 */
return new class extends Migration
{
    public function up()
    {
        // database-first: ข้ามถ้ามีตารางอยู่แล้ว
        if (Schema::hasTable('access_quote')) {
            return;
        }

        Schema::create('access_quote', function (Blueprint $table) {
            $table->id();

            $table->string('TestNo', 15)->unique();
            $table->string('Remark', 50)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('access_quote');
    }
};
