<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Planning;
use App\Models\PlanningPacking;
use App\Models\PlanningWarehouse;
use App\Models\PlanningStatus;
use App\Services\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * หน้าพนักงานหน้างาน (Worker) — อัพเดทสถานะงานผลิต (11/08/2569)
 *
 *  ขอบเขตงานที่เข้าถึงได้ (20/10/2569): งานที่จ่ายให้ตัวเอง (tb_planning.empno = ผู้ล็อกอิน)
 *  **หรือ** งานในแผนกที่ตัวเองสังกัด (แผนกของงาน = emp.dept) — ให้คนที่ทำแทนกรอกเลขที่ใบเบิกหางานเองได้
 *  โดยไม่ต้องย้ายชื่อพนักงานในแผน · ตารางเริ่มต้นยังแสดงเฉพาะงานของตัวเอง งานของคนอื่นในแผนก
 *  ต้องค้นด้วยเลขที่ใบเบิก (red_bill_code) เท่านั้น
 *
 *  ความปลอดภัย: ทุกเมธอดตรวจขอบเขตนี้ที่ฝั่ง server เสมอ (accessibleJobOrFail)
 *  (middleware 'worker' คุมชั้นแรกว่าเป็น Worker จริง)
 */
class WorkerPlanningController extends Controller
{
    // ชื่อสถานะ "ส่ง QC รอผล" (มีหลาย id ตามแผนก — id 8/22/32/36 — แต่ชื่อเหมือนกันทุกแผนก จึงจับคู่ด้วยชื่อ)
    // เข้าสถานะนี้ = เซ็ต qc_date/qc_time เป็นเวลาที่เปลี่ยน · ออกจากสถานะนี้ = คงค่าเดิมไว้ (ไม่ล้าง)
    private const QC_WAIT_STATUS = 'ส่ง QC รอผล';

    // empno ของพนักงานที่ล็อกอินอยู่ (Worker)
    private function currentEmpno(): ?string
    {
        $account = AccessControl::currentAccount();

        return $account?->empno;
    }

    // แผนกที่ผู้ล็อกอินสังกัด (emp.dept เก็บเป็นชื่อแผนก = ค่าเดียวกับ tb_planning.company)
    private function currentDept(): ?string
    {
        $dept = trim((string) (AccessControl::currentAccount()?->dept ?? ''));

        return $dept !== '' ? $dept : null;
    }

    // แผนกของงานใน SQL — ตรงกับ jobCompany() (ใช้ของ item ก่อน ว่างค่อยใช้ header)
    private const JOB_COMPANY_SQL = "COALESCE(NULLIF(tb_planning.company, ''), tb_planning_header.company)";

    // ดึง planning ที่ผู้ล็อกอินเข้าถึงได้ — งานของตัวเอง หรืองานในแผนกตัวเอง (ไม่ใช่ → 403)
    private function accessibleJobOrFail($id): Planning
    {
        $job = Planning::with('planning_header')->find($id);

        if (!$job || !$this->canAccess($job)) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงงานนี้ (ต้องเป็นงานในแผนกของคุณ)');
        }

