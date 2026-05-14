<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Water extends Model
{
    protected $fillable = ['use_unit','amount','payment_date','slip'];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'waters';
    
}