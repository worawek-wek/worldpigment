<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $table = 'machine';

    protected $guarded = [];

    // ตาราง machine ไม่มีคอลัมน์ created_at/updated_at
    public $timestamps = false;

    /**
     * ป้ายกำกับสำหรับ dropdown เลือกเครื่องจักร: รหัสเครื่อง + ความเร็วรอบในวงเล็บ
     * เช่น "CPX45/270 (900-1800  40x60)" — ถ้าไม่มี speed_rpm จะคืนแค่รหัสเครื่อง
     * อัพเดท 21/07/2569: เพิ่มการแสดง speed_rpm ต่อท้ายรหัสเครื่องจักร
     */
    public function displayLabel(): string
    {
        $code  = trim((string) $this->MBX);
        $speed = trim((string) $this->speed_rpm);

        return $speed === '' ? $code : $code.' - ('.$speed.')';
    }
}
