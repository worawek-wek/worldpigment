<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * แปลงค่า dept เดิมที่เก็บเป็น "ชื่อแผนก" (CP/MB/DB/SPP) ให้เป็น id ของ tb_departments
     */
    public function up()
    {
        $map = DB::table('tb_departments')->pluck('id', 'name'); // ['CP' => 1, ...]

        foreach ($map as $name => $id) {
            DB::table('tb_planning_status')
                ->where('dept', $name)
                ->update(['dept' => $id]);
        }
    }

    /**
     * ย้อนกลับ: แปลง id กลับเป็นชื่อแผนก
     */
    public function down()
    {
        $map = DB::table('tb_departments')->pluck('name', 'id'); // [1 => 'CP', ...]

        foreach ($map as $id => $name) {
            DB::table('tb_planning_status')
                ->where('dept', $id)
                ->update(['dept' => $name]);
        }
    }
};