        return $job;
    }

    private function canAccess(Planning $job): bool
    {
        $empno = $this->currentEmpno();
        if ($empno !== null && $job->empno === $empno) {
            return true;
        }

        $dept = $this->currentDept();

        return $dept !== null && $this->jobCompany($job) === $dept;
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

    // สถานะที่เลือกได้ = ทุกสถานะที่เปิดใช้งานของแผนกงานนั้น
    private function statusesForJob(Planning $job)
    {
        $company = $this->jobCompany($job);
        $dept_id = Department::where('name', $company)->value('id');

        return PlanningStatus::where('dept', $dept_id)
            ->where('is_active', 'Y')
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'name', 'is_packing', 'is_warehouse']);
    }

    public function index()
    {
        $account = AccessControl::currentAccount();
        $name    = trim(($account->empname ?? '').' '.($account->empsur ?? '')) ?: $account->empno;

        return view('worker.planning.index', [
            'worker_name' => $name,
        ]);
    }

    // ตารางงาน + กรองวัน inplan
    //  - ไม่กรอกคำค้น = งานของตัวเอง (เหมือนเดิม)
    //  - กรอกคำค้น  = งานของตัวเองที่ตรงกับ เลขที่ใบเบิก/รหัสสี/รหัสเครื่อง
    //                 + งานของคนอื่นในแผนกตัวเองที่ตรงกับ "เลขที่ใบเบิก" (พิมพ์บางส่วนได้) — 20/10/2569
    public function datatable(Request $request)
    {
        $empno  = $this->currentEmpno();
        $dept   = $this->currentDept();
        $search = trim((string) $request->get('search'));
        $date   = $request->get('date');
        $like   = '%'.$search.'%';

        $jobs = Planning::query()
            ->leftJoin('tb_planning_header', 'tb_planning_header.id', '=', 'tb_planning.planning_header_id')
            ->leftJoin('customer', 'customer.code', '=', 'tb_planning_header.custno')
            ->leftJoin('emp', 'emp.empno', '=', 'tb_planning.empno')
            ->where(function ($q) use ($empno, $dept, $search, $like) {
                // งานของตัวเอง
                $q->where(function ($own) use ($empno, $search, $like) {
                    $own->where('tb_planning.empno', $empno)
                        ->when($search !== '', function ($w) use ($like) {
                            $w->where(function ($sub) use ($like) {
                                $sub->where('tb_planning.red_bill_code', 'LIKE', $like)
                                    ->orWhere('tb_planning.itemno', 'LIKE', $like)
                                    ->orWhere('tb_planning.machine_no', 'LIKE', $like);
                            });
                        });
                });
                // งานของคนอื่นในแผนกตัวเอง — ต้องค้นด้วยเลขที่ใบเบิกเท่านั้น
                if ($search !== '' && $dept !== null) {
                    $q->orWhere(function ($other) use ($dept, $like) {
                        $other->whereRaw(self::JOB_COMPANY_SQL.' = ?', [$dept])
                            ->where('tb_planning.red_bill_code', 'LIKE', $like);
                    });
                }
            })
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
            ->when(!empty($date), fn ($q) => $q->whereDate('tb_planning.inplan', $date))
            ->orderBy('tb_planning.inplan', 'desc')
            ->orderBy('tb_planning.id', 'desc')
            ->get([
                'tb_planning.id',
                'tb_planning.red_bill_code',
                'tb_planning.itemno',
                'tb_planning.machine_no',
                'tb_planning.weight', // น้ำหนัก TP (เดิมแสดง quantity = น้ำหนัก PO — เปลี่ยน 29/10/2569 ตามที่ผู้ใช้สั่ง)
                'tb_planning.inplan',
                'tb_planning.lot',
                'tb_planning.planning_status',
                'tb_planning.empno',
                'emp.empname',
                'emp.empsur',
                'customer.name as cust_name',
            ]);

        return view('worker.planning.partials.table', [
            'jobs'      => $jobs,
            'total'     => $jobs->count(),
            'my_empno'  => $empno,
            'searching' => $search !== '',
            'no_dept'   => $dept === null,
        ]);
    }

    // keep-alive ของ logout อัตโนมัติ — เวลาใช้งานล่าสุดถูกบันทึกใน middleware 'worker' แล้ว (20/10/2569)
    public function ping()
    {
        return response()->json(['status' => 200]);
    }

    // Modal ดูรายละเอียด (อ่านอย่างเดียว) + ประวัติการเปลี่ยนสถานะล่าสุด
    public function detail(Request $request)
    {
        $job = $this->accessibleJobOrFail($request->get('id'));

        // แนบชื่อผู้เปลี่ยน (changed_by = emp.empno) สำหรับคอลัมน์ "โดย" (22/10/2569)
        $logs = DB::table('tb_planning_status_log')
            ->leftJoin('emp', 'emp.empno', '=', 'tb_planning_status_log.changed_by')
            ->where('tb_planning_status_log.planning_id', $job->id)
            ->orderBy('tb_planning_status_log.changed_at', 'desc')
            ->orderBy('tb_planning_status_log.id', 'desc')
            ->limit(20)
            ->get(['tb_planning_status_log.*', 'emp.empname', 'emp.empsur']);

        return view('worker.planning.partials.detail', [
            'job'     => $job,
            'company' => $this->jobCompany($job),
            'logs'    => $logs,
        ]);
    }

    // Modal เลือก/อัพเดทสถานะ
    public function statusForm(Request $request)
    {
        $job = $this->accessibleJobOrFail($request->get('id'));

        return view('worker.planning.partials.status-form', [
            'job'          => $job,
            'statuses'     => $this->statusesForJob($job),
            // แถวบรรจุเดิมของงาน — แสดงในการ์ดบรรจุเมื่อเลือกสถานะที่ is_packing='Y' (22/10/2569)
            'packing_rows' => $job->packings,
            // แถวเข้าคลังเดิมของงาน — แสดงเมื่อเลือกสถานะที่ is_warehouse='Y' (29/10/2569)
            'warehouse_rows' => $job->warehouses,
        ]);
    }

    // บันทึกสถานะใหม่ — แตะเฉพาะ planning_status + เขียน log (ตรวจสิทธิ์เข้าถึงซ้ำ)
    //  changed_by = คนที่กดจริง (คนทำแทนก็บันทึกเป็นรหัสตัวเอง) · ไม่แตะ tb_planning.empno
    public function statusUpdate(Request $request)
    {
        $request->validate([
            'id'     => 'required|integer',
            'status' => 'required|string|max:255',
            // การบรรจุ (ส่งมาเฉพาะเมื่อเลือกสถานะที่ is_packing='Y') — array คู่ขนานแบบเดียวกับฟอร์ม Planning Item
            'packing_datetime'   => 'nullable|array',
            'packing_datetime.*' => 'nullable|date_format:Y-m-d H:i',
            'weight_packing'     => 'nullable|array',
            'weight_packing.*'   => 'nullable|string|max:255',
            'pack_remark'        => 'nullable|array',
            'pack_remark.*'      => 'nullable|string|max:1000',
            // การเข้าคลัง (ส่งมาเฉพาะเมื่อเลือกสถานะที่ is_warehouse='Y', 29/10/2569)
            'warehouse_datetime'   => 'nullable|array',
            'warehouse_datetime.*' => 'nullable|date_format:Y-m-d H:i',
            'weight_warehouse'     => 'nullable|array',
            'weight_warehouse.*'   => 'nullable|string|max:255',
            'warehouse_remark'     => 'nullable|array',
            'warehouse_remark.*'   => 'nullable|string|max:1000',
        ], [
            'packing_datetime.*.date_format' => 'รูปแบบวันเวลาที่บรรจุเสร็จไม่ถูกต้อง',
            'pack_remark.*.max'              => 'หมายเหตุการบรรจุยาวเกินไป',
            'warehouse_datetime.*.date_format' => 'รูปแบบวันเวลาที่เข้าคลังไม่ถูกต้อง',
            'warehouse_remark.*.max'           => 'หมายเหตุการเข้าคลังยาวเกินไป',
        ]);

        $job   = $this->accessibleJobOrFail($request->get('id'));
        $empno = $this->currentEmpno();
        $new   = trim((string) $request->get('status'));

        // บล็อกฝั่ง server: งานที่ปิดแล้ว (end_job='Y' หรือ end_order='Y') ห้ามเปลี่ยนสถานะ
        // กัน bypass ฝั่ง client (ยิงตรง / เปิด modal ค้างไว้แล้วงานถูกปิดทีหลัง) — ยึดค่าจริงใน DB
        if ($this->jobIsClosed($job)) {
            return response()->json([
                'status'     => 422,
                'message'    => 'งานนี้ถูกปิดแล้ว ไม่สามารถเปลี่ยนสถานะได้',
                'job_closed' => true, // ธงให้ฝั่งจอปิด modal + reload ตาราง (งานหลุดจากตารางไปแล้ว)
            ], 422);
        }

        // ต้องเป็นสถานะที่เปิดใช้งานของแผนกงานนี้เท่านั้น (กันยิงค่าตามใจ)
        $statusRow = $this->statusesForJob($job)->firstWhere('name', $new);
        if (!$statusRow) {
            return response()->json([
                'status'  => 422,
                'message' => 'สถานะไม่ถูกต้องสำหรับแผนกของงานนี้',
            ], 422);
        }

        // สถานะที่เปิดสถานะบรรจุ (is_packing='Y') → บันทึกการบรรจุด้วย (22/10/2569)
        // ยึดค่า is_packing จาก DB ไม่เชื่อฝั่งจอ · สถานะอื่น = ไม่แตะแถวบรรจุเลย
        $savePacking = ($statusRow->is_packing ?? 'N') === 'Y' && $request->has('packing_datetime');

        // สถานะที่เปิดสถานะเข้าคลัง (is_warehouse='Y') → บันทึกการเข้าคลังด้วย (29/10/2569) — กติกาเดียวกับการบรรจุ
        $saveWarehouse = ($statusRow->is_warehouse ?? 'N') === 'Y' && $request->has('warehouse_datetime');

        $old = $job->planning_status;

        if ($saveWarehouse) {
            // เพิ่มได้อย่างเดียว — แถวเดิมไม่ถูกแก้ไข/ลบ · ไม่รวมน้ำหนักไปช่องไหน
            DB::transaction(function () use ($job, $request) {
                PlanningWarehouse::appendRows(
                    $job->id,
                    (array) $request->input('warehouse_datetime', []),
                    (array) $request->input('weight_warehouse', []),
                    (array) $request->input('warehouse_remark', [])
                );
            });
        }

        if ($savePacking) {
            DB::transaction(function () use ($job, $request) {
                // เพิ่มได้อย่างเดียว — แถวเดิมไม่ถูกแก้ไข/ลบ (22/10/2569)
                $total = PlanningPacking::appendRows(
                    $job->id,
                    (array) $request->input('packing_datetime', []),
                    (array) $request->input('weight_packing', []),
                    (array) $request->input('pack_remark', [])
                );

                // น้ำหนักที่ผลิตได้ = ผลรวมน้ำหนักบรรจุทุกแถว (เหมือนฟอร์ม Planning Item) — ไม่มีน้ำหนักเลย = คงค่าเดิม
                if ($total !== null) {
                    Planning::where('id', $job->id)->update(['weight_produced' => $total]);
                }
            });
        }

        if ($old !== $new) {
            DB::transaction(function () use ($job, $old, $new, $empno) {
                // แตะเฉพาะคอลัมน์สถานะ (ไม่ใช้ update ทั้ง model)
                $fields = ['planning_status' => $new];

                // เข้าสู่ "ส่ง QC รอผล" → บันทึกวัน/เวลาที่เปลี่ยนสถานะลง qc_date / qc_time
                // (ออกจากสถานะนี้ไปสถานะอื่น = คงค่าเดิมไว้ ไม่ล้างเป็น null)
                if ($new === self::QC_WAIT_STATUS) {
                    $now = now();
                    $fields['qc_date'] = $now->toDateString();   // Y-m-d
                    $fields['qc_time'] = $now->format('H:i:s');  // TIME
                }

                Planning::where('id', $job->id)->update($fields);

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
