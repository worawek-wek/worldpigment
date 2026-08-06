<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // database-first: ข้ามถ้าตารางถูกสร้างมือใน DB ไปแล้ว
        if (Schema::hasTable('TestDet')) {
            return;
        }

        Schema::create('TestDet', function (Blueprint $table) {
            $table->string('TestNo', 10)->nullable();
            $table->string('PdCode', 17)->nullable();
            $table->double('Compp')->nullable();
            $table->double('CNet')->nullable();
            $table->string('CHK', 100)->nullable();
            $table->double('PdPrice')->nullable();
            $table->string('TestNo2', 10)->nullable();

            $table->index('TestNo');
            $table->index('PdCode');
        });
    }

    public function down()
    {
        Schema::dropIfExists('TestDet');
    }
};
