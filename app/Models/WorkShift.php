<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * tb_work_shift — กะการผลิต (master) (10/11/2569)
 * ใช้เป็นตัวเลือกช่อง "กะการผลิต" ในฟอร์มแก้ไข Planning Item
 * ⚠ tb_planning.work_shift เก็บ "ชื่อกะ" เป็นข้อความ (ไม่ใช่ id) — แก้/ลบ master ไม่กระทบแผนเก่า
 */
class WorkShift extends Model
{
    protected $table = 'tb_work_shift';

    protected $guarded = [];

    /** รายชื่อกะที่เปิดใช้งาน เรียง sort → id */
    public static function activeNames()
    {
        return static::where('is_active', 'Y')->orderBy('sort')->orderBy('id')->pluck('name');
    }
}
