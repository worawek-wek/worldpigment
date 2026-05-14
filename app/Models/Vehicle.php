<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    // use HasFactory;
    protected $fillable = [
        'receipt_number',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'vehicles';
    
    public function room()
    {
        return $this->hasOne('App\Models\Room', 'id', 'ref_room_id');
    }
    public function renter()
    {
        return $this->hasOne('App\Models\Renter', 'id', 'ref_renter_id');
    }
    public function room_for_rent()
    {
        return $this->hasOne('App\Models\RoomForRents', 'id', 'ref_room_for_rent_id');
    }
    public function type()
    {
        return $this->hasOne('App\Models\VehicleType', 'id', 'ref_type_id');
    }
}
