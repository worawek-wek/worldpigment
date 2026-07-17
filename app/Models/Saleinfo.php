<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * กำหนดราคา (ราคาสินค้าต่อลูกค้า)
 *
 * ชื่อคอลัมน์ยึดตามตาราง `uprice` ของเดิม เพื่อให้ copy ข้อมูลเก่ามาได้ตรงๆ
 * — `uprice` ใช้อ่านอย่างเดียว ระบบเขียนลง tb_saleinfo เท่านั้น
 */
class Saleinfo extends Model
{
    use HasFactory;

    protected $table = 'tb_saleinfo';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $guarded = [];
}
