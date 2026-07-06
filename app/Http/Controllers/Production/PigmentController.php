<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Pigment;
use App\Models\Planning;
use App\Models\PlanningHeader;

/**
 * จัดการรายการ Pigment จาก modal ในหน้า Planning Item + หน้าอนุมัติ Pigment
 * แยกจาก Semi โดยสมบูรณ์ — บันทึกลงตาราง tb_pigment (ไม่เกี่ยวกับ tb_semi_pigment)
 */
class PigmentController extends Controller
{
    /* ===================== หน้า: อนุมัติ Pigment ===================== */

    public function index()
    {
        return view('production-planning.pigment.index');
    }

    public function datatable()
    {
        $status = request('status');
        $data = $this->baseQuery()
            ->when(!empty($status), fn ($q) => $q->where('status', $status));

        return DataTables::of($data)
            ->addColumn('rownum', fn ($row) => $row->rownum)
            ->addColumn('status_badge', fn ($row) => $this->statusBadge($row))
            ->editColumn('order_date', fn ($row) => $row->order_date ? \Carbon\Carbon::parse($row->order_date)->format('d/m/Y') : '-')
            ->editColumn('want_date', fn ($row) => $row->want_date ? \Carbon\Carbon::parse($row->want_date)->format('d/m/Y') : '-')
            ->addColumn('action', fn ($row) => $this->actionButtons($row))
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    /**
     * ฟอร์มแก้ไข/อนุมัติรายการ Pigment (สำหรับ modal หน้าอนุมัติ)
     */
    public function editForm()
    {
        $pigment = Pigment::find(request('id'));

        if (!$pigment) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการ']);
        }

        $html = view('production-planning.pigment.edit', compact('pigment'))->render();

        return response()->json(['status' => 200, 'data' => $html]);
    }

    /**
     * รายละเอียดรายการ Pigment (สำหรับ modal)
     */
    public function detail()
    {
        $pigment = Pigment::find(request('id'));

        if (!$pigment) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการ']);
        }

        $html = view('production-planning.pigment.detail', compact('pigment'))->render();

