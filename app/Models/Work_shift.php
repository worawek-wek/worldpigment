<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Work_shift extends Model
{
    // use HasFactory;
    protected $fillable = [
        'work_shift_name',
        'from_time',
        'to_time'
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'work_shifts';
}
