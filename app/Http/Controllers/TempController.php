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
            })
            // คอลัมน์ "ลำดับ" ถูกเอาออกจากตารางแล้ว จึงตั้งลำดับเริ่มต้นให้เรียงตาม sort ใน DB ที่นี่แทน
            // เติมเฉพาะตอนผู้ใช้ยังไม่คลิก sort คอลัมน์อื่น (ไม่งั้น Yajra จะเรียงซ้อนทำให้คลิก sort ไม่มีผล)
            ->when(empty(request('order')), function ($q) {
                $q->orderBy('sort', 'asc');
            });

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

        $data = [
            'Temp1'     => $request->name,
            // switch ในฟอร์ม: ติ๊ก = ส่ง is_active (Y), ไม่ติ๊ก = ไม่ส่ง (N)
            'is_active' => $request->has('is_active') ? 'Y' : 'N',
        ];

        // ฟอร์มไม่มีช่อง "ลำดับ" แล้ว: ระเบียนใหม่ตั้งต้น sort = 0,
        // ส่วนการแก้ไขระเบียนเดิมจะไม่แตะค่า sort เดิม (คงลำดับที่เคยตั้งไว้)
        if (!$request->id) {
            $data['sort'] = 0;
        }

        $temp = Temp::updateOrCreate(['id' => $request->id], $data);

        return response()->json([
            'status'  => 200,
            'message' => $request->id ? 'แก้ไขข้อมูลสำเร็จ' : 'เพิ่มข้อมูลสำเร็จ',
            // คืนข้อมูลที่เพิ่งบันทึก เพื่อให้ modal ซ้อน (เช่น หน้า product) เอาไป append เป็น option ใน dropdown ได้ทันที
            'data'    => [
                'id'    => $temp->id,
                'Temp1' => $temp->Temp1,
            ],
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
