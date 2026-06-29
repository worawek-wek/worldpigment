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
            // Inplan = วันที่ inplan ล่าสุด (มากสุด) ในบรรดาแผน (planning) ของ Order นี้
            ->editColumn('custname', fn ($row) => $row->custname ?: '-')
            ->addColumn('inplan', fn ($row) => $row->plannings_max_inplan ? \Carbon\Carbon::parse($row->plannings_max_inplan)->format('d/m/Y') : '-')
            // สถานะ = รวมสถานะของทุกรายการ (plan + sub plan ทุกชั้น) คั่นด้วย ","
            ->addColumn('status_list', function ($row) {
                $statuses = [];
                $this->collectStatuses($row->plannings, $statuses);
                $statuses = array_values(array_filter($statuses, fn ($s) => $s !== null && $s !== ''));
                return empty($statuses) ? '-' : implode(', ', $statuses);
            })
            ->editColumn('custwant', fn ($row) => $row->custwant ? \Carbon\Carbon::parse($row->custwant)->format('d/m/Y') : '-')
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
            // หมายเหตุ: ต้อง select() ก่อน withCount/withMax ไม่งั้น select จะลบคอลัมน์ subquery ที่ withCount/withMax เพิ่มไว้
            ->select([
                'tb_planning_header.*',
                DB::raw('ROW_NUMBER() OVER (ORDER BY tb_planning_header.id DESC) AS rownum'),
                // ชื่อลูกค้า ดึงจากตาราง Order (morder) ตาม orderno
                DB::raw('(SELECT m.Custname FROM morder m WHERE m.Orderno = tb_planning_header.orderno LIMIT 1) AS custname'),
            ])
            ->withCount('plannings')
            ->withMax('plannings', 'inplan')
            ->with('plannings.subHeadersRecursive')
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

    /**
     * รวบรวมสถานะ (planning_status) ของรายการ plan และ sub plan ทุกชั้นแบบ recursive
     */
    private function collectStatuses($plannings, array &$statuses): void
    {
        foreach ($plannings as $p) {
            $statuses[] = $p->planning_status;
            foreach ($p->subHeadersRecursive as $header) {
                $this->collectStatuses($header->planningsRecursive, $statuses);
            }
        }
    }

    public function detail()
    {
        $planning_header_id = request('planning_header_id');

        $planning_header = PlanningHeader::with([
            'plannings.subHeadersRecursive',
        ])->find($planning_header_id);

        $html = view('production-planning.order-plan.detail', compact('planning_header'))->render();

        return response()->json([
            'status' => 200,
            'data'   => $html,
        ]);
    }
}
