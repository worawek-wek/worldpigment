<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testmain extends Model
{
    use HasFactory;

    protected $table = 'testmain';
    // ใช้ id (auto-increment) เป็นตัวอ้างอิงหลักของแอป — SendNo ยังเป็น PRIMARY KEY ใน DB (legacy)
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $guarded = [];

    /**
     * ประเภทตัวอย่าง (TestType) → ป้ายชื่อสำหรับแสดงผล เช่น "3 : MB สีเม็ด"
     *
     * ค่าที่เก็บใน DB เป็นตัวเลข 1–4 (ของเดิมจาก Access) — ป้ายชื่ออยู่ใน
     * config/color_matching.php เพื่อให้ dropdown กับหน้าแสดงผลใช้ชุดเดียวกัน
     * ค่าที่ไม่รู้จัก → คืนค่าดิบ (ไม่กลืนข้อมูลที่มีอยู่จริงใน DB ทิ้ง)
     */
    public function testTypeLabel(): string
    {
        $code = trim((string) $this->TestType);
        if ($code === '') {
            return '';
        }

        $label = config('color_matching.test_type_options')[$code] ?? null;

        return $label === null ? $code : $code . ' : ' . $label;
    }

    /**
     * ผลการทดสอบตัวอย่างสี (TyResp) → ข้อมูลสำหรับแสดง badge
     *
     * เทียบเท่า Select Case ในโปรแกรม Access เดิม — รวมไว้ที่เดียว
     * เพื่อให้ตาราง / หน้ารายละเอียด แสดงเหมือนกันเสมอ
     *
     * @return array{code:string, group:string, label:string, class:string}
     */
    public function testResult(): array
    {
        $code = trim((string) $this->TyResp);
        $opt  = config('color_matching.test_result_options')[$code] ?? null;

        // ยังไม่เคยบันทึกผล (TyResp ว่าง / ค่าแปลกปลอม) → ถือเป็น "ยังไม่ตอบ"
        $group = $opt['group'] ?? 'pending';
        $label = $opt['label'] ?? 'ยังไม่ตอบ';

        return match ($group) {
            'ordered' => ['code' => $code, 'group' => 'ordered', 'label' => $label,                   'class' => 'bg-label-success'],
            'reject'  => ['code' => $code, 'group' => 'reject',  'label' => 'ไม่สั่งซื้อ: ' . $label, 'class' => 'bg-label-danger'],
            default   => ['code' => $code, 'group' => 'pending', 'label' => $label,                   'class' => 'bg-label-secondary'],
        };
    }
}
