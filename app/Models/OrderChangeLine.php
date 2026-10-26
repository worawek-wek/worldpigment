<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 1 การกระทำกับ 1 รายการ (suborder) ในใบเปลี่ยนแปลงคำสั่งซื้อ (20/10/2569)
 */
class OrderChangeLine extends Model
{
    protected $table = 'tb_order_change_line';

    protected $guarded = [];

    /** การกระทำ → ป้ายไทย + สี badge */
    public const ACTIONS = [
        'postpone' => ['label' => 'เลื่อนกำหนดส่ง', 'badge' => 'bg-label-warning'],
        'split'    => ['label' => 'แยกส่ง',        'badge' => 'bg-label-info'],
        'weight'   => ['label' => 'เปลี่ยนน้ำหนัก',  'badge' => 'bg-label-danger'],
    ];

    public function change()
    {
        return $this->belongsTo(OrderChange::class, 'change_id');
    }
}
