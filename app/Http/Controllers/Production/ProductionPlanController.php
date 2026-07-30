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
use App\Models\Emp;
use App\Models\ProdMethod;
use App\Models\PlanningProdMethod;

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
            // วันเวลาที่บรรจุเสร็จ (packing_datetie) — เก็บเป็น datetime แสดง วัน/เดือน/ปี ชั่วโมง:นาที
            ->editColumn('packing_datetie', fn ($row) => $row->packing_datetie ? \Carbon\Carbon::parse($row->packing_datetie)->format('d/m/Y H:i') : '-')
            // เลขที่ใบเบิก: ดึงจาก red_bill_code (ว่าง = แสดง -)
            ->addColumn('red_bill_code', fn ($row) => $row->red_bill_code ?: '-')
            // สถานะภายใน: รวม planning_status ของ planning item ที่อยู่ใน header (planning_code) เดียวกัน
            // เฉพาะรายการ planning เท่านั้น — ไม่ดึงสถานะของ semi/pigment มา — ตัดค่าซ้ำ คั่นด้วย ,
            ->addColumn('inner_status', function ($row) {
                $statuses = Planning::where('planning_header_id', $row->planning_header_id)
                    ->pluck('planning_status')
                    ->filter(fn ($s) => $s !== null && $s !== '')
                    ->unique()
                    ->values();

                $text = $statuses->isEmpty()
                    ? '-'
                    : $statuses->map(fn ($s) => e($s))->implode(', ');

                // บรรทัดที่ 2: สถานะปิดงานของ item แถวนี้ (อ้างอิงคอลัมน์ end_job) — รูปแบบเดียวกับ badge กะ ใน Inplan
                $end_job_badge = ($row->end_job ?? 'N') === 'Y'
                    ? '<span class="badge bg-label-success">ปิดงาน</span>'
                    : '<span class="badge bg-label-warning">ยังไม่ปิดงาน</span>';

                return $text.'<br>'.$end_job_badge;
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

        // สถานะปิดงาน (end_job): 'all' = ทั้งหมด (ไม่กรอง), 'Y' = ปิดงาน, 'N' = ยังไม่ปิดงาน
        // ค่าอื่น/ไม่ได้ส่งมา = ใช้ค่าเริ่มต้น 'N' (ยังไม่ปิดงาน)
        $end_job = request('end_job', 'N');
        $end_job = in_array($end_job, ['all', 'Y', 'N'], true) ? $end_job : 'N';

        // ค้นหาช่วงวันที่: เลือกฟิลด์ได้เฉพาะ inplan / custwant / packing_datetie (whitelist กัน SQL injection ที่ชื่อคอลัมน์)
        $date_field = request('date_field');
        $date_field = in_array($date_field, ['inplan', 'custwant', 'packing_datetie'], true) ? $date_field : 'inplan';
        $date_start = request('date_start');
        $date_end   = request('date_end');

        // เมื่อค้นหาตามวันเวลาที่บรรจุเสร็จ (และมีการระบุช่วงวันที่) → เรียงตามวันเวลาบรรจุเสร็จ จากล่าสุดไปเก่า
        $sort_by_packing = $date_field === 'packing_datetie' && (!empty($date_start) || !empty($date_end));
        // ลำดับสำหรับ ROW_NUMBER (#) ให้ตรงกับลำดับที่แสดงผล — ประกอบจากค่าที่ whitelist แล้วเท่านั้น
        $row_order = $sort_by_packing
            ? 'tb_planning.packing_datetie DESC, tb_planning.id DESC'
            : 'tb_planning.id DESC';

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
                DB::raw('ROW_NUMBER() OVER (ORDER BY '.$row_order.') AS rownum')
            ])
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('tb_planning.machine_no', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning.itemno', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning_header.orderno', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning_header.planning_code', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning_header.custno', 'LIKE', '%'.$search.'%')
                        // ค้นหาจากพนักงานผู้รับผิดชอบ: รหัสพนักงาน หรือ ชื่อ/นามสกุล (ตาราง emp อ้างด้วย tb_planning.empno)
                        ->orWhere('tb_planning.empno', 'LIKE', '%'.$search.'%')
                        ->orWhereExists(function ($sub) use ($search) {
                            $sub->select(DB::raw(1))->from('emp')
                                ->whereColumn('emp.empno', 'tb_planning.empno')
                                ->where(function ($q) use ($search) {
                                    $q->where('emp.empname', 'LIKE', '%'.$search.'%')
                                        ->orWhere('emp.empsur', 'LIKE', '%'.$search.'%')
                                        // รองรับการพิมพ์ "ชื่อ นามสกุล" ติดกันเป็นข้อความเดียว
                                        ->orWhereRaw("CONCAT(emp.empname, ' ', emp.empsur) LIKE ?", ['%'.$search.'%']);
                                });
                        });
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
            // กรองด้วยสถานะปิดงาน — 'ยังไม่ปิดงาน' นับรวมค่า NULL/ว่าง ด้วย
            ->when($end_job === 'Y', function ($query) {
                $query->where('tb_planning.end_job', 'Y');
            })
            ->when($end_job === 'N', function ($query) {
                $query->where(function ($query) {
                    $query->where('tb_planning.end_job', '!=', 'Y')
                        ->orWhereNull('tb_planning.end_job');
                });
            })
            // กรองช่วงวันที่ตามฟิลด์ที่เลือก (inplan / custwant) — ระบุด้านเดียวหรือทั้งช่วงก็ได้
            ->when(!empty($date_start), function ($query) use ($date_field, $date_start) {
                $query->whereDate('tb_planning.'.$date_field, '>=', $date_start);
            })
            ->when(!empty($date_end), function ($query) use ($date_field, $date_end) {
                $query->whereDate('tb_planning.'.$date_field, '<=', $date_end);
            })
            // เรียงผลลัพธ์: ปกติเรียงตาม id ล่าสุด; ถ้าค้นตามวันเวลาบรรจุเสร็จให้เรียงตามวันเวลานั้น จากล่าสุดไปเก่า
            ->when($sort_by_packing, function ($query) {
                $query->orderBy('tb_planning.packing_datetie', 'desc');
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
                $planning_header = PlanningHeader::with('plannings.subHeadersRecursive')
                    ->find($planning->planning_header_id);
            }
        } elseif ($planning_header_id) {
            $planning_header = PlanningHeader::with('plannings.subHeadersRecursive')
                ->find($planning_header_id);
        }

        // เงื่อนไขปิดออเดอร์: end_job ของ item ทั้งต้นไม้ (recursive) ต้องเป็น 'Y' ครบทุกแถว
        $all_jobs_done = $planning_header ? $this->allEndJobsDone($planning_header) : false;

        $html = view('production-planning.planning.planning-form', [
            'planning_header' => $planning_header,
            'all_jobs_done'   => $all_jobs_done,
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
        $last_itemno    = null;

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
                        // แผนการผลิตที่สร้างจาก semi นี้ (ถ้าอนุมัติแล้ว) — ใช้แสดงสถานะในคอลัมน์ "จัดการ"
                        'result_planning_id'  => $r->result_planning_id,
                        'plan_status'         => $r->result_planning?->planning_status,
                        // สถานะปิดงานของแผนที่สร้างจาก semi นี้ (คอลัมน์ end_job ของ tb_planning)
                        'plan_end_job'        => $r->result_planning?->end_job,
                        // สถานะปิดออเดอร์ของแผน semi (end_order ของ tb_planning_header) — ใช้เป็นเกณฑ์ปิด end_job ใบแม่
                        'plan_end_order'      => $r->result_planning?->planning_header?->end_order,
                        'plan_code'           => $r->result_planning?->planning_header?->planning_code,
                    ];
                };

                // เรียงให้ "รออนุมัติ" อยู่บนสุด แล้วตามด้วยอนุมัติ/ปฏิเสธ
                $orderByStatus = "FIELD(status, 'request', 'approved', 'reject')";

                $semi_list = SemiPigment::with('result_planning.planning_header')
                    ->where('planning_id', $planning_id)
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

            // ตั้งต้น Item No. ให้ item ใหม่ ด้วย itemno ของ item ล่าสุดใน header เดียวกัน
            $last_itemno = Planning::where('planning_header_id', $planning_header_id)
                ->whereNotNull('itemno')
                ->where('itemno', '!=', '')
                ->orderByDesc('id')
                ->value('itemno');
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

        // เงื่อนไขปิดงาน (end_job): งาน Semi ของ item นี้ต้องจบงานครบก่อนจึงจะติ๊กได้ (item ใหม่ยังไม่มี semi → true)
        $item_semi_jobs_done = $planning_item ? $this->itemSemiJobsDone($planning_item) : true;

        // พนักงานผู้รับผิดชอบ: รายชื่อพนักงานในแผนก (emp.dept = company ของ item)
        $employees    = $this->employeesByDept($item_company);
        $selected_emp = $planning_item?->empno ? Emp::find($planning_item->empno) : null;

        // สถานะวิธีการผลิต: master (dropdown) + แถวเดิมของ item
        $prod_methods     = ProdMethod::where('is_active', 'Y')
            ->orderBy('sort', 'asc')->orderBy('name', 'asc')
            ->get(['id', 'name']);
        $prod_method_rows = $planning_item ? $planning_item->prodMethods : collect();

        $html = view('production-planning.planning.planning-item-form', [
            'planning_item'      => $planning_item,
            'planning_header_id' => $planning_header_id,
            'parent_header'      => $parent_header,
            'semi_list'          => $semi_list,
            'pigment_list'       => $pigment_list,
            'parent_orderno'     => $parent_orderno,
            'last_itemno'        => $last_itemno,
            'machines' => $machines,
            'planning_statuses' => $planning_statuses,
            'departments' => $departments,
            'item_semi_jobs_done' => $item_semi_jobs_done,
            'employees'    => $employees,
            'selected_emp' => $selected_emp,
            'prod_methods'     => $prod_methods,
            'prod_method_rows' => $prod_method_rows,
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html
        ]);
    }

    // รายชื่อพนักงานผู้รับผิดชอบตามแผนก (emp.dept = ชื่อแผนก) เฉพาะที่เปิดใช้งาน
    private function employeesByDept(?string $dept)
    {
        if (!$dept) {
            return collect();
        }

        return Emp::where('dept', $dept)
            ->where('is_active', 'Y')
            ->orderBy('empname', 'asc')
            ->get(['empno', 'empname', 'empsur']);
    }

    // คืนตัวเลือกเครื่องจักร + สถานะ Planning + พนักงาน ตามแผนก (company) ที่เลือก
    // ใช้เมื่อผู้ใช้เปลี่ยน dropdown แผนกในโมดัลแก้ไข item เพื่อโหลดชุดตัวเลือกใหม่
    public function deptOptions(Request $request)
    {
        $company = $request->get('company');

        // code = ค่าที่บันทึกลง machine_no, label = รหัส + ความเร็วรอบสำหรับแสดงใน dropdown
        $machines = Machine::where('dept', $company)
            ->get()
            ->map(fn (Machine $m) => [
                'code'  => $m->MBX,
                'label' => $m->displayLabel(),
            ])
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

        $employees = $this->employeesByDept($company)->map(fn ($e) => [
            'empno' => $e->empno,
            'name'  => trim($e->empname.' '.$e->empsur),
        ])->values();

        return response()->json([
            'status'    => 200,
            'machines'  => $machines,
            'statuses'  => $statuses,
            'employees' => $employees,
        ]);
    }

    public function saveItem(Request $request)
    {
        // ช่องตัวเลขในฟอร์มแสดงผลด้วย number_format จึงอาจมีจุลภาคหลักพัน (เช่น "1,250.00")
        // ตัดจุลภาคออกก่อน validate ไม่งั้น rule numeric จะไม่ผ่าน
        foreach (['quantity', 'weight', 'weight_produced', 'weight_packing'] as $numeric_field) {
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
            'weight_packing'     => 'nullable|numeric|min:0',
            'red_bill_code'      => 'nullable|string|max:255',
            'end_job'            => 'nullable|in:Y,N',
            'empno'              => 'nullable|string|max:50|exists:emp,empno',
            'machine_no'         => 'nullable|string|max:255',
            'plan_type'          => 'nullable|string|max:255',
            'planning_status'    => 'nullable|string|max:255',
            'inplan'         => 'nullable|date',
            'work_shift'         => 'nullable|in:A,B,C',
            'start_date'         => 'nullable|date',
            'start_time'         => 'nullable|date_format:H:i',
            'end_date'           => 'nullable|date',
            'end_time'           => 'nullable|date_format:H:i',
            'qc_date'            => 'nullable|date',
            'qc_time'            => 'nullable|string|max:10',
            'qc_status'          => 'nullable|string|max:255',
            'packing_datetie'    => 'nullable|string|max:255',
            'pack_remark'        => 'nullable|string|max:1000',
            'mdate'              => 'nullable|date',
            'custwant'           => 'nullable|date',
            'senddate'           => 'nullable|date',
            // สถานะวิธีการผลิต (การ์ดสีน้ำเงิน) — array คู่ขนาน
            'prod_method_id'     => 'nullable|array',
            'prod_method_id.*'   => 'nullable|integer|exists:tb_prod_method,id',
            'prod_method_date.*' => 'nullable|date',
            'prod_method_start.*'=> 'nullable|date_format:H:i',
            'prod_method_end.*'  => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ]);
        }

        // ออเดอร์ถูกปิดแล้ว (end_order = Y) → ห้ามเพิ่ม/แก้ไข Planning Item ของ header นี้
        // ตรวจซ้ำฝั่ง server กันการ bypass attribute disabled ฝั่ง client (ทั้งเพิ่มใหม่และแก้ไข)
        $target_header = PlanningHeader::find($request->planning_header_id);
        if ($target_header && ($target_header->end_order ?? 'N') === 'Y') {
            return response()->json([
                'status'  => 422,
                'message' => 'ออเดอร์นี้ถูกปิดแล้ว (End Order) ไม่สามารถเพิ่มหรือแก้ไข Planning ได้',
            ]);
        }

        $fields = $request->only([
            'planning_header_id', 'company', 'itemno', 'quantity', 'lot', 'weight',
            'weight_produced', 'weight_packing', 'red_bill_code', 'end_job', 'empno',
            'machine_no', 'plan_type', 'planning_status', 'inplan', 'work_shift', 'start_date', 'start_time', 'end_date', 'end_time',
            'qc_date', 'qc_time', 'qc_status', 'packing_datetie', 'pack_remark',
            'mdate', 'custwant', 'senddate', 'remark'
        ]);

        // หมายเหตุ: Semi / Pigment ไม่ถูกบันทึกที่นี่อีกต่อไป
        // ย้ายไปบันทึกลงฐานข้อมูลทันทีผ่าน modal เพิ่ม/แก้ไข Semi (SemiPigmentController::entryStore/entryUpdate)

        $planning_id = $request->planning_id;
        $is_update   = !empty($planning_id);

        // เงื่อนไขปิดงาน (end_job = Y): ถ้า item มีคำขอ Semi แผน Semi ทุกใบต้องถูกปิดออเดอร์ (end_order = Y) ก่อน
        // ตรวจซ้ำฝั่ง server กันการ bypass attribute disabled ฝั่ง client
        if (($request->end_job ?? 'N') === 'Y' && $is_update) {
            $item = Planning::find($planning_id);
            if ($item && !$this->itemSemiJobsDone($item)) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'ยังปิดงานไม่ได้ เพราะแผน Semi ยังปิดออเดอร์ (End Order) ไม่ครบทุกใบ',
                ]);
            }
        }

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
                $created     = Planning::create($fields);
                $planning_id = $created->id;
            }

            // 2) sync สถานะวิธีการผลิต (การ์ดสีน้ำเงิน) — ลบของเดิมแล้ว insert ใหม่จาก array ที่ส่งมา
            $this->syncProdMethods($planning_id, $request);

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

    /**
     * sync สถานะวิธีการผลิต (tb_planning_prod_method) ของ planning หนึ่ง ๆ
     * ลบแถวเดิมทั้งหมดแล้ว insert ใหม่จาก array คู่ขนานที่ส่งมา (ข้ามแถวที่ว่างทั้งหมด)
     * เรียกภายในทรานแซกชันของ saveItem
     */
    private function syncProdMethods($planning_id, Request $request): void
    {
        PlanningProdMethod::where('planning_id', $planning_id)->delete();

        $methods = $request->input('prod_method_id', []);
        $dates   = $request->input('prod_method_date', []);
        $starts  = $request->input('prod_method_start', []);
        $ends    = $request->input('prod_method_end', []);

        $count = max(count($methods), count($dates), count($starts), count($ends));

        for ($i = 0; $i < $count; $i++) {
            $method_id = $methods[$i] ?? null;
            $work_date = $dates[$i]   ?? null;
            $start     = $starts[$i]  ?? null;
            $end       = $ends[$i]    ?? null;

            // ข้ามแถวที่ว่างทั้งหมด
            if (empty($method_id) && empty($work_date) && empty($start) && empty($end)) {
                continue;
            }

            PlanningProdMethod::create([
                'planning_id'    => $planning_id,
                'prod_method_id' => $method_id ?: null,
                'work_date'      => $work_date ?: null,
                'start_time'     => $start ?: null,
                'end_time'       => $end ?: null,
                'sort'           => $i,
            ]);
        }
    }

    /**
     * รวบรวมค่า end_job ของ item ตรงของ header + item ของ sub-header (semi/pigment) ทุกชั้นแบบ recursive
     * (อ้างอิงรูปแบบเดียวกับ OrderPlanController::collectStatuses)
     */
    private function collectEndJobs($plannings, array &$jobs): void
    {
        foreach ($plannings as $p) {
            $jobs[] = $p->end_job;
            foreach ($p->subHeadersRecursive as $header) {
                $this->collectEndJobs($header->planningsRecursive, $jobs);
            }
        }
    }

    /**
     * true เมื่อมี item อย่างน้อย 1 รายการ และ end_job ทุกแถวที่เกี่ยวข้อง (recursive) = 'Y'
     */
    private function allEndJobsDone(PlanningHeader $header): bool
    {
        $header->loadMissing('plannings.subHeadersRecursive');
        $jobs = [];
        $this->collectEndJobs($header->plannings, $jobs);

        return count($jobs) > 0 && collect($jobs)->every(fn ($j) => $j === 'Y');
    }

    /**
     * true เมื่องาน Semi ของ item นี้ "จบงาน" ครบทุกรายการ (ใช้เป็นเงื่อนไขก่อนอนุญาตให้ปิดงาน end_job ของ item)
     * - ถ้า item ไม่มีคำขอ Semi เลย → ไม่มีข้อจำกัด (คืน true)
     *
     * เงื่อนไข (ตรงกับสถานะที่แสดงในคอลัมน์ "จัดการ" ของตาราง Semi ใน modal) — คำขอ Semi ที่ไม่ถูกปฏิเสธ
     * (status != reject) ทุกใบต้อง:
     *   1) "สร้างแผนการผลิตแล้ว" (มี result_planning_id) — ถ้ายังรออนุมัติ/อนุมัติแล้วแต่ยังไม่สร้างแผน → ยังปิดงานไม่ได้
     *   2) แผน Semi ที่สร้าง (tb_planning_header ของ result_planning) ต้อง "ปิดออเดอร์" (end_order = 'Y') แล้ว
     *
     * หมายเหตุ: เมื่อสร้างแผนจาก Semi ระบบสร้าง tb_planning_header (plan_type=semi) ให้ 1 ใบ ซึ่งมี end_order
     * ของตัวเอง — และ end_order = 'Y' ตั้งได้ก็ต่อเมื่อ end_job ของ item ในแผน semi นั้นครบแล้ว (allEndJobsDone)
     * จึงใช้ end_order เป็นสัญญาณ "แผน semi เสร็จสมบูรณ์" ที่ตรงความหมายกว่าการไล่เช็ค end_job รายตัว
     */
    private function itemSemiJobsDone(Planning $item): bool
    {
        $semis = SemiPigment::with('result_planning.planning_header')
            ->where('planning_id', $item->id)
            ->where('type', 'semi')
            ->where('status', '!=', SemiPigment::STATUS_REJECT)
            ->get();

        foreach ($semis as $semi) {
            // (1) ยังไม่ได้สร้างแผนการผลิต (รออนุมัติ / อนุมัติแล้วแต่ยังไม่สร้าง) → ยังปิดงานไม่ได้
            //     result_planning_id ว่าง = NULL หรือ 0 (ให้ตรงกับ !empty() ที่ใช้แสดงผลใน modal)
            if (empty($semi->result_planning_id)) {
                return false;
            }

            // (2) สร้างแผนแล้ว แต่แผน Semi นั้นยังไม่ถูกปิดออเดอร์ (end_order != 'Y') → ยังปิดงานไม่ได้
            $semi_header = $semi->result_planning?->planning_header;
            if (($semi_header->end_order ?? 'N') !== 'Y') {
                return false;
            }
        }

        return true;
    }

    /**
     * บันทึกสถานะปิดออเดอร์ (end_order) ของ PlanningHeader
     * - ปลด (N) ได้ตลอด
     * - ติ๊ก (Y) ได้ต่อเมื่อ end_job ของ item ที่เกี่ยวข้องทั้งต้นไม้เป็น 'Y' ครบ (ตรวจซ้ำฝั่ง server กัน bypass)
     */
    public function saveEndOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'planning_header_id' => 'required|exists:tb_planning_header,id',
            'end_order'          => 'required|in:Y,N',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ]);
        }

        $header = PlanningHeader::find($request->planning_header_id);

        // ตรวจเงื่อนไขเฉพาะตอนจะติ๊กปิดออเดอร์ (Y); การปลด (N) ทำได้เสมอ
        if ($request->end_order === 'Y' && !$this->allEndJobsDone($header)) {
            return response()->json([
                'status'  => 422,
                'message' => 'ยังปิดออเดอร์ไม่ได้ เพราะยังมีรายการที่ยังไม่จบงาน (End Job)',
            ]);
        }

        $header->update(['end_order' => $request->end_order]);

        return response()->json([
            'status'             => 200,
            'message'            => $request->end_order === 'Y' ? 'ปิดออเดอร์สำเร็จ' : 'ยกเลิกการปิดออเดอร์แล้ว',
            'planning_header_id' => $header->id,
        ]);
    }
}
