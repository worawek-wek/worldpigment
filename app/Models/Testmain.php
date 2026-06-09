<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testmain extends Model
{
    use HasFactory;

    protected $table = 'testmain';
    // ใช้ id (auto-increment) เป็นตัวอ้างอิงหลักของแอป — SendNo ยังเป็น PRIMARY KEY ใน DB (legacy)
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $guarded = [];
}
