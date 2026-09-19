<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * zcolorrate — ราคาขั้นต่ำของ "กลุ่มราคา" A/B/C แยกตามรหัสสินค้า (19/09/2569)
 *
 * ตาราง legacy (MyISAM, ไม่มี id / ไม่มี timestamps) — คีย์คือ `colorno` ตรง ๆ
 * เวลาที่แก้ล่าสุดเก็บที่คอลัมน์ `RDate` ของเดิม ไม่ใช่ created_at/updated_at
 *
 * ใครใช้ค่านี้บ้าง:
 *   - OrderController::priceData()      → ช่อง "ขั้นต่ำ" ในกล่องราคาใบสั่งซื้อ
 *   - OrderController::colorRateFloor() → ด่านราคาตอนบันทึกใบสั่งซื้อ
 */
class ColorRate extends Model
{
    protected $table = 'zcolorrate';

    protected $primaryKey = 'colorno';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];
}
