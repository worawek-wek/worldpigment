<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PlanningHeader;

class Planning extends Model
{
    use HasFactory;

    use HasFactory;

    protected $table = 'tb_planning';

    protected $guarded = [];

    public function planning_header()
    {
        return $this->belongsTo(PlanningHeader::class, 'planning_header_id', 'id');
    }

}
