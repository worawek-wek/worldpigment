<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Base model ของตารางที่ยกมาจากไฟล์ Access `formula_2000.mdb`
 *
 * ⚠ 05/08/2569 — เปลี่ยนมาใช้ connection ปกติ (MySQL) แทน connection 'access'
 *   เพราะเครื่อง server ของลูกค้าไม่มีไฟล์ .mdb และไม่มี ODBC driver
 *   ตารางบน MySQL ใช้ชื่อ `access_<ชื่อตารางเดิม>` (ดู migration create_access_mirror_tables)
 */
class AccessModel extends Model
{
    // ปิดไว้ตอนขึ้น server — เปิดคืนเมื่อจะกลับไปอ่านไฟล์ .mdb โดยตรง (ตั้ง ACCESS_DB_PATH ใน .env ด้วย)
    // protected $connection = 'access';

    public $timestamps = false;

    public function getTable()
    {
        return $this->table;
    }
}