<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PlanningHeader;

class Planning extends Model
{
    use HasFactory;

    protected $table = 'tb_planning';

    protected $guarded = [];

    public function planning_header()
    {
        return $this->belongsTo(PlanningHeader::class, 'planning_header_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(Planning::class, 'parent_planning_id', 'id');
    }

    // sub-order headers ที่ auto-สร้างจาก planning นี้
    public function semi_headers()
    {
        return $this->hasMany(PlanningHeader::class, 'parent_planning_id', 'id')
                    ->where('plan_type', 'semi');
    }

    public function pigment_headers()
    {
        return $this->hasMany(PlanningHeader::class, 'parent_planning_id', 'id')
                    ->where('plan_type', 'pigment');
    }

    // sub-order headers ทั้งหมด (semi + pigment) ที่ auto-สร้างจาก planning นี้
    public function sub_headers()
    {
        return $this->hasMany(PlanningHeader::class, 'parent_planning_id', 'id')
                    ->whereIn('plan_type', ['semi', 'pigment']);
    }

    // โหลด sub-headers แบบ recursive (ย่อยลงไปได้ไม่จำกัดชั้น)
    public function subHeadersRecursive()
    {
        return $this->sub_headers()->with('planningsRecursive');
    }

}
