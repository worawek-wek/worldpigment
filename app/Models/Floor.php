<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'floors';

    public function building()
    {
        return $this->belongsTo(Building::class, 'ref_building_id');
    }
    public function room()
    {
        return $this->hasMany('App\Models\Room', 'ref_floor_id', 'id');
    }
}
