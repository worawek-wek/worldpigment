<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * เปลี่ยน tb_planning.qc_status จาก ENUM('PASSED','FAILED','PENDINGREVISION')
 * → BIGINT UNSIGNED (เก็บ id ที่อ้างอิง master tb_qc_status) — 09/09/2569
 *
 * ⚠ ต้องแปลงค่าเดิม (สตริง enum) → id ก่อนเปลี่ยนชนิดคอลัมน์
 *   ไม่งั้น STRICT_TRANS_TABLES จะ error หรือแปลงสตริงเป็น 0
 *
 * ขั้นตอน up():
 *   1) enum → varchar(255)  (คงค่าสตริงเดิมไว้)
 *   2) map สตริงเดิม → id ของ master (firstOrCreate กันกรณี master ยังว่าง / seeder ยังไม่รัน)
 *   3) ค่าที่เหลือซึ่งไม่ใช่ตัวเลข → NULL (กันตกค้าง)
 *   4) varchar → bigint unsigned
 *
 * raw SQL เพราะโปรเจกต์ไม่มี doctrine/dbal (แก้ชนิดคอลัมน์ผ่าน Blueprint ตรง ๆ ไม่ได้)
 * idempotent: เช็คชนิดคอลัมน์ปัจจุบันก่อน
 */
return new class extends Migration
{
    /** map: ค่า enum เดิม → ชื่อใน master tb_qc_status (+ ลำดับ sort ตอนสร้างครั้งแรก) */
    private array $map = [
        'PASSED'          => ['ผ่าน', 1],
        'FAILED'          => ['ไม่ผ่าน', 2],
        'PENDINGREVISION' => ['รอสูตรปรับแก้', 3],
    ];

    public function up(): void
    {
        if (!$this->columnTypeStartsWith('enum')) {
            return; // แปลงไปแล้ว — no-op
        }

        // 1) enum → varchar คงค่าสตริงเดิม
        DB::statement("ALTER TABLE `tb_planning` MODIFY `qc_status` VARCHAR(255) NULL");

        // 2) map สตริงเดิม → id ของ master
        foreach ($this->map as $old => [$name, $sort]) {
            $id = DB::table('tb_qc_status')->where('name', $name)->value('id');
            if (!$id) {
                $id = DB::table('tb_qc_status')->insertGetId([
                    'name'       => $name,
                    'sort'       => $sort,
                    'is_active'  => 'Y',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('tb_planning')->where('qc_status', $old)->update(['qc_status' => $id]);
        }

        // 3) ค่าที่เหลือซึ่งไม่ใช่ตัวเลขล้วน → NULL (กันตกค้างจนแปลงชนิดพัง)
        DB::statement("UPDATE `tb_planning` SET `qc_status` = NULL
            WHERE `qc_status` IS NOT NULL AND `qc_status` NOT REGEXP '^[0-9]+$'");

        // 4) varchar → bigint unsigned (ให้ตรงชนิดกับ tb_qc_status.id)
        DB::statement("ALTER TABLE `tb_planning` MODIFY `qc_status` BIGINT UNSIGNED NULL");
    }

    public function down(): void
    {
        if ($this->columnTypeStartsWith('enum')) {
            return; // เป็น enum อยู่แล้ว — no-op
        }

        // 1) int → varchar
        DB::statement("ALTER TABLE `tb_planning` MODIFY `qc_status` VARCHAR(255) NULL");

        // 2) map id → สตริง enum เดิม (ผ่าน name ของ master)
        foreach ($this->map as $enum => [$name]) {
            $id = DB::table('tb_qc_status')->where('name', $name)->value('id');
            if ($id) {
                DB::table('tb_planning')->where('qc_status', $id)->update(['qc_status' => $enum]);
            }
        }

        // 3) ค่าที่ไม่ตรงกับ enum 3 ค่า → NULL (กัน strict error ตอนแปลงกลับเป็น enum)
        DB::statement("UPDATE `tb_planning` SET `qc_status` = NULL
            WHERE `qc_status` IS NOT NULL
            AND `qc_status` NOT IN ('PASSED','FAILED','PENDINGREVISION')");

        // 4) varchar → enum เดิม
        DB::statement("ALTER TABLE `tb_planning`
            MODIFY `qc_status` ENUM('PASSED','FAILED','PENDINGREVISION') NULL");
    }

    /** ชนิดคอลัมน์ qc_status ปัจจุบันขึ้นต้นด้วย $prefix หรือไม่ (เช่น 'enum', 'bigint') */
    private function columnTypeStartsWith(string $prefix): bool
    {
        $col = DB::select("SHOW COLUMNS FROM `tb_planning` LIKE 'qc_status'");
        $type = strtolower($col[0]->Type ?? '');
        return str_starts_with($type, $prefix);
    }
};
