<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ค่าตัวคูณ/หาร/บวก ของเงื่อนไขราคา ที่ผู้ใช้แก้เองจากหน้าจอ (10/08/2569)
 *
 * เก็บเฉพาะแถวที่ถูกแก้ — แถวไหนไม่มีที่นี่ = ใช้ค่าตั้งต้นจาก config/product_price.php
 * ผูกกับ config ด้วย `rule_key` (= คีย์ 'key' ของแถวนั้นใน config)
 */
class PriceRule extends Model
{
    use HasFactory;

    protected $table = 'tb_price_rule';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'mul' => 'float',
        'div' => 'float',
        'add' => 'float',
    ];
}
