<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * tb_color_rate_history — ประวัติการแก้ไขกลุ่มราคา (zcolorrate) 15/10/2569
 *
 * เขียนจาก ColorRateController::store()/destroy() เท่านั้น
 * 1 แถว = 1 ครั้งที่ เพิ่ม / แก้ไข / ลบ (old_* = ค่าก่อน, new_* = ค่าหลัง)
 */
class ColorRateHistory extends Model
{
    protected $table = 'tb_color_rate_history';

    public $timestamps = false;

    protected $guarded = [];

    // ป้ายไทย + สี badge ของแต่ละการกระทำ — อย่า hard-code ซ้ำใน blade
    public const ACTIONS = [
        'create' => ['label' => 'เพิ่ม',  'badge' => 'bg-label-success'],
        'update' => ['label' => 'แก้ไข', 'badge' => 'bg-label-warning'],
        'delete' => ['label' => 'ลบ',    'badge' => 'bg-label-danger'],
    ];
}
