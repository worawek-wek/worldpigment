<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningPacking extends Model
{
    use HasFactory;

    // ตารางลูก: การบรรจุของแต่ละ planning item (1 planning → หลายแถว)
    protected $table = 'tb_planning_packing';

    protected $guarded = [];
}
