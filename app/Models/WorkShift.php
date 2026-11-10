<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * tb_work_shift — กะการผลิต (master) (10/11/2569)
 * ใช้เป็นตัวเลือกช่อง "กะการผลิต" ในฟอร์มแก้ไข Planning Item
 * ⚠ tb_planning.work_shift เก็บ "ชื่อกะ" เป็นข้อความ (ไม่ใช่ id) — แก้/ลบ master ไม่กระทบแผนเก่า
 * shift_group = กลุ่มของกะ (10/11/2569) — key = ค่าที่เก็บใน DB, value = ป้ายภาษาไทย
 */
class WorkShift extends Model
{
    protected $table = 'tb_work_shift';

    protected $guarded = [];

    public const GROUPS = [
        'Normal Working' => 'เวลาปกติ',
        'Overtime'       => 'ล่วงเวลา',
    ];

    /** รายชื่อกะที่เปิดใช้งาน เรียง sort → id */
    public static function activeNames()
    {
        return static::where('is_active', 'Y')->orderBy('sort')->orderBy('id')->pluck('name');
    }

    /** กะที่เปิดใช้งาน จัดกลุ่มตาม shift_group: ['Normal Working' => Collection<name>, ...] */
    public static function activeNamesByGroup()
    {
        $rows = static::where('is_active', 'Y')->orderBy('sort')->orderBy('id')->get(['name', 'shift_group']);

        $groups = [];
        foreach (array_keys(self::GROUPS) as $key) {
            $groups[$key] = $rows->where('shift_group', $key)->pluck('name')->values();
        }

        return $groups;
    }

    /** ชื่อกะทั้งหมด (รวมที่ปิดใช้งาน) ของกลุ่มที่ระบุ — ใช้กรองงานตามกลุ่ม */
    public static function namesInGroup(string $group)
    {
        return static::where('shift_group', $group)->pluck('name');
    }

    public static function groupLabel(?string $group): string
    {
        return self::GROUPS[$group] ?? ($group ?: '-');
    }
}
