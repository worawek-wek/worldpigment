<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckInDayRoomRent extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'check_in_day_room_rents';

    public function room()
    {
        return $this->hasOne('App\Models\Room', 'occupancy_id', 'occupancy_id');
    }
    public function room_for_rent()
    {
        return $this->hasOne('App\Models\RoomForRent', 'occupancy_id', 'occupancy_id');
    }
    public function check_in_day_discount_lists()
    {
        return $this->hasMany('App\Models\CheckInDayDiscountList', 'ref_check_in_day_room_rent_id', 'id');
    }
}
