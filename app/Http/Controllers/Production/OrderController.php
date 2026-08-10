<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Morder;
use App\Models\PlanningHeader;
use App\Models\Planning;
use App\Models\Department;

class OrderController extends Controller
{
    public function index()
    {
        $departments = Department::where('is_active', 'Y')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return view('production-planning.order.index', [
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
            ->editColumn('Mdate', fn ($row) => $row->Mdate ? \Carbon\Carbon::parse($row->Mdate)->format('d/m/Y') : '-')
            // รหัสสินค้า (Itemno) ของ suborder ทั้งหมดในออเดอร์ — ตัดค่าซ้ำ/ว่างทิ้ง แล้วคั่นด้วย ", "
            ->addColumn('itemno_list', function($row) {
                $items = $row->suborders
                    ->pluck('Itemno')
                    ->map(fn ($v) => trim((string) $v))
                    ->filter()
                    ->unique()
                    ->values();

                return $items->isEmpty() ? '-' : $items->implode(', ');
            })
            ->addColumn('plan_badge', function($row) {
                if ($row->has_plan) {
                    return '<span class="badge bg-label-success">สร้างแล้ว</span>';
                }
                return '<span class="badge bg-label-secondary">ยังไม่สร้าง</span>';
            })
            ->addColumn('btnedit', function($row) {
                $btn_view = '<button class="btn btn-sm btn-icon btn-label-primary me-2 btn_view" data-orderno="'.$row->Orderno.'" title="ดูรายละเอียด">
                    <i class="ti ti-eye ti-sm"></i>
                </button>';

                if ($row->has_plan) {
                    $btn_edit = '<button class="btn btn-sm btn-icon btn-label-secondary btn_edit" data-orderno="'.$row->Orderno.'" title="สร้างแผนการผลิตแล้ว" disabled>
                        <i class="ti ti-checks ti-sm"></i>
                    </button>';
                } else {
                    $btn_edit = '<button class="btn btn-sm btn-icon btn-label-warning btn_edit" data-orderno="'.$row->Orderno.'" title="สร้างแผนการผลิต">
                        <i class="ti ti-download ti-sm"></i>
                    </button>';
                }

                return $btn_view.$btn_edit;
            })
            ->rawColumns(['plan_badge', 'btnedit']) // 👈 บอกให้ column นี้ render HTML
            ->make(true);
    }

    public function dataQuery()
    {
        $search = request('search');
        $company = request('company');

        $data = Morder::select([
                'morder.*',
                DB::raw('ROW_NUMBER() OVER (ORDER BY morder.Mdate DESC) AS rownum'),
                DB::raw("EXISTS(
                    SELECT 1 FROM tb_planning_header
                    WHERE tb_planning_header.orderno = morder.Orderno
                      AND tb_planning_header.plan_type = 'ORDER'
                ) AS has_plan")
            ])
            // โหลด suborder ล่วงหน้า (เฉพาะคีย์ + Itemno) เพื่อรวมรหัสสินค้าโดยไม่เกิด N+1
            ->with(['suborders' => function ($query) {
                $query->select('Orderno', 'Itemno');
            }])
            ->where('morder.appv', '-1') // เฉพาะ order ที่อนุมัติแล้ว
            // มี suborder อย่างน้อย 1 ตัวที่ EndP เป็นค่าว่าง (ใช้ EXISTS กัน row ซ้ำกรณี suborder มีหลายตัว)
            ->whereHas('suborders', function ($query) {
                $query->whereNull('EndP');
            })
            ->when(!empty($search), function ($query) use ($search) {

                $query->where(function ($query) use ($search) {
                    $query->where('morder.Orderno', 'LIKE', '%'.$search.'%')
                        ->orWhere('morder.Custno', 'LIKE', '%'.$search.'%')
                        ->orWhere('morder.Custname', 'LIKE', '%'.$search.'%');
                });
            })
            ->when(!empty($company), function ($query) use ($company) {
                $query->where('morder.Company', 'LIKE', '%'.$company.'%');
            })
            ->orderby('morder.Mdate', 'desc');

        return $data;
    }

    public function detail()
    {
        $orderno =  request('orderno');

        $order = Morder::where('Orderno', $orderno)->first();

        $html = view('production-planning.order.order-detail',compact('order'))->render();

        return response()->json([
            'status' => 200,
            'data' => $html
        ]);
    }

    public function convertplanning()
    {
        $orderno =  request('orderno');

        $order = Morder::where('Orderno', $orderno)->first();
        $check_plan = PlanningHeader::where('orderno', $orderno)
            ->where('plan_type', 'ORDER')
            ->first();

        if(!is_null($check_plan)){
            return response()->json([
                'status' => 500,
                'message' => 'มีการสร้าง Order นี้แล้ว'
            ]);
        }

        // ดึง custwant ของ suborder ทุกตัว (ตัดค่าว่างทิ้ง) เพื่อหาวันที่น้อยสุด/หลังสุด
        $custwants = $order->suborders
            ->pluck('custwant')
            ->filter(fn ($date) => !empty($date))
            ->values();

        // หมายเหตุ header: รวม Remark ของ suborder ทุกตัว (ตัดค่าว่าง แต่เก็บทุกหมายเหตุแม้ซ้ำกัน) คั่นด้วย ", " เมื่อมีมากกว่า 1 รายการ
        $header_remark = $order->suborders
            ->pluck('Remark')
            ->map(fn ($r) => trim((string) $r))
            ->filter()
            ->implode(', ');

        $data_planning_header = [
            'planning_code' => $order->Orderno,
            'mdate' => $order->Mdate,
            'company' => $order->Company,
            'orderno' => $order->Orderno,
            'custno' => $order->Custno,
            'saleno' => $order->supno,
            'netqty' => $order->netqty,
            'custwant' => $custwants->max() ?: null, // วันที่หลังสุดจาก suborder.custwant
            'remark' => $header_remark ?: null,       // หมายเหตุจาก suborder (คั่นด้วย , ถ้ามีหลายรายการ)
            'plan_type' => 'ORDER',
        ];

        $planning_header = PlanningHeader::create($data_planning_header);

        foreach($order->suborders as $suborder){
            $planning = [
                'planning_header_id' => $planning_header->id,
                'itemno'             => $suborder->Itemno,
                'plan_type'          => 'ORDER',
                'mdate'              => $order->Mdate ?: null,
                'custwant'           => $suborder->custwant ?: null,
                'remark'             => $suborder->Remark ?: null,
                'red_bill_code'      => $order->Orderno, // เลขที่ Order → ใส่เป็นเลขที่ใบเบิก
                'quantity'           => $order->netqty, // น้ำหนักสั่งตาม Order
            ];

            Planning::create($planning);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Success'
        ]);
    }
}
