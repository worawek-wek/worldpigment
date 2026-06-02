<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\PlanningHeader;
use App\Models\Planning;

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
                'tb_planning_header.mdate as header_mdate',
                DB::raw('ROW_NUMBER() OVER (ORDER BY tb_planning.id DESC) AS rownum')
            ])
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
            $planning_item = Planning::with([
                'semi_headers.plannings',
                'pigment_headers.plannings',
                'planning_header',
            ])->find($planning_id);

            if ($planning_item) {
                $parent_header  = $planning_item->planning_header;
                $parent_orderno = $parent_header?->orderno;

                $semi_list = $planning_item->semi_headers->map(function ($header) {
                    $p = $header->plannings->first();
                    return [
                        'company'  => $header->company,
                        'mdate'    => $header->mdate,
                        'custwant' => $header->custwant,
                        'senddate' => $header->senddate,
                        'custno'   => $header->custno,
                        'itemno'   => $p?->itemno,
                        'quantity' => $p?->quantity,
                    ];
                })->values()->toArray();

                $pigment_list = $planning_item->pigment_headers->map(function ($header) {
                    $p = $header->plannings->first();
                    return [
                        'company'  => $header->company,
                        'mdate'    => $header->mdate,
                        'custwant' => $header->custwant,
                        'senddate' => $header->senddate,
                        'custno'   => $header->custno,
                        'itemno'   => $p?->itemno,
                        'quantity' => $p?->quantity,
                    ];
                })->values()->toArray();
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
            'machine_no', 'plan_type', 'planning_status', 'start_date',
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

            $parent_orderno = $parent->planning_header?->orderno;

            // 2) sync sub-orders (ลบเดิม → สร้างใหม่)
            $this->syncSubOrders($parent, 'semi',    $semi_list,    $parent_orderno);
            $this->syncSubOrders($parent, 'pigment', $pigment_list, $parent_orderno);

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
     * ลบ sub-order headers (และ plannings ของมัน) เดิมทั้งหมด
     * แล้วสร้างใหม่ตาม $entries
     */
    private function syncSubOrders(Planning $parent, string $type, array $entries, ?string $parent_orderno): void
    {
        // ลบ plannings ที่อยู่ใต้ sub-headers เดิมก่อน
        $old_header_ids = PlanningHeader::where('parent_planning_id', $parent->id)
            ->where('plan_type', $type)
            ->pluck('id');

        if ($old_header_ids->isNotEmpty()) {
            Planning::whereIn('planning_header_id', $old_header_ids)->delete();
            PlanningHeader::whereIn('id', $old_header_ids)->delete();
        }

        // สร้างใหม่
        foreach ($entries as $i => $entry) {
            if (empty($entry['itemno'])) continue;

            $sub_header = PlanningHeader::create([
                'planning_code'      => $parent_orderno . '-' .strtoupper($type) . '-' . ($i + 1),
                'plan_type'          => $type,
                'parent_planning_id' => $parent->id,
                'company'            => $entry['company']   ?? null,
                'mdate'              => !empty($entry['mdate']) ? $entry['mdate'] : null,
                'custwant'           => !empty($entry['custwant']) ? $entry['custwant'] : null,
                'senddate'           => !empty($entry['senddate']) ? $entry['senddate'] : null,
                'custno'             => $entry['custno']    ?? null,
                'orderno'            => $parent_orderno,
            ]);

            Planning::create([
                'planning_header_id' => $sub_header->id,
                'parent_planning_id' => $parent->id,
                'plan_type'          => $type,
                'itemno'             => $entry['itemno']   ?? null,
                'quantity'           => $entry['quantity'] ?? null,
                'mdate'              => !empty($entry['mdate']) ? $entry['mdate'] : null,
                'custwant'           => !empty($entry['custwant']) ? $entry['custwant'] : null,
                'senddate'           => !empty($entry['senddate']) ? $entry['senddate'] : null,
            ]);
        }
    }
}
