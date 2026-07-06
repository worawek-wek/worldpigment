<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Planning;
use App\Models\PlanningHeader;
use App\Models\User;

class Pigment extends Model
{
    use HasFactory;

    protected $table = 'tb_pigment';

    protected $guarded = [];

    // สถานะ (ENUM) — แยกชุดของ Pigment เอง ไม่เกี่ยวกับ SemiPigment
    const STATUS_REQUEST  = 'request';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECT   = 'reject';

    // ป้ายกำกับภาษาไทยของแต่ละสถานะ
    public static $statusLabels = [
        self::STATUS_REQUEST  => 'รออนุมัติ',
        self::STATUS_APPROVED => 'อนุมัติ',
        self::STATUS_REJECT   => 'ไม่อนุมัติ',
    ];

    public function statusLabel(): string
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }

    public function planning()
    {
        return $this->belongsTo(Planning::class, 'planning_id', 'id');
    }

    public function planning_header()
    {
        return $this->belongsTo(PlanningHeader::class, 'planning_header_id', 'id');
    }

    // planning ที่ถูกสร้างหลังอนุมัติแล้ว
    public function result_planning()
    {
        return $this->belongsTo(Planning::class, 'result_planning_id', 'id');
    }

    // ผู้อนุมัติ (อ้างอิงจาก users.id เก็บใน approver_code)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_code', 'id');
    }
}
