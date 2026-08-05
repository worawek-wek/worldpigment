<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningProdMethod extends Model
{
    use HasFactory;

    protected $table = 'tb_planning_prod_method';

    protected $guarded = [];

    // วิธีการผลิต (master) ที่แถวนี้อ้างถึง
    public function prodMethod()
    {
        return $this->belongsTo(ProdMethod::class, 'prod_method_id');
    }

    // Temp (master) ที่แถวนี้อ้างถึง
    public function temp()
    {
        return $this->belongsTo(Temp::class, 'temp_id');
    }
}