        return response()->json(['status' => 200, 'data' => $html]);
    }

    /**
     * อนุมัติรายการ Pigment (บันทึกข้อมูลฟอร์ม + เปลี่ยนสถานะเป็นอนุมัติ)
     */
    public function approve(Request $request)
    {
        $pigment = Pigment::find($request->id);

        if (!$pigment) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการ']);
        }

        if ($pigment->status !== Pigment::STATUS_REQUEST) {
            return response()->json(['status' => 500, 'message' => 'รายการนี้ดำเนินการไปแล้ว']);
        }

        $validator = Validator::make($request->all(), [
            'itemno' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 422, 'message' => $validator->errors()->first()]);
        }

        $pigment->update(array_merge($this->entryFields($request), [
            'status'        => Pigment::STATUS_APPROVED,
            'approver_code' => Auth::id(),
            'approve_date'  => now(),
        ]));

        return response()->json(['status' => 200, 'message' => 'อนุมัติรายการ Pigment สำเร็จ']);
    }

    /**
     * ไม่อนุมัติรายการ Pigment
     */
    public function reject(Request $request)
    {
        $pigment = Pigment::find($request->id);

        if (!$pigment) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการ']);
        }

        if ($pigment->status !== Pigment::STATUS_REQUEST) {
            return response()->json(['status' => 500, 'message' => 'รายการนี้ดำเนินการไปแล้ว']);
        }

        $pigment->update([
            'status'        => Pigment::STATUS_REJECT,
            'approver_code' => Auth::id(),
            'approve_date'  => now(),
        ]);

        return response()->json(['status' => 200, 'message' => 'ไม่อนุมัติรายการ Pigment สำเร็จ']);
    }

    /**
     * นำข้อมูลที่อนุมัติแล้ว → สร้างแผนการผลิต (กดจากหน้าอนุมัติแล้ว)
     */
    public function convertplanning(Request $request)
    {
        $pigment = Pigment::find($request->id);

        if (!$pigment) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการ']);
        }

        if ($pigment->status !== Pigment::STATUS_APPROVED) {
            return response()->json(['status' => 500, 'message' => 'รายการนี้ยังไม่ได้อนุมัติ']);
        }

        if ($pigment->result_planning_id) {
            return response()->json(['status' => 500, 'message' => 'มีการสร้างแผนการผลิตแล้ว']);
        }

        DB::beginTransaction();
        try {
            $planning = $this->createPlanningFromPigment($pigment);
            $pigment->update(['result_planning_id' => $planning->id]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }

        return response()->json(['status' => 200, 'message' => 'สร้างแผนการผลิตสำเร็จ']);
    }

    /**
     * เพิ่มรายการ Pigment (รออนุมัติ) — บันทึกลงฐานข้อมูลทันทีจาก modal
     */
    public function entryStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'planning_id' => 'required|exists:tb_planning,id',
            'itemno'      => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 422, 'message' => $validator->errors()->first()]);
        }

        $planning = Planning::with('planning_header')->find($request->planning_id);
        if (!$planning) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบ Planning Item']);
        }

        $pigment = Pigment::create(array_merge($this->entryFields($request), [
            'planning_id'        => $planning->id,
            'planning_header_id' => $planning->planning_header_id,
            'orderno'            => $planning->planning_header?->orderno,
            'status'             => Pigment::STATUS_REQUEST,
        ]));

        return response()->json([
            'status'  => 200,
            'message' => 'เพิ่มรายการ Pigment สำเร็จ',
            'data'    => $this->entryPayload($pigment),
        ]);
    }

    /**
     * แก้ไขรายการ Pigment (เฉพาะที่ยัง "รออนุมัติ")
     */
    public function entryUpdate(Request $request)
    {
        $pigment = Pigment::find($request->id);
        if (!$pigment) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการ']);
        }

        if ($pigment->status !== Pigment::STATUS_REQUEST) {
            return response()->json(['status' => 500, 'message' => 'รายการนี้ถูกดำเนินการแล้ว แก้ไขไม่ได้']);
        }

        $validator = Validator::make($request->all(), [
            'itemno' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 422, 'message' => $validator->errors()->first()]);
        }

        $pigment->update($this->entryFields($request));

        return response()->json([
            'status'  => 200,
            'message' => 'แก้ไขรายการ Pigment สำเร็จ',
            'data'    => $this->entryPayload($pigment),
        ]);
    }

    /**
     * ลบรายการ Pigment (เฉพาะที่ยัง "รออนุมัติ")
     */
    public function entryDestroy(Request $request)
    {
        $pigment = Pigment::find($request->id);
        if (!$pigment) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการ']);
        }

        if ($pigment->status !== Pigment::STATUS_REQUEST) {
            return response()->json(['status' => 500, 'message' => 'รายการนี้ถูกดำเนินการแล้ว ลบไม่ได้']);
        }

        $pigment->delete();

        return response()->json(['status' => 200, 'message' => 'ลบรายการ Pigment สำเร็จ']);
    }

    /**
     * รับค่าฟิลด์ที่แก้ไขได้จาก modal → คอลัมน์ของ tb_pigment
     * (ตัดฟิลด์ที่ไม่ใช้ของ Pigment ออก: company, semi_code, primary_color, lot_no, red_bill_code, increase_production)
     */
    private function entryFields(Request $request): array
    {
        $val = fn ($k) => $request->filled($k) ? $request->input($k) : null;

        $weightRequest = $val('weight_request');
        // ถ้ายังไม่ระบุน้ำหนักที่จะผลิต ให้ใช้น้ำหนักที่จะใช้เป็นค่าเริ่มต้น
        $weightProduction = $val('weight_production') ?? $weightRequest;

        return [
            'order_date'        => $val('mdate'),
            'want_date'         => $val('custwant'),
            'custno'            => $val('custno'),
            'itemno'            => $request->input('itemno'),
            'weight_request'    => $weightRequest,
            'balance'           => $val('balance'),
            'retrospective'     => $val('retrospective'),
            'weight_production' => $weightProduction,
        ];
    }

    /**
     * แปลงข้อมูล Pigment → โครงสร้างที่ฝั่ง JS (displayRow) ใช้สร้างแถวในตาราง
     */
    private function entryPayload(Pigment $pigment): array
    {
        return [
            'id'                => $pigment->id,
            'mdate'             => $pigment->order_date ? substr($pigment->order_date, 0, 10) : '',
            'custwant'          => $pigment->want_date ? substr($pigment->want_date, 0, 10) : '',
            'custno'            => $pigment->custno,
            'itemno'            => $pigment->itemno,
            'weight_request'    => $pigment->weight_request,
            'balance'           => $pigment->balance,
            'retrospective'     => $pigment->retrospective,
            'weight_production' => $pigment->weight_production,
        ];
    }

    /* ===================== helpers (หน้าอนุมัติ) ===================== */

    private function baseQuery()
    {
        $search = request('search');

        return Pigment::query()
            ->select([
                'tb_pigment.*',
                DB::raw('ROW_NUMBER() OVER (ORDER BY tb_pigment.id DESC) AS rownum')
            ])
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('itemno', 'LIKE', '%'.$search.'%')
                      ->orWhere('custno', 'LIKE', '%'.$search.'%')
                      ->orWhere('orderno', 'LIKE', '%'.$search.'%');
                });
            })
            ->orderBy('tb_pigment.id', 'desc');
    }

    private function statusBadge(Pigment $row): string
    {
        $cls = [
            Pigment::STATUS_REQUEST  => 'bg-label-warning',
            Pigment::STATUS_APPROVED => 'bg-label-success',
            Pigment::STATUS_REJECT   => 'bg-label-danger',
        ][$row->status] ?? 'bg-label-secondary';

        $badge = '<span class="badge '.$cls.'">'.$row->statusLabel().'</span>';

        // อนุมัติแล้ว → แสดงสถานะการสร้างแผนการผลิตต่อท้าย
        if ($row->status === Pigment::STATUS_APPROVED) {
            $badge .= '<div class="mt-1">'.$this->planBadge($row).'</div>';
        }

        return $badge;
    }

    private function planBadge(Pigment $row): string
    {
        if ($row->result_planning_id) {
            return '<span class="badge bg-label-success">สร้างแล้ว</span>';
        }
        return '<span class="badge bg-label-secondary">ยังไม่สร้าง</span>';
    }

    /**
     * ปุ่มในคอลัมน์ "จัดการ" — กำหนดตามสถานะของรายการ
     * - อนุมัติแล้ว: ดูรายละเอียด + สร้างแผนการผลิต
     * - รออนุมัติ / ไม่อนุมัติ: แก้ไข / รายละเอียด
     */
    private function actionButtons(Pigment $row): string
    {
        if ($row->status === Pigment::STATUS_APPROVED) {
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
     * นำข้อมูล Pigment ที่อนุมัติแล้ว → สร้าง PlanningHeader + Planning (แผนการผลิต)
     */
    private function createPlanningFromPigment(Pigment $pigment): Planning
    {
        $header = PlanningHeader::create([
            'planning_code'      => ($pigment->orderno ?: 'PG') . '-PIGMENT-' . $pigment->id,
            'plan_type'          => 'pigment',
            'parent_planning_id' => $pigment->planning_id,
            'mdate'              => $pigment->order_date,
            'custwant'           => $pigment->want_date,
            'custno'             => $pigment->custno,
            'orderno'            => $pigment->orderno,
            'netqty'             => $pigment->weight_production,
        ]);

        return Planning::create([
            'planning_header_id' => $header->id,
            'parent_planning_id' => $pigment->planning_id,
            'plan_type'          => 'pigment',
            'itemno'             => $pigment->itemno,
            'quantity'           => $pigment->weight_production,
            'weight'             => $pigment->weight_production,
            'mdate'              => $pigment->order_date,
            'custwant'           => $pigment->want_date,
        ]);
    }
}
