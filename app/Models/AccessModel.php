<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessModel extends Model
{
    protected $connection = 'access';

    public $timestamps = false;

    public function getTable()
    {
        return $this->table;
    }
}