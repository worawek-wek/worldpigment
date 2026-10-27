<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * เพิ่มตัวเลือก "เครดิต" ใน tb_credit_term (27/10/2569 ตามที่ผู้ใช้สั่ง)
 * ช่องไทย / อังกฤษ ใช้ข้อความเดียวกัน · ต่อท้ายลำดับเดิม
 * "อื่น ๆ" ไม่ได้เพิ่มเป็นแถว — ฟอร์มลูกค้าพิมพ์ค่าเองได้ (select2-tags)
 */
return new class extends Migration
{
    private const TERMS = [
        'WPC,WH=60,WI=30',
        'WPC,WH=90,WI=30',
        'WPC,WH=90,WI=60',
        'WPC=60,WI=30',
        'WPC=90,WI=60',
        'WH,WPC=60,WI=60',
        'WH,WPC=90,WI=30',
        'WH,WPC=90,WI=60',
        'WH30วัน,WI90วัน',
        'WH=30 WI=30',
        'WH=60 WI=30',
        'WH=60, WPI 90',
        'WI=0%,WH/WPC=4%',
        'WPC=30,WPI=60',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('tb_credit_term')) {
            return;
        }

        $sort = (int) DB::table('tb_credit_term')->max('sort');
        foreach (self::TERMS as $name) {
            if (DB::table('tb_credit_term')->where('name_th', $name)->exists()) {
                continue;
            }
            DB::table('tb_credit_term')->insert([
                'name_th'    => $name,
                'name_en'    => $name,
                'sort'       => ++$sort,
                'is_active'  => 'Y',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_credit_term')) {
            DB::table('tb_credit_term')->whereIn('name_th', self::TERMS)->delete();
        }
    }
};
