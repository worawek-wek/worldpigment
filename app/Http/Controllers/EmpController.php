<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Emp;
use App\Models\Department;
use App\Models\Role;

class EmpController extends Controller
{
    public function index()
    {
        // แผนกสำหรับ dropdown ค้นหา (เฉพาะที่เปิดใช้งาน)
        $departments = Department::where('is_active', 'Y')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return view('employee.index', [
            'departments' => $departments,
        ]);
    }

    public function datatable(Request $request)
    {
        $employees = Emp::query()
            ->leftJoin('roles', 'roles.id', '=', 'emp.role_id')
            ->select([
                'emp.empno', 'emp.empname', 'emp.empsur', 'emp.supno',
                'emp.user', 'emp.dept', 'emp.role_id', 'emp.is_active',
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

        // กรองตามแผนก (emp.dept เก็บเป็นชื่อแผนก — ตรงกับ value ของ dropdown)
        if ($request->filled('dept')) {
            $employees->where('emp.dept', $request->dept);
        }

        // หมายเหตุ: ไม่ hard-code orderBy ที่นี่แล้ว — ให้ Yajra จัดการเรียงตามที่คลิกหัวคอลัมน์
        // (ลำดับเริ่มต้นตาม empno ถูกกำหนดเป็นค่า default order ในฝั่ง DataTables ของ employee/index)

        $rownum = 0;

        return DataTables::of($employees)
            ->addColumn('rownum', function () use (&$rownum) {
                return ++$rownum;
            })
            ->addColumn('department_name', function ($emp) {
                return $emp->dept ?: '-';
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

        // role ค่าเริ่มต้นสำหรับ "เพิ่มพนักงานใหม่" เท่านั้น (ตอนแก้ไขยึด role เดิมของพนักงาน)
        $defaultRoleId = $employee ? null : Role::defaultRoleId();

        $html = view('employee.employee-form', [
            'employee'      => $employee,
            'departments'   => $departments,
            'roles'         => $roles,
            'defaultRoleId' => $defaultRoleId,
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
            'dept'          => 'nullable|string|exists:tb_departments,name',
            'role_id'       => 'nullable|integer|exists:roles,id',
            'is_active'     => 'nullable|in:Y,N',
            'signature'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];

        $messages = [
            'signature.image' => 'ไฟล์ลายเซ็นต้องเป็นรูปภาพเท่านั้น',
            'signature.mimes' => 'ลายเซ็นต้องเป็นไฟล์ประเภท: jpeg, png, jpg, gif หรือ webp',
            'signature.max'   => 'ขนาดไฟล์ลายเซ็นต้องไม่เกิน 2MB',
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

        $validator = Validator::make($request->all(), $rules, $messages);

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
            'dept'          => $request->dept ?: null,
            'role_id'       => $request->role_id ?: null,
            // switch ในฟอร์ม: ติ๊ก = ส่ง is_active (Y), ไม่ติ๊ก = ไม่ส่ง (N)
            'is_active'     => $request->has('is_active') ? 'Y' : 'N',
        ];

        // ตั้ง/เปลี่ยนรหัสผ่านเฉพาะเมื่อกรอกมา (เว้นว่าง = คงรหัสเดิม) — เก็บเป็น bcrypt hash
        if ($request->filled('password')) {
            $fields['password'] = Hash::make($request->password);
        }

        // โหลดข้อมูลเดิม (กรณีแก้ไข) เพื่ออ้างชื่อไฟล์ลายเซ็นเก่าไว้จัดการต่อ
        $existing = $is_edit ? Emp::find($request->edit_empno) : null;
        if ($is_edit && !$existing) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบพนักงานที่ต้องการแก้ไข',
            ]);
        }
        $oldSignature = $existing?->signature;

        // กดปุ่มลบรูปลายเซ็น: ลบไฟล์เดิมทิ้งและเคลียร์ค่าใน DB
        if ($request->boolean('remove_signature')) {
            $this->deleteSignatureFile($oldSignature);
            $fields['signature'] = null;
            $oldSignature = null;
        }

        // อัพโหลดรูปลายเซ็นเฉพาะเมื่อมีไฟล์ส่งมา (แก้ไข: เว้นว่าง = คงไฟล์เดิม)
        // เก็บเป็นชื่อไฟล์ใน emp.signature และย้ายไฟล์ไปที่ upload/employees/ พร้อมลบไฟล์เดิมทิ้ง
        if ($request->file('signature')) {
            $file = $request->file('signature');
            $extension = $file->getClientOriginalExtension();
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $image_name = $originalName.rand().'.'.$extension;
            $file->move('upload/employees/', $image_name);
            $this->deleteSignatureFile($oldSignature);
            $fields['signature'] = $image_name;
        }

        if ($is_edit) {
            $employee = $existing;
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

    // ลบไฟล์รูปลายเซ็นออกจาก upload/employees/ (ถ้ามีไฟล์อยู่จริง)
    private function deleteSignatureFile(?string $filename): void
    {
        if (!$filename) {
            return;
        }
        $path = public_path('upload/employees/'.$filename);
        if (File::exists($path)) {
            File::delete($path);
        }
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
