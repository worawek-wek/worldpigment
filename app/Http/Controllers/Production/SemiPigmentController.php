<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\SemiPigment;
use App\Models\Planning;
use App\Models\PlanningHeader;

class SemiPigmentController extends Controller
{
    /* ===================== หน้า: รออนุมัติ ===================== */

    public function index()
    {
        return view('production-planning.semi-pigment.index');
    }

    public function datatable()
    {
        // กรองตามสถานะที่เลือก (รออนุมัติ / อนุมัติแล้ว / ไม่อนุมัติ) — ค่าว่าง = ทุกสถานะ
        $status = request('status');
        $data = $this->baseQuery()
            ->when(!empty($status), fn ($q) => $q->where('status', $status));

        return DataTables::of($data)
            ->addColumn('rownum', fn ($row) => $row->rownum)
            ->addColumn('type_badge', fn ($row) => $this->typeBadge($row))
            ->addColumn('status_badge', fn ($row) => $this->statusBadge($row))
            ->editColumn('order_date', fn ($row) => $row->order_date ? \Carbon\Carbon::parse($row->order_date)->format('d/m/Y') : '-')
            ->editColumn('want_date', fn ($row) => $row->want_date ? \Carbon\Carbon::parse($row->want_date)->format('d/m/Y') : '-')
            ->addColumn('action', fn ($row) => $this->actionButtons($row))
            ->rawColumns(['type_badge', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * ฟอร์มแก้ไขรายการ Semi/Pigment (สำหรับ modal หน้ารออนุมัติ)
     */
    public function editForm()
    {
        $sp = SemiPigment::find(request('id'));

        if (!$sp) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการ']);
        }

        $companies = ['CP', 'MB', 'DB', 'SPP'];

        $html = view('production-planning.semi-pigment.edit', compact('sp', 'companies'))->render();

        return response()->json([
            'status' => 200,
            'data'   => $html
        ]);
    }

    /**
     * รายละเอียดรายการ Semi/Pigment (สำหรับ modal)
     */
    public function detail()
    {
        $sp = SemiPigment::find(request('id'));

        if (!$sp) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการ']);
        }

        $html = view('production-planning.semi-pigment.detail', compact('sp'))->render();

        return response()->json([
            'status' => 200,
            'data'   => $html
        ]);
    }

    /**
     * นำข้อมูลที่อนุมัติแล้ว → สร้างแผนการผลิต (กดจากหน้าอนุมัติแล้ว)
     */
    public function convertplanning(Request $request)
    {
        $sp = SemiPigment::find($request->id);

        if (!$sp) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการ']);
        }

        if ($sp->status !== SemiPigment::STATUS_APPROVED) {
            return response()->json(['status' => 500, 'message' => 'รายการนี้ยังไม่ได้อนุมัติ']);
        }

        if ($sp->result_planning_id) {
            return response()->json(['status' => 500, 'message' => 'มีการสร้างแผนการผลิตแล้ว']);
        }

        DB::beginTransaction();
        try {
            $planning = $this->createPlanningFromSemiPigment($sp);
            $sp->update(['result_planning_id' => $planning->id]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 500,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'สร้างแผนการผลิตสำเร็จ'
        ]);
    }

    /* ===================== เพิ่ม / แก้ไข / ลบ จาก modal หน้า Planning Item ===================== */

