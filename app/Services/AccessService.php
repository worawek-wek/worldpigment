<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * อ่านข้อมูลที่ยกมาจากไฟล์ Access `formula_2000.mdb`
 *
 * ⚠ 05/08/2569 — เปลี่ยนมาอ่านจาก **MySQL** แทนการต่อไฟล์ .mdb โดยตรง
 *   เพราะเครื่อง server ของลูกค้าไม่มีไฟล์ .mdb และไม่มี ODBC driver
 *   ข้อมูลถูกคัดลอกมาไว้ในตาราง `access_*` (ดู migration create_access_mirror_tables)
 *
 *     Compo   → access_compo
 *     PdPrice → access_pdprice
 *     TestMai → access_testmai
 *
 *   โค้ดเดิมที่ต่อ ODBC ยังคอมเมนต์ไว้ในแต่ละ method — ถ้าจะกลับไปอ่านไฟล์จริง
 *   ให้สลับกลับ แล้วตั้ง ACCESS_DB_PATH ใน .env ให้ชี้ไฟล์ .mdb
 */
class AccessService
{
    /**
     * ดึงข้อมูล Compo
     */
    public function getCompo()
    {
        return DB::table('access_compo')->get();

        // return DB::connection('access')
        //     ->select("SELECT * FROM Compo");
    }


    /**
     * ดึงข้อมูล PdPrice
     */
    public function getPdPrice()
    {
        return DB::table('access_pdprice')->get();

        // return DB::connection('access')
        //     ->select("SELECT * FROM PdPrice");
    }


    /**
     * ดึงข้อมูล TestMai
     */
    public function getTestMai()
    {
        return DB::table('access_testmai')->get();

        // return DB::connection('access')
        //     ->select("SELECT * FROM TestMai");
    }
}
