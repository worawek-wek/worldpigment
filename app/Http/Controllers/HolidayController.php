<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Holiday;

/**
 * ตารางวันหยุดนักขัตฤกษ์ (master data) — 01/09/2569
 *
 * หน้าจัดการข้อมูลอย่างเดียว ยังไม่ผูกกับแผนการผลิต/ใบสั่งซื้อ
 */
class HolidayController extends Controller
{
    public function index()
    {
        return view('holiday.index', [
            'years'       => $this->yearOptions(),
            'currentYear' => (int) date('Y'),
            'types'       => Holiday::TYPES,
        ]);
    }

    /**
     * ปีที่ให้เลือกในตัวกรอง = ปีที่มีข้อมูลจริง + ปีปัจจุบัน + ปีหน้า
     * (เผื่อเพิ่มวันหยุดของปีที่ยังไม่มีข้อมูลสักแถว)
     */
    private function yearOptions(): array
    {
        $years = Holiday::selectRaw('YEAR(holiday_date) as y')
            ->distinct()
            ->pluck('y')
            ->map(fn ($y) => (int) $y)
            ->all();

        $years[] = (int) date('Y');
        $years[] = (int) date('Y') + 1;

        $years = array_values(array_unique(array_filter($years)));
        rsort($years);

        return $years;
    }

