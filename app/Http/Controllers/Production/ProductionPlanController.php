<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\PlanningHeader;
use App\Models\Planning;
use App\Models\SemiPigment;
use App\Models\Pigment;
use App\Models\Machine;
use App\Models\PlanningStatus;
use App\Models\Department;

class ProductionPlanController extends Controller
{
    public function index()
    {
        $departments = Department::where('is_active', 'Y')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return view('production-planning.planning.index', [
            'departments' => $departments,
        ]);
    }

    public function datatable()
    {
        $data = $this->dataQuery();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('rownum', function($row) {
                return $row->rownum;
            })
            // แผนกจริงของ item: ใช้ของ item ก่อน ถ้าว่างจึง fallback ไปที่ header
            ->editColumn('company', fn ($row) => $row->company ?: $row->header_company)
            // Inplan: แสดงวันที่ แล้วขึ้นบรรทัดใหม่แสดงกะการผลิต (work_shift) ถ้ามี — เช่น "กะ A"
            ->editColumn('inplan', function ($row) {
                $date = $row->inplan ? \Carbon\Carbon::parse($row->inplan)->format('d/m/Y') : '-';
                if (!empty($row->work_shift)) {
                    $date .= '<br><span class="badge bg-label-info">กะ '.e($row->work_shift).'</span>';
                }
                return $date;
            })
            ->editColumn('custwant', fn ($row) => $row->custwant ? \Carbon\Carbon::parse($row->custwant)->format('d/m/Y') : '-')
            // สถานะภายใน: รวม planning_status ของ planning item ที่อยู่ใน header (planning_code) เดียวกัน
            // เฉพาะรายการ planning เท่านั้น — ไม่ดึงสถานะของ semi/pigment มา — ตัดค่าซ้ำ คั่นด้วย ,
            ->addColumn('inner_status', function ($row) {
                $statuses = Planning::where('planning_header_id', $row->planning_header_id)
                    ->pluck('planning_status')
                    ->filter(fn ($s) => $s !== null && $s !== '')
                    ->unique()
                    ->values();

                if ($statuses->isEmpty()) {
                    return '-';
                }

                return $statuses->map(fn ($s) => e($s))->implode(', ');
            })
            ->addColumn('btnedit', function($row) {
                $btn_view = '<button class="btn btn-sm btn-icon btn-info me-2 btn_view" data-planning_id="'.$row->id.'" title ="ลบ">
                    <i class="ti ti-eye text-white ti-sm"></i>
                </button>';
                $btn_edit = '<button class="btn btn-sm btn-icon btn-warning me-2 btn_edit" data-planning_id="'.$row->id.'" title ="แก้ไข">
                    <i class="ti ti-pencil text-white ti-sm"></i>
                </button>';

                // return $btn_view.$btn_edit;
                return $btn_edit;
            })
            ->rawColumns(['inplan', 'inner_status', 'btnedit']) // 👈 บอกให้ column นี้ render HTML
            ->make(true);
    }

