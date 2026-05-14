<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Renter extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'renters';
    
    public function province()
    {
        return $this->belongsTo(\App\Models\Province::class, 'ref_province_id');
    }
    
    public function district()
    {
        return $this->belongsTo(\App\Models\District::class, 'ref_district_id');
    }
    
    public function subdistrict()
    {
        return $this->belongsTo(\App\Models\Subdistrict::class, 'ref_subdistrict_id');
    }

    public function branch()
    {
        return $this->hasOne('App\Models\Branch', 'id', 'ref_branch_id');
    }
    public function room_for_rent()
    {
        return $this->hasOne('App\Models\RoomForRents', 'ref_renter_id', 'id');
    }

    public function vehicle()
    {
        return $this->hasOne(\App\Models\Vehicle::class, 'ref_renter_id', 'id');
    }

    public function vehicles()
    {
        return $this->hasMany(\App\Models\Vehicle::class, 'ref_renter_id', 'id');
    }
    
    public function fullName()
    {
        $prefix = $this->prefix ?? '';
        $name = $this->name ?? '';
        $surname = $this->surname ?? '';

        return trim("{$prefix} {$name} {$surname}");
    }

    public function fullThaiAddress()
    {
        $address = $this->address ?? '';
        $zipcode = $this->subdistrict?->zip_code ?? '';
        $subdistrict = $this->subdistrict?->name_in_thai ?? '';
        $district = $this->district?->name_in_thai ?? '';
        $province = $this->province?->name_in_thai ?? '';

        return trim("$address $subdistrict $district $province $zipcode");
    }
    
}
