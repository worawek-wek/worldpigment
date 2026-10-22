<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningPacking extends Model
{
    use HasFactory;

    // ตารางลูก: การบรรจุของแต่ละ planning item (1 planning → หลายแถว)
    protected $table = 'tb_planning_packing';

    protected $guarded = [];

    /**
     * sync การบรรจุของ planning หนึ่ง ๆ (ใช้ร่วมกัน: ฟอร์มแก้ไข Planning Item + Worker portal, 22/10/2569)
     * ลบแถวเดิมทั้งหมดแล้ว insert ใหม่จาก array คู่ขนาน (ข้ามแถวที่ว่างทั้งหมด) — เรียกภายในทรานแซกชันของผู้เรียก
     * คืนผลรวมน้ำหนักบรรจุ (null = ไม่มีแถวไหนกรอกน้ำหนัก)
     */
    public static function syncRows($planning_id, array $datetimes, array $weights, array $remarks): ?float
    {
        static::where('planning_id', $planning_id)->delete();

        $count = max(count($datetimes), count($weights), count($remarks));
        $total = null;

        for ($i = 0; $i < $count; $i++) {
            $datetime = $datetimes[$i] ?? null;
            $weight   = $weights[$i]   ?? null;
            $remark   = $remarks[$i]   ?? null;

            $datetime = ($datetime !== null && trim((string) $datetime) !== '') ? trim((string) $datetime) : null;
            $remark   = ($remark   !== null && trim((string) $remark)   !== '') ? trim((string) $remark)   : null;

            // ช่องน้ำหนักมาจาก js-number-format (มีคอมมาหลักพัน) → ตัดคอมมาแล้วแปลงเป็นตัวเลข
            $weight_raw = ($weight !== null) ? str_replace(',', '', trim((string) $weight)) : '';
            $weight_val = ($weight_raw !== '' && is_numeric($weight_raw)) ? (float) $weight_raw : null;

            // ข้ามแถวที่ว่างทั้งหมด
            if ($datetime === null && $weight_val === null && $remark === null) {
                continue;
            }

            if ($weight_val !== null) {
                $total = ($total ?? 0) + $weight_val;
            }

            static::create([
                'planning_id'      => $planning_id,
                'packing_datetime' => $datetime,
                'weight_packing'   => $weight_val,
                'pack_remark'      => $remark,
                'sort'             => $i,
            ]);
        }

        return $total;
    }

    /**
     * บันทึกการบรรจุแบบ "เพิ่มได้อย่างเดียว" (Worker portal, 22/10/2569)
     *  - ทุกแถวที่ส่งมา = แถวใหม่ (insert, sort ต่อท้าย) · ข้ามแถวที่ว่างทั้งหมด
     *  - แถวเดิมไม่ถูกแก้ไข/ลบเลย (ฝั่งจอแสดงอ่านอย่างเดียว และไม่ส่งมา)
     * คืนผลรวมน้ำหนักบรรจุทุกแถวของ planning หลังบันทึก (null = ไม่มีแถวไหนมีน้ำหนัก)
     */
    public static function appendRows($planning_id, array $datetimes, array $weights, array $remarks): ?float
    {
        $nextSort = ((int) static::where('planning_id', $planning_id)->max('sort')) + 1;

        $count = max(count($datetimes), count($weights), count($remarks));

        for ($i = 0; $i < $count; $i++) {
            $datetime = trim((string) ($datetimes[$i] ?? ''));
            $remark   = trim((string) ($remarks[$i] ?? ''));

            $weight_raw = str_replace(',', '', trim((string) ($weights[$i] ?? '')));
            $weight_val = ($weight_raw !== '' && is_numeric($weight_raw)) ? (float) $weight_raw : null;

            // ข้ามแถวที่ว่างทั้งหมด
            if ($datetime === '' && $weight_val === null && $remark === '') {
                continue;
            }

            static::create([
                'planning_id'      => $planning_id,
                'packing_datetime' => $datetime !== '' ? $datetime : null,
                'weight_packing'   => $weight_val,
                'pack_remark'      => $remark !== '' ? $remark : null,
                'sort'             => $nextSort++,
            ]);
        }

        $hasWeight = static::where('planning_id', $planning_id)->whereNotNull('weight_packing')->exists();

        return $hasWeight
            ? (float) static::where('planning_id', $planning_id)->sum('weight_packing')
            : null;
    }
}
