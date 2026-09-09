<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Planning;
use App\Models\QcStatus;
use App\Services\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * หน้าพนักงาน QC — ตรวจงานที่ "ส่ง QC รอผล" แล้วบันทึกผล QC (09/09/2569)
 *
 *  โครงเลียนแบบ WorkerPlanningController แต่ต่างที่:
 *   - แสดง "ทุกงาน" ที่ planning_status = 'ส่ง QC รอผล' (ไม่กรองตาม empno — QC ตรวจงานของทุกคน)
 *     ความปลอดภัยคุมด้วย middleware 'qc' (เป็นพนักงาน QC จริง)
 *   - บันทึกผลลง tb_planning.qc_status (id → master tb_qc_status) + tb_planning.qc_datetime (เวลาปัจจุบัน)
 *   - ไม่แตะ planning_status → หลังบันทึก งานยังอยู่ในตาราง (QC ซ้ำ/แก้ผลได้)
 */
class QcPlanningController extends Controller
{
    // ชื่อสถานะที่งานต้องอยู่จึงจะเข้ามาให้ QC ตรวจ (จับคู่ด้วยชื่อ เพราะมีหลาย id ตามแผนก)
    private const QC_WAIT_STATUS = 'ส่ง QC รอผล';

    // empno ของพนักงานที่ล็อกอินอยู่ (QC)
    private function currentEmpno(): ?string
    {
        return AccessControl::currentAccount()?->empno;
    }

    // ดึงงานตาม id (พร้อม header) — ไม่พบ = 404 (QC เห็นได้ทุกงาน ไม่ผูก empno)
    private function jobOrFail($id): Planning
    {
        $job = Planning::with(['planning_header', 'qcStatus'])
            ->where('id', $id)
            ->first();

        if (!$job) {
            abort(404, 'ไม่พบงานนี้');
        }

        return $job;
    }

    // แผนกของงาน (ใช้ของ item ก่อน ถ้าว่าง fallback ไป header)
    private function jobCompany(Planning $job): ?string
    {
        return $job->company ?: optional($job->planning_header)->company;
    }

    // งานถูกปิดแล้วหรือยัง — ปิดงานราย item (end_job='Y') หรือปิดออเดอร์ทั้ง header (end_order='Y')
    private function jobIsClosed(Planning $job): bool
    {
        if (($job->end_job ?? 'N') === 'Y') {
            return true;
        }

        return (optional($job->planning_header)->end_order ?? 'N') === 'Y';
    }

