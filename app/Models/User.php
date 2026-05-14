<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attributes that appends to returned entities.
     *
     * @var array
     */
    // protected $appends = ['photo']; ไม่แน่ใจ

    /**
     * The getter that return accessible URL for user photo.
     *
     * @var array
     */
    
    public function position()
    {
        return $this->hasOne('App\Models\Position', 'id', 'ref_position_id');
    }
    public function permission_group_has_user_branch($permission_id)
    {
        return $this->hasOne('App\Models\PermissionGroupHasUserBranch', 'ref_user_id', 'id')->where('ref_branch_id', session("branch_id"))->where('ref_permission_id', $permission_id);
    }
    public function user_has_branch()
    {
        return $this->hasOne('App\Models\UserHasBranch', 'ref_user_id', 'id')->where('ref_branch_id', session("branch_id"));
    }
    public function branch()
    {
        return $this->hasOne('App\Models\Branch', 'id', 'ref_branch_id');
    }
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'ref_user_id');
    }
    // public function getPhotoUrlAttribute()
    // {
    //     if ($this->foto !== null) {
    //         return url('media/user/' . $this->id . '/' . $this->foto);
    //     } else {
    //         return url('media-example/no-image.png');
    //     }
    // }
}
