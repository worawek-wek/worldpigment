<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionGroupHasUserBranch extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'permission_group_has_user_branchs';

    public function permission()
    {
        return $this->hasOne('App\Models\Permission', 'id', 'ref_permission_id');
    }
    public function permission_group()
    {
        return $this->hasOne('App\Models\PermissionGroup', 'id', 'ref_permission_group_id');
    }
}
