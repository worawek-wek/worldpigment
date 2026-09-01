<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        // วันหยุดนักขัตฤกษ์ ปี 2569 — รันซ้ำได้ ไม่ทับข้อมูลที่แก้ไว้ (01/09/2569)
        $this->call(HolidaySeeder::class);
    }
}
