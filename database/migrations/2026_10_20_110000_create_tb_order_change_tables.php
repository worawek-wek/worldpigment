<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * การเปลี่ยนแปลงคำสั่งซื้อ (20/10/2569) — แทนช่อง Cdate1-3 / Cno1-3 / Change1-3 / Oldweight ของ suborder
 * ที่เก็บได้แค่ 3 ครั้ง และแยกส่งไม่ได้
 *
 *   tb_order_change       หัวใบเปลี่ยนแปลง (1 เอกสาร)
 *   tb_order_change_line  1 แถว = 1 การกระทำกับ 1 รายการ (เลื่อน / แยกส่ง / เปลี่ยนน้ำหนัก)
 *   tb_order_change_run   เลขรันของเลขที่ใบเปลี่ยนแปลง แยกตาม prefix (แผนก)
 *   suborder.parent_runno แถวที่แยกออกมา ชี้กลับไปแถวต้นทาง (แยกซ้อนได้ไม่จำกัดชั้น)
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_order_change')) {
            Schema::create('tb_order_change', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->string('change_no', 30)->unique();          // เลขที่ใบเปลี่ยนแปลง
                $table->dateTime('change_date')->nullable();         // วันที่ใบเปลี่ยนแปลง
                $table->string('orderno', 15)->nullable()->index();  // ใบสั่งซื้อ (ใบเก่าจาก Access อาจครอบหลายใบ → ดูที่ line)
                $table->text('reason')->nullable();                  // เหตุผล
                $table->string('source', 10)->default('web');        // web = บันทึกจากระบบนี้ · legacy = ย้ายมาจาก Cdate/Oldweight เดิม
                $table->string('created_by_type', 10)->nullable();   // emp / user
                $table->string('created_by', 20)->nullable();        // emp.empno / users.id
                $table->string('created_by_name', 100)->nullable();  // ชื่อ ณ ตอนบันทึก
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tb_order_change_line')) {
            Schema::create('tb_order_change_line', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('change_id')->constrained('tb_order_change')->cascadeOnDelete();
                $table->string('orderno', 15);
                $table->integer('runno');                            // รายการที่ถูกเปลี่ยน (suborder.Runno)
                $table->string('action', 10);                        // postpone / split / weight
                $table->date('old_senddate')->nullable();
                $table->date('new_senddate')->nullable();            // postpone = กำหนดใหม่ · split = กำหนดของส่วนที่แยก
                $table->double('old_stock')->nullable();
                $table->double('old_prod')->nullable();
                $table->double('new_stock')->nullable();             // น้ำหนักของแถวต้นทางหลังเปลี่ยน
                $table->double('new_prod')->nullable();
                $table->integer('split_runno')->nullable()->index(); // แถว suborder ใหม่ที่แยกออกไป
                $table->double('split_stock')->nullable();
                $table->double('split_prod')->nullable();
                $table->text('remark')->nullable();
                $table->timestamps();

                $table->index(['orderno', 'runno']);
            });
        }

        if (!Schema::hasTable('tb_order_change_run')) {
            Schema::create('tb_order_change_run', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->string('prefix', 10)->primary();
                $table->unsignedInteger('last_no')->default(0);      // เลขล่าสุดที่ใช้ไปแล้ว
            });

            // ตั้งต้นเลขรันต่อจากเลขเดิมที่ใช้ในระบบ Access (Cno1-3 / Weightno เช่น CP000099) กันเลขชน
            $max = [];
            foreach (['Cno1', 'Cno2', 'Cno3', 'Weightno'] as $col) {
                $vals = DB::table('suborder')->whereNotNull($col)->where($col, '<>', '')->distinct()->pluck($col);
                foreach ($vals as $v) {
                    if (preg_match('/^([A-Z]{1,4})(\d+)$/', strtoupper(trim($v)), $m)) {
                        $max[$m[1]] = max($max[$m[1]] ?? 0, (int) $m[2]);
                    }
                }
            }
            foreach ($max as $prefix => $no) {
                DB::table('tb_order_change_run')->insert(['prefix' => $prefix, 'last_no' => $no]);
            }
        }

        // suborder เป็น MyISAM legacy — ALTER จะ rebuild ทั้งตาราง (ราว 8,500 แถว)
        if (!Schema::hasColumn('suborder', 'parent_runno')) {
            DB::statement('ALTER TABLE `suborder` ADD COLUMN `parent_runno` INT NULL DEFAULT NULL, ADD INDEX `suborder_parent_runno_index` (`parent_runno`)');
        }
    }

    public function down()
    {
        if (Schema::hasColumn('suborder', 'parent_runno')) {
            DB::statement('ALTER TABLE `suborder` DROP INDEX `suborder_parent_runno_index`, DROP COLUMN `parent_runno`');
        }
        Schema::dropIfExists('tb_order_change_line');
        Schema::dropIfExists('tb_order_change_run');
        Schema::dropIfExists('tb_order_change');
    }
};
