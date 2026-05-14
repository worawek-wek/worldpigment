<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubDistrict extends Model
{
    // use HasFactory;
    protected $fillable = [
        'subdistrict_name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'subdistricts';
}
