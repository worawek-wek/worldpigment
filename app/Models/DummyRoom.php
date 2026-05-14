<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DummyRoom extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'dummy_rooms';
    
    
    public function room()
    {
        return $this->hasOne('App\Models\Room', 'id', 'ref_room_id');
    }
}
