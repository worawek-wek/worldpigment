<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ผู้ติดต่อของลูกค้า (ตาราง legacy `contact`) — 24/08/2569
 *
 * PK เป็นคีย์ผสม (code + contactname) และไม่มี id → Eloquent update ทีละแถวไม่ได้
 * ตอนบันทึกจึงใช้วิธี "ลบทั้งหมดของลูกค้ารายนั้นแล้ว insert ใหม่" ใน CustomerController::syncContacts()
 * Model ตัวนี้จึงใช้สำหรับ "อ่าน" เป็นหลัก
 */
class CustomerContact extends Model
{
    protected $table = 'contact';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
