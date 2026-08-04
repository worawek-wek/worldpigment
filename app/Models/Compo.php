<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\AccessModel;

class Compo extends AccessModel
{
    protected $table = 'Compo';

    protected $primaryKey = null;

    public $incrementing = false;
}