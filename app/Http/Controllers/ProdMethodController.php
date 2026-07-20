<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ProdMethod;
use App\Models\Department;

class ProdMethodController extends Controller
{
    public function index()
    {
        $departments = Department::where('is_active', 'Y')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return view('prod-method.index', compact('departments'));
    }

    public function datatable()
    {
        $methods = ProdMethod::select(['id', 'name', 'dept', 'sort', 'is_active'])
            ->when(request('search'), function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('dept', 'like', "%{$search}%");
            })
            ->when(request('dept'), function ($q, $dept) {
                $q->where('dept', $dept);
            })
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc');

        $rownum = 0;

        return DataTables::of($methods)
            ->addColumn('rownum', function () use (&$rownum) {
                return ++$rownum;
            })
            ->addColumn('dept_label', function ($method) {
                return $method->dept ?: '-';
            })
            ->addColumn('status_switch', function ($method) {
                $checked = $method->is_active === 'Y' ? 'checked' : '';
                return '<div class="form-check form-switch d-flex justify-content-center mb-0">
                            <input class="form-check-input switch_status" type="checkbox" role="switch"
                                data-id="'.$method->id.'" '.$checked.'>
                        </div>';
            })
            ->addColumn('btnedit', function ($method) {
                return '<button type="button" class="btn btn-sm btn-icon btn-warning btn_edit" data-id="'.$method->id.'" title="แก้ไข">
                            <i class="ti ti-pencil ti-sm"></i>
                        </button>';
            })
            ->rawColumns(['status_switch', 'btnedit'])
            ->make(true);
    }

    public function edit()
    {
        $id = request('id');

        $method = $id ? ProdMethod::find($id) : null;

        $departments = Department::where('is_active', 'Y')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        $html = view('prod-method.prod-method-form', [
            'method'      => $method,
            'departments' => $departments,
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'dept' => 'nullable|string|max:50|exists:tb_departments,name',
            'sort' => 'nullable|integer',
        ], [
            'name.required' => 'กรุณากรอกชื่อวิธีการผลิต',
            'dept.exists'   => 'ไม่พบแผนกที่เลือก',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ]);
        }

        ProdMethod::updateOrCreate(
            ['id' => $request->id],
            [
                'name'      => $request->name,
                'dept'      => $request->dept ?: null,
                'sort'      => $request->sort ?: 0,
                // switch ในฟอร์ม: ติ๊ก = ส่ง is_active (Y), ไม่ติ๊ก = ไม่ส่ง (N)
                'is_active' => $request->has('is_active') ? 'Y' : 'N',
            ]
        );

        return response()->json([
            'status'  => 200,
            'message' => $request->id ? 'แก้ไขวิธีการผลิตสำเร็จ' : 'เพิ่มวิธีการผลิตสำเร็จ',
        ]);
    }

    // สลับสถานะเปิด-ปิดใช้งานจากหน้าตาราง
    public function toggleStatus(Request $request)
    {
        $method = ProdMethod::find($request->id);

        if (!$method) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบวิธีการผลิตที่ต้องการ',
            ]);
        }

        $method->is_active = $request->is_active === 'Y' ? 'Y' : 'N';
        $method->save();

        return response()->json([
            'status'    => 200,
            'message'   => $method->is_active === 'Y' ? 'เปิดใช้งานแล้ว' : 'ปิดใช้งานแล้ว',
            'is_active' => $method->is_active,
        ]);
    }
}
