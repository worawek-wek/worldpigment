<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // use HasFactory;
    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'categorys';

    public function equipments()
    {
        return $this->hasOne('App\Models\Equipments', 'ref_equipments_id', 'id')->orderBy('id', 'DESC');
    }
}
