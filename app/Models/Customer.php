<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ข้อมูลลูกค้า (ตาราง legacy `customer`) — 24/08/2569
 *
 * ตารางเดิมจาก Access: PK = code (varchar 6, ผู้ใช้กรอกเอง ไม่ auto increment)
 * ไม่มี created_at/updated_at และเป็น MyISAM (ไม่รองรับ transaction)
 *
 * คอลัมน์ checkbox แบบ Access (RP / CER / PO / MSDS) เก็บ -1 = ติ๊ก, 0/NULL = ไม่ติ๊ก
 */
class Customer extends Model
{
    protected $table = 'customer';

    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    /** ผู้ติดต่อของลูกค้ารายนี้ (ตาราง contact, PK = code + contactname) */
    public function contacts()
    {
        return $this->hasMany(CustomerContact::class, 'code', 'code');
    }
}
