<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $guarded = [];

    // เมนูที่ role นี้เข้าถึงได้
    public function permissions()
    {
        return $this->hasMany(RolePermission::class, 'role_id');
    }

    // พนักงานที่ถูกกำหนด role นี้
    public function employees()
    {
        return $this->hasMany(Emp::class, 'role_id');
    }

    // รายการ menu_key ที่ role นี้เข้าถึงได้ (array)
    public function menuKeys(): array
    {
        return $this->permissions->pluck('menu_key')->all();
    }

    // id ของ role ที่ถูกตั้งเป็นค่าเริ่มต้นสำหรับพนักงานใหม่ (ไม่มี = null)
    public static function defaultRoleId(): ?int
    {
        return static::where('is_default', 'Y')->value('id');
    }
}
