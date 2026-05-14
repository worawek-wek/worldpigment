<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'discounts';
    
    public function room_has_discount()
    {
        return $this->hasOne('App\Models\RoomHasDiscount', 'ref_discount_id', 'id');
    }
}
