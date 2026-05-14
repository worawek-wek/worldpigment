<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckInDayDiscountList extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'check_in_day_discount_lists';

    public function check_in_day_room_rent()
    {
        return $this->hasOne('App\Models\CheckInDayRoomRent', 'id', 'ref_check_in_day_room_rent_id');
    }
    // public function room_for_rent()
    // {
    //     return $this->hasOne('App\Models\RoomForRent', 'occupancy_id', 'occupancy_id');
    // }
}
