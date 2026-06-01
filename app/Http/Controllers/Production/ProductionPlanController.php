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

                return $btn_view.$btn_edit;
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
                'tb_planning_header.company',
                'tb_planning_header.orderno',
                'tb_planning_header.mdate',
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

        $planning = Planning::where('id', $planning_id)->first();

        $other_plannings = null;
        if($planning){
            $other_plannings = $planning->planning_header
                ->plannings()
                ->where('id', '!=', $planning_id)
                ->get();
        }

        $data = [
            'planning' => $planning,
            'other_plannings' => $other_plannings
        ];

        $html = view('production-planning.planning.planning-form', $data)->render();

        return response()->json([
            'status' => 200,
            'data' => $html
        ]);
    }
}