    public function datatable()
    {
        $holidays = Holiday::select(['id', 'holiday_date', 'name', 'type', 'remark', 'is_active'])
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                        ->orWhere('remark', 'like', "%{$search}%");
                });
            })
            ->when(request('year'), function ($q, $year) {
                $q->whereYear('holiday_date', $year);
            })
            ->when(request('type'), function ($q, $type) {
                $q->where('type', $type);
            })
            ->when(in_array(request('status'), ['Y', 'N'], true), function ($q) {
                $q->where('is_active', request('status'));
            });
        // หมายเหตุ: ไม่ hard-code orderBy ที่นี่ — ให้ Yajra เรียงตามหัวคอลัมน์ที่คลิก
        // (ลำดับเริ่มต้น = วันที่ เก่า→ใหม่ กำหนดเป็น order ในฝั่ง DataTables ของ holiday/index)

        $rownum = 0;

        return DataTables::of($holidays)
            ->addColumn('rownum', function () use (&$rownum) {
                return ++$rownum;
            })
            ->addColumn('date_label', function ($holiday) {
                $date = (string) $holiday->holiday_date;

                return '<div class="fw-medium">'.date('d/m/Y', strtotime($date)).'</div>
                        <small class="text-muted">'.e(Holiday::thaiDate($date)).'</small>';
            })
            ->addColumn('weekday', function ($holiday) {
                $date    = (string) $holiday->holiday_date;
                $weekend = in_array((int) date('w', strtotime($date)), [0, 6], true);

                // เสาร์-อาทิตย์ = วันหยุดประจำสัปดาห์อยู่แล้ว ทำสีต่างไว้ให้สังเกตง่าย
                return $weekend
                    ? '<span class="text-muted">'.e(Holiday::thaiWeekday($date)).'</span>'
                    : e(Holiday::thaiWeekday($date));
            })
            ->addColumn('type_label', function ($holiday) {
                return '<span class="badge '.Holiday::typeBadge($holiday->type).'">'
                    .e(Holiday::typeLabel($holiday->type)).'</span>';
            })
            ->addColumn('remark_label', function ($holiday) {
                return $holiday->remark ?: '-';
            })
            ->addColumn('status_switch', function ($holiday) {
                $checked = $holiday->is_active === 'Y' ? 'checked' : '';

                return '<div class="form-check form-switch d-flex justify-content-center mb-0">
                            <input class="form-check-input switch_status" type="checkbox" role="switch"
                                data-id="'.$holiday->id.'" '.$checked.'>
                        </div>';
            })
            ->addColumn('btnaction', function ($holiday) {
                return '<div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-sm btn-icon btn-warning btn_edit" data-id="'.$holiday->id.'" title="แก้ไข">
                                <i class="ti ti-pencil ti-sm"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-danger btn_delete" data-id="'.$holiday->id.'" title="ลบ">
                                <i class="ti ti-trash ti-sm"></i>
                            </button>
                        </div>';
            })
            ->rawColumns(['date_label', 'weekday', 'type_label', 'status_switch', 'btnaction'])
            ->make(true);
    }

    public function edit()
    {
        $id = request('id');

        $holiday = $id ? Holiday::find($id) : null;

        $html = view('holiday.holiday-form', [
            'holiday' => $holiday,
            'types'   => Holiday::TYPES,
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html,
        ]);
    }

    public function store(Request $request)
    {
        $date = $this->parseDate($request->holiday_date);

        $validator = Validator::make(
            array_merge($request->all(), ['holiday_date' => $date]),
            [
                'holiday_date' => [
                    'required',
                    'date',
                    // 1 วัน = 1 แถว (ตรงกับ unique index ของตาราง) — แก้ไขแถวเดิมไม่ต้องชนตัวเอง
                    Rule::unique('tb_holiday', 'holiday_date')->ignore($request->id),
                ],
                'name'   => 'required|string|max:255',
                'type'   => ['required', Rule::in(array_keys(Holiday::TYPES))],
                'remark' => 'nullable|string|max:255',
            ],
            [
                'holiday_date.required' => 'กรุณาเลือกวันที่',
                'holiday_date.date'     => 'รูปแบบวันที่ไม่ถูกต้อง',
                'holiday_date.unique'   => 'มีวันหยุดของวันที่นี้อยู่แล้ว',
                'name.required'         => 'กรุณากรอกชื่อวันหยุด',
                'type.required'         => 'กรุณาเลือกประเภทวันหยุด',
                'type.in'               => 'ประเภทวันหยุดไม่ถูกต้อง',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ]);
        }

        Holiday::updateOrCreate(
            ['id' => $request->id],
            [
                'holiday_date' => $date,
                'name'         => $request->name,
                'type'         => $request->type,
                'remark'       => $request->remark ?: null,
                // switch ในฟอร์ม: ติ๊ก = ส่ง is_active (Y), ไม่ติ๊ก = ไม่ส่ง (N)
                'is_active'    => $request->has('is_active') ? 'Y' : 'N',
            ]
        );

        return response()->json([
            'status'  => 200,
            'message' => $request->id ? 'แก้ไขวันหยุดสำเร็จ' : 'เพิ่มวันหยุดสำเร็จ',
        ]);
    }

    public function destroy(Request $request)
    {
        $holiday = Holiday::find($request->id);

        if (!$holiday) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบวันหยุดที่ต้องการลบ',
            ]);
        }

        $holiday->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'ลบวันหยุดสำเร็จ',
        ]);
    }

    // สลับสถานะเปิด-ปิดใช้งานจากหน้าตาราง
    public function toggleStatus(Request $request)
    {
        $holiday = Holiday::find($request->id);

        if (!$holiday) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบวันหยุดที่ต้องการ',
            ]);
        }

        $holiday->is_active = $request->is_active === 'Y' ? 'Y' : 'N';
        $holiday->save();

        return response()->json([
            'status'    => 200,
            'message'   => $holiday->is_active === 'Y' ? 'เปิดใช้งานแล้ว' : 'ปิดใช้งานแล้ว',
            'is_active' => $holiday->is_active,
        ]);
    }

    /** ปฏิทินรายปี 12 เดือน (คืน HTML ผ่าน AJAX) */
    public function calendar()
    {
        $year = (int) (request('year') ?: date('Y'));

        $holidays = Holiday::whereYear('holiday_date', $year)
            ->orderBy('holiday_date')
            ->get()
            ->keyBy(fn ($h) => (string) $h->holiday_date);

        $html = view('holiday.calendar', [
            'year'     => $year,
            'holidays' => $holidays,
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html,
            'count'  => $holidays->where('is_active', 'Y')->count(),
        ]);
    }

    /**
     * รับวันที่จากฟอร์ม (flatpickr ส่งมาเป็น d/m/Y) แปลงเป็น Y-m-d
     * รองรับ Y-m-d ด้วย เผื่อถูกยิงมาตรง ๆ · แปลงไม่ได้ = คืน null ให้ validator ตีกลับ
     */
    private function parseDate($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $value, $m)) {
            [, $d, $mo, $y] = $m;

            return checkdate((int) $mo, (int) $d, (int) $y)
                ? sprintf('%04d-%02d-%02d', $y, $mo, $d)
                : null;
        }

        if (preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2})$#', $value, $m)) {
            [, $y, $mo, $d] = $m;

            return checkdate((int) $mo, (int) $d, (int) $y)
                ? sprintf('%04d-%02d-%02d', $y, $mo, $d)
                : null;
        }

        return null;
    }
}
