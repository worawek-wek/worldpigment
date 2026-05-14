<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeExpenses extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'income_expenses';
    
    public function category()
    {
        return $this->hasOne('App\Models\Category', 'id', 'ref_category_id');
    }
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'ref_user_id');
    }
    public function room()
    {
        return $this->hasOne('App\Models\Room', 'id', 'ref_room_id');
    }
    public function branch()
    {
        return $this->hasOne('App\Models\Branch', 'id', 'ref_branch_id');
    }
    public function receipt()
    {
        return $this->hasOne('App\Models\Receipt', 'id', 'ref_receipt_id');
    }
    public function payment_list()
    {
        return $this->hasMany('App\Models\IncomeList', 'ref_payment_id', 'id');
    }
    // ความสัมพันธ์ใหม่ (จาก ref_payment_id => ref_receipt_id)
    public function receipt_payment_list()
    {
        return $this->hasMany('App\Models\PaymentList', 'ref_payment_id', 'ref_receipt_id')->where('document_type', 2);
    }
    // ✅ ใช้ตัวนี้เป็นค่า default
    public function getTotalAmountAttribute()
    {
        $lists = $this->payment_list;

        $total = $lists->where('discount', 0)->sum('price');
        $discount = $lists->where('discount', 1)->sum('price');

        return $total - $discount;
    }

    // ✅ หากคุณยังอยากดึงยอดรวมจาก payment_list แบบเก่า
    public function getTotalFromPaymentList()
    {
        $lists = $this->receipt_payment_list;

        $total = $lists->where('discount', 0)->sum('price');
        $discount = $lists->where('discount', 1)->sum('price');

        return $total - $discount;
    }
    public function getTotalFromPaymentListAttribute()
{
    $lists = $this->receipt_payment_list;

    $total = $lists->where('discount', 0)->sum('price');
    $discount = $lists->where('discount', 1)->sum('price');

    return $total - $discount;
}
}
