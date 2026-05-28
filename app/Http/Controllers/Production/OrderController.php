<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Morder;

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
            ->addColumn('btnedit', function($row) {
                $btn_view = '<button class="btn btn-sm btn-icon btn-label-primary me-2 btn_view" data-orderno="'.$row->Orderno.'" title ="ลบ">
                    <i class="ti ti-eye ti-sm"></i>
                </button>';
                $btn_edit = '<button class="btn btn-sm btn-icon btn-label-warning btn_edit" data-orderno="'.$row->Orderno.'" title ="แก้ไข">
                    <i class="ti ti-pencil ti-sm"></i>
                </button>';

                return $btn_view.$btn_edit;
            })
            ->rawColumns(['btnedit']) // 👈 บอกให้ column นี้ render HTML
            ->make(true);
    }

    public function dataQuery()
    {
        $search = request('search');
        $company = request('company');

        $data = Morder::select([
                'morder.*',
                DB::raw('ROW_NUMBER() OVER (ORDER BY morder.Mdate DESC) AS rownum')
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
}
