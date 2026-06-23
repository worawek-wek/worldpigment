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
use App\Models\Machine;
use App\Models\PlanningStatus;

class ProductionPlanController extends Controller
{
    public function index()
    {
        // เครื่องจักรจัดกลุ่มตามแผนก (dept) เพื่อให้ dropdown กรองตาม company ฝั่ง JS
        $machines_by_dept = Machine::orderBy('dept')->orderBy('MBX')->get()
            ->groupBy('dept')
            ->map(fn ($g) => $g->pluck('MBX')->values())
            ->toArray();

        // ตัวเลือก plan_type ดึงจาก enum ของคอลัมน์โดยตรง
        $plan_types = $this->planTypeOptions();

        return view('production-planning.planning.index', compact('machines_by_dept', 'plan_types'));
    }

    /**
     * อ่านค่า enum ของคอลัมน์ plan_type มาเป็น array
     */
    private function planTypeOptions(): array
    {
        $col = DB::selectOne("SHOW COLUMNS FROM tb_planning_header LIKE 'plan_type'");

        if (!$col || !preg_match('/enum\((.*)\)/i', $col->Type, $m)) {
            return [];
        }

        return array_map(fn ($v) => trim($v, "'"), str_getcsv($m[1]));
    }

    public function datatable()
    {
        $data = $this->dataQuery();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('rownum', function($row) {
                return $row->rownum;
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
            ->rawColumns(['btnedit']) // 👈 บอกให้ column นี้ render HTML
            ->make(true);
    }

    public function dataQuery()
    {
        $search = request('search');
        $company = request('company');

        $data = Planning::
            leftJoin('tb_planning_header', 'tb_planning_header.id', '=', 'tb_planning.planning_header_id')
            ->select([
                'tb_planning.*',
                'tb_planning_header.company as company',
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
                $query->where('tb_planning_header.company', $company);
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

    /**
     * สร้างแผนการผลิตเอง (กรอกข้อมูลเอง) — สร้าง PlanningHeader + Planning (หลายรายการ) พร้อมกัน
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'planning_code'      => 'required|string|max:255',
            'company'            => 'required|string|max:255',
            'orderno'            => 'nullable|string|max:255',
            'custno'             => 'nullable|string|max:255',
            'saleno'             => 'nullable|string|max:255',
            'netqty'             => 'nullable|numeric',
            'mdate'              => 'nullable|date',
            'custwant'           => 'nullable|date',
            'senddate'           => 'nullable|date',
            'plan_type'          => 'nullable|string|max:255',
            'remark'             => 'nullable|string|max:1000',
            'items'              => 'required|array|min:1',
            'items.*.itemno'     => 'required|string|max:255',
            'items.*.quantity'   => 'nullable|numeric',
            'items.*.weight'     => 'nullable|numeric',
            'items.*.lot'        => 'nullable|string|max:255',
            'items.*.machine_no' => 'nullable|string|max:255',
        ], [
            'items.required'        => 'กรุณาเพิ่มรายการ Planning อย่างน้อย 1 รายการ',
            'items.*.itemno.required' => 'กรุณากรอก Item No. ให้ครบทุกรายการ',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ]);
        }

        $header_fields = $request->only([
            'planning_code', 'company', 'orderno', 'custno', 'saleno',
            'netqty', 'mdate', 'custwant', 'senddate', 'plan_type', 'remark'
        ]);

        $plan_type = $request->input('plan_type');

        DB::beginTransaction();
        try {
            $header = PlanningHeader::create($header_fields);

            foreach ($request->input('items', []) as $item) {
                if (empty($item['itemno'])) continue;

                Planning::create([
                    'planning_header_id' => $header->id,
                    'plan_type'          => $plan_type,
                    'itemno'             => $item['itemno'],
                    'quantity'           => $item['quantity']   ?? null,
                    'weight'             => $item['weight']     ?? null,
                    'lot'                => $item['lot']        ?? null,
                    'machine_no'         => $item['machine_no'] ?? null,
                    'mdate'              => $request->input('mdate')    ?: null,
                    'custwant'           => $request->input('custwant') ?: null,
                ]);
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
            'message'            => 'สร้างแผนการผลิตสำเร็จ',
            'planning_header_id' => $header->id
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

                $pigment_list = SemiPigment::where('planning_id', $planning_id)
                    ->where('type', 'pigment')
                    ->orderByRaw($orderByStatus)
                    ->orderBy('id')
                    ->get()->map($mapRow)->values()->toArray();
            }
        } elseif ($planning_header_id) {
            $parent_header  = PlanningHeader::find($planning_header_id);
            $parent_orderno = $parent_header?->orderno;
        }

        $machines = Machine::where('dept',$parent_header->company)->get();

        // สถานะ Planning ตามแผนก (company) ของ header — เฉพาะที่เปิดใช้งาน
        $planning_statuses = PlanningStatus::where('dept', $parent_header->company)
            ->where('is_active', 'Y')
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $html = view('production-planning.planning.planning-item-form', [
            'planning_item'      => $planning_item,
            'planning_header_id' => $planning_header_id,
            'parent_header'      => $parent_header,
            'semi_list'          => $semi_list,
            'pigment_list'       => $pigment_list,
            'parent_orderno'     => $parent_orderno,
            'machines' => $machines,
            'planning_statuses' => $planning_statuses
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html
        ]);
    }

    public function saveItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'planning_header_id' => 'required|exists:tb_planning_header,id',
            'itemno'             => 'required|string|max:255',
            'quantity'           => 'nullable|numeric|min:0',
            'lot'                => 'nullable|string|max:255',
            'weight'             => 'nullable|numeric|min:0',
            'machine_no'         => 'nullable|string|max:255',
            'plan_type'          => 'nullable|string|max:255',
            'planning_status'    => 'nullable|string|max:255',
            'inplan'         => 'nullable|date',
            'start_date'         => 'nullable|date',
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
            'planning_header_id', 'itemno', 'quantity', 'lot', 'weight',
            'machine_no', 'plan_type', 'planning_status', 'inplan','start_date',
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
