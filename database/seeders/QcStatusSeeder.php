<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QcStatus;

/**
 * สถานะ QC เริ่มต้น — ตาราง tb_qc_status (09/09/2569)
 *
 * ใช้ firstOrCreate คีย์ด้วย name → รันซ้ำได้ และ**ไม่ทับ**ค่าที่ผู้ใช้แก้ไว้เอง
 * (แถวเดิมที่มีชื่อซ้ำจะไม่ถูกสร้าง/แก้ ส่วน sort/is_active เป็นค่าเริ่มต้นตอนสร้างครั้งแรกเท่านั้น)
 */
class QcStatusSeeder extends Seeder
{
    public function run()
    {
        // [ชื่อสถานะ, ลำดับ]
        $statuses = [
            ['ผ่าน', 1],
            ['ไม่ผ่าน', 2],
            ['รอสูตรปรับแก้', 3],
        ];

        foreach ($statuses as [$name, $sort]) {
            QcStatus::firstOrCreate(
                ['name' => $name],
                [
                    'sort'      => $sort,
                    'is_active' => 'Y',
                ]
            );
        }
    }
}
