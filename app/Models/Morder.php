<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubOrder;

class Morder extends Model
{
    use HasFactory;

    protected $table = 'morder';

    protected $guarded = [];

    public function suborders()
    {
        return $this->hasMany(SubOrder::class, 'Orderno', 'Orderno');
    }

}
