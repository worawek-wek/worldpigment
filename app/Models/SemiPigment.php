<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Planning;
use App\Models\PlanningHeader;

class SemiPigment extends Model
{
    use HasFactory;

    protected $table = 'tb_semi_pigment';

    protected $guarded = [];

    // สถานะ (ENUM)
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
}
