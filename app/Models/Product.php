<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Temp;

class Product extends Model
{
    use HasFactory;

    protected $table = 'tb_products';

    protected $guarded = [];

    // ความสัมพันธ์ไปยัง Temp (master) — tb_products.temp_id → temp.id
    public function temp()
    {
        return $this->belongsTo(Temp::class, 'temp_id', 'id');
    }
}
