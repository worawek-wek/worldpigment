<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Chemical Safety (ฟอร์มใบส่ง ต.ย.) — มาตรฐานความปลอดภัยสารเคมีของชิ้นงาน
        // เช่น EN 71:3, RoHS2 (EU2011/65/EU) หรือค่าที่ผู้ใช้พิมพ์เอง ("อื่นๆ ระบุ" ในฟอร์มกระดาษ)
        // เก็บเป็น varchar เพราะรายการมาตรฐานเพิ่มได้เรื่อย ๆ (ตั้งชื่อสไตล์เดียวกับ column เดิมของ testmain)
        Schema::table('testmain', function (Blueprint $table) {
            $table->string('ChemSafety', 255)->nullable()->after('ColorChar');
        });
    }

    public function down()
    {
        Schema::table('testmain', function (Blueprint $table) {
            $table->dropColumn('ChemSafety');
        });
    }
};
