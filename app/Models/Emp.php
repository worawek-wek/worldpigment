<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emp extends Model
{
    use HasFactory;

    protected $table = 'emp';

    // ตาราง emp ใช้ empno (varchar) เป็นคีย์หลัก ไม่ auto-increment และไม่มี created_at/updated_at
    protected $primaryKey = 'empno';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];

    // ไม่ส่งรหัสผ่าน (hash) ออกไปกับ response JSON
    protected $hidden = ['password', 'pwd'];

    // แผนกที่พนักงานสังกัด
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // สิทธิ์การใช้งาน (role) ของพนักงาน
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
