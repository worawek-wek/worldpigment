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

class OrderController extends Controller
{
    public function index()
    {
        return view('production-planning.order.index');
    }

    public function datatable()
    {
        $data = $this->dataQuery();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('rownum', function($row) {
                return $row->rownum;
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

        $data_planning_header = [
            'planning_code' => $order->Orderno,
            'mdate' => $order->Mdate,
            'company' => $order->Company,
            'orderno' => $order->Orderno,
            'custno' => $order->Custno,
            'saleno' => $order->Emp,
            'netqty' => $order->netqty,
            'plan_type' => 'ORDER',
        ];

        $planning_header = PlanningHeader::create($data_planning_header);

        foreach($order->suborders as $suborder){
            $planning = [
                'planning_header_id' => $planning_header->id,
                'itemno'             => $suborder->Itemno,
                'plan_type'          => 'ORDER',
                'mdate'              => $order->Mdate    ?: null,
                'custwant'           => $suborder->custwant ?: null,
                'remark'             => $suborder->Remark ?: null,
            ];

            Planning::create($planning);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Success'
        ]);
    }
}
