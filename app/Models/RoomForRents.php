<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomForRents extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'room_for_rents';
    
    public function room()
    {
        return $this->hasOne('App\Models\Room', 'id', 'ref_room_id');
    }

    public function occupancy()
    {
        return $this->hasOne('App\Models\Occupancy', 'id', 'ref_occupancy_id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'ref_user_id');
    }

    public function room_3()
    {
        return $this->hasOne('App\Models\Room', 'id', 'ref_room_id')->where('status',3);
    }

    public function rent_bill_day_check_in()
    {
        return $this->hasOne('App\Models\RentBill', 'ref_room_for_rent_id', 'id');
    }

    public function rent_bills()
    {
        return $this->hasOne('App\Models\RentBill', 'ref_room_for_rent_id', 'id');
    }

    public function rent_bill_reserve()
    {
        return $this->hasOne('App\Models\RentBill', 'ref_room_for_rent_id', 'id')->where('ref_type_id', 3);
    }

    public function branch()
    {
        return $this->hasOne('App\Models\Branch', 'id', 'ref_branch_id');
    }
    public function renter()
    {
        return $this->hasOne('App\Models\Renter', 'id', 'ref_renter_id');
    }
    
    public function vehicles()
    {
        return $this->hasMany(\App\Models\Vehicle::class, 'ref_room_for_rent_id', 'id');
    }
    
    public function rent_bill_not_pay()
    {
        return $this->hasMany('App\Models\RentBill', 'ref_room_for_rent_id', 'id')->where('ref_type_id', 3)->where('ref_status_id', "!=" , 5);
    }

    public function fullThaiAddress()
    {
        return $this->renter->fullThaiAddress();

    }
}
