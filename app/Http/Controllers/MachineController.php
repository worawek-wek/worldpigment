<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Machine;
use App\Models\Department;

class MachineController extends Controller
{
    // รหัสแผนก (dept) ที่มีอยู่จริงในตาราง machine — ใช้เป็นตัวเลือก filter หน้า index
    private function deptCodes()
    {
        return Machine::whereNotNull('dept')
            ->where('dept', '!=', '')
            ->distinct()
            ->orderBy('dept', 'asc')
            ->pluck('dept');
    }

    // รายชื่อแผนกที่เปิดใช้งาน (ใช้เติม dropdown แผนกในฟอร์มเพิ่ม/แก้ไข)
    private function activeDepartments()
    {
        return Department::where('is_active', 'Y')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);
    }

    public function index()
    {
        return view('machine.index', [
            'dept_codes' => $this->deptCodes(),
        ]);
    }

    public function datatable()
    {
        $query = Machine::select(['id', 'dept', 'MBX', 'speed_rpm'])
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('MBX', 'like', "%{$search}%")
                        ->orWhere('dept', 'like', "%{$search}%");
                });
            })
            ->when(request('dept'), function ($q, $dept) {
                $q->where('dept', $dept);
            })
            ->orderBy('dept', 'asc')
            ->orderBy('MBX', 'asc');

        $rownum = 0;

        return DataTables::of($query)
            ->addColumn('rownum', function () use (&$rownum) {
                return ++$rownum;
            })
            // ตัดข้อความที่ยาวในตาราง (แสดงย่อ + tooltip ค่าเต็ม)
            ->editColumn('speed_rpm', function ($machine) {
                $full = $machine->speed_rpm;
                if ($full === null || $full === '') {
                    return '<span class="text-muted">-</span>';
                }
                $short = Str::limit($full, 40);
                return '<span title="'.e($full).'">'.e($short).'</span>';
            })
            ->addColumn('btnedit', function ($machine) {
                return '<button type="button" class="btn btn-sm btn-icon btn-warning btn_edit me-1" data-id="'.$machine->id.'" title="แก้ไข">
                            <i class="ti ti-pencil ti-sm"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-icon btn-danger btn_delete" data-id="'.$machine->id.'" title="ลบ">
                            <i class="ti ti-trash ti-sm"></i>
                        </button>';
            })
            ->rawColumns(['speed_rpm', 'btnedit'])
            ->make(true);
    }

    public function edit()
    {
        $id = request('id');

        $machine = $id ? Machine::find($id) : null;

        $html = view('machine.machine-form', [
            'machine'     => $machine,
            'departments' => $this->activeDepartments(),
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dept'      => ['required', 'string', 'max:50', 'exists:tb_departments,name'],
            'MBX'       => ['required', 'string', 'max:20'],
            'speed_rpm' => ['nullable', 'string', 'max:255'],
        ], [
            'dept.required' => 'กรุณาเลือกแผนก',
            'dept.exists'   => 'ไม่พบแผนกที่เลือก',
            'MBX.required'  => 'กรุณากรอกชื่อ/รหัสเครื่องจักร',
            'MBX.max'       => 'ชื่อ/รหัสเครื่องจักรต้องไม่เกิน 20 ตัวอักษร',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ]);
        }

        // กันเครื่องจักรซ้ำ (แผนก + MBX เดียวกัน) — ยกเว้นแถวที่กำลังแก้ไขเอง
        $duplicate = Machine::where('dept', $request->dept)
            ->where('MBX', $request->MBX)
            ->when($request->id, function ($q, $id) {
                $q->where('id', '!=', $id);
            })
            ->exists();

        if ($duplicate) {
            return response()->json([
                'status'  => 422,
                'message' => 'มีเครื่องจักรนี้ในแผนกนี้อยู่แล้ว',
            ]);
        }

        Machine::updateOrCreate(
            ['id' => $request->id],
            [
                'dept'      => $request->dept,
                'MBX'       => $request->MBX,
                'speed_rpm' => $request->speed_rpm,
            ]
        );

        return response()->json([
            'status'  => 200,
            'message' => $request->id ? 'แก้ไขเครื่องจักรสำเร็จ' : 'เพิ่มเครื่องจักรสำเร็จ',
        ]);
    }

    public function destroy(Request $request)
    {
        $machine = Machine::find($request->id);

        if (!$machine) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบเครื่องจักรที่ต้องการลบ',
            ]);
        }

        $machine->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'ลบเครื่องจักรแล้ว',
        ]);
    }
}
