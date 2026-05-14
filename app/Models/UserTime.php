<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTime extends Model
{
    // use HasFactory;
    protected $fillable = [
        'branch_name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'user_times';
    
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'ref_user_id');
    }
}
