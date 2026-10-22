<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\PlanningStatus;
use App\Models\Department;

class PlanningStatusController extends Controller
{
    // แผนกที่ให้เลือกใน dropdown ฟอร์ม + ตัวกรองค้นหา (22/10/2569 ตามที่ผู้ใช้สั่ง) — จับคู่ tb_departments.name
    public const DEPT_OPTIONS = ['CP', 'MB', 'DB', 'SPP'];

    /**
     * แผนกที่แสดงใน dropdown — เฉพาะ DEPT_OPTIONS (เรียงตามลำดับในค่าคงที่)
     * $keepId = แผนกเดิมของรายการที่แก้ไข: ถ้าไม่อยู่ในรายการ ให้เติมกลับเข้าไป กันค่าหายตอนบันทึกทับ
     */
    private function departmentOptions($keepId = null)
    {
        return Department::where('is_active', 'Y')
            ->where(function ($q) use ($keepId) {
                $q->whereIn('name', self::DEPT_OPTIONS);
                if (!empty($keepId)) {
                    $q->orWhere('id', $keepId);
                }
            })
            ->get(['id', 'name'])
            ->sortBy(function ($d) {
                $pos = array_search($d->name, self::DEPT_OPTIONS, true);
                return $pos === false ? 99 : $pos;
            })
            ->values();
    }

    public function index()
    {
        $departments = $this->departmentOptions();

        return view('production-planning.planning-status.index', [
            'departments' => $departments,
        ]);
    }

    public function datatable()
    {
        $data = $this->dataQuery();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('dept_name', function($row) {
                return $row->dept_name ?? '-';
            })
            ->addColumn('is_active_label', function($row) {
                if ($row->is_active === 'Y') {
                    return '<span class="badge bg-label-success">เปิดใช้งาน</span>';
                }
                return '<span class="badge bg-label-secondary">ปิดใช้งาน</span>';
            })
            ->addColumn('is_packing_label', function($row) {
                if ($row->is_packing === 'Y') {
                    return '<span class="badge bg-label-info">เปิด</span>';
                }
                return '<span class="badge bg-label-secondary">ปิด</span>';
            })
            ->addColumn('btnedit', function($row) {
                return '<button class="btn btn-sm btn-icon btn-warning me-2 btn_edit" data-id="'.$row->id.'" title="แก้ไข">
                    <i class="ti ti-pencil text-white ti-sm"></i>
                </button>';
            })
            ->rawColumns(['is_packing_label', 'is_active_label', 'btnedit'])
            ->make(true);
    }

    public function dataQuery()
    {
        $search = request('search');
        $dept   = request('dept');

        $data = PlanningStatus::query()
            ->leftJoin('tb_departments', 'tb_departments.id', '=', 'tb_planning_status.dept')
            ->select([
                'tb_planning_status.*',
                'tb_departments.name as dept_name',
            ])
            ->when(!empty($search), function ($query) use ($search) {
                $query->where('tb_planning_status.name', 'LIKE', '%'.$search.'%');
            })
            ->when(!empty($dept), function ($query) use ($dept) {
                $query->where('tb_planning_status.dept', $dept);
            });
        // หมายเหตุ: ไม่ hard-code orderBy ที่นี่แล้ว — ให้ Yajra จัดการเรียงตามที่คลิกหัวคอลัมน์
        // (ลำดับเริ่มต้นตามคอลัมน์ "ลำดับ"/sort ถูกกำหนดเป็นค่า default order ในฝั่ง DataTables ของ planning-status/index)

        return $data;
    }

    public function edit()
    {
        $id = request('id');

        $planning_status = $id ? PlanningStatus::find($id) : null;

        $departments = $this->departmentOptions($planning_status?->dept);

        $html = view('production-planning.planning-status.form', [
            'planning_status' => $planning_status,
            'departments'     => $departments,
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html
        ]);
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'dept'      => 'nullable|integer|exists:tb_departments,id',
            'sort'      => 'nullable|integer|min:0',
            'is_packing' => 'nullable|in:Y,N',
            'is_active' => 'nullable|in:Y,N',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ]);
        }

        // ด่านฝั่ง server: แผนกต้องอยู่ใน DEPT_OPTIONS (ยกเว้นคงแผนกเดิมของรายการที่แก้ไข)
        if (!empty($request->dept)) {
            $deptName = Department::where('id', $request->dept)->value('name');
            $oldDept  = !empty($request->id) ? PlanningStatus::where('id', $request->id)->value('dept') : null;
            if (!in_array($deptName, self::DEPT_OPTIONS, true) && (string) $oldDept !== (string) $request->dept) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'แผนกต้องเป็น ' . implode(', ', self::DEPT_OPTIONS) . ' เท่านั้น',
                ]);
            }
        }

        $fields = [
            'name'      => $request->name,
            'dept'      => $request->dept ?: null,
            'sort'      => $request->sort ?: 0,
            // switch ไม่ติ๊ก = ไม่ส่ง key มา → N
            'is_packing' => $request->is_packing === 'Y' ? 'Y' : 'N',
            'is_active' => $request->is_active ?: 'N',
        ];

        $id        = $request->id;
        $is_update = !empty($id);

        try {
            if ($is_update) {
                PlanningStatus::where('id', $id)->update($fields);
            } else {
                PlanningStatus::create($fields);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }

        return response()->json([
            'status'  => 200,
            'message' => $is_update ? 'แก้ไขสถานะ Planning สำเร็จ' : 'เพิ่มสถานะ Planning สำเร็จ'
        ]);
    }
}
