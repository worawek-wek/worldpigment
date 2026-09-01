<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use App\Models\Holiday;

/**
 * นับวันทำการโดยข้ามวันหยุด — 01/09/2569
 *
 * "วันหยุด" = วันหยุดประจำสัปดาห์ (config/holiday.php → weekly_off, ปัจจุบัน = วันอาทิตย์)
 *            + วันที่มีในตาราง tb_holiday ที่ is_active = 'Y'
 *
 * ใช้ที่: PriceApprovalController::defaultValidTo() (ช่อง "อนุมัติราคาถึง" ของฟอร์มขออนุมัติราคาพิเศษ)
 */
class HolidayService
{
    /** กันวนไม่จบถ้าตั้งค่าจนไม่เหลือวันทำการเลย (เช่น weekly_off ครบ 7 วัน) */
    private const MAX_SCAN_DAYS = 366;

    /** cache ต่อ 1 request: 'Y-m-d' => true */
    private static ?array $holidayCache = null;

    /** ตาราง tb_holiday มีจริงไหม (server ที่ยังไม่ได้รัน migration/SQL จะไม่มี) */
    private static ?bool $tableExists = null;

    /** วันหยุดประจำสัปดาห์ (0 = อาทิตย์) */
    public static function weeklyOff(): array
    {
        return array_map('intval', (array) config('holiday.weekly_off', [0]));
    }

    /** วันนี้เป็นวันหยุดไหม (รับ Carbon / 'Y-m-d' / timestamp string) */
    public static function isHoliday($date): bool
    {
        $day = self::toCarbon($date);

        if (in_array((int) $day->dayOfWeek, self::weeklyOff(), true)) {
            return true;
        }

        return isset(self::holidayMap()[$day->format('Y-m-d')]);
    }

    /** ตรงข้ามกับ isHoliday() */
    public static function isWorkingDay($date): bool
    {
        return !self::isHoliday($date);
    }

    /**
     * วันทำการถัดไปนับจาก $from ไป $days วัน (ไม่นับตัว $from เอง)
     *
     * ตัวอย่างที่ผู้ใช้ระบุ: วันนี้ที่ 1 · วันที่ 2 เป็นวันหยุด → คืนวันที่ 3
     *
     * @param  mixed  $from  วันตั้งต้น (ว่าง = วันนี้)
     * @param  int    $days  จำนวนวันทำการที่ต้องการนับไป (อย่างน้อย 1)
     * @return string 'Y-m-d'
     */
    public static function nextWorkingDay($from = null, int $days = 1): string
    {
        $day  = self::toCarbon($from);
        $left = max(1, $days);

        for ($i = 0; $i < self::MAX_SCAN_DAYS && $left > 0; $i++) {
            $day->addDay();

            if (self::isWorkingDay($day)) {
                $left--;
            }
        }

        // หา $days วันทำการไม่ครบภายใน 1 ปี (ตั้งค่าผิดจนไม่เหลือวันทำการ)
        // → คืนวันสุดท้ายที่ไล่ถึง ดีกว่าวนไม่จบหรือคืนค่าว่าง
        return $day->format('Y-m-d');
    }

    /** ล้าง cache (ใช้ในสคริปต์/เทสต์ที่เพิ่มวันหยุดแล้วคำนวณต่อทันที) */
    public static function flush(): void
    {
        self::$holidayCache = null;
        self::$tableExists  = null;
    }

    /** วันหยุดที่เปิดใช้งานทั้งหมด → map 'Y-m-d' => true (อ่าน DB ครั้งเดียวต่อ request) */
    private static function holidayMap(): array
    {
        if (self::$holidayCache !== null) {
            return self::$holidayCache;
        }

        if (self::$tableExists === null) {
            self::$tableExists = Schema::hasTable('tb_holiday');
        }

        if (!self::$tableExists) {
            // ยังไม่ได้สร้างตาราง → ถือว่าไม่มีวันหยุด (เหลือแค่วันหยุดประจำสัปดาห์)
            // ไม่ปล่อย exception เพราะหน้าที่เรียกใช้เป็นฟอร์มหลักของระบบ
            return self::$holidayCache = [];
        }

        $dates = Holiday::where('is_active', 'Y')->pluck('holiday_date');

        $map = [];
        foreach ($dates as $d) {
            $map[substr((string) $d, 0, 10)] = true;
        }

        return self::$holidayCache = $map;
    }

    private static function toCarbon($value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy()->startOfDay();
        }

        $value = trim((string) $value);

        if ($value === '') {
            return Carbon::today();
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable $e) {
            return Carbon::today();
        }
    }
}
