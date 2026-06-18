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

class ProductionPlanController extends Controller
{
    public function index()
    {
        $data = $this->dataQuery();

        return view('production-planning.planning.index');
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
            'planning_header' => $planning_header
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
                        'company'      => $r->company,
                        'mdate'        => !empty($r->order_date) ? $r->order_date : null,
                        'custwant'     => !empty($r->want_date) ? $r->want_date : null,
                        'custno'       => $r->custno,
                        'itemno'       => $r->itemno,
                        'quantity'     => $r->quantity,
                        'status'       => $r->status,
                        'status_label' => $r->statusLabel(),
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

        $html = view('production-planning.planning.planning-item-form', [
            'planning_item'      => $planning_item,
            'planning_header_id' => $planning_header_id,
            'parent_header'      => $parent_header,
            'semi_list'          => $semi_list,
            'pigment_list'       => $pigment_list,
            'parent_orderno'     => $parent_orderno,
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

        // semi / pigment — decode JSON string
        $semi_list    = json_decode($request->input('semi_json',    '[]'), true) ?? [];
        $pigment_list = json_decode($request->input('pigment_json', '[]'), true) ?? [];

        $fields['semi']    = json_encode($semi_list);
        $fields['pigment'] = json_encode($pigment_list);

        $planning_id = $request->planning_id;
        $is_update   = !empty($planning_id);

        DB::beginTransaction();
        try {
            // 1) บันทึก / อัปเดต planning หลัก
            if ($is_update) {
                Planning::where('id', $planning_id)->update($fields);
                $parent = Planning::with('planning_header')->find($planning_id);
            } else {
                $parent = Planning::create($fields);
                $parent->load('planning_header');
            }

            // 2) บันทึก Semi / Pigment ลงตารางรออนุมัติ (แทนการสร้าง planning อัตโนมัติ)
            $this->syncSemiPigment($parent, 'semi',    $semi_list);
            $this->syncSemiPigment($parent, 'pigment', $pigment_list);

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
     * ลบรายการ Semi/Pigment ที่ "รออนุมัติ" เดิมของ planning นี้ แล้วบันทึกใหม่
     * (รายการที่ "อนุมัติ" แล้วจะไม่ถูกแตะต้อง เพราะถูกแปลงเป็นแผนการผลิตไปแล้ว)
     */
    private function syncSemiPigment(Planning $parent, string $type, array $entries): void
    {
        SemiPigment::where('planning_id', $parent->id)
            ->where('type', $type)
            ->where('status', SemiPigment::STATUS_REQUEST)
            ->delete();

        $orderno = $parent->planning_header?->orderno;

        foreach ($entries as $entry) {
            if (empty($entry['itemno'])) continue;

            SemiPigment::create([
                'planning_id'        => $parent->id,
                'planning_header_id' => $parent->planning_header_id,
                'orderno'            => $orderno,
                'type'               => $type,
                'company'            => $entry['company']  ?? null,
                'order_date'         => !empty($entry['mdate'])    ? $entry['mdate']    : null,
                'want_date'          => !empty($entry['custwant']) ? $entry['custwant'] : null,
                'custno'             => $entry['custno']   ?? null,
                'itemno'             => $entry['itemno']   ?? null,
                'quantity'           => (isset($entry['quantity']) && $entry['quantity'] !== '') ? $entry['quantity'] : null,
                'status'             => SemiPigment::STATUS_REQUEST,
            ]);
        }
    }
}
