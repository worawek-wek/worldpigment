<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomMoveHistorie extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'room_move_histories';

    public function old_room()
    {
        return $this->hasOne('App\Models\Room', 'id', 'old_room_id');
    }
    public function new_room()
    {
        return $this->hasOne('App\Models\Room', 'id', 'new_room_id');
    }
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'ref_user_id');
    }
}
