<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHasBranch extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'user_has_branchs';

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'ref_user_id');
    }
    public function branch()
    {
        return $this->hasOne('App\Models\Branch', 'id', 'ref_branch_id');
    }
    public function position()
    {
        return $this->hasOne('App\Models\Position', 'id', 'ref_position_id');
    }
}
