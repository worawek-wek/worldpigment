<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * ขยาย qmast.Term (เทอมการชำระเงิน) จาก varchar(20) → varchar(100)
 * ฟอร์มถอด maxlength ออกแล้ว (ผู้ใช้พิมพ์เทอมยาว ๆ ได้) ถ้าไม่ขยายคอลัมน์ MySQL จะตัดข้อความทิ้งเงียบ ๆ
 *
 * ใช้ raw SQL แทน Schema::table()->change() เพราะ change() ต้องพึ่ง doctrine/dbal
 * และ schema นี้เป็น database-first (ไม่ได้ถูกนิยามด้วย migration ทั้งหมด)
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `qmast` MODIFY `Term` VARCHAR(100) NULL');
    }

    public function down(): void
    {
        // ย้อนกลับ: ตัดค่าที่ยาวเกิน 20 ก่อน ไม่งั้น ALTER จะ error/ตัดทิ้งเอง
        DB::statement('UPDATE `qmast` SET `Term` = LEFT(`Term`, 20) WHERE CHAR_LENGTH(`Term`) > 20');
        DB::statement('ALTER TABLE `qmast` MODIFY `Term` VARCHAR(20) NULL');
    }
};
