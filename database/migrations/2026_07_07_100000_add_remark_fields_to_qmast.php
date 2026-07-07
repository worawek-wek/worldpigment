<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // section "หมายเหตุ" ของใบเสนอราคา — คอลัมน์ใหม่ที่ qmast เดิมยังไม่มี
        // (ราคานี้มีผลวันที่ = ValidFrom→Validto, จำนวนส่งมอบขั้นต่ำ = Qremark,
        //  เทอมการชำระเงิน = Term ใช้คอลัมน์เดิม)
        Schema::table('qmast', function (Blueprint $table) {
            $table->string('resin_price_note', 100)->nullable()->after('Revisedate'); // ราคาเม็ดพลาสติก (ข้อความ)
            $table->string('delivery_place', 100)->nullable()->after('resin_price_note'); // สถานที่ส่งสินค้า
            $table->string('delivery_term', 50)->nullable()->after('delivery_place');      // เทอมการส่งมอบสินค้า (เช่น DDP)
            $table->text('other_notes')->nullable()->after('delivery_term');               // หมายเหตุอื่น (JSON array)
        });
    }

    public function down()
    {
        Schema::table('qmast', function (Blueprint $table) {
            $table->dropColumn(['resin_price_note', 'delivery_place', 'delivery_term', 'other_notes']);
        });
    }
};
