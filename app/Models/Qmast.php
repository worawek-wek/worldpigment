<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Qmast extends Model
{
    use HasFactory;

    protected $table = 'qmast';
    // id (AUTO_INCREMENT) เป็น PRIMARY KEY แล้ว; Qno = เลขที่ใบเสนอราคา (ยังใช้อ้างอิงในโค้ด)
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $guarded = [];
}