    public function dataQuery()
    {
        $search = request('search');
        $company = request('company');
        $planning_status = request('planning_status');

        // ค้นหาช่วงวันที่: เลือกฟิลด์ได้เฉพาะ inplan / custwant (whitelist กัน SQL injection ที่ชื่อคอลัมน์)
        $date_field = request('date_field');
        $date_field = in_array($date_field, ['inplan', 'custwant'], true) ? $date_field : 'inplan';
        $date_start = request('date_start');
        $date_end   = request('date_end');

        $data = Planning::
            leftJoin('tb_planning_header', 'tb_planning_header.id', '=', 'tb_planning.planning_header_id')
            ->select([
                'tb_planning.*', // มีคอลัมน์ company (ของ item) อยู่แล้ว
                // แผนกของ header ดึงมาเป็นชื่อแยก (header_company) กันชนกับ tb_planning.company
                // เมื่อ Yajra ห่อ query เป็น subquery นับจำนวนแถว MySQL ห้ามมีชื่อคอลัมน์ซ้ำใน derived table
                'tb_planning_header.company as header_company',
                'tb_planning_header.orderno as orderno',
                'tb_planning_header.planning_code as planning_code',
                'tb_planning_header.mdate as header_mdate',
                DB::raw('ROW_NUMBER() OVER (ORDER BY tb_planning.id DESC) AS rownum')
            ])
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('tb_planning.machine_no', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning.itemno', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning_header.orderno', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning_header.planning_code', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning_header.custno', 'LIKE', '%'.$search.'%');
                });
            })
             ->when(!empty($company), function ($query) use ($company) {
                // กรองด้วยแผนกจริงของ item (item ก่อน แล้ว fallback header)
                $query->whereRaw('COALESCE(tb_planning.company, tb_planning_header.company) = ?', [$company]);
            })
            // กรองด้วยสถานะการวางแผน — สถานะผูกกับแผนก (ค่าที่ส่งมาเป็นชื่อสถานะของแผนกที่เลือก)
            ->when(!empty($planning_status), function ($query) use ($planning_status) {
                $query->where('tb_planning.planning_status', $planning_status);
            })
            // กรองช่วงวันที่ตามฟิลด์ที่เลือก (inplan / custwant) — ระบุด้านเดียวหรือทั้งช่วงก็ได้
            ->when(!empty($date_start), function ($query) use ($date_field, $date_start) {
                $query->whereDate('tb_planning.'.$date_field, '>=', $date_start);
            })
            ->when(!empty($date_end), function ($query) use ($date_field, $date_end) {
                $query->whereDate('tb_planning.'.$date_field, '<=', $date_end);
            })
            ->orderby('tb_planning.id', 'desc');

        // $data = $data->get();
        // foreach($data as $value){
        //     dd($value->planning_header);
        // }

        return $data;
    }

    public function edit()
    {
        $planning_id = request('planning_id');
        $planning_header_id = request('planning_header_id');

        $planning_header = null;

        if ($planning_id) {
            $planning = Planning::find($planning_id);
            if ($planning) {
                $planning_header = PlanningHeader::with('plannings')
                    ->find($planning->planning_header_id);
            }
        } elseif ($planning_header_id) {
            $planning_header = PlanningHeader::with('plannings')
                ->find($planning_header_id);
        }

        $html = view('production-planning.planning.planning-form', [
            'planning_header' => $planning_header,
        ])->render();

        return response()->json([
            'status' => 200,
            'data' => $html
        ]);
    }

    public function editItem()
    {
        $planning_id        = request('planning_id');
        $planning_header_id = request('planning_header_id');

        $planning_item  = null;
        $semi_list      = [];
        $pigment_list   = [];
        $parent_orderno = null;

        $parent_header = null;

        if ($planning_id) {
            $planning_item = Planning::with('planning_header')->find($planning_id);

            if ($planning_item) {
                $parent_header  = $planning_item->planning_header;
                $parent_orderno = $parent_header?->orderno;

                // โหลดทุกรายการพร้อมสถานะ (รออนุมัติ = แก้ไขได้, อนุมัติ/ปฏิเสธ = ล็อกอ่านอย่างเดียว)
                $mapRow = function (SemiPigment $r) {
                    return [
                        'id'                  => $r->id,
                        'company'             => $r->company,
                        'mdate'               => !empty($r->order_date) ? $r->order_date : null,
                        'custwant'            => !empty($r->want_date) ? $r->want_date : null,
                        'custno'              => $r->custno,
                        'itemno'              => $r->itemno,
                        'semi_code'           => $r->semi_code,
                        'primary_color'       => $r->primary_color,
                        'weight_request'      => $r->weight_request,
                        'balance'             => $r->balance,
                        'lot_no'              => $r->lot_no,
                        'retrospective'       => $r->retrospective,
                        'increase_production' => $r->increase_production,
                        'weight_production'   => $r->weight_production,
                        'red_bill_code'       => $r->red_bill_code,
                        'status'              => $r->status,
                        'status_label'        => $r->statusLabel(),
                    ];
                };

                // เรียงให้ "รออนุมัติ" อยู่บนสุด แล้วตามด้วยอนุมัติ/ปฏิเสธ
                $orderByStatus = "FIELD(status, 'request', 'approved', 'reject')";

                $semi_list = SemiPigment::where('planning_id', $planning_id)
                    ->where('type', 'semi')
                    ->orderByRaw($orderByStatus)
                    ->orderBy('id')
                    ->get()->map($mapRow)->values()->toArray();

                // Pigment แยกเป็นตารางของตัวเอง (tb_pigment) — ไม่มีฟิลด์ semi (company/semi_code/primary_color/lot_no/red_bill/increase)
                $mapPigmentRow = function (Pigment $r) {
                    return [
                        'id'                => $r->id,
                        'mdate'             => !empty($r->order_date) ? $r->order_date : null,
                        'custwant'          => !empty($r->want_date) ? $r->want_date : null,
                        'custno'            => $r->custno,
                        'itemno'            => $r->itemno,
                        'weight_request'    => $r->weight_request,
                        'balance'           => $r->balance,
                        'retrospective'     => $r->retrospective,
                        'weight_production' => $r->weight_production,
                        'status'            => $r->status,
                        'status_label'      => $r->statusLabel(),
                    ];
                };

                $pigment_list = Pigment::where('planning_id', $planning_id)
                    ->orderByRaw($orderByStatus)
                    ->orderBy('id')
                    ->get()->map($mapPigmentRow)->values()->toArray();
            }
        } elseif ($planning_header_id) {
            $parent_header  = PlanningHeader::find($planning_header_id);
            $parent_orderno = $parent_header?->orderno;
        }

        // แผนกจริงของ item: ใช้แผนกที่ตั้งไว้ที่ item ก่อน ถ้าว่างจึง fallback ไปที่ header
        $item_company = ($planning_item && $planning_item->company)
            ? $planning_item->company
            : $parent_header?->company;

        $machines = Machine::where('dept', $item_company)->get();

        // สถานะ Planning ตามแผนก (company) ของ item — เฉพาะที่เปิดใช้งาน
        // dept ใน tb_planning_status เก็บเป็น id ของ tb_departments จึง map ชื่อแผนก (company) → id ก่อน
        $dept_id = \App\Models\Department::where('name', $item_company)->value('id');
        $planning_statuses = PlanningStatus::where('dept', $dept_id)
            ->where('is_active', 'Y')
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // รายชื่อแผนก (department) สำหรับ dropdown Company ใน modal เพิ่ม Semi/Pigment
        $departments = Department::where('is_active', 'Y')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        $html = view('production-planning.planning.planning-item-form', [
            'planning_item'      => $planning_item,
            'planning_header_id' => $planning_header_id,
            'parent_header'      => $parent_header,
            'semi_list'          => $semi_list,
            'pigment_list'       => $pigment_list,
            'parent_orderno'     => $parent_orderno,
            'machines' => $machines,
            'planning_statuses' => $planning_statuses,
            'departments' => $departments
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html
        ]);
    }

    // คืนตัวเลือกเครื่องจักร + สถานะ Planning ตามแผนก (company) ที่เลือก
    // ใช้เมื่อผู้ใช้เปลี่ยน dropdown แผนกในโมดัลแก้ไข item เพื่อโหลดชุดตัวเลือกใหม่
    public function deptOptions(Request $request)
    {
        $company = $request->get('company');

        $machines = Machine::where('dept', $company)
            ->get()
            ->pluck('MBX')
            ->values();

        // dept ใน tb_planning_status เก็บเป็น id ของ tb_departments จึง map ชื่อแผนก → id ก่อน
        $dept_id = Department::where('name', $company)->value('id');
        $statuses = PlanningStatus::where('dept', $dept_id)
            ->where('is_active', 'Y')
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->pluck('name')
            ->values();

        return response()->json([
            'status'   => 200,
            'machines' => $machines,
            'statuses' => $statuses,
        ]);
    }

    public function saveItem(Request $request)
    {
        // ช่องตัวเลขในฟอร์มแสดงผลด้วย number_format จึงอาจมีจุลภาคหลักพัน (เช่น "1,250.00")
        // ตัดจุลภาคออกก่อน validate ไม่งั้น rule numeric จะไม่ผ่าน
        foreach (['quantity', 'weight', 'weight_produced'] as $numeric_field) {
            if ($request->filled($numeric_field)) {
                $request->merge([
                    $numeric_field => str_replace(',', '', $request->input($numeric_field)),
                ]);
            }
        }

        $validator = Validator::make($request->all(), [
            'planning_header_id' => 'required|exists:tb_planning_header,id',
            'company'            => 'nullable|string|max:255',
            'itemno'             => 'required|string|max:255',
            'quantity'           => 'nullable|numeric|min:0',
            'lot'                => 'nullable|string|max:255',
            'weight'             => 'nullable|numeric|min:0',
            'weight_produced'    => 'nullable|numeric|min:0',
            'red_bill_code'      => 'nullable|string|max:255',
            'machine_no'         => 'nullable|string|max:255',
            'plan_type'          => 'nullable|string|max:255',
            'planning_status'    => 'nullable|string|max:255',
            'inplan'         => 'nullable|date',
            'work_shift'         => 'nullable|in:A,B,C',
            'start_date'         => 'nullable|date',
            'start_time'         => 'nullable|date_format:H:i',
            'qc_date'            => 'nullable|date',
            'qc_time'            => 'nullable|string|max:10',
            'qc_status'          => 'nullable|string|max:255',
            'packing_datetie'    => 'nullable|string|max:255',
            'mdate'              => 'nullable|date',
            'custwant'           => 'nullable|date',
            'senddate'           => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ]);
        }

        $fields = $request->only([
            'planning_header_id', 'company', 'itemno', 'quantity', 'lot', 'weight',
            'weight_produced', 'red_bill_code',
            'machine_no', 'plan_type', 'planning_status', 'inplan', 'work_shift', 'start_date', 'start_time',
            'qc_date', 'qc_time', 'qc_status', 'packing_datetie',
            'mdate', 'custwant', 'senddate', 'remark'
        ]);

        // หมายเหตุ: Semi / Pigment ไม่ถูกบันทึกที่นี่อีกต่อไป
        // ย้ายไปบันทึกลงฐานข้อมูลทันทีผ่าน modal เพิ่ม/แก้ไข Semi (SemiPigmentController::entryStore/entryUpdate)

        $planning_id = $request->planning_id;
        $is_update   = !empty($planning_id);

        DB::beginTransaction();
        try {
            // 1) บันทึก / อัปเดต planning หลัก
            if ($is_update) {
                // ถ้าวันที่ส่งสินค้า (senddate) เปลี่ยนไปจากเดิม → เก็บค่าเดิมไว้ใน senddate_log (สะสมต่อท้าย คั่นด้วย comma)
                $existing = Planning::find($planning_id);
                if ($existing) {
                    $old_send = $existing->senddate ? \Carbon\Carbon::parse($existing->senddate)->format('Y-m-d') : null;
                    $new_send = !empty($fields['senddate']) ? \Carbon\Carbon::parse($fields['senddate'])->format('Y-m-d') : null;
                    if ($old_send && $old_send !== $new_send) {
                        $fields['senddate_log'] = $existing->senddate_log
                            ? $existing->senddate_log . ',' . $old_send
                            : $old_send;
                    }

                    // ถ้าย้ายแผนก (company เปลี่ยน) → เครื่องจักร/สถานะเดิมเป็นของแผนกเก่า ล้างทิ้งเพื่อกันค่าที่ไม่ตรงแผนกใหม่
                    // (กันกรณี JS ฝั่งหน้าเว็บไม่ได้ล้างให้)
                    $old_company = $existing->company ?: null;
                    $new_company = !empty($fields['company']) ? $fields['company'] : null;
                    if ($old_company !== $new_company) {
                        $fields['machine_no']      = null;
                        $fields['planning_status'] = null;
                    }
                }

                Planning::where('id', $planning_id)->update($fields);
            } else {
                Planning::create($fields);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 500,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }

        return response()->json([
            'status'             => 200,
            'message'            => $is_update ? 'แก้ไขข้อมูล Planning สำเร็จ' : 'เพิ่มข้อมูล Planning สำเร็จ',
            'planning_header_id' => $fields['planning_header_id']
        ]);
    }
}