    // ตัวเลือกสถานะ QC ที่เปิดใช้งาน (master)
    private function qcStatusOptions()
    {
        return QcStatus::where('is_active', 'Y')
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'name']);
    }

    public function index()
    {
        $account = AccessControl::currentAccount();
        $name    = trim(($account->empname ?? '').' '.($account->empsur ?? '')) ?: $account->empno;

        return view('qc.planning.index', [
            'qc_name'      => $name,
            'qc_statuses'  => $this->qcStatusOptions(),
        ]);
    }

    // ตารางงานที่รอ QC — "ส่ง QC รอผล" ทั้งหมด + ยังไม่ปิดงาน
    public function datatable(Request $request)
    {
        $search    = trim((string) $request->get('search'));
        $date      = $request->get('date');
        // ตัวกรองผล QC: '' = ทั้งหมด · 'none' = ยังไม่ตรวจ (qc_status IS NULL) · เลข = id ของ tb_qc_status
        $qcFilter  = trim((string) $request->get('qc_status'));

        $jobs = Planning::query()
            ->leftJoin('tb_planning_header', 'tb_planning_header.id', '=', 'tb_planning.planning_header_id')
            ->leftJoin('customer', 'customer.code', '=', 'tb_planning_header.custno')
            ->leftJoin('tb_qc_status', 'tb_qc_status.id', '=', 'tb_planning.qc_status')
            // เฉพาะงานที่รอ QC เท่านั้น
            ->where('tb_planning.planning_status', self::QC_WAIT_STATUS)
            // ตัดงานที่ปิดแล้วออก: ปิดงานราย item (end_job='Y') หรือปิดออเดอร์ทั้ง header (end_order='Y')
            // นับ NULL เป็น "ยังไม่ปิด" ด้วย (NULL != 'Y' ใน MySQL ให้ผล NULL ไม่ใช่ true)
            ->where(function ($q) {
                $q->where('tb_planning.end_job', '!=', 'Y')
                    ->orWhereNull('tb_planning.end_job');
            })
            ->where(function ($q) {
                $q->where('tb_planning_header.end_order', '!=', 'Y')
                    ->orWhereNull('tb_planning_header.end_order');
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('tb_planning.red_bill_code', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning.itemno', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning.machine_no', 'LIKE', '%'.$search.'%');
                });
            })
            ->when(!empty($date), fn ($q) => $q->whereDate('tb_planning.inplan', $date))
            // ตัวกรองผล QC (ซ้อนบนเงื่อนไขเดิม — ยังคง planning_status = 'ส่ง QC รอผล' อยู่)
            ->when($qcFilter === 'none', fn ($q) => $q->whereNull('tb_planning.qc_status'))
            ->when($qcFilter !== '' && $qcFilter !== 'none',
                fn ($q) => $q->where('tb_planning.qc_status', (int) $qcFilter))
            ->orderBy('tb_planning.qc_date', 'asc')
            ->orderBy('tb_planning.qc_time', 'asc')
            ->orderBy('tb_planning.id', 'asc')
            ->get([
                'tb_planning.id',
                'tb_planning.red_bill_code',
                'tb_planning.itemno',
                'tb_planning.machine_no',
                'tb_planning.quantity',
                'tb_planning.inplan',
                'tb_planning.lot',
                'tb_planning.planning_status',
                'tb_planning.qc_date',
                'tb_planning.qc_time',
                'tb_planning.qc_datetime',
                'tb_qc_status.name as qc_status_name',
                'customer.name as cust_name',
            ]);

        return view('qc.planning.partials.table', [
            'jobs'  => $jobs,
            'total' => $jobs->count(),
        ]);
    }

    // Modal ดูรายละเอียด (อ่านอย่างเดียว) + ประวัติการเปลี่ยนสถานะล่าสุด
    public function detail(Request $request)
    {
        $job = $this->jobOrFail($request->get('id'));

        $logs = DB::table('tb_planning_status_log')
            ->where('planning_id', $job->id)
            ->orderBy('changed_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        return view('qc.planning.partials.detail', [
            'job'     => $job,
            'company' => $this->jobCompany($job),
            'logs'    => $logs,
        ]);
    }

    // Modal เลือก/บันทึกผล QC
    public function resultForm(Request $request)
    {
        $job = $this->jobOrFail($request->get('id'));

        return view('qc.planning.partials.result-form', [
            'job'      => $job,
            'statuses' => $this->qcStatusOptions(),
        ]);
    }

    // บันทึกผล QC — แตะเฉพาะ qc_status + qc_datetime (เวลาปัจจุบัน) ไม่แตะ planning_status
    public function resultUpdate(Request $request)
    {
        $request->validate([
            'id'        => 'required|integer',
            'qc_status' => 'required|integer|exists:tb_qc_status,id',
        ]);

        $job = $this->jobOrFail($request->get('id'));

        // บล็อกฝั่ง server: งานที่ปิดแล้ว ห้ามบันทึกผล (กัน bypass ฝั่ง client)
        if ($this->jobIsClosed($job)) {
            return response()->json([
                'status'     => 422,
                'message'    => 'งานนี้ถูกปิดแล้ว ไม่สามารถบันทึกผล QC ได้',
                'job_closed' => true, // ธงให้ฝั่งจอปิด modal + reload ตาราง
            ], 422);
        }

        // งานต้องยังอยู่ในสถานะ "ส่ง QC รอผล" (กันบันทึกงานที่หลุดจากคิวไปแล้ว)
        if ($job->planning_status !== self::QC_WAIT_STATUS) {
            return response()->json([
                'status'     => 422,
                'message'    => 'งานนี้ไม่ได้อยู่ในสถานะรอ QC แล้ว',
                'job_closed' => true, // ให้ฝั่งจอ reload ตาราง (งานหลุดจากคิว)
            ], 422);
        }

        $qcStatusId = (int) $request->get('qc_status');

        // แตะเฉพาะ 2 คอลัมน์นี้ (ไม่ใช้ update ทั้ง model)
        Planning::where('id', $job->id)->update([
            'qc_status'   => $qcStatusId,
            'qc_datetime' => now(),
        ]);

        return response()->json([
            'status'  => 200,
            'message' => 'บันทึกผล QC เรียบร้อย',
        ]);
    }
}
