<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * - เปลี่ยนชื่อคอลัมน์ quantity -> weight_request
     * - เพิ่มคอลัมน์ใหม่: semi_code, primary_color, balance, lot_no,
     *   retrospective, increase_production, weight_production, red_bill_code
     */
    public function up()
    {
        // เปลี่ยนชื่อ quantity -> weight_request (ใช้ raw SQL เพราะไม่มี doctrine/dbal)
        DB::statement("ALTER TABLE tb_semi_pigment
            CHANGE quantity weight_request DECIMAL(15,2) NULL");

        Schema::table('tb_semi_pigment', function (Blueprint $table) {
            $table->string('semi_code')->nullable()->after('itemno');               // รหัสกึ่งสำเร็จรูป
            $table->string('primary_color')->nullable()->after('semi_code');         // แม่สี
            $table->decimal('balance', 15, 2)->nullable()->after('weight_request');  // ยอดคงเหลือวันนี้
            $table->string('lot_no')->nullable()->after('balance');                  // ล็อตที่
            $table->decimal('retrospective', 15, 2)->nullable()->after('lot_no');    // ยอดใช้ย้อนหลัง 2 เดือน
            $table->decimal('increase_production', 15, 2)->nullable()->after('retrospective'); // ผลิตเพิ่ม
            $table->decimal('weight_production', 15, 2)->nullable()->after('increase_production'); // น้ำหนักที่จะผลิต
            $table->string('red_bill_code')->nullable()->after('weight_production'); // รหัสบิลแดง
        });
    }

    public function down()
    {
        Schema::table('tb_semi_pigment', function (Blueprint $table) {
            $table->dropColumn([
                'semi_code',
                'primary_color',
                'balance',
                'lot_no',
                'retrospective',
                'increase_production',
                'weight_production',
                'red_bill_code',
            ]);
        });

        DB::statement("ALTER TABLE tb_semi_pigment
            CHANGE weight_request quantity DECIMAL(15,2) NULL");
    }
};
