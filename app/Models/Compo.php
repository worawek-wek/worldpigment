<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\AccessModel;

class Compo extends AccessModel
{
    // ตารางบน MySQL (สำเนาของ Compo ในไฟล์ Access) — เดิมคือ 'Compo' บน connection 'access' (05/08/2569)
    protected $table = 'access_compo';

    // เดิมตั้ง primaryKey = null / incrementing = false เพราะตารางใน Access ไม่มี PK
    // ตาราง access_compo บน MySQL มี id auto-increment แล้ว จึงใช้ค่า default ของ Eloquent ได้
}