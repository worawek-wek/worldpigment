<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Emp;

class RoleController extends Controller
{
    public function index()
    {
        return view('role.index');
    }

    public function datatable(Request $request)
    {
        $roles = Role::query()
            ->withCount('employees')
            ->select(['id', 'name', 'description', 'is_active']);

        if ($request->filled('search')) {
            $search = $request->search;
            $roles->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', '%'.$search.'%')
                    ->orWhere('description', 'LIKE', '%'.$search.'%');
            });
        }

        // หมายเหตุ: ไม่ hard-code orderBy ที่นี่แล้ว — ให้ Yajra จัดการเรียงตามที่คลิกหัวคอลัมน์
        // (ลำดับเริ่มต้นตามชื่อ Role ถูกกำหนดเป็นค่า default order ในฝั่ง DataTables ของ role/index)

        $rownum = 0;

        return DataTables::of($roles)
            ->addColumn('rownum', function () use (&$rownum) {
                return ++$rownum;
            })
            ->addColumn('employees_count', function ($role) {
                return $role->employees_count;
            })
            ->addColumn('status_badge', function ($role) {
                if ($role->is_active === 'Y') {
                    return '<span class="badge bg-label-success">เปิดใช้งาน</span>';
                }
                return '<span class="badge bg-label-secondary">ปิดใช้งาน</span>';
            })
            ->addColumn('btnedit', function ($role) {
                return '<button type="button" class="btn btn-sm btn-icon btn-warning btn_edit me-1" data-id="'.$role->id.'" title="แก้ไข">
                            <i class="ti ti-pencil ti-sm"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-icon btn-danger btn_delete" data-id="'.$role->id.'" title="ลบ">
                            <i class="ti ti-trash ti-sm"></i>
                        </button>';
            })
            ->rawColumns(['status_badge', 'btnedit'])
            ->make(true);
    }

    public function edit(Request $request)
    {
        $id   = $request->id;
        $role = $id ? Role::with('permissions')->find($id) : null;

        $html = view('role.role-form', [
            'role'         => $role,
            'menu'         => config('menu'),
            'selectedKeys' => $role ? $role->menuKeys() : [],
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html,
        ]);
    }

    public function store(Request $request)
    {
        $is_edit = $request->filled('id');

        $validator = Validator::make($request->all(), [
            'name'          => [
                'required', 'string', 'max:255',
                Rule::unique('roles', 'name')->ignore($is_edit ? $request->id : null),
            ],
            'description'   => 'nullable|string|max:255',
            'is_active'     => 'nullable|in:Y,N',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors(),
            ]);
        }

        // รับเฉพาะ menu_key ที่มีอยู่จริงใน config/menu.php (กันค่าปลอม)
        $validKeys = $this->allMenuKeys();
        $selected  = array_values(array_intersect((array) $request->permissions, $validKeys));

        $fields = [
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->has('is_active') ? 'Y' : 'N',
        ];

        try {
            DB::transaction(function () use ($request, $is_edit, $fields, $selected, &$role) {
                if ($is_edit) {
                    $role = Role::find($request->id);
                    if ($role) {
                        $role->update($fields);
                    }
                } else {
                    $role = Role::create($fields);
                }

                if ($role) {
                    RolePermission::where('role_id', $role->id)->delete();
                    if (!empty($selected)) {
                        $rows = array_map(fn ($key) => [
                            'role_id'  => $role->id,
                            'menu_key' => $key,
                        ], $selected);
                        RolePermission::insert($rows);
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'เกิดข้อผิดพลาด: '.$e->getMessage(),
            ]);
        }

        if (empty($role)) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบ Role ที่ต้องการแก้ไข',
            ]);
        }

        return response()->json([
            'status'  => 200,
            'message' => $is_edit ? 'แก้ไข Role สำเร็จ' : 'เพิ่ม Role สำเร็จ',
        ]);
    }

    public function destroy(Request $request)
    {
        $role = Role::find($request->id);

        if (!$role) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบ Role ที่ต้องการลบ',
            ]);
        }

        try {
            DB::transaction(function () use ($role) {
                // ปลด role ออกจากพนักงานที่ผูกอยู่ก่อน แล้วค่อยลบ role + สิทธิ์
                Emp::where('role_id', $role->id)->update(['role_id' => null]);
                RolePermission::where('role_id', $role->id)->delete();
                $role->delete();
            });
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'เกิดข้อผิดพลาด: '.$e->getMessage(),
            ]);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'ลบ Role สำเร็จ',
        ]);
    }

    // รวบรวม key เมนูทั้งหมด (รวม sub_menu ทุกระดับ) ที่เลือกเป็นสิทธิ์ได้ — ข้าม header
    private function allMenuKeys(): array
    {
        $keys = [];
        foreach (config('menu') as $key => $menu) {
            if (isset($menu['type']) && $menu['type'] === 'header') {
                continue;
            }
            $keys[] = $key;
            $this->collectSubMenuKeys($menu, $keys);
        }
        return $keys;
    }

    // เก็บ key ของ sub_menu แบบ recursive (รองรับเมนูซ้อนหลายชั้น)
    private function collectSubMenuKeys(array $menu, array &$keys): void
    {
        if (!isset($menu['sub_menu'])) {
            return;
        }
        foreach ($menu['sub_menu'] as $subKey => $sub) {
            $keys[] = $subKey;
            $this->collectSubMenuKeys($sub, $keys);
        }
    }
}
