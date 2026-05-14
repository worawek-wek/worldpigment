<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'contracts';

    public function occupancy()
    {
        return $this->hasOne('App\Models\Occupancy', 'id', 'ref_occupancy_id');
    }
    public function room()
    {
        return $this->hasOne('App\Models\Room', 'id', 'ref_room_id');
    }
    public function renter()
    {
        return $this->hasOne('App\Models\Renter', 'id', 'ref_renter_id');
    }
    // public function room_for_rent()
    // {
    //     return $this->hasOne('App\Models\RoomForRents', 'id', 'ref_room_for_rent_id');
    // }
    public function fullThaiAddress()
    {
        return $this->address;
    }
}
