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
    public function inde()
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
                $btn_edit = '<button class="btn btn-sm btn-icon btn-warning me-2 btn_edit" data-member_id="'.$row->Orderno.'" title ="แก้ไข">
                    <i class="ti ti-pencil text-white ti-sm"></i>
                </button>';
                $btn_delete = '<button class="btn btn-sm btn-icon btn-danger me-2 btn_delete" data-member_id="'.$row->Orderno.'" title ="ลบ">
                    <i class="ti ti-trash text-white ti-sm"></i>
                </button>';
                return $btn_edit.$btn_delete;
            })
            ->rawColumns(['btnedit']) // 👈 บอกให้ column นี้ render HTML
            ->make(true);
    }

    public function dataQuery()
    {
        $search = request('search');

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
            ->orderby('morder.Mdate', 'desc');

        // $data = $data->get();

        // foreach($data as $value){
        //     dd($value->suborders);
        // }

        return $data;
    }
}
