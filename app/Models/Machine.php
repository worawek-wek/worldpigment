<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $table = 'machine';

    protected $guarded = [];

    // ตาราง machine ไม่มีคอลัมน์ created_at/updated_at
    public $timestamps = false;

}
