<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();          // ชื่อ role
                $table->string('description')->nullable();
                $table->enum('is_active', ['Y', 'N'])->default('Y');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('role_id');
                $table->string('menu_key', 100);           // key ของเมนูใน config/menu.php ที่ role เข้าถึงได้
                $table->index('role_id');
                $table->unique(['role_id', 'menu_key']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
    }
};
