<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * เพิ่ม unique index ให้ tb_products.product_code — 12/09/2569
 *
 * เหตุผล: ก่อนหน้านี้ product_code ซ้ำกันได้ทั้งใน validation และ DB (index ธรรมดา)
 * ทำให้รายงานเครื่องจักร (leftJoin tb_products) แสดงรายการซ้ำ (row fan-out)
 * ตอนนี้บังคับไม่ซ้ำที่ ProductController::store() แล้ว — เพิ่ม unique index เป็นด่านสุดท้าย
 *
 * DB ของลูกค้าเป็น database-first — เช็คทุกอย่างจาก information_schema จริง (idempotent)
 */
return new class extends Migration
{
    private string $table   = 'tb_products';
    private string $column  = 'product_code';
    private string $uniqIdx = 'tb_products_product_code_unique';
    private string $plainIdx = 'tp_products_product_code_index';

    public function up(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        // มี unique index อยู่แล้ว → ไม่ต้องทำอะไร (idempotent)
        if ($this->hasUniqueOnColumn()) {
            return;
        }

        // กัน migration ล้มแบบงง ๆ — ถ้ายังมีรหัสซ้ำต้องเคลียร์ก่อน
        $dups = DB::table($this->table)
            ->select($this->column, DB::raw('COUNT(*) as c'))
            ->whereNotNull($this->column)
            ->groupBy($this->column)
            ->havingRaw('COUNT(*) > 1')
            ->pluck($this->column)
            ->all();

        if (!empty($dups)) {
            throw new \RuntimeException(
                'มีรหัสสินค้าซ้ำใน tb_products จึงเพิ่ม unique index ไม่ได้ กรุณาแก้ให้ไม่ซ้ำก่อน: '
                . implode(', ', $dups)
            );
        }

        // มี index ธรรมดาชื่อเดิมอยู่ → ถอดก่อน แล้วค่อยสร้าง unique (คอลัมน์เดียวไม่ต้องมี 2 index)
        if ($this->indexExists($this->plainIdx)) {
            Schema::table($this->table, function (Blueprint $t) {
                $t->dropIndex($this->plainIdx);
            });
        }

        Schema::table($this->table, function (Blueprint $t) {
            $t->unique($this->column, $this->uniqIdx);
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        // ถอด unique แล้วคืน index ธรรมดา (ให้ตรงกับ schema เดิมก่อน migration นี้)
        if ($this->indexExists($this->uniqIdx)) {
            Schema::table($this->table, function (Blueprint $t) {
                $t->dropUnique($this->uniqIdx);
            });
        }

        if (!$this->indexExists($this->plainIdx)) {
            Schema::table($this->table, function (Blueprint $t) {
                $t->index($this->column, $this->plainIdx);
            });
        }
    }

    /** มี index ชื่อนี้อยู่บนตารางไหม (จาก information_schema) */
    private function indexExists(string $name): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $this->table)
            ->where('INDEX_NAME', $name)
            ->exists();
    }

    /** มี unique index (index ใดก็ได้) ครอบคอลัมน์ product_code อยู่แล้วไหม */
    private function hasUniqueOnColumn(): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $this->table)
            ->where('COLUMN_NAME', $this->column)
            ->where('NON_UNIQUE', 0)
            ->exists();
    }
};
