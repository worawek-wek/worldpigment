<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * tb_planning_remark — หมายเหตุแผนการผลิต (ตัวเลือกสำเร็จรูป) (08/09/2569)
 * ตาราง InnoDB มี created_at / updated_at จึงเปิด timestamps ตามค่าเริ่มต้น
 */
class PlanningRemark extends Model
{
    use HasFactory;

    protected $table = 'tb_planning_remark';

    protected $guarded = [];
}
