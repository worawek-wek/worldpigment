<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Planning;

class PlanningHeader extends Model
{
    use HasFactory;

    protected $table = 'tb_planning_header';

    protected $guarded = [];

    public function plannings()
    {
        return $this->hasMany(Planning::class, 'planning_header_id', 'id');
    }

    // link กลับหา planning item แม่ (สำหรับ sub-order headers)
    public function parent_planning()
    {
        return $this->belongsTo(Planning::class, 'parent_planning_id', 'id');
    }
}
