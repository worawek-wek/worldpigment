<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentList extends Model
{
    // use HasFactory;
    protected $fillable = [
        'title',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'payment_lists';
    
    public function room_for_rent()
    {
        return $this->hasOne('App\Models\RoomForRents', 'id', 'ref_room_for_rent_id');
    }
    public function invoice()
    {
        return $this->hasOne('App\Models\RentBill', 'id', 'ref_payment_id')->where('document_type', 1);
    }
    public function receipt()
    {
        return $this->hasOne('App\Models\Receipt', 'id', 'ref_payment_id')->where('document_type', 2);
    }
    public function getBeforeVatAttribute()
    {
        return $this->price / 1.07;
    }
    public function getVatAttribute()
    {
        return $this->price - $this->before_vat;
    }
}
