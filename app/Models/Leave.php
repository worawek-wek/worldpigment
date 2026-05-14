<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    // use HasFactory;
    protected $fillable = [
        'leave_name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'leaves';
}
