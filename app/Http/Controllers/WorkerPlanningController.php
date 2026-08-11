<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Planning;
use App\Models\PlanningStatus;
use App\Services\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * หน้าพนักงานหน้างาน (Worker) — อัพเดทสถานะงานผลิต "ของตัวเอง" (11/08/2569)
 *
 *  ความปลอดภัย: ทุกเมธอดกรอง/ตรวจ tb_planning.empno = empno ของผู้ล็อกอินที่ฝั่ง server เสมอ
 *  (middleware 'worker' คุมชั้นแรกว่าเป็น Worker จริง)
 */
class WorkerPlanningController extends Controller
{
    // empno ของพนักงานที่ล็อกอินอยู่ (Worker)
    private function currentEmpno(): ?string
    {
        $account = AccessControl::currentAccount();

        return $account?->empno;
    }

    // ดึง planning ที่ต้องเป็น "งานของผู้ล็อกอิน" เท่านั้น (ไม่ใช่ → 403)
    private function ownJobOrFail($id): Planning
    {
        $empno = $this->currentEmpno();

        $job = Planning::with('planning_header')
            ->where('id', $id)
            ->where('empno', $empno)
            ->first();

        if (!$job) {
            abort(403, 'ไม่ใช่งานของคุณ');
        }

        return $job;
    }

    // แผนกของงาน (ใช้ของ item ก่อน ถ้าว่าง fallback ไป header)
    private function jobCompany(Planning $job): ?string
    {
        return $job->company ?: optional($job->planning_header)->company;
    }

    // สถานะที่เลือกได้ = ทุกสถานะที่เปิดใช้งานของแผนกงานนั้น
    private function statusesForJob(Planning $job)
    {
        $company = $this->jobCompany($job);
        $dept_id = Department::where('name', $company)->value('id');

        return PlanningStatus::where('dept', $dept_id)
            ->where('is_active', 'Y')
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'name']);
    }

    public function index()
    {
        $account = AccessControl::currentAccount();
        $name    = trim(($account->empname ?? '').' '.($account->empsur ?? '')) ?: $account->empno;

        return view('worker.planning.index', [
            'worker_name' => $name,
        ]);
    }

    // ตารางงานของตัวเอง — ค้นหารวม (red_bill_code / itemno / machine_no) + กรองวัน inplan
    public function datatable(Request $request)
    {
        $empno  = $this->currentEmpno();
        $search = trim((string) $request->get('search'));
        $date   = $request->get('date');

        $jobs = Planning::query()
            ->leftJoin('tb_planning_header', 'tb_planning_header.id', '=', 'tb_planning.planning_header_id')
            ->leftJoin('customer', 'customer.code', '=', 'tb_planning_header.custno')
            ->where('tb_planning.empno', $empno)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('tb_planning.red_bill_code', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning.itemno', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning.machine_no', 'LIKE', '%'.$search.'%');
                });
            })
            ->when(!empty($date), fn ($q) => $q->whereDate('tb_planning.inplan', $date))
            ->orderBy('tb_planning.inplan', 'desc')
            ->orderBy('tb_planning.id', 'desc')
            ->get([
                'tb_planning.id',
                'tb_planning.red_bill_code',
                'tb_planning.itemno',
                'tb_planning.machine_no',
                'tb_planning.quantity',
                'tb_planning.inplan',
                'tb_planning.lot',
                'tb_planning.planning_status',
                'customer.name as cust_name',
            ]);

        return view('worker.planning.partials.table', [
            'jobs'  => $jobs,
            'total' => $jobs->count(),
        ]);
    }

    // Modal ดูรายละเอียด (อ่านอย่างเดียว) + ประวัติการเปลี่ยนสถานะล่าสุด
    public function detail(Request $request)
    {
        $job = $this->ownJobOrFail($request->get('id'));

        $logs = DB::table('tb_planning_status_log')
            ->where('planning_id', $job->id)
            ->orderBy('changed_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        return view('worker.planning.partials.detail', [
            'job'     => $job,
            'company' => $this->jobCompany($job),
            'logs'    => $logs,
        ]);
    }

    // Modal เลือก/อัพเดทสถานะ
    public function statusForm(Request $request)
    {
        $job = $this->ownJobOrFail($request->get('id'));

        return view('worker.planning.partials.status-form', [
            'job'      => $job,
            'statuses' => $this->statusesForJob($job),
        ]);
    }

    // บันทึกสถานะใหม่ — แตะเฉพาะ planning_status + เขียน log (ตรวจ ownership ซ้ำ)
    public function statusUpdate(Request $request)
    {
        $request->validate([
            'id'     => 'required|integer',
            'status' => 'required|string|max:255',
        ]);

        $job   = $this->ownJobOrFail($request->get('id'));
        $empno = $this->currentEmpno();
        $new   = trim((string) $request->get('status'));

        // ต้องเป็นสถานะที่เปิดใช้งานของแผนกงานนี้เท่านั้น (กันยิงค่าตามใจ)
        $allowed = $this->statusesForJob($job)->pluck('name')->all();
        if (!in_array($new, $allowed, true)) {
            return response()->json([
                'status'  => 422,
                'message' => 'สถานะไม่ถูกต้องสำหรับแผนกของงานนี้',
            ], 422);
        }

        $old = $job->planning_status;

        if ($old !== $new) {
            DB::transaction(function () use ($job, $old, $new, $empno) {
                // แตะเฉพาะคอลัมน์สถานะ (ไม่ใช้ update ทั้ง model)
                Planning::where('id', $job->id)->update(['planning_status' => $new]);

                DB::table('tb_planning_status_log')->insert([
                    'planning_id' => $job->id,
                    'old_status'  => $old,
                    'new_status'  => $new,
                    'changed_by'  => $empno,
                    'changed_at'  => now(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            });
        }

        return response()->json([
            'status'  => 200,
            'message' => 'อัพเดทสถานะเรียบร้อย',
        ]);
    }
}
