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
     * บันทึกแถวการเข้าคลังจากฟอร์มแก้ไข Planning Item แบบเทียบ id (02/11/2569) — เรียกภายในทรานแซกชันของผู้เรียก
     *  - แถวที่มี id = แก้ไขแถวนั้น · ไม่มี id = เพิ่มแถวใหม่ต่อท้าย · แถวว่างทั้งหมด = ข้าม
     *  - ลบเฉพาะแถวที่ฟอร์มเคยโหลดมา ($loaded_ids) แล้วหายไปจากฟอร์ม
     *  - แถวที่ฟอร์มไม่เคยเห็น (Worker เพิ่มระหว่างเปิดฟอร์มค้าง) ไม่ถูกแตะ
     */
    public static function mergeRows($planning_id, array $ids, array $loaded_ids, array $datetimes, array $weights, array $remarks): void
    {
        $nextSort = ((int) static::where('planning_id', $planning_id)->max('sort')) + 1;

        $count = max(count($ids), count($datetimes), count($weights), count($remarks));
        $keep  = [];

        for ($i = 0; $i < $count; $i++) {
            $id       = (int) ($ids[$i] ?? 0);
            $datetime = trim((string) ($datetimes[$i] ?? ''));
            $remark   = trim((string) ($remarks[$i] ?? ''));

            // ช่องน้ำหนักมาจาก js-number-format (มีคอมมาหลักพัน) → ตัดคอมมาแล้วแปลงเป็นตัวเลข
            $weight_raw = str_replace(',', '', trim((string) ($weights[$i] ?? '')));
            $weight_val = ($weight_raw !== '' && is_numeric($weight_raw)) ? (float) $weight_raw : null;

            // ข้ามแถวที่ว่างทั้งหมด (แถวเดิมที่ถูกล้างค่า = ไม่อยู่ใน $keep จึงถูกลบด้านล่าง)
            if ($datetime === '' && $weight_val === null && $remark === '') {
                continue;
            }

            $data = [
                'warehouse_datetime' => $datetime !== '' ? $datetime : null,
                'weight_warehouse' => $weight_val,
                'warehouse_remark' => $remark !== '' ? $remark : null,
            ];

            if ($id > 0) {
                static::where('planning_id', $planning_id)->where('id', $id)->update($data);
                $keep[] = $id;
            } else {
                static::create($data + ['planning_id' => $planning_id, 'sort' => $nextSort++]);
            }
        }

        static::where('planning_id', $planning_id)
            ->whereIn('id', array_diff($loaded_ids, $keep))
            ->delete();
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
