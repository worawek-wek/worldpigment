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
        return view('production-planning.order');
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
                $btn_view = '<button class="btn btn-sm btn-icon btn-info me-2 btn_view" data-member_id="'.$row->Orderno.'" title ="ลบ">
                    <i class="ti ti-eye text-white ti-sm"></i>
                </button>';
                $btn_edit = '<button class="btn btn-sm btn-icon btn-warning me-2 btn_edit" data-member_id="'.$row->Orderno.'" title ="แก้ไข">
                    <i class="ti ti-pencil text-white ti-sm"></i>
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
}
