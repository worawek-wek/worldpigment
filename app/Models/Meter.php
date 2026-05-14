<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meter extends Model
{
    // use HasFactory;
    protected $fillable = [
        'ref_reason_id', // เพิ่มเข้าไปถ้าจะใช้ Mass Assignment
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'meters';
    
    protected $appends = ['reason_name']; // เพื่อให้ reason_name ติดมาด้วยเวลาเรียก toArray()

    public function getReasonNameAttribute()
    {
        return match ($this->ref_reason_id) {
            1 => 'มิเตอร์เต็ม',
            2 => 'เปลี่ยนมิเตอร์',
            default => '',
        };
    }
    
}
