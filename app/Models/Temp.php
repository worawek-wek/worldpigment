<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Temp extends Model
{
    use HasFactory;

    protected $table = 'temp';

    // ตาราง temp ไม่มีคอลัมน์ created_at / updated_at
    public $timestamps = false;

    protected $guarded = [];
}
