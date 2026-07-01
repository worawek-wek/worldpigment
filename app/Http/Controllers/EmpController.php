<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Emp;
use App\Models\Department;
use App\Models\Role;

class EmpController extends Controller
{
    public function index()
    {
        return view('employee.index');
    }

    public function datatable(Request $request)
    {
        $employees = Emp::query()
            ->leftJoin('tb_departments', 'tb_departments.id', '=', 'emp.department_id')
            ->leftJoin('roles', 'roles.id', '=', 'emp.role_id')
            ->select([
                'emp.empno', 'emp.empname', 'emp.empsur', 'emp.supno',
                'emp.user', 'emp.department_id', 'emp.role_id', 'emp.is_active',
                'tb_departments.name as department_name',
                'roles.name as role_name',
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $employees->where(function ($query) use ($search) {
                $query->where('emp.empno', 'LIKE', '%'.$search.'%')
                    ->orWhere('emp.empname', 'LIKE', '%'.$search.'%')
                    ->orWhere('emp.empsur', 'LIKE', '%'.$search.'%')
                    ->orWhere('emp.supno', 'LIKE', '%'.$search.'%')
                    ->orWhere('emp.user', 'LIKE', '%'.$search.'%');
            });
        }

        $employees->orderBy('emp.empno', 'asc');

        $rownum = 0;

        return DataTables::of($employees)
            ->addColumn('rownum', function () use (&$rownum) {
                return ++$rownum;
            })
            ->addColumn('department_name', function ($emp) {
                return $emp->department_name ?? '-';
            })
            ->addColumn('role_name', function ($emp) {
                return $emp->role_name ?? '-';
            })
            ->addColumn('status_badge', function ($emp) {
                if ($emp->is_active === 'Y') {
                    return '<span class="badge bg-label-success">เปิดใช้งาน</span>';
                }
                return '<span class="badge bg-label-secondary">ปิดใช้งาน</span>';
            })
            ->addColumn('btnedit', function ($emp) {
                return '<button type="button" class="btn btn-sm btn-icon btn-warning btn_edit me-1" data-empno="'.e($emp->empno).'" title="แก้ไข">
                            <i class="ti ti-pencil ti-sm"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-icon btn-danger btn_delete" data-empno="'.e($emp->empno).'" title="ลบ">
                            <i class="ti ti-trash ti-sm"></i>
                        </button>';
            })
            ->rawColumns(['status_badge', 'btnedit'])
            ->make(true);
    }

    public function edit(Request $request)
    {
        $empno = $request->empno;

        $employee = $empno ? Emp::find($empno) : null;

        $departments = Department::where('is_active', 'Y')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        $roles = Role::where('is_active', 'Y')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        $html = view('employee.employee-form', [
            'employee'    => $employee,
            'departments' => $departments,
            'roles'       => $roles,
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html,
        ]);
    }

    public function store(Request $request)
    {
        // แก้ไข: มี edit_empno (คีย์เดิมที่ readonly ในฟอร์ม) / เพิ่ม: ไม่มี
        $is_edit = $request->filled('edit_empno');

        $rules = [
            'empname'       => 'required|string|max:10',
            'empsur'        => 'nullable|string|max:20',
            'supno'         => 'nullable|string|max:2',
            'password'      => 'nullable|string|min:4|max:255',
            'department_id' => 'nullable|integer|exists:tb_departments,id',
            'role_id'       => 'nullable|integer|exists:roles,id',
            'is_active'     => 'nullable|in:Y,N',
        ];

        // เพิ่มใหม่ต้องกรอกรหัสพนักงานและห้ามซ้ำ
        if (!$is_edit) {
            $rules['empno'] = 'required|string|max:4|unique:emp,empno';
        }

        // username ห้ามซ้ำ (เว้นว่างได้) — ตอนแก้ไขยกเว้นแถวตัวเอง
        if ($request->filled('user')) {
            $rules['user'] = [
                'string', 'max:50',
                Rule::unique('emp', 'user')->ignore($is_edit ? $request->edit_empno : null, 'empno'),
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors(),
            ]);
        }

        $fields = [
            'empname'       => $request->empname,
            'empsur'        => $request->empsur,
            'supno'         => $request->supno,
            'user'          => $request->user ?: null,
            'department_id' => $request->department_id ?: null,
            'role_id'       => $request->role_id ?: null,
            // switch ในฟอร์ม: ติ๊ก = ส่ง is_active (Y), ไม่ติ๊ก = ไม่ส่ง (N)
            'is_active'     => $request->has('is_active') ? 'Y' : 'N',
        ];

        // ตั้ง/เปลี่ยนรหัสผ่านเฉพาะเมื่อกรอกมา (เว้นว่าง = คงรหัสเดิม) — เก็บเป็น bcrypt hash
        if ($request->filled('password')) {
            $fields['password'] = Hash::make($request->password);
        }

        if ($is_edit) {
            $employee = Emp::find($request->edit_empno);
            if (!$employee) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'ไม่พบพนักงานที่ต้องการแก้ไข',
                ]);
            }
            $employee->update($fields);
        } else {
            $employee = Emp::create(array_merge(['empno' => $request->empno], $fields));
        }

        return response()->json([
            'status'  => 200,
            'message' => $is_edit ? 'แก้ไขข้อมูลพนักงานสำเร็จ' : 'เพิ่มพนักงานสำเร็จ',
            'data'    => $employee,
        ]);
    }

    public function destroy(Request $request)
    {
        $employee = Emp::find($request->empno);

        if (!$employee) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบพนักงานที่ต้องการลบ',
            ]);
        }

        $employee->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'ลบพนักงานสำเร็จ',
        ]);
    }
}
