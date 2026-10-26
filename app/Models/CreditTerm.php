<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * tb_credit_term — ตัวเลือก "เครดิต" ของฟอร์มฐานข้อมูลลูกค้า (26/10/2569)
 * แยกข้อความไทย (name_th) / อังกฤษ (name_en)
 * ⚠ customer.term เก็บ "ข้อความ name_th" ไม่ใช่ id (ค่าเดิมของลูกค้าเป็นข้อความอิสระ)
 */
class CreditTerm extends Model
{
    protected $table = 'tb_credit_term';

    protected $guarded = [];

    /** ตัวเลือกที่เปิดใช้งาน เรียงตาม sort */
    public static function activeList()
    {
        return static::where('is_active', 'Y')->orderBy('sort')->orderBy('id')->get(['id', 'name_th', 'name_en']);
    }
}
