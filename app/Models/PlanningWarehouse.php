<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningWarehouse extends Model
{
    use HasFactory;

    // ตารางลูก: การเข้าคลังของแต่ละ planning item (1 planning → หลายแถว) (29/10/2569)
    protected $table = 'tb_planning_warehouse';

    protected $guarded = [];

    /**
     * sync การเข้าคลังของ planning หนึ่ง ๆ (ตรรกะเดียวกับ PlanningPacking::syncRows)
     * ลบแถวเดิมทั้งหมดแล้ว insert ใหม่จาก array คู่ขนาน (ข้ามแถวที่ว่างทั้งหมด) — เรียกภายในทรานแซกชันของผู้เรียก
     */
    public static function syncRows($planning_id, array $datetimes, array $weights, array $remarks): void
    {
        static::where('planning_id', $planning_id)->delete();

        $count = max(count($datetimes), count($weights), count($remarks));

        for ($i = 0; $i < $count; $i++) {
            $datetime = trim((string) ($datetimes[$i] ?? ''));
            $remark   = trim((string) ($remarks[$i] ?? ''));

            // ช่องน้ำหนักมาจาก js-number-format (มีคอมมาหลักพัน) → ตัดคอมมาแล้วแปลงเป็นตัวเลข
            $weight_raw = str_replace(',', '', trim((string) ($weights[$i] ?? '')));
            $weight_val = ($weight_raw !== '' && is_numeric($weight_raw)) ? (float) $weight_raw : null;

            // ข้ามแถวที่ว่างทั้งหมด
            if ($datetime === '' && $weight_val === null && $remark === '') {
                continue;
            }

            static::create([
                'planning_id'        => $planning_id,
                'warehouse_datetime' => $datetime !== '' ? $datetime : null,
                'weight_warehouse'   => $weight_val,
                'warehouse_remark'   => $remark !== '' ? $remark : null,
                'sort'               => $i,
            ]);
        }
    }

    /**
     * บันทึกการเข้าคลังแบบ "เพิ่มได้อย่างเดียว" (Worker portal, 29/10/2569 — ตรรกะเดียวกับ PlanningPacking::appendRows)
     *  - ทุกแถวที่ส่งมา = แถวใหม่ (insert, sort ต่อท้าย) · ข้ามแถวที่ว่างทั้งหมด
     *  - แถวเดิมไม่ถูกแก้ไข/ลบเลย
     */
    public static function appendRows($planning_id, array $datetimes, array $weights, array $remarks): void
    {
        $nextSort = ((int) static::where('planning_id', $planning_id)->max('sort')) + 1;

        $count = max(count($datetimes), count($weights), count($remarks));

        for ($i = 0; $i < $count; $i++) {
            $datetime = trim((string) ($datetimes[$i] ?? ''));
            $remark   = trim((string) ($remarks[$i] ?? ''));

            $weight_raw = str_replace(',', '', trim((string) ($weights[$i] ?? '')));
            $weight_val = ($weight_raw !== '' && is_numeric($weight_raw)) ? (float) $weight_raw : null;

            if ($datetime === '' && $weight_val === null && $remark === '') {
                continue;
            }

            static::create([
                'planning_id'        => $planning_id,
                'warehouse_datetime' => $datetime !== '' ? $datetime : null,
                'weight_warehouse'   => $weight_val,
                'warehouse_remark'   => $remark !== '' ? $remark : null,
                'sort'               => $nextSort++,
            ]);
        }
    }
}
