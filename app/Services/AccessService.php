<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AccessService
{
    /**
     * ดึงข้อมูล Compo
     */
    public function getCompo()
    {
        return DB::connection('access')
            ->select("SELECT * FROM Compo");
    }


    /**
     * ดึงข้อมูล PdPrice
     */
    public function getPdPrice()
    {
        return DB::connection('access')
            ->select("SELECT * FROM PdPrice");
    }


    /**
     * ดึงข้อมูล TestMai
     */
    public function getTestMai()
    {
        return DB::connection('access')
            ->select("SELECT * FROM TestMai");
    }
}
