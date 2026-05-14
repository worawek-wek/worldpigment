<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLeave extends Model
{
    // use HasFactory;
    protected $fillable = [
        'ref_user_id',
        'ref_leave_id',
        'detail',
        'from_date',
        'to_date',
        'type_day',
        'file_name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'user_leaves';
    
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'ref_user_id');
    }
    public function approve()
    {
        return $this->hasOne('App\Models\User', 'id', 'ref_approve_id');
    }
    public function leave()
    {
        return $this->hasOne('App\Models\Leave', 'id', 'ref_leave_id');
    }
    public function status()
    {
        return $this->hasOne('App\Models\StatusUserLeave', 'id', 'ref_status_id');
    }
    public function boss_status()
    {
        return $this->hasOne('App\Models\StatusUserLeave', 'id', 'ref_boss_status_id');
    }
}
