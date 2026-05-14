<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomHasDiscount extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'room_has_discounts';

    public function discount()
    {
        return $this->hasOne('App\Models\Discount', 'id', 'ref_discount_id');
    }
}
