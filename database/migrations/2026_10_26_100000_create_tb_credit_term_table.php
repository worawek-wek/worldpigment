<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ตัวเลือก "เครดิต" ในฟอร์มฐานข้อมูลลูกค้า (26/10/2569)
 *
 * 1) สร้าง tb_credit_term — เก็บข้อความไทย / อังกฤษ แยกช่อง + ข้อมูลตั้งต้น 10 แถว
 * 2) ขยาย customer.term จาก varchar(15) → varchar(100)
 *    เพราะฟอร์มเก็บ "ข้อความภาษาไทย" ของตัวเลือกลง customer.term (ยาวเกิน 15)
 *    หน้าอื่นที่อ่าน customer.term อยู่ (ใบสั่งซื้อ/ใบเสนอราคา/กำหนดราคา) จึงยังทำงานได้
 */
return new class extends Migration
{
    private const TERMS = [
        ['เงินสดเมื่อส่งมอบ', 'Cash on Delivery - COD'],
        ['โอนชำระเงินก่อนส่งสินค้า', 'T/T before Delivery'],
        ['เงินสดเมื่อมารับสินค้า', 'Cash on Goods Pick-up'],
        ['เครดิต 7 วัน นับจากวันส่งสินค้า', 'Net 7 days after Delivery Date'],
        ['เครดิต 15 วัน นับจากวันส่งสินค้า', 'Net 15 days after Delivery Date'],
        ['เครดิต 30 วัน นับจากวันส่งสินค้า', 'Net 30 days after Delivery Date'],
        ['เครดิต 45 วัน นับจากวันส่งสินค้า', 'Net 45 days after Delivery Date'],
        ['เครดิต 60 วัน นับจากวันส่งสินค้า', 'Net 60 days after Delivery Date'],
        ['เครดิต 90 วัน นับจากวันส่งสินค้า', 'Net 90 days after Delivery Date'],
        ['เครดิต 120 วัน นับจากวันส่งสินค้า', 'Net 120 days after Delivery Date'],
    ];

    public function up(): void
    {
        if (!Schema::hasTable('tb_credit_term')) {
            Schema::create('tb_credit_term', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->string('name_th', 100);
                $table->string('name_en', 100)->nullable();
                $table->integer('sort')->default(0)->index();
                $table->char('is_active', 1)->default('Y');
                $table->timestamps();
            });
        }

        foreach (self::TERMS as $i => [$th, $en]) {
            if (!DB::table('tb_credit_term')->where('name_th', $th)->exists()) {
                DB::table('tb_credit_term')->insert([
                    'name_th'    => $th,
                    'name_en'    => $en,
                    'sort'       => $i + 1,
                    'is_active'  => 'Y',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->setTermLength(100);
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_credit_term');
        // ⚠ ไม่หด customer.term กลับเป็น 15 — ค่าที่บันทึกจากตัวเลือกใหม่ยาวเกิน จะถูกตัด/ALTER ไม่ผ่าน
    }

    /** ขยาย customer.term (raw SQL — โปรเจกต์ไม่มี doctrine/dbal) */
    private function setTermLength(int $len): void
    {
        if (!Schema::hasTable('customer') || !Schema::hasColumn('customer', 'term')) {
            return;
        }

        $col = DB::selectOne(
            "SELECT CHARACTER_MAXIMUM_LENGTH AS len FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer' AND COLUMN_NAME = 'term'"
        );
        if ($col && (int) $col->len >= $len) {
            return;
        }

        // ตาราง legacy อาจมี default เป็น zero-date ⇒ ผ่อน sql_mode เฉพาะ session นี้
        $mode = DB::selectOne('SELECT @@SESSION.sql_mode AS m')->m;
        DB::statement("SET SESSION sql_mode=''");
        try {
            DB::statement("ALTER TABLE `customer` MODIFY `term` VARCHAR({$len}) NULL");
        } finally {
            DB::statement('SET SESSION sql_mode=?', [$mode]);
        }
    }
};
