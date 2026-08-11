<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Emp extends Authenticatable
{
    use HasFactory;

    protected $table = 'emp';

    // ตาราง emp ใช้ empno (varchar) เป็นคีย์หลัก ไม่ auto-increment และไม่มี created_at/updated_at
    protected $primaryKey = 'empno';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];

    // ชื่อ Role ที่ถือว่าเป็น "พนักงานหน้างาน" (Worker) — เก็บที่เดียวกันสะกดเพี้ยน (11/08/2569)
    public const WORKER_ROLE_NAME = 'Worker';

    // ไม่ส่งรหัสผ่าน (hash) ออกไปกับ response JSON
    protected $hidden = ['password', 'pwd'];

    // เป็นพนักงานหน้างาน (Worker) หรือไม่ — ดูจากชื่อ role
    public function isWorker(): bool
    {
        return $this->role_id
            && $this->role
            && $this->role->name === self::WORKER_ROLE_NAME;
    }

    // ตาราง emp ไม่มีคอลัมน์ remember_token → ปิดการใช้ remember token
    public function getRememberTokenName()
    {
        return '';
    }

    // แผนกที่พนักงานสังกัด (เก็บเป็นชื่อแผนกใน emp.dept → อ้าง tb_departments.name)
    public function department()
    {
        return $this->belongsTo(Department::class, 'dept', 'name');
    }

    // สิทธิ์การใช้งาน (role) ของพนักงาน
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
