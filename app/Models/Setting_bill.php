<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting_bill extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'setting_bill';
    public $timestamps = true;
    protected $fillable = [
        'type', 'company_name', 'address', 'tax_no',
        'phone', 'email', 'type_doc', 'detail_footer', 'detail_doc'
    ];

}
