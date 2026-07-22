<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Models\PlanningHeader;
use App\Models\Department;

class OrderPlanController extends Controller
{
    public function index()
    {
        $departments = Department::where('is_active', 'Y')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return view('production-planning.order-plan.index', [
            'departments' => $departments,
        ]);
    }

    public function datatable()
    {
        $data = $this->dataQuery();

        return DataTables::of($data)
            ->addIndexColumn()
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
                $text = empty($statuses) ? '-' : implode(', ', array_map(fn ($s) => e($s), $statuses));

                // บรรทัดที่ 2: สถานะปิดงานของ Order (อ้างอิงคอลัมน์ end_order ของ tb_planning_header)
                $end_order_badge = ($row->end_order ?? 'N') === 'Y'
                    ? '<span class="badge bg-label-success">ปิดงาน</span>'
                    : '<span class="badge bg-label-warning">ยังไม่ปิดงาน</span>';

                return $text.'<br>'.$end_order_badge;
            })
            ->editColumn('custwant', fn ($row) => $row->custwant ? \Carbon\Carbon::parse($row->custwant)->format('d/m/Y') : '-')
            ->addColumn('btnedit', function ($row) {
                return '<button class="btn btn-sm btn-icon btn-label-primary btn_view" data-planning_header_id="'.$row->id.'" title="ดูรายละเอียด">
                    <i class="ti ti-eye ti-sm"></i>
                </button>';
            })
            // mapping การเรียงลำดับสำหรับคอลัมน์ที่มาจาก subquery/aggregate
            ->orderColumn('inplan', $this->maxInplanSql() . ' $1')
            ->orderColumn('custname', $this->custnameSql() . ' $1')
            ->orderColumn('item_count', $this->itemCountSql() . ' $1')
            ->rawColumns(['status_list', 'btnedit'])
            ->make(true);
    }

    // ── subquery ที่ใช้ทั้งใน select / filter / order ──
    private function maxInplanSql(): string
    {
        return '(SELECT MAX(p.inplan) FROM tb_planning p WHERE p.planning_header_id = tb_planning_header.id)';
    }

    private function custnameSql(): string
    {
        return '(SELECT m.Custname FROM morder m WHERE m.Orderno = tb_planning_header.orderno LIMIT 1)';
    }

    private function itemCountSql(): string
    {
        return '(SELECT COUNT(*) FROM tb_planning p WHERE p.planning_header_id = tb_planning_header.id)';
    }

    public function dataQuery()
    {
        $search         = request('search');
        $company        = request('company');
        $inplanStart    = request('inplan_start');
        $inplanEnd      = request('inplan_end');
        $custwantStart  = request('custwant_start');
        $custwantEnd    = request('custwant_end');

        // สถานะปิดงาน (end_order): 'all' = ทั้งหมด (ไม่กรอง), 'Y' = ปิดงาน, 'N' = ยังไม่ปิดงาน
        // ค่าอื่น/ไม่ได้ส่งมา = ใช้ค่าเริ่มต้น 'N' (ยังไม่ปิดงาน)
        $endOrder = request('end_order', 'N');
        $endOrder = in_array($endOrder, ['all', 'Y', 'N'], true) ? $endOrder : 'N';

        $maxInplan = $this->maxInplanSql();

        // แผนการผลิตหลัก (Order) = header ที่ parent_planning_id ว่าง
        // ไม่ดึง header ที่ระบบสร้างภายใน (semi/pigment sub-headers ที่มี parent_planning_id)
        $data = PlanningHeader::query()
            ->whereNull('parent_planning_id')
            // หมายเหตุ: ต้อง select() ก่อน withCount/withMax ไม่งั้น select จะลบคอลัมน์ subquery ที่ withCount/withMax เพิ่มไว้
            ->select([
                'tb_planning_header.*',
                // ชื่อลูกค้า ดึงจากตาราง Order (morder) ตาม orderno
                DB::raw($this->custnameSql() . ' AS custname'),
            ])
            ->withCount('plannings')
            ->withMax('plannings', 'inplan')
            ->with('plannings.subHeadersRecursive')
            // ค้นหา: รหัส Order / รหัสลูกค้า / ชื่อลูกค้า (ชื่อลูกค้าอยู่ตาราง morder)
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('tb_planning_header.orderno', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning_header.custno', 'LIKE', '%'.$search.'%')
                        ->orWhereExists(function ($sub) use ($search) {
                            $sub->select(DB::raw(1))->from('morder')
                                ->whereColumn('morder.Orderno', 'tb_planning_header.orderno')
                                ->where('morder.Custname', 'LIKE', '%'.$search.'%');
                        });
                });
            })
            ->when(!empty($company), function ($query) use ($company) {
                $query->where('tb_planning_header.company', $company);
            })
            // กรองด้วยสถานะปิดงาน — 'ยังไม่ปิดงาน' นับรวมค่า NULL/ว่าง ด้วย
            ->when($endOrder === 'Y', function ($query) {
                $query->where('tb_planning_header.end_order', 'Y');
            })
            ->when($endOrder === 'N', function ($query) {
                $query->where(function ($query) {
                    $query->where('tb_planning_header.end_order', '!=', 'Y')
                        ->orWhereNull('tb_planning_header.end_order');
                });
            })
            // ค้นหาช่วงวันที่ Inplan (เทียบกับ inplan ล่าสุด/มากสุดของแผน)
            ->when(!empty($inplanStart), fn ($q) => $q->whereRaw("$maxInplan >= ?", [$inplanStart]))
            ->when(!empty($inplanEnd),   fn ($q) => $q->whereRaw("$maxInplan <= ?", [$inplanEnd]))
            // ค้นหาช่วงวันที่ Custwant
            ->when(!empty($custwantStart), fn ($q) => $q->whereDate('tb_planning_header.custwant', '>=', $custwantStart))
            ->when(!empty($custwantEnd),   fn ($q) => $q->whereDate('tb_planning_header.custwant', '<=', $custwantEnd));

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
