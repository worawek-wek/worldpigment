<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\PlanningStatus;

class PlanningStatusController extends Controller
{
    public function index()
    {
        return view('production-planning.planning-status.index');
    }

    public function datatable()
    {
        $data = $this->dataQuery();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('rownum', function($row) {
                return $row->rownum;
            })
            ->addColumn('is_active_label', function($row) {
                if ($row->is_active === 'Y') {
                    return '<span class="badge bg-label-success">เปิดใช้งาน</span>';
                }
                return '<span class="badge bg-label-secondary">ปิดใช้งาน</span>';
            })
            ->addColumn('btnedit', function($row) {
                return '<button class="btn btn-sm btn-icon btn-warning me-2 btn_edit" data-id="'.$row->id.'" title="แก้ไข">
                    <i class="ti ti-pencil text-white ti-sm"></i>
                </button>';
            })
            ->rawColumns(['is_active_label', 'btnedit'])
            ->make(true);
    }

    public function dataQuery()
    {
        $search = request('search');
        $dept   = request('dept');

        $data = PlanningStatus::query()
            ->select([
                'tb_planning_status.*',
                DB::raw('ROW_NUMBER() OVER (ORDER BY tb_planning_status.sort ASC, tb_planning_status.id ASC) AS rownum')
            ])
            ->when(!empty($search), function ($query) use ($search) {
                $query->where('tb_planning_status.name', 'LIKE', '%'.$search.'%');
            })
            ->when(!empty($dept), function ($query) use ($dept) {
                $query->where('tb_planning_status.dept', $dept);
            })
            ->orderby('tb_planning_status.sort', 'asc')
            ->orderby('tb_planning_status.id', 'asc');

        return $data;
    }

    public function edit()
    {
        $id = request('id');

        $planning_status = $id ? PlanningStatus::find($id) : null;

        $html = view('production-planning.planning-status.form', [
            'planning_status' => $planning_status,
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html
        ]);
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'dept'      => 'nullable|string|max:255',
            'sort'      => 'nullable|integer|min:0',
            'is_active' => 'nullable|in:Y,N',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ]);
        }

        $fields = [
            'name'      => $request->name,
            'dept'      => $request->dept,
            'sort'      => $request->sort ?: 0,
            'is_active' => $request->is_active ?: 'N',
        ];

        $id        = $request->id;
        $is_update = !empty($id);

        try {
            if ($is_update) {
                PlanningStatus::where('id', $id)->update($fields);
            } else {
                PlanningStatus::create($fields);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }

        return response()->json([
            'status'  => 200,
            'message' => $is_update ? 'แก้ไขสถานะ Planning สำเร็จ' : 'เพิ่มสถานะ Planning สำเร็จ'
        ]);
    }
}
