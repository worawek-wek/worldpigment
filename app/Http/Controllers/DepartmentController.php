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
        $departments = Department::select(['id', 'name', 'description', 'is_active']);

        $rownum = 0;

        return DataTables::of($departments)
            ->addColumn('rownum', function () use (&$rownum) {
                return ++$rownum;
            })
            ->addColumn('status_switch', function ($department) {
                $checked = $department->is_active === 'Y' ? 'checked' : '';
                return '<div class="form-check form-switch d-flex justify-content-center mb-0">
                            <input class="form-check-input switch_status" type="checkbox" role="switch"
                                data-id="'.$department->id.'" '.$checked.'>
                        </div>';
            })
            ->addColumn('btnedit', function ($department) {
                return '<button type="button" class="btn btn-sm btn-icon btn-warning btn_edit" data-id="'.$department->id.'" title="แก้ไข">
                            <i class="ti ti-pencil ti-sm"></i>
                        </button>';
            })
            ->rawColumns(['status_switch', 'btnedit'])
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
                // switch ในฟอร์ม: ติ๊ก = ส่ง is_active มา (Y), ไม่ติ๊ก = ไม่ส่ง (N)
                'is_active' => $request->has('is_active') ? 'Y' : 'N',
            ]
        );

        return response()->json([
            'status' => 200,
            'message' => 'Department saved successfully!',
            'data' => $department
        ]);
    }

    // สลับสถานะเปิด-ปิดใช้งานจากหน้าตาราง
    public function toggleStatus(Request $request)
    {
        $department = Department::find($request->id);

        if (!$department) {
            return response()->json([
                'status' => 404,
                'message' => 'ไม่พบแผนกที่ต้องการ'
            ]);
        }

        $department->is_active = $request->is_active === 'Y' ? 'Y' : 'N';
        $department->save();

        return response()->json([
            'status'    => 200,
            'message'   => $department->is_active === 'Y' ? 'เปิดใช้งานแผนกแล้ว' : 'ปิดใช้งานแผนกแล้ว',
            'is_active' => $department->is_active,
        ]);
    }
}
