<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        return view('department.index');
    }

    public function datatable()
    {
        $departments = Department::select(['id', 'name', 'description']);

        $rownum = 0;

        return DataTables::of($departments)
            ->addColumn('rownum', function () use (&$rownum) {
                return ++$rownum;
            })
            ->addColumn('btnedit', function ($department) {
                return '<button type="button" class="btn btn-sm btn-icon btn-warning btn_edit" data-id="'.$department->id.'" title="แก้ไข">
                            <i class="ti ti-pencil ti-sm"></i>
                        </button>';
            })
            ->rawColumns(['btnedit'])
            ->make(true);
    }

    public function edit()
    {
        $id = request('id');

        $department = $id ? Department::find($id) : null;

        $html = view('department.department-form', [
            'department' => $department,
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        $department = Department::updateOrCreate(
            ['id' => $request->id],
            [
                'name' => $request->name,
                'description' => $request->description,
            ]
        );

        return response()->json([
            'status' => 200,
            'message' => 'Department saved successfully!',
            'data' => $department
        ]);
    }
}
