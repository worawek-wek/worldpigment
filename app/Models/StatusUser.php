<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusUserLeave extends Model
{
    // use HasFactory;
    protected $fillable = [
        'status_name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'status_user_leaves';
}