    /**
     * เพิ่มรายการ Semi/Pigment (รออนุมัติ) — บันทึกลงฐานข้อมูลทันทีจาก modal
     */
    public function entryStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'planning_id' => 'required|exists:tb_planning,id',
            'type'        => 'required|in:semi,pigment',
            'itemno'      => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 422, 'message' => $validator->errors()->first()]);
        }

        $planning = Planning::with('planning_header')->find($request->planning_id);
        if (!$planning) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบ Planning Item']);
        }

        $sp = SemiPigment::create(array_merge($this->entryFields($request), [
            'planning_id'        => $planning->id,
            'planning_header_id' => $planning->planning_header_id,
            'orderno'            => $planning->planning_header?->orderno,
            'type'               => $request->input('type'),
            'status'             => SemiPigment::STATUS_REQUEST,
        ]));

        return response()->json([
            'status'  => 200,
            'message' => 'เพิ่มรายการสำเร็จ',
            'data'    => $this->entryPayload($sp),
        ]);
    }

    /**
     * สร้าง Semi แบบกรอกเอง (จากหน้า Planning) — ไม่ผูกกับแผนการผลิตใดๆ
     * บันทึกเป็นสถานะ "รออนุมัติ" เพื่อเข้าสู่ขั้นตอนอนุมัติตามปกติ
     */
    public function standaloneStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'itemno' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 422, 'message' => $validator->errors()->first()]);
        }

        $sp = SemiPigment::create(array_merge($this->entryFields($request), [
            'planning_id'        => null,
            'planning_header_id' => null,
            'orderno'            => null,
            'type'               => 'semi',
            'status'             => SemiPigment::STATUS_REQUEST,
        ]));

        return response()->json([
            'status'  => 200,
            'message' => 'สร้าง Semi สำเร็จ เข้าสู่รายการรออนุมัติแล้ว',
            'data'    => $this->entryPayload($sp),
        ]);
    }

    /**
     * แก้ไขรายการ Semi/Pigment (เฉพาะที่ยัง "รออนุมัติ")
     */
    public function entryUpdate(Request $request)
    {
        $sp = SemiPigment::find($request->id);
        if (!$sp) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการ']);
        }

        if ($sp->status !== SemiPigment::STATUS_REQUEST) {
            return response()->json(['status' => 500, 'message' => 'รายการนี้ถูกดำเนินการแล้ว แก้ไขไม่ได้']);
        }

        $validator = Validator::make($request->all(), [
            'itemno' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 422, 'message' => $validator->errors()->first()]);
        }

        $sp->update($this->entryFields($request));

        return response()->json([
            'status'  => 200,
            'message' => 'แก้ไขรายการสำเร็จ',
            'data'    => $this->entryPayload($sp),
        ]);
    }

    /**
     * ลบรายการ Semi/Pigment (เฉพาะที่ยัง "รออนุมัติ")
     */
    public function entryDestroy(Request $request)
    {
        $sp = SemiPigment::find($request->id);
        if (!$sp) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการ']);
        }

        if ($sp->status !== SemiPigment::STATUS_REQUEST) {
            return response()->json(['status' => 500, 'message' => 'รายการนี้ถูกดำเนินการแล้ว ลบไม่ได้']);
        }

        $sp->delete();

        return response()->json(['status' => 200, 'message' => 'ลบรายการสำเร็จ']);
    }

    /**
     * รับค่าฟิลด์ที่แก้ไขได้จาก modal → คอลัมน์ของ tb_semi_pigment
     */
    private function entryFields(Request $request): array
    {
        $val = fn ($k) => $request->filled($k) ? $request->input($k) : null;

        $weightRequest = $val('weight_request');
        // ถ้ายังไม่ระบุน้ำหนักที่จะผลิต ให้ใช้น้ำหนักที่จะใช้เป็นค่าเริ่มต้น
        $weightProduction = $val('weight_production') ?? $weightRequest;

        return [
            'company'             => $val('company'),
            'order_date'          => $val('mdate'),
            'want_date'           => $val('custwant'),
            'custno'              => $val('custno'),
            'itemno'              => $request->input('itemno'),
            'semi_code'           => $val('semi_code'),
            'primary_color'       => $val('primary_color'),
            'weight_request'      => $weightRequest,
            'balance'             => $val('balance'),
            'lot_no'              => $val('lot_no'),
            'retrospective'       => $val('retrospective'),
            'increase_production' => $val('increase_production'),
            'weight_production'   => $weightProduction,
            'red_bill_code'       => $val('red_bill_code'),
        ];
    }

    /**
     * แปลงข้อมูล SemiPigment → โครงสร้างที่ฝั่ง JS (displayRow) ใช้สร้างแถวในตาราง
     */
    private function entryPayload(SemiPigment $sp): array
    {
        return [
            'id'                  => $sp->id,
            'company'             => $sp->company,
            'mdate'               => $sp->order_date ? substr($sp->order_date, 0, 10) : '',
            'custwant'            => $sp->want_date ? substr($sp->want_date, 0, 10) : '',
            'custno'              => $sp->custno,
            'itemno'              => $sp->itemno,
            'semi_code'           => $sp->semi_code,
            'primary_color'       => $sp->primary_color,
            'weight_request'      => $sp->weight_request,
            'balance'             => $sp->balance,
            'lot_no'              => $sp->lot_no,
            'retrospective'       => $sp->retrospective,
            'increase_production' => $sp->increase_production,
            'weight_production'   => $sp->weight_production,
            'red_bill_code'       => $sp->red_bill_code,
        ];
    }

    /* ===================== อนุมัติ / ไม่อนุมัติ ===================== */

    /**
     * อนุมัติรายการ Semi/Pigment (ยังไม่สร้างแผน — ไปสร้างที่หน้าอนุมัติแล้ว)
     */
    public function approve(Request $request)
    {
        $sp = SemiPigment::find($request->id);

        if (!$sp) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการ']);
        }

        if ($sp->status !== SemiPigment::STATUS_REQUEST) {
            return response()->json(['status' => 500, 'message' => 'รายการนี้ดำเนินการไปแล้ว']);
        }

        $validator = Validator::make($request->all(), [
            'itemno' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 422, 'message' => $validator->errors()->first()]);
        }

        // บันทึกข้อมูลฟอร์ม + เปลี่ยนสถานะเป็นอนุมัติในคราวเดียว
        $sp->update(array_merge($this->entryFields($request), [
            'status'        => SemiPigment::STATUS_APPROVED,
            'approver_code' => Auth::id(),
            'approve_date'  => now(),
        ]));

        return response()->json([
            'status'  => 200,
            'message' => 'อนุมัติรายการสำเร็จ'
        ]);
    }

    /**
     * ไม่อนุมัติรายการ Semi/Pigment
     */
    public function reject(Request $request)
    {
        $sp = SemiPigment::find($request->id);

        if (!$sp) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการ']);
        }

        if ($sp->status !== SemiPigment::STATUS_REQUEST) {
            return response()->json(['status' => 500, 'message' => 'รายการนี้ดำเนินการไปแล้ว']);
        }

        $sp->update([
            'status'        => SemiPigment::STATUS_REJECT,
            'approver_code' => Auth::id(),
            'approve_date'  => now(),
        ]);

        return response()->json([
            'status'  => 200,
            'message' => 'ไม่อนุมัติรายการสำเร็จ'
        ]);
    }

    /* ===================== helpers ===================== */

    private function baseQuery()
    {
        $search = request('search');
        $type   = request('type');

        return SemiPigment::query()
            ->select([
                'tb_semi_pigment.*',
                DB::raw('ROW_NUMBER() OVER (ORDER BY tb_semi_pigment.id DESC) AS rownum')
            ])
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('itemno', 'LIKE', '%'.$search.'%')
                      ->orWhere('custno', 'LIKE', '%'.$search.'%')
                      ->orWhere('orderno', 'LIKE', '%'.$search.'%')
                      ->orWhere('company', 'LIKE', '%'.$search.'%');
                });
            })
            ->when(!empty($type), fn ($q) => $q->where('type', $type))
            ->orderBy('tb_semi_pigment.id', 'desc');
    }

    private function typeBadge(SemiPigment $row): string
    {
        $cls = $row->type === 'semi' ? 'bg-label-primary' : 'bg-label-success';
        return '<span class="badge '.$cls.'">'.strtoupper($row->type).'</span>';
    }

    private function statusBadge(SemiPigment $row): string
    {
        $cls = [
            SemiPigment::STATUS_REQUEST  => 'bg-label-warning',
            SemiPigment::STATUS_APPROVED => 'bg-label-success',
            SemiPigment::STATUS_REJECT   => 'bg-label-danger',
        ][$row->status] ?? 'bg-label-secondary';

        $badge = '<span class="badge '.$cls.'">'.$row->statusLabel().'</span>';

        // อนุมัติแล้ว → แสดงสถานะการสร้างแผนการผลิตต่อท้าย (ยังไม่สร้าง / สร้างแล้ว)
        if ($row->status === SemiPigment::STATUS_APPROVED) {
            $badge .= '<div class="mt-1">'.$this->planBadge($row).'</div>';
        }

        return $badge;
    }

    /**
     * ป้ายสถานะการสร้างแผนการผลิตของรายการที่อนุมัติแล้ว
     */
    private function planBadge(SemiPigment $row): string
    {
        if ($row->result_planning_id) {
            return '<span class="badge bg-label-success">สร้างแล้ว</span>';
        }
        return '<span class="badge bg-label-secondary">ยังไม่สร้าง</span>';
    }

    /**
     * ปุ่มในคอลัมน์ "จัดการ" — กำหนดตามสถานะของรายการ
     * - อนุมัติแล้ว: ดูรายละเอียด + สร้างแผนการผลิต (แบบหน้า "อนุมัติแล้ว")
     * - รออนุมัติ / ไม่อนุมัติ: แก้ไข / รายละเอียด
     */
    private function actionButtons(SemiPigment $row): string
    {
        if ($row->status === SemiPigment::STATUS_APPROVED) {
            $btn_view = '<button class="btn btn-sm btn-icon btn-label-primary me-2 btn_view" data-id="'.$row->id.'" title="ดูรายละเอียด">
                            <i class="ti ti-eye ti-sm"></i>
                        </button>';

            if ($row->result_planning_id) {
                $btn_plan = '<button class="btn btn-sm btn-icon btn-label-secondary btn_create_plan" data-id="'.$row->id.'" title="สร้างแผนการผลิตแล้ว" disabled>
                                <i class="ti ti-checks ti-sm"></i>
                            </button>';
            } else {
                $btn_plan = '<button class="btn btn-sm btn-icon btn-label-warning btn_create_plan" data-id="'.$row->id.'" title="สร้างแผนการผลิต">
                                <i class="ti ti-download ti-sm"></i>
                            </button>';
            }

            return $btn_view.$btn_plan;
        }

        return '<button class="btn btn-sm btn-icon btn-warning btn_edit" data-id="'.$row->id.'" title="แก้ไข / รายละเอียด">
                    <i class="ti ti-pencil ti-sm"></i>
                </button>';
    }

    /**
     * นำข้อมูล Semi/Pigment ที่อนุมัติแล้ว → สร้าง PlanningHeader + Planning (แผนการผลิต)
     */
    private function createPlanningFromSemiPigment(SemiPigment $sp): Planning
    {
        $header = PlanningHeader::create([
            'planning_code'      => ($sp->orderno ?: 'SP') . '-' . strtoupper($sp->type) . '-' . $sp->id,
            'plan_type'          => $sp->type,
            'parent_planning_id' => $sp->planning_id,
            'company'            => $sp->company,
            'mdate'              => $sp->order_date,
            'custwant'           => $sp->want_date,
            'custno'             => $sp->custno,
            'orderno'            => $sp->orderno,
            'netqty'             => $sp->weight_production,
        ]);

        return Planning::create([
            'planning_header_id' => $header->id,
            'parent_planning_id' => $sp->planning_id,
            'plan_type'          => $sp->type,
            'itemno'             => $sp->itemno,
            'quantity'           => $sp->weight_production,
            'weight'             => $sp->weight_production,
            'mdate'              => $sp->order_date,
            'custwant'           => $sp->want_date,
        ]);
    }
}
