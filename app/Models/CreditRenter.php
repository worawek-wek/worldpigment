<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditRenter extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'credit_renter';

    public function building()
    {
        return $this->belongsTo(Building::class, 'ref_building_id');
    }
    public function renter()
    {
        return $this->hasOne('App\Models\Renter', 'id', 'ref_renter_id');
    }
}
