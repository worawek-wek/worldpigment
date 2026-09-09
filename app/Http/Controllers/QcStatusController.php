<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\QcStatus;

/**
 * สถานะ QC (master) — ตาราง tb_qc_status (09/09/2569)
 * โครงเดียวกับ PlanningRemarkController: หน้ารายการ + modal ฟอร์ม + สลับสถานะจากตาราง
 * ⚠ ตาราง tb_qc_status ไม่มี created_by / updated_by จึงไม่เขียน 2 คอลัมน์นี้
 */
class QcStatusController extends Controller
{
    public function index()
    {
        return view('qc-status.index');
    }

    public function datatable()
    {
        $items = QcStatus::select(['id', 'name', 'sort', 'is_active'])
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

        return DataTables::of($items)
            ->addColumn('rownum', function () use (&$rownum) {
                return ++$rownum;
            })
            ->editColumn('name', function ($item) {
                return $item->name ?: '-';
            })
            ->addColumn('status_switch', function ($item) {
                $checked = $item->is_active === 'Y' ? 'checked' : '';
                return '<div class="form-check form-switch d-flex justify-content-center mb-0">
                            <input class="form-check-input switch_status" type="checkbox" role="switch"
                                data-id="'.$item->id.'" '.$checked.'>
                        </div>';
            })
            ->addColumn('btnaction', function ($item) {
                return '<div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-sm btn-icon btn-warning btn_edit" data-id="'.$item->id.'" title="แก้ไข">
                                <i class="ti ti-pencil ti-sm"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-danger btn_delete" data-id="'.$item->id.'" title="ลบ">
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

        $item = $id ? QcStatus::find($id) : null;

        $html = view('qc-status.qc-status-form', [
            'item' => $item,
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
            'name.required' => 'กรุณากรอกชื่อสถานะ QC',
            'sort.integer'  => 'ลำดับต้องเป็นตัวเลข',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ]);
        }

        $data = [
            'name'      => $request->name,
            'sort'      => (int) $request->sort,
            // switch ในฟอร์ม: ติ๊ก = ส่ง is_active (Y), ไม่ติ๊ก = ไม่ส่ง (N)
            'is_active' => $request->has('is_active') ? 'Y' : 'N',
        ];

        QcStatus::updateOrCreate(['id' => $request->id], $data);

        return response()->json([
            'status'  => 200,
            'message' => $request->id ? 'แก้ไขข้อมูลสำเร็จ' : 'เพิ่มข้อมูลสำเร็จ',
        ]);
    }

    public function destroy(Request $request)
    {
        $item = QcStatus::find($request->id);

        if (!$item) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบข้อมูลที่ต้องการลบ',
            ]);
        }

        $item->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'ลบข้อมูลสำเร็จ',
        ]);
    }

    // สลับสถานะเปิด-ปิดใช้งานจากหน้าตาราง
    public function toggleStatus(Request $request)
    {
        $item = QcStatus::find($request->id);

        if (!$item) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบข้อมูลที่ต้องการ',
            ]);
        }

        $item->is_active = $request->is_active === 'Y' ? 'Y' : 'N';
        $item->save();

        return response()->json([
            'status'    => 200,
            'message'   => $item->is_active === 'Y' ? 'เปิดใช้งานแล้ว' : 'ปิดใช้งานแล้ว',
            'is_active' => $item->is_active,
        ]);
    }
}
