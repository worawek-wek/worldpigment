<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningStatus extends Model
{
    use HasFactory;

    protected $table = 'tb_planning_status';

    protected $guarded = [];

    // แผนกที่ผูกกับสถานะ — dept เก็บ id ของ tb_departments
    public function department()
    {
        return $this->belongsTo(Department::class, 'dept');
    }
}
