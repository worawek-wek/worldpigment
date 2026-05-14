<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Occupancy extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'occupancys';

    public function check_in_day_room_rent()
    {
        return $this->hasOne('App\Models\Room', 'ref_occupancy_id', 'id');
    }
    public function receipt_rent_bill()
    {
        return $this->hasOne('App\Models\Receipt', 'ref_occupancy_id', 'id')->where('ref_type_id', 1);
    }
    public function receipt_reserve_bill()
    {
        return $this->hasOne('App\Models\Receipt', 'ref_occupancy_id', 'id')->where('ref_type_id', 3);
    }
    public function room_for_rent()
    {
        return $this->hasOne('App\Models\RoomForRents', 'ref_occupancy_id', 'id');
    }
    public function room()
    {
        return $this->hasOne('App\Models\Room', 'id', 'ref_room_id');
    }
    public function contract()
    {
        return $this->hasOne('App\Models\Contract', 'ref_occupancy_id', 'id');
    }
}
