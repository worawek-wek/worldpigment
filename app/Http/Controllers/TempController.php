<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Temp;

class TempController extends Controller
{
    public function index()
    {
        return view('temp.index');
    }

    public function datatable()
    {
        $temps = Temp::select(['id', 'Temp1', 'sort', 'is_active'])
            ->when(request('search'), function ($q, $search) {
                $q->where('Temp1', 'like', "%{$search}%");
            })
            ->when(in_array(request('status'), ['Y', 'N'], true), function ($q) {
                $q->where('is_active', request('status'));
            });
        // หมายเหตุ: ไม่ hard-code orderBy ที่นี่แล้ว — ให้ Yajra จัดการเรียงตามที่คลิกหัวคอลัมน์
        // (ลำดับเริ่มต้นตามคอลัมน์ sort ถูกกำหนดเป็นค่า default order ในฝั่ง DataTables ของ temp/index)

        $rownum = 0;

        return DataTables::of($temps)
            ->addColumn('rownum', function () use (&$rownum) {
                return ++$rownum;
            })
            ->addColumn('name', function ($temp) {
                return $temp->Temp1 ?: '-';
            })
            ->addColumn('status_switch', function ($temp) {
                $checked = $temp->is_active === 'Y' ? 'checked' : '';
                return '<div class="form-check form-switch d-flex justify-content-center mb-0">
                            <input class="form-check-input switch_status" type="checkbox" role="switch"
                                data-id="'.$temp->id.'" '.$checked.'>
                        </div>';
            })
            ->addColumn('btnaction', function ($temp) {
                return '<div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-sm btn-icon btn-warning btn_edit" data-id="'.$temp->id.'" title="แก้ไข">
                                <i class="ti ti-pencil ti-sm"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-danger btn_delete" data-id="'.$temp->id.'" title="ลบ">
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

        $temp = $id ? Temp::find($id) : null;

        $html = view('temp.temp-form', [
            'temp' => $temp,
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'sort' => 'nullable|integer',
        ], [
            'name.required' => 'กรุณากรอกชื่อ',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ]);
        }

        Temp::updateOrCreate(
            ['id' => $request->id],
            [
                'Temp1'     => $request->name,
                'sort'      => $request->sort ?: 0,
                // switch ในฟอร์ม: ติ๊ก = ส่ง is_active (Y), ไม่ติ๊ก = ไม่ส่ง (N)
                'is_active' => $request->has('is_active') ? 'Y' : 'N',
            ]
        );

        return response()->json([
            'status'  => 200,
            'message' => $request->id ? 'แก้ไขข้อมูลสำเร็จ' : 'เพิ่มข้อมูลสำเร็จ',
        ]);
    }

    // ลบรายการ
    public function destroy(Request $request)
    {
        $temp = Temp::find($request->id);

        if (!$temp) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบข้อมูลที่ต้องการลบ',
            ]);
        }

        $temp->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'ลบข้อมูลสำเร็จ',
        ]);
    }

    // สลับสถานะเปิด-ปิดใช้งานจากหน้าตาราง
    public function toggleStatus(Request $request)
    {
        $temp = Temp::find($request->id);

        if (!$temp) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบข้อมูลที่ต้องการ',
            ]);
        }

        $temp->is_active = $request->is_active === 'Y' ? 'Y' : 'N';
        $temp->save();

        return response()->json([
            'status'    => 200,
            'message'   => $temp->is_active === 'Y' ? 'เปิดใช้งานแล้ว' : 'ปิดใช้งานแล้ว',
            'is_active' => $temp->is_active,
        ]);
    }
}
