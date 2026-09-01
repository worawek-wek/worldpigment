<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * วันหยุดนักขัตฤกษ์ (tb_holiday) — 01/09/2569
 *
 * เป็นตารางข้อมูลหลัก (master) ยังไม่ถูกผูกกับระบบอื่น
 */
class Holiday extends Model
{
    use HasFactory;

    protected $table = 'tb_holiday';

    protected $guarded = [];

    /** ประเภทวันหยุด — key ที่เก็บใน DB ห้ามเปลี่ยน */
    public const TYPES = [
        'public'     => 'วันหยุดนักขัตฤกษ์',
        'substitute' => 'วันหยุดชดเชย',
        'company'    => 'วันหยุดบริษัท',
    ];

    /** สีของ badge ประเภท (คลาสของ theme) */
    public const TYPE_BADGES = [
        'public'     => 'bg-label-danger',
        'substitute' => 'bg-label-warning',
        'company'    => 'bg-label-info',
    ];

    public static function typeLabel(?string $type): string
    {
        return self::TYPES[$type] ?? ($type ?: '-');
    }

    public static function typeBadge(?string $type): string
    {
        return self::TYPE_BADGES[$type] ?? 'bg-label-secondary';
    }

    /** ชื่อวันในสัปดาห์ภาษาไทย (0 = อาทิตย์) */
    public const WEEKDAYS = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];

    /** ชื่อเดือนย่อภาษาไทย (1 = ม.ค.) */
    public const MONTHS_SHORT = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
        7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.',
    ];

    /** ชื่อเดือนเต็มภาษาไทย (1 = มกราคม) */
    public const MONTHS_FULL = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
        7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
    ];

    /** '2026-01-01' → '1 ม.ค. 2569' (พ.ศ.) */
    public static function thaiDate(?string $date): string
    {
        if (!$date) {
            return '-';
        }
        $ts = strtotime($date);
        if (!$ts) {
            return '-';
        }

        return (int) date('j', $ts).' '.self::MONTHS_SHORT[(int) date('n', $ts)].' '.((int) date('Y', $ts) + 543);
    }

    /** '2026-01-01' → 'พฤหัสบดี' */
    public static function thaiWeekday(?string $date): string
    {
        $ts = $date ? strtotime($date) : false;

        return $ts ? self::WEEKDAYS[(int) date('w', $ts)] : '-';
    }
}
