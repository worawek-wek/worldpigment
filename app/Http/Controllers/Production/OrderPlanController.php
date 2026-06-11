<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Models\PlanningHeader;

class OrderPlanController extends Controller
{
    public function index()
    {
        return view('production-planning.order-plan.index');
    }

    public function datatable()
    {
        $data = $this->dataQuery();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('rownum', function ($row) {
                return $row->rownum;
            })
            ->addColumn('item_count', function ($row) {
                return $row->plannings_count;
            })
            ->addColumn('btnedit', function ($row) {
                return '<button class="btn btn-sm btn-icon btn-label-primary btn_view" data-planning_header_id="'.$row->id.'" title="ดูรายละเอียด">
                    <i class="ti ti-eye ti-sm"></i>
                </button>';
            })
            ->rawColumns(['btnedit'])
            ->make(true);
    }

    public function dataQuery()
    {
        $search  = request('search');
        $company = request('company');

        // แผนการผลิตหลัก (Order) = header ที่ parent_planning_id ว่าง
        // ไม่ดึง header ที่ระบบสร้างภายใน (semi/pigment sub-headers ที่มี parent_planning_id)
        $data = PlanningHeader::query()
            ->whereNull('parent_planning_id')
            ->withCount('plannings')
            ->select([
                'tb_planning_header.*',
                DB::raw('ROW_NUMBER() OVER (ORDER BY tb_planning_header.id DESC) AS rownum'),
            ])
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('tb_planning_header.planning_code', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning_header.orderno', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning_header.custno', 'LIKE', '%'.$search.'%');
                });
            })
            ->when(!empty($company), function ($query) use ($company) {
                $query->where('tb_planning_header.company', $company);
            })
            ->orderby('tb_planning_header.id', 'desc');

        return $data;
    }

    public function detail()
    {
        $planning_header_id = request('planning_header_id');

        $planning_header = PlanningHeader::with([
            'plannings.semi_headers.plannings',
            'plannings.pigment_headers.plannings',
        ])->find($planning_header_id);

        $html = view('production-planning.order-plan.detail', compact('planning_header'))->render();

        return response()->json([
            'status' => 200,
            'data'   => $html,
        ]);
    }
}
