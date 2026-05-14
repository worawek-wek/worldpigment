<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentBill extends Model
{
    // use HasFactory;
    protected $fillable = [
        'invoice_number',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'rent_bills';
    
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'ref_user_id');
    }
    public function occupancy()
    {
        return $this->hasOne('App\Models\Occupancy', 'id', 'ref_occupancy_id');
    }
    public function contract()
    {
        return $this->hasOne('App\Models\Contract', 'id', 'ref_contract_id');
    }
    public function room()
    {
        return $this->hasOne('App\Models\Room', 'id', 'ref_room_id');
    }
    public function room_for_rent()
    {
        return $this->hasOne('App\Models\RoomForRents', 'id', 'ref_room_for_rent_id');
    }
    public function additional_costs()
    {
        return $this->hasMany('App\Models\AdditionalCosts', 'ref_rent_bill_id', 'id');
    }
    public function receipt() // ไม่เอาใบเสร็จยกเลิกแล้ว
    {
        return $this->hasMany(Receipt::class, 'ref_rent_bill_id', 'id')->where('ref_status_id', '!=', 0);
    }
    public function cancel_receipt() // เอาเฉพาะใบเสร็จยกเลิกแล้ว
    {
        return $this->hasMany(Receipt::class, 'ref_rent_bill_id', 'id')->where('ref_status_id', 0);
    }
    public function receipt_move_out()
    {
        return $this->hasOne(Receipt::class, 'ref_rent_bill_id', 'id')->where('ref_status_id', '!=', 0);
    }
    public function cancel_receipt_move_out()
    {
        return $this->hasMany(Receipt::class, 'ref_rent_bill_id', 'id')->where('ref_status_id', 0);
    }
    public function receipt_pay_cash()
    {
        return $this->hasMany(Receipt::class, 'ref_rent_bill_id', 'id')->where('payment_channel',1);
    }
    public function receipt_pay_transfer()
    {
        return $this->hasMany(Receipt::class, 'ref_rent_bill_id', 'id')->where('payment_channel',2);
    }
    public function getTotalPaidIncludingFineAttribute()  // total_paid_including_fine
    {
        return $this->receipt->sum(function ($receipt) {
            $total = $receipt->payment_list->where('discount', 0)->sum('price');
            $discount = $receipt->payment_list->where('discount', 1)->sum('price');
            return $total - $discount;
        });
    }
    public function getTotalPaidCashAttribute()  // total_paid_cash
    {
        return $this->receipt_pay_cash->sum(function ($receipt_pay_cash) {
            $total = $receipt_pay_cash->payment_list->where('discount', 0)->sum('price');
            $discount = $receipt_pay_cash->payment_list->where('discount', 1)->sum('price');
            return $total - $discount;
        });
    }
    public function getTotalPaidTransferAttribute()  // total_paid_cash
    {
        return $this->receipt_pay_transfer->sum(function ($receipt_pay_transfer) {
            $total = $receipt_pay_transfer->payment_list->where('discount', 0)->sum('price');
            $discount = $receipt_pay_transfer->payment_list->where('discount', 1)->sum('price');
            return $total - $discount;
        });
    }
    public function getTotalPaidAmountAttribute()
    {
        return $this->receipt->sum(function ($receipt) {
            $total = $receipt->payment_list_not_fine->where('discount', 0)->sum('price');
            $discount = $receipt->payment_list_not_fine->where('discount', 1)->sum('price');
            return $total - $discount;
        });
    }
    public function getTotalNotDiscountAmountAttribute()
    {
        return $this->receipt->sum(function ($receipt) {
            return $receipt->payment_list->where('discount', 0)->sum('price');
        });
    }
    public function status()
    {
        return $this->hasOne('App\Models\StatusRentBill', 'id', 'ref_status_id');
    }
    public function payment_list()
    {
        return $this->hasMany('App\Models\PaymentList', 'ref_payment_id', 'id')->where('document_type', 1);
    }
    
    public function payment_list_not_paid() // รายการที่ยังไม่ชำระ
    {
        return $this->hasMany('App\Models\PaymentList', 'ref_payment_id', 'id')->where('document_type', 1)->where('paid', 0);
    }
    public function payment_not_discount()
    {
        return $this->hasMany('App\Models\PaymentList', 'ref_payment_id', 'id')
                    ->where('document_type', 1)
                    ->where('discount', 0);
    }
    public function payment_advance_receipt()
    {
        return $this->hasOne('App\Models\PaymentList', 'ref_payment_id', 'id')
                    ->where('document_type', 1)
                    ->where('title', 'like', 'หักจากยอดชำระเงินล่วงหน้า');
    }
    public function payment_rent_room()
    {
        return $this->hasOne('App\Models\PaymentList', 'ref_payment_id', 'id')
                    ->where('document_type', 1)
                    ->where('title', 'like', '%ค่าเช่าห้อง (Room rate)%');
    }
    public function payment_car_parking_fee()
    {
        return $this->hasOne('App\Models\PaymentList', 'ref_payment_id', 'id')
                    ->where('document_type', 1)
                    ->where('title', 'like', '%ค่าที่จอดรถยนต์%');
    }
    public function payment_motorcycle_parking_fee()
    {
        return $this->hasOne('App\Models\PaymentList', 'ref_payment_id', 'id')
                    ->where('document_type', 1)
                    ->where('title', 'like', '%ค่าที่จอดมอเตอร์ไซค์%');
    }
    public function payment_water()
    {
        return $this->hasOne('App\Models\PaymentList', 'ref_payment_id', 'id')
                    ->where('document_type', 1)
                    ->where('title', 'like', '%ค่าน้ำ%');
    }

    public function payment_electricity()
    {
        return $this->hasOne('App\Models\PaymentList', 'ref_payment_id', 'id')
                    ->where('document_type', 1)
                    ->where('title', 'like', '%ค่าไฟฟ้า%');
    }
    public function getTotalAmountAttribute()
    {

        $lists = $this->payment_list; // ใช้ attribute ที่ถูกโหลดแล้ว

        $total = $lists->where('discount', 0)->sum('price');
        $discount = $lists->where('discount', 1)->sum('price');


        return $total - $discount;
    }
    public function getBalanceAmountAttribute()
    {
        // ดึงยอดรวมที่ต้องจ่ายจาก payment_list ของ RentBill (document_type = 1)
        $this->loadMissing('payment_list', 'receipt.payment_list');

        $billAmount = $this->payment_list->where('discount', 0)->sum('price')
                    - $this->payment_list->where('discount', 1)->sum('price');

        // รวมยอดที่จ่ายแล้วใน receipt
        $paidAmount = $this->receipt->sum(function ($receipt) {
            return $receipt->total_amount; // ต้องมี accessor นี้ใน Receipt
        });

        return $billAmount - $paidAmount;
    }
    public function getTotalELEAmountAttribute()
    {
        return $this->payment_list
                ->where('discount', 0)
                ->filter(function($item) {
                    return str_contains($item->title, 'ค่าไฟ');
                })
                ->sum('price');
    }
    public function getTotalWaterAmountAttribute()
    {
        return $this->payment_list
                ->where('discount', 0)
                ->filter(function($item) {
                    return str_contains($item->title, 'ค่าน้ำ');
                })
                ->sum('price');
    }
    public function previousMeter()
    {
        return $this->hasOne(Meter::class, 'ref_room_id', 'ref_room_id')
            ->where(function ($q) {
                $prevMonth = $this->month - 1;
                $prevYear = $this->year;

                if ($prevMonth <= 0) {
                    $prevMonth = 12;
                    $prevYear--;
                }

                $q->where('month', $prevMonth)
                ->where('year', $prevYear);
            });
    }
}
