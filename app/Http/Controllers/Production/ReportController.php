<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Machine;
use App\Models\Planning;

/**
 * รายงานการผลิต — เพิ่ม 2026-07-31
 *
 *  - machine()        : หน้ารายงานผลิตตามเครื่องจักร (ส่วนค้นหา + ผลลัพธ์)
 *  - machineOptions() : คืนเครื่องจักรตามแผนกที่เลือก (cascade dropdown)
 *  - employee()       : รายงานผลิตตามพนักงาน (placeholder)
 */
class ReportController extends Controller
{
    // รายชื่อแผนกที่เปิดใช้งาน (เติม dropdown แผนก) — value ใช้ "ชื่อแผนก" ให้ตรงกับ machine.dept
    private function activeDepartments()
    {
        return Department::where('is_active', 'Y')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);
    }

    public function machine()
    {
        return view('production-planning.report.machine', [
            'departments' => $this->activeDepartments(),
        ]);
    }

    // ตัวเลือกเครื่องจักรของแผนกที่เลือก (dept = ชื่อแผนกตรงกับ machine.dept)
    public function machineOptions(Request $request)
    {
        $dept = $request->get('dept');

        $machines = Machine::where('dept', $dept)
            ->orderBy('MBX', 'asc')
            ->get()
            ->map(fn (Machine $m) => [
                'id'    => $m->id,
                'code'  => $m->MBX,
                'label' => $m->displayLabel(),
            ])
            ->values();

        return response()->json([
            'status'   => 200,
            'machines' => $machines,
        ]);
    }

    // ตารางรายงานผลิตตามเครื่องจักร (ดึงจาก tb_planning เป็นหลัก) — จัดกลุ่มตามเครื่องจักร
    //  - แผนก       → tb_planning.company
    //  - เครื่องจักร  → tb_planning.machine_no (เก็บเป็น code เดียวกับ machine.MBX)
    //  - ช่วงวันที่   → tb_planning.inplan
    //
    // การจัดเรียง (ตามที่ผู้ใช้ต้องการ):
    //  - เรียงลำดับ "กลุ่มเครื่องจักร" ตาม inplan เก่าสุดในกลุ่ม (เก่าสุดขึ้นก่อน)
    //  - ภายในกลุ่ม เรียง inplan เก่าสุดขึ้นก่อน
    //  - แถว/กลุ่มที่ไม่มี inplan (NULL) จัดไว้ท้ายสุด
    public function machineTable(Request $request)
    {
        $company    = $request->get('dept');        // ชื่อแผนก (ตรงกับ tb_planning.company)
        $machine_no = $request->get('machine_no');  // code เครื่องจักร
        $date_start = $request->get('date_start');
        $date_end   = $request->get('date_end');

        $rows = Planning::query()
            ->leftJoin('tb_planning_header', 'tb_planning_header.id', '=', 'tb_planning.planning_header_id')
            // ชื่อลูกค้า: customer.code = header.custno
            ->leftJoin('customer', 'customer.code', '=', 'tb_planning_header.custno')
            ->select([
                'tb_planning.id',
                'tb_planning.machine_no',
                'tb_planning.inplan',
                'tb_planning.red_bill_code',
                'tb_planning.itemno',
                'tb_planning.lot',
                'tb_planning.quantity',
                'customer.name as cust_name',
            ])
            ->when(!empty($company), fn ($q) => $q->where('tb_planning.company', $company))
            ->when(!empty($machine_no), fn ($q) => $q->where('tb_planning.machine_no', $machine_no))
            ->when(!empty($date_start), fn ($q) => $q->whereDate('tb_planning.inplan', '>=', $date_start))
            ->when(!empty($date_end), fn ($q) => $q->whereDate('tb_planning.inplan', '<=', $date_end))
            ->get();

        // เปรียบเทียบวันที่ inplan (รูปแบบ Y-m-d เทียบ string ได้ตรงลำดับเวลา) — NULL/ว่าง ให้ไปท้ายสุด
        $compareInplan = function ($a, $b) {
            $a = $a ?: null;
            $b = $b ?: null;
            if ($a === $b) {
                return 0;
            }
            if ($a === null) {
                return 1;   // ไม่มีวันที่ → ท้ายสุด
            }
            if ($b === null) {
                return -1;
            }
            return strcmp($a, $b); // เก่าสุดขึ้นก่อน
        };

        // จัดกลุ่มตามเครื่องจักร (ค่าว่าง = "ไม่ระบุเครื่องจักร")
        $groups = $rows
            ->groupBy(fn ($r) => ($r->machine_no === null || $r->machine_no === '') ? '' : $r->machine_no)
            ->map(function ($items, $machine) use ($compareInplan) {
                // เรียงภายในกลุ่ม: inplan เก่าสุดก่อน (แถววันเดียวกันเรียงตาม id)
                $sorted = $items->sort(function ($x, $y) use ($compareInplan) {
                    $c = $compareInplan($x->inplan, $y->inplan);
                    return $c !== 0 ? $c : ($x->id <=> $y->id);
                })->values();

                // inplan เก่าสุดของกลุ่ม (ข้ามค่า NULL/ว่าง)
                $minInplan = $sorted->pluck('inplan')->filter()->sort()->first();

                return [
                    'machine' => $machine,
                    'min'     => $minInplan ?: null,
                    'items'   => $sorted,
                ];
            })
            // เรียงลำดับกลุ่มตาม inplan เก่าสุดในกลุ่ม (กลุ่มที่ไม่มีวันที่ไปท้ายสุด)
            ->sort(function ($a, $b) use ($compareInplan) {
                $c = $compareInplan($a['min'], $b['min']);
                return $c !== 0 ? $c : strcmp((string) $a['machine'], (string) $b['machine']);
            })
            ->values();

        return view('production-planning.report.partials.machine-table', [
            'groups' => $groups,
            'total'  => $rows->count(),
        ]);
    }

    public function employee()
    {
        return view('production-planning.report.employee');
    }
}
