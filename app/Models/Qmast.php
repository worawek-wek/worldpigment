<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Qmast extends Model
{
    use HasFactory;

    protected $table = 'qmast';
    // qmast ไม่มี id — Qno เป็นเลขที่ใบเสนอราคา (unique) ใช้เป็น key
    protected $primaryKey = 'Qno';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];
}
