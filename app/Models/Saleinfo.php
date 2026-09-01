<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * กำหนดราคา (ราคาสินค้าต่อลูกค้า)
 *
 * ⚠ ย้ายมาเขียนลงตาราง legacy `uprice` โดยตรงแล้ว (29/08/2569 ตามที่ผู้ใช้ยืนยัน)
 * เดิมเขียนลง `tb_saleinfo` ที่สร้างใหม่ — ทำให้ราคาอยู่ 2 ที่ไม่ตรงกัน
 * และหน้าอื่นที่อ่าน `uprice` (ใบสั่งซื้อ / อนุมัติใบสั่งซื้อ / ขออนุมัติราคาพิเศษ)
 * มองไม่เห็นราคาที่ตั้งจากเมนูนี้เลย
 *
 * `uprice` เป็น MyISAM และเดิมไม่มี primary key — migration
 * `add_id_to_uprice_table` เพิ่มคอลัมน์ `id` ให้เพื่อใช้ edit/update/delete รายแถว
 *
 * ไม่มี created_at / updated_at ในตารางนี้ → ปิด timestamps
 * (เวลาบันทึกล่าสุดเก็บที่คอลัมน์ `AuthDate` ของเดิมแทน)
 */
class Saleinfo extends Model
{
    use HasFactory;

    protected $table = 'uprice';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $guarded = [];
}
