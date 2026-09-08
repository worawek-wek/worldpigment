<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\PlanningRemark;
use App\Services\AccessControl;

/**
 * หมายเหตุแผนการผลิต (master) — ตาราง tb_planning_remark (08/09/2569)
 * โครงเดียวกับ TempController: หน้ารายการ + modal ฟอร์ม + สลับสถานะจากตาราง
 */
class PlanningRemarkController extends Controller
{
    public function index()
    {
        return view('planning-remark.index');
    }

    public function datatable()
    {
        $remarks = PlanningRemark::select(['id', 'name', 'sort', 'is_active'])
            ->when(request('search'), function ($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->when(in_array(request('status'), ['Y', 'N'], true), function ($q) {
                $q->where('is_active', request('status'));
            })
            // ลำดับเริ่มต้นเรียงตาม sort → id (เติมเฉพาะตอนผู้ใช้ยังไม่คลิก sort คอลัมน์อื่น)
            ->when(empty(request('order')), function ($q) {
                $q->orderBy('sort', 'asc')->orderBy('id', 'asc');
            });

        $rownum = 0;

        return DataTables::of($remarks)
            ->addColumn('rownum', function () use (&$rownum) {
                return ++$rownum;
            })
            ->editColumn('name', function ($remark) {
                return $remark->name ?: '-';
            })
            ->addColumn('status_switch', function ($remark) {
                $checked = $remark->is_active === 'Y' ? 'checked' : '';
                return '<div class="form-check form-switch d-flex justify-content-center mb-0">
                            <input class="form-check-input switch_status" type="checkbox" role="switch"
                                data-id="'.$remark->id.'" '.$checked.'>
                        </div>';
            })
            ->addColumn('btnaction', function ($remark) {
                return '<div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-sm btn-icon btn-warning btn_edit" data-id="'.$remark->id.'" title="แก้ไข">
                                <i class="ti ti-pencil ti-sm"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-danger btn_delete" data-id="'.$remark->id.'" title="ลบ">
                                <i class="ti ti-trash ti-sm"></i>
                            </button>
                        </div>';
            })
            ->rawColumns(['status_switch', 'btnaction'])
            ->make(true);
    }

    public function edit()
    {
        $id = request('id');

        $remark = $id ? PlanningRemark::find($id) : null;

        $html = view('planning-remark.planning-remark-form', [
            'remark' => $remark,
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
            'sort' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'กรุณากรอกหมายเหตุ',
            'sort.integer'  => 'ลำดับต้องเป็นตัวเลข',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ]);
        }

        $userId = AccessControl::currentAccount()?->id;

        $data = [
            'name'       => $request->name,
            'sort'       => (int) $request->sort,
            // switch ในฟอร์ม: ติ๊ก = ส่ง is_active (Y), ไม่ติ๊ก = ไม่ส่ง (N)
            'is_active'  => $request->has('is_active') ? 'Y' : 'N',
            'updated_by' => $userId,
        ];

        if (!$request->id) {
            $data['created_by'] = $userId;
        }

        PlanningRemark::updateOrCreate(['id' => $request->id], $data);

        return response()->json([
            'status'  => 200,
            'message' => $request->id ? 'แก้ไขข้อมูลสำเร็จ' : 'เพิ่มข้อมูลสำเร็จ',
        ]);
    }

    public function destroy(Request $request)
    {
        $remark = PlanningRemark::find($request->id);

        if (!$remark) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบข้อมูลที่ต้องการลบ',
            ]);
        }

        $remark->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'ลบข้อมูลสำเร็จ',
        ]);
    }

    // สลับสถานะเปิด-ปิดใช้งานจากหน้าตาราง
    public function toggleStatus(Request $request)
    {
        $remark = PlanningRemark::find($request->id);

        if (!$remark) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบข้อมูลที่ต้องการ',
            ]);
        }

        $remark->is_active  = $request->is_active === 'Y' ? 'Y' : 'N';
        $remark->updated_by = AccessControl::currentAccount()?->id;
        $remark->save();

        return response()->json([
            'status'    => 200,
            'message'   => $remark->is_active === 'Y' ? 'เปิดใช้งานแล้ว' : 'ปิดใช้งานแล้ว',
            'is_active' => $remark->is_active,
        ]);
    }
}
