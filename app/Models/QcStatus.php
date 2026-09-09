<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * tb_qc_status — สถานะ QC (ตัวเลือกสำเร็จรูป, master) (09/09/2569)
 * ตาราง InnoDB มี created_at / updated_at จึงเปิด timestamps ตามค่าเริ่มต้น
 * ⚠ ต่างจาก tb_planning_remark ตรงที่ตารางนี้ "ไม่มี" คอลัมน์ created_by / updated_by
 */
class QcStatus extends Model
{
    use HasFactory;

    protected $table = 'tb_qc_status';

    protected $guarded = [];
}
