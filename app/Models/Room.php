<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'rooms';

    public function floor()
    {
        return $this->belongsTo(Floor::class, 'ref_floor_id');
    }
    public function occupancy() // การจองปัจจุบัน
    {
        return $this->hasOne('App\Models\Occupancy', 'id', 'ref_occupancy_id');
    }
    public function occupancy_reserve()
    {
        return $this->hasMany('App\Models\Occupancy', 'ref_room_id', 'id')->where('status', 1);
    }
    public function occupancy_day()
    {
        return $this->hasOne('App\Models\Occupancy', 'ref_room_id', 'id')->where('status', 1)->where('booking_type', 1);
    }
    public function rent_bill()
    {
        return $this->hasMany('App\Models\RentBill', 'ref_room_id', 'id');
    }
    public function rent_initial_bill_overdue()
    {
        return $this->hasMany('App\Models\RentBill', 'ref_room_id', 'id')->whereIn('ref_status_id', [3, 7])->where('initial_bill', 1);
    }
    public function rent_room_bill_overdue()
    {
        return $this->hasMany('App\Models\RentBill', 'ref_room_id', 'id')->where('ref_status_id', 7)->where('ref_type_id', 1);
    }
    public function rent_bill_247() // บิลค้างชำระ
    {
        return $this->hasMany('App\Models\RentBill', 'ref_room_id', 'id')->whereIn('ref_status_id', [2,4,7]);
    }
    public function rent_bill_overdue() // บิลค้างชำระ
    {
        return $this->hasMany('App\Models\RentBill', 'ref_room_id', 'id')->whereIn('ref_status_id', [2,4,7]);
    }
    public function room_status()
    {
        return $this->hasOne('App\Models\StatusRoom', 'id', 'status');
    }
    public function contract()
    {
        return $this->hasOne('App\Models\Contract', 'ref_occupancy_id', 'ref_occupancy_id');
    }
    public function room_for_rent_main()
    {
        return $this->hasOne(RoomForRents::class, 'ref_room_id', 'id')
                    ->where('status', 1)
                    ->latest('id');
    }
    public function room_for_rent_reserve()
    {
        return $this->hasOne('App\Models\RoomForRents', 'ref_occupancy_id', 'ref_occupancy_id');
    }
    public function room_for_rent()
    {
        return $this->hasOne('App\Models\RoomForRents', 'ref_occupancy_id', 'ref_occupancy_id');
    }
    public function room_for_rent_s()
    {
        return $this->hasMany('App\Models\RoomForRents', 'ref_room_id', 'id');
    }
    public function room_for_rent_all() // สำหรับ ผู้เช่า ปัจจุบัน
    {
        return $this->hasMany('App\Models\RoomForRents', 'ref_occupancy_id', 'ref_occupancy_id');
    }
    // public function room_has_asset()
    // {
    //     return $this->hasOne('App\Models\RoomForRents', 'ref_room_id', 'id')->where('status', 0);
    // }
    public function room_has_service()
    {
        return $this->hasMany('App\Models\RoomHasService', 'ref_room_id', 'id');
    }
    public function room_has_discount()
    {
        return $this->hasMany('App\Models\RoomHasDiscount', 'ref_room_id', 'id');
    }
    public function meterCurrent()
    {
        return $this->hasOne(Meter::class, 'ref_room_id');
    }

    public function meterPrevious()
    {
        return $this->hasOne(Meter::class, 'ref_room_id');
    }
    public function notificate()
    {
        return $this->hasMany('App\Models\Notificate', 'ref_room_id', 'id')->where('approve', 0);
    }
}
