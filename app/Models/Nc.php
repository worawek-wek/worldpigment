<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * ระบบ Complaint & NCR — ตารางหลัก nc (legacy) — 26/11/2569
 * 1 แถว = 1 เรื่องร้องเรียน / NCR · PK id เพิ่มโดย migration 2026_11_26_100000_add_id_to_nc_table
 */
class Nc extends Model
{
    protected $table = 'nc';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'NCRDate'   => 'datetime',
        'CauseDate' => 'date',
        'CloseDate' => 'date',
    ];

    /** สี badge ของผลตรวจสอบ (nc.degree → result.status) */
    public const DEGREE_BADGES = [
        'T' => 'bg-label-danger',    // จริง
        'F' => 'bg-label-success',   // ไม่จริง
        'S' => 'bg-label-info',      // บริการ
        'C' => 'bg-label-secondary', // ยกเลิก
    ];

    /** ตัวเลือกผลตรวจสอบจากตาราง result: ['T' => 'จริง', ...] */
    public static function degreeOptions(): array
    {
        return DB::table('result')->orderBy('status')->pluck('rem', 'status')->all();
    }

    /** ตัวเลือกสรุปสาเหตุ: ['A' => 'A - คำอธิบาย' หรือ 'A'] */
    public static function causeTypeOptions(): array
    {
        $out = [];
        foreach (config('complaint.cause_types', []) as $key => $label) {
            $out[$key] = $label ? "{$key} - {$label}" : $key;
        }

        return $out;
    }

    /** NCRNo ที่ "ออกเลขแล้ว" จริง (ไม่ว่าง และไม่ใช่ "-" / "--") */
    public static function isRealNcrNo($no): bool
    {
        $no = trim((string) $no);

        return $no !== '' && trim($no, '-') !== '';
    }
}
