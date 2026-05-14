<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusRoom extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'status_rooms';
}
