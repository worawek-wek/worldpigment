<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
        // หน้านี้แสดงรายการที่ยังไม่อนุมัติ (รออนุมัติ + ไม่อนุมัติ)
        $status = request('status');
        $data = $this->baseQuery()
            ->where('status', '!=', SemiPigment::STATUS_APPROVED)
            ->when(!empty($status), fn ($q) => $q->where('status', $status));

        return DataTables::of($data)
            ->addColumn('rownum', fn ($row) => $row->rownum)
            ->addColumn('type_badge', fn ($row) => $this->typeBadge($row))
            ->addColumn('status_badge', fn ($row) => $this->statusBadge($row))
            ->addColumn('action', function ($row) {
                if ($row->status === SemiPigment::STATUS_REQUEST) {
                    return '<button class="btn btn-sm btn-success btn_approve me-1" data-id="'.$row->id.'" title="อนุมัติ">
                                <i class="ti ti-check ti-sm"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn_reject" data-id="'.$row->id.'" title="ไม่อนุมัติ">
                                <i class="ti ti-x ti-sm"></i>
                            </button>';
                }
                return '<span class="text-muted small">'
                        .($row->approve_date ? date('d/m/Y H:i', strtotime($row->approve_date)) : '-')
                        .'</span>';
            })
            ->rawColumns(['type_badge', 'status_badge', 'action'])
            ->make(true);
    }

    /* ===================== หน้า: อนุมัติแล้ว ===================== */

    public function approvedIndex()
    {
        return view('production-planning.semi-pigment.approved');
    }

    public function approvedDatatable()
    {
        // หน้านี้แสดงเฉพาะรายการที่อนุมัติแล้ว
        $data = $this->baseQuery()
            ->where('status', SemiPigment::STATUS_APPROVED);

        return DataTables::of($data)
            ->addColumn('rownum', fn ($row) => $row->rownum)
            ->addColumn('type_badge', fn ($row) => $this->typeBadge($row))
            ->addColumn('plan_badge', function ($row) {
                if ($row->result_planning_id) {
                    return '<span class="badge bg-label-success">สร้างแล้ว</span>';
                }
                return '<span class="badge bg-label-secondary">ยังไม่สร้าง</span>';
            })
            ->addColumn('action', function ($row) {
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
            })
            ->rawColumns(['type_badge', 'plan_badge', 'action'])
            ->make(true);
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

        $sp->update([
            'status'        => SemiPigment::STATUS_APPROVED,
            'approver_code' => Auth::id(),
            'approve_date'  => now(),
        ]);

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

        return '<span class="badge '.$cls.'">'.$row->statusLabel().'</span>';
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
        ]);

        return Planning::create([
            'planning_header_id' => $header->id,
            'parent_planning_id' => $sp->planning_id,
            'plan_type'          => $sp->type,
            'itemno'             => $sp->itemno,
            'quantity'           => $sp->quantity,
            'mdate'              => $sp->order_date,
            'custwant'           => $sp->want_date,
        ]);
    }
}
