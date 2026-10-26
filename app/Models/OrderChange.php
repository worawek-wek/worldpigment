<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * หัวใบเปลี่ยนแปลงคำสั่งซื้อ (20/10/2569) — 1 ใบมีได้หลายบรรทัด (tb_order_change_line)
 * source: web = บันทึกจากระบบนี้ · legacy = ย้ายมาจาก suborder.Cdate1-3 / Oldweight ของระบบ Access
 */
class OrderChange extends Model
{
    protected $table = 'tb_order_change';

    protected $guarded = [];

    public function lines()
    {
        return $this->hasMany(OrderChangeLine::class, 'change_id')->orderBy('id');
    }
}
