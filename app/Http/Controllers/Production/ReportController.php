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
        ['groups' => $groups, 'total' => $total] = $this->buildMachineReport($request);

        return view('production-planning.report.partials.machine-table', [
            'groups' => $groups,
            'total'  => $total,
        ]);
    }

    // บันทึกลำดับคิวใหม่ (drag & drop) ของ 1 บล็อก = เครื่องจักรเดียว + วันเดียว — 2026-08-08
    //  - รับ ids: array ของ tb_planning.id ที่เรียงตามลำดับใหม่แล้ว (จากบนลงล่าง)
    //  - เขียน queue_sort = 1..N ตามลำดับที่ส่งมา
    //  - การกันลากข้ามวัน/ข้ามเครื่องทำที่ฝั่ง UI (SortableJS) — ที่นี่เชื่อชุด id ที่ส่งมา
    public function machineQueueReorder(Request $request)
    {
        $ids = $request->input('ids', []);

        if (! is_array($ids) || empty($ids)) {
            return response()->json(['status' => 422, 'message' => 'ไม่มีรายการให้จัดคิว'], 422);
        }

        $ids = array_values(array_filter(array_map('intval', $ids)));

        foreach ($ids as $i => $id) {
            Planning::where('id', $id)->update(['queue_sort' => $i + 1]);
        }

        return response()->json(['status' => 200, 'message' => 'บันทึกลำดับคิวแล้ว']);
    }

    // สร้างข้อมูลรายงาน (query + จัดกลุ่มตามเครื่องจักร) ใช้ร่วมกันทั้งตารางบนจอ / Excel / PDF
    private function buildMachineReport(Request $request): array
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
                'tb_planning.queue_sort', // ลำดับคิวที่จัดไว้ (ต่อเครื่อง+ต่อวัน) — NULL = ยังไม่จัดคิว
                'tb_planning.red_bill_code',
                'tb_planning.itemno',
                'tb_planning.lot',
                'tb_planning.quantity',
                'tb_planning.weight', // น้ำหนัก TP (Weight)
                'tb_planning.remark',
                'customer.name as cust_name',
                // Speed (RPM) ต่อเครื่องจักร: machine.speed_rpm (MBX = machine_no) — subquery กัน row ซ้ำ
                \Illuminate\Support\Facades\DB::raw('(SELECT m.speed_rpm FROM machine m WHERE m.MBX = tb_planning.machine_no LIMIT 1) AS speed_rpm'),
            ])
            ->when(!empty($company), fn ($q) => $q->where('tb_planning.company', $company))
            ->when(!empty($machine_no), fn ($q) => $q->where('tb_planning.machine_no', $machine_no))
            ->when(!empty($date_start), fn ($q) => $q->whereDate('tb_planning.inplan', '>=', $date_start))
            ->when(!empty($date_end), fn ($q) => $q->whereDate('tb_planning.inplan', '<=', $date_end))
            ->get();

        // ── สถานะวิธีการผลิต (tb_planning_prod_method): แนบขั้นตอนของแต่ละรายการผลิต ──
        // โหลดครั้งเดียวสำหรับทุก planning id แล้ว group ตาม planning_id
        $planningIds = $rows->pluck('id')->all();
        $stepsByPlanning = collect();
        if (!empty($planningIds)) {
            $stepsByPlanning = \Illuminate\Support\Facades\DB::table('tb_planning_prod_method as pm')
                ->leftJoin('tb_prod_method as m', 'm.id', '=', 'pm.prod_method_id')
                ->whereIn('pm.planning_id', $planningIds)
                ->orderBy('pm.work_date')
                ->orderBy('pm.start_time')
                ->orderBy('pm.sort')
                ->get([
                    'pm.planning_id',
                    'pm.work_date',
                    'pm.start_time',
                    'pm.end_time',
                    'pm.sort',
                    'm.name as method_name',
                ])
                ->groupBy('planning_id');
        }

        // แนบ steps + คำนวณ job_key (เวลาเริ่มขั้นตอนแรก; ถ้าไม่มีขั้นตอน = inplan 00:00) ให้แต่ละรายการ
        $rows->each(function ($it) use ($stepsByPlanning) {
            $steps = $stepsByPlanning->get($it->id, collect())->values();
            $it->steps = $steps;

            if ($steps->isNotEmpty()) {
                $first = $steps->first(); // เรียง work_date,start_time มาแล้วจาก query
                $wd = $first->work_date ? substr($first->work_date, 0, 10) : null;
                $st = $first->start_time ? substr($first->start_time, 0, 8) : '00:00:00';
                $it->job_key = $wd ? ($wd.' '.$st) : ($it->inplan ? substr($it->inplan, 0, 10).' 00:00:00' : null);
            } else {
                $it->job_key = $it->inplan ? substr($it->inplan, 0, 10).' 00:00:00' : null;
            }

            // วันของงาน (ใช้จัดกลุ่มคิวต่อวัน + กันลากข้ามวันฝั่ง UI) = วันที่ของ job_key
            $it->day_key    = $it->job_key ? substr($it->job_key, 0, 10) : null;
            $it->queue_sort = $it->queue_sort === null ? null : (int) $it->queue_sort;
        });

        // เปรียบเทียบ key เวลา (รูปแบบ Y-m-d H:i:s เทียบ string ได้ตรงลำดับเวลา) — NULL/ว่าง ให้ไปท้ายสุด
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
                // เรียงภายในกลุ่มเครื่อง:
                //   1) วันเก่าก่อน (day_key; ไม่มีวัน → ท้ายสุด)
                //   2) ภายในวันเดียวกัน: ลำดับคิวที่จัดไว้ (queue_sort) มาก่อน — ยังไม่จัดคิว (NULL) ไปท้ายวัน
                //   3) fallback: เวลาเริ่มขั้นตอน (job_key) แล้วตามด้วย id
                $sorted = $items->sort(function ($x, $y) use ($compareInplan) {
                    $c = $compareInplan($x->day_key, $y->day_key);
                    if ($c !== 0) {
                        return $c;
                    }

                    $qx = $x->queue_sort;
                    $qy = $y->queue_sort;
                    if ($qx !== null || $qy !== null) {
                        if ($qx === null) {
                            return 1;   // ยังไม่จัดคิว → ท้ายวัน
                        }
                        if ($qy === null) {
                            return -1;
                        }
                        if ($qx !== $qy) {
                            return $qx <=> $qy;
                        }
                    }

                    $c = $compareInplan($x->job_key, $y->job_key);
                    return $c !== 0 ? $c : ($x->id <=> $y->id);
                })->values();

                // job_key เก่าสุดของกลุ่ม (ข้ามค่า NULL/ว่าง)
                $minKey = $sorted->pluck('job_key')->filter()->sort()->first();

                return [
                    'machine' => $machine,
                    'min'     => $minKey ?: null,
                    'items'   => $sorted,
                ];
            })
            // เรียงลำดับกลุ่มตาม inplan เก่าสุดในกลุ่ม (กลุ่มที่ไม่มีวันที่ไปท้ายสุด)
            ->sort(function ($a, $b) use ($compareInplan) {
                $c = $compareInplan($a['min'], $b['min']);
                return $c !== 0 ? $c : strcmp((string) $a['machine'], (string) $b['machine']);
            })
            ->values();

        return [
            'groups'  => $groups,
            'total'   => $rows->count(),
            'filters' => [
                'dept'       => $company,
                'machine_no' => $machine_no,
                'date_start' => $date_start,
                'date_end'   => $date_end,
            ],
        ];
    }

    // Export Excel (.xlsx) — คงรูปแบบจัดกลุ่มตามเครื่องจักรเหมือนบนหน้าจอ
    public function machineExcel(Request $request)
    {
        ['groups' => $groups, 'total' => $total, 'filters' => $filters] = $this->buildMachineReport($request);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายงานผลิตตามเครื่องจักร');

        // คอลัมน์ตามฟอร์ม — คอลัมน์ที่ยังไม่มีข้อมูลใน tb_planning เว้นค่าว่างไว้ก่อน
        // (Revise, TP, Resin, CODE, Pack, Batch, สูตรตัวอย่าง)
        $headers = [
            '#', 'วันที่ลงแผน', 'Revise', 'Cust Name', 'เลขที่ใบเบิก', 'PRODUCT NO', 'LOT',
            'น้ำหนักออเดอร์', 'TP', 'Resin', 'CODE', 'Speed (RPM)', 'Pack', 'Batch',
            'สูตรตัวอย่าง', 'Remark',
        ];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'];
        $lastCol = 'P';
        $weightCol = 'H'; // คอลัมน์น้ำหนักออเดอร์ (ใช้ทำผลรวมต่อเครื่อง)

        // หัวรายงาน
        $sheet->setCellValue('A1', 'รายงานผลิตตามเครื่องจักร');
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', $this->filterSummary($filters, $total));
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // แถวหัวตาราง
        $r = 4;
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i].$r, $h);
        }
        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFont()->setBold(true);
        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E9ECEF');
        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getAlignment()->setWrapText(true);

        $rownum = 0;
        $r++;
        foreach ($groups as $group) {
            $machineLabel = $group['machine'] !== '' ? $group['machine'] : 'ไม่ระบุเครื่องจักร';

            // หัวกลุ่มเครื่องจักร
            $sheet->setCellValue("A{$r}", 'เครื่องจักร: '.$machineLabel);
            $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
            $sheet->getStyle("A{$r}")->getFont()->setBold(true);
            $sheet->getStyle("A{$r}")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F1F3F5');
            $r++;

            $groupSum = 0;
            foreach ($group['items'] as $it) {
                // แถวขั้นตอน "สถานะวิธีการผลิต / การล้าง" (แสดงก่อนแถวผลิต)
                foreach ($it->steps as $s) {
                    $sheet->setCellValue("A{$r}", '↳');
                    $sheet->setCellValue("B{$r}", $s->work_date ? \Carbon\Carbon::parse($s->work_date)->format('d/m/Y') : '');
                    $desc = 'ขั้นตอน: '.($s->method_name ?: '-')
                        .' ('.($s->start_time ? substr($s->start_time, 0, 5) : '--').'–'.($s->end_time ? substr($s->end_time, 0, 5) : '--').')';
                    $sheet->setCellValue("C{$r}", $desc);
                    $sheet->mergeCells("C{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F8F9FA');
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFont()->setItalic(true);
                    $r++;
                }

                // แถวผลิตสินค้า
                $sheet->setCellValue("A{$r}", ++$rownum);
                $sheet->setCellValue("B{$r}", $it->inplan ? \Carbon\Carbon::parse($it->inplan)->format('d/m/Y') : '-');
                $sheet->setCellValue("C{$r}", '');  // Revise (เว้นว่าง)
                $sheet->setCellValue("D{$r}", $it->cust_name ?: '-');
                $sheet->setCellValue("E{$r}", $it->red_bill_code ?: '-');
                $sheet->setCellValueExplicit("F{$r}", $it->itemno ?: '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue("G{$r}", $it->lot ?: '-');
                $sheet->setCellValue("H{$r}", $it->quantity !== null ? number_format($it->quantity, 2) : '-');
                $sheet->setCellValue("I{$r}", $it->weight !== null ? number_format($it->weight, 2) : '');  // TP = น้ำหนัก TP (Weight)
                $sheet->setCellValue("J{$r}", '');  // Resin (เว้นว่าง)
                $sheet->setCellValue("K{$r}", '');  // CODE (เว้นว่าง)
                $sheet->setCellValue("L{$r}", $it->speed_rpm ?: '');
                $sheet->setCellValue("M{$r}", '');  // Pack (เว้นว่าง)
                $sheet->setCellValue("N{$r}", '');  // Batch (เว้นว่าง)
                $sheet->setCellValue("O{$r}", '');  // สูตรตัวอย่าง (เว้นว่าง)
                $sheet->setCellValue("P{$r}", $it->remark ?: '');
                $groupSum += (float) ($it->quantity ?? 0);
                $r++;
            }

            // แถวรวมต่อเครื่องจักร
            $sheet->setCellValue("A{$r}", 'รวม '.$machineLabel);
            $sheet->mergeCells("A{$r}:G{$r}");
            $sheet->setCellValue("{$weightCol}{$r}", number_format($groupSum, 2));
            $sheet->setCellValue("F{$r}", $group['items']->count().' รายการ');
            $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFont()->setBold(true);
            $r++;
        }

        if ($total === 0) {
            $sheet->setCellValue("A{$r}", 'ไม่พบข้อมูลตามเงื่อนไขที่เลือก');
            $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'report-machine-'.now()->format('Ymd-His').'.xls';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet))->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    // Export PDF — คงรูปแบบจัดกลุ่มตามเครื่องจักรเหมือนบนหน้าจอ
    public function machinePdf(Request $request)
    {
        ['groups' => $groups, 'total' => $total, 'filters' => $filters] = $this->buildMachineReport($request);

        $html = view('production-planning.report.partials.machine-pdf', [
            'groups'  => $groups,
            'total'   => $total,
            'summary' => $this->filterSummary($filters, $total),
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L', // แนวนอน (คอลัมน์เยอะตามฟอร์ม)
            'margin_left'   => 6,
            'margin_right'  => 6,
            'margin_top'    => 8,
            'margin_bottom' => 8,
        ]);
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="report-machine.pdf"',
        ]);
    }

    // ข้อความสรุปเงื่อนไขค้นหา (ใช้บนหัว Excel/PDF)
    private function filterSummary(array $filters, int $total): string
    {
        $parts = [];
        $parts[] = 'แผนก: '.($filters['dept'] ?: 'ทุกแผนก');
        $parts[] = 'เครื่องจักร: '.($filters['machine_no'] ?: 'ทุกเครื่องจักร');

        $ds = $filters['date_start'] ? \Carbon\Carbon::parse($filters['date_start'])->format('d/m/Y') : '-';
        $de = $filters['date_end'] ? \Carbon\Carbon::parse($filters['date_end'])->format('d/m/Y') : '-';
        $parts[] = 'ช่วงวันที่ Inplan: '.$ds.' - '.$de;
        $parts[] = 'พบ '.number_format($total).' รายการ';

        return implode('   |   ', $parts);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  รายงานผลิตตามพนักงาน (time-grid รายวัน) — 2026-08-11
    //
    //  รูปแบบตามฟอร์มกระดาษ "แผนและการผลิตจริง": 1 แถวกลุ่ม = 1 พนักงาน,
    //  คอลัมน์ = ช่วงเวลา 9 ช่อง (8-9 … 16-17, OT — ข้ามพักเที่ยง 12-13)
    //  ในแต่ละช่องมี รหัสสี / จำนวน / รหัสเครื่อง / วิธีการผลิต
    //
    //  แหล่งข้อมูล:
    //   - พนักงาน  → tb_planning.empno (→ emp.empname/empsur)
    //   - รหัสสี    → tb_planning.itemno   | จำนวน → tb_planning.quantity
    //   - เครื่อง   → tb_planning.machine_no
    //   - วิธี+เวลา → tb_planning_prod_method (method_name + start_time/end_time)
    //  งานที่ไม่ระบุพนักงาน (empno ว่าง) → รวมกลุ่ม "ไม่ระบุพนักงาน" ต่อท้ายสุด
    // ─────────────────────────────────────────────────────────────────────────
    public function employee()
    {
        return view('production-planning.report.employee', [
            'departments' => $this->activeDepartments(),
        ]);
    }

    // ตัวเลือกพนักงานของแผนกที่เลือก (dept = ชื่อแผนกตรงกับ emp.dept)
    public function employeeOptions(Request $request)
    {
        $dept = $request->get('dept');

        $employees = \App\Models\Emp::when(!empty($dept), fn ($q) => $q->where('dept', $dept))
            ->orderBy('empname', 'asc')
            ->get(['empno', 'empname', 'empsur'])
            ->map(fn ($e) => [
                'empno' => $e->empno,
                'label' => trim(($e->empname ?? '').' '.($e->empsur ?? '')) ?: $e->empno,
            ])
            ->values();

        return response()->json([
            'status'    => 200,
            'employees' => $employees,
        ]);
    }

    // ตาราง time-grid (AJAX)
    public function employeeTable(Request $request)
    {
        ['groups' => $groups, 'slots' => $slots, 'total' => $total] = $this->buildEmployeeReport($request);

        return view('production-planning.report.partials.employee-table', [
            'groups' => $groups,
            'slots'  => $slots,
            'total'  => $total,
        ]);
    }

    // นิยามช่วงเวลา (หน่วยเป็นนาทีจากเที่ยงคืน) — ข้ามพักเที่ยง 12:00-13:00
    private function timeSlots(): array
    {
        return [
            ['label' => '8:00-9:00',   'start' => 480,  'end' => 540],
            ['label' => '9:00-10:00',  'start' => 540,  'end' => 600],
            ['label' => '10:00-11:00', 'start' => 600,  'end' => 660],
            ['label' => '11:00-12:00', 'start' => 660,  'end' => 720],
            ['label' => '13:00-14:00', 'start' => 780,  'end' => 840],
            ['label' => '14:00-15:00', 'start' => 840,  'end' => 900],
            ['label' => '15:00-16:00', 'start' => 900,  'end' => 960],
            ['label' => '16:00-17:00', 'start' => 960,  'end' => 1020],
            ['label' => 'OT',          'start' => 1020, 'end' => 1440],
        ];
    }

    // แปลง "HH:MM[:SS]" → นาทีจากเที่ยงคืน (null ถ้าว่าง/รูปแบบผิด)
    private function timeToMin(?string $t): ?int
    {
        if (empty($t) || !preg_match('/^(\d{1,2}):(\d{2})/', $t, $m)) {
            return null;
        }

        return ((int) $m[1]) * 60 + (int) $m[2];
    }

    // รวมค่าซ้ำ/ค่าว่างออก คืนเป็น array ของ string (ให้ view จัดรูปแบบเอง)
    private function uniqueValues(array $arr): array
    {
        return array_values(array_unique(array_filter(
            array_map(fn ($v) => trim((string) $v), $arr),
            fn ($v) => $v !== ''
        )));
    }

    // สร้างข้อมูลรายงาน (query + จัดกลุ่มตามพนักงาน + วางลงกริดเวลา) ใช้ร่วมทั้งจอ/Excel/PDF
    private function buildEmployeeReport(Request $request): array
    {
        $company = $request->get('dept');   // ชื่อแผนก (ตรงกับ tb_planning.company)
        $empno   = $request->get('empno');
        $date    = $request->get('date') ?: now()->format('Y-m-d');

        // งานของวันนั้น: มีขั้นตอนผลิต work_date = วันนั้น หรือ inplan = วันนั้น
        $rows = Planning::query()
            ->leftJoin('emp', 'emp.empno', '=', 'tb_planning.empno')
            ->select([
                'tb_planning.id',
                'tb_planning.empno',
                'tb_planning.machine_no',
                'tb_planning.itemno',
                'tb_planning.quantity',
                'tb_planning.inplan',
                'tb_planning.company',
                'tb_planning.start_time as p_start',
                'tb_planning.end_time as p_end',
                'emp.empname',
                'emp.empsur',
            ])
            ->when(!empty($company), fn ($q) => $q->where('tb_planning.company', $company))
            ->when(!empty($empno), fn ($q) => $q->where('tb_planning.empno', $empno))
            ->where(function ($q) use ($date) {
                $q->whereDate('tb_planning.inplan', $date)
                  ->orWhereExists(function ($sub) use ($date) {
                      $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                          ->from('tb_planning_prod_method as pm2')
                          ->whereColumn('pm2.planning_id', 'tb_planning.id')
                          ->whereDate('pm2.work_date', $date);
                  });
            })
            ->get();

        // ขั้นตอนผลิตของวันนั้น (หรือ work_date ว่าง) สำหรับทุก planning id
        $ids = $rows->pluck('id')->all();
        $stepsByPlanning = collect();
        if (!empty($ids)) {
            $stepsByPlanning = \Illuminate\Support\Facades\DB::table('tb_planning_prod_method as pm')
                ->leftJoin('tb_prod_method as m', 'm.id', '=', 'pm.prod_method_id')
                ->whereIn('pm.planning_id', $ids)
                ->where(function ($q) use ($date) {
                    $q->whereDate('pm.work_date', $date)->orWhereNull('pm.work_date');
                })
                ->orderBy('pm.start_time')
                ->orderBy('pm.sort')
                ->get([
                    'pm.planning_id',
                    'pm.start_time',
                    'pm.end_time',
                    'pm.sort',
                    'm.name as method_name',
                ])
                ->groupBy('planning_id');
        }

        $slots = $this->timeSlots();

        $groups = $rows
            ->groupBy(fn ($r) => ($r->empno === null || $r->empno === '') ? '' : $r->empno)
            ->map(function ($items, $empno) use ($stepsByPlanning, $slots) {
                $first = $items->first();
                $name  = trim(($first->empname ?? '').' '.($first->empsur ?? ''));
                $label = $empno === '' ? 'ไม่ระบุพนักงาน' : ($name ?: $empno);

                // เตรียมเซลล์ว่างทั้ง 9 ช่อง
                $cells = [];
                foreach ($slots as $i => $s) {
                    $cells[$i] = ['color' => [], 'qty' => null, 'machine' => [], 'method' => []];
                }
                $unscheduled = [];

                foreach ($items as $job) {
                    $steps      = $stepsByPlanning->get($job->id, collect());
                    $placedCols = [];

                    // 1) วางตามเวลาในแต่ละขั้นตอนผลิต
                    foreach ($steps as $st) {
                        $a = $this->timeToMin($st->start_time);
                        $b = $this->timeToMin($st->end_time);
                        if ($a === null || $b === null || $b <= $a) {
                            continue;
                        }
                        foreach ($slots as $i => $slot) {
                            if ($a < $slot['end'] && $b > $slot['start']) {
                                $cells[$i]['method'][]  = $st->method_name ?: '';
                                $cells[$i]['machine'][] = $job->machine_no ?: '';
                                $cells[$i]['color'][]   = $job->itemno ?: '';
                                $placedCols[]           = $i;
                            }
                        }
                    }

                    // 2) fallback: ใช้เวลาระดับงาน (planning.start_time/end_time)
                    if (empty($placedCols)) {
                        $a = $this->timeToMin($job->p_start);
                        $b = $this->timeToMin($job->p_end);
                        if ($a !== null && $b !== null && $b > $a) {
                            $methodNames = $this->uniqueValues($steps->pluck('method_name')->all());
                            foreach ($slots as $i => $slot) {
                                if ($a < $slot['end'] && $b > $slot['start']) {
                                    foreach ($methodNames as $mn) {
                                        $cells[$i]['method'][] = $mn;
                                    }
                                    $cells[$i]['machine'][] = $job->machine_no ?: '';
                                    $cells[$i]['color'][]   = $job->itemno ?: '';
                                    $placedCols[]           = $i;
                                }
                            }
                        }
                    }

                    // 3) จำนวน: ใส่ในช่องแรกสุดที่งานนี้ครอบครอง
                    if (!empty($placedCols)) {
                        $fc = min($placedCols);
                        if ($cells[$fc]['qty'] === null && $job->quantity !== null) {
                            $cells[$fc]['qty'] = $job->quantity;
                        }
                    } else {
                        // ไม่มีเวลาเลย → แยกไปแสดงเป็น "รายการไม่ระบุเวลา"
                        $unscheduled[] = [
                            'color'   => $job->itemno,
                            'qty'     => $job->quantity,
                            'machine' => $job->machine_no,
                            'method'  => implode(', ', $this->uniqueValues($steps->pluck('method_name')->all())),
                        ];
                    }
                }

                // แปลง array ในเซลล์ให้เหลือค่าไม่ซ้ำ (view จัด <br> / \n เอง)
                foreach ($cells as $i => $c) {
                    $cells[$i]['color']   = $this->uniqueValues($c['color']);
                    $cells[$i]['machine'] = $this->uniqueValues($c['machine']);
                    $cells[$i]['method']  = $this->uniqueValues($c['method']);
                }

                return [
                    'empno'       => $empno,
                    'label'       => $label,
                    'cells'       => $cells,
                    'unscheduled' => $unscheduled,
                    'job_count'   => $items->count(),
                ];
            })
            // เรียงตามชื่อพนักงาน กลุ่ม "ไม่ระบุ" ไปท้ายสุด
            ->sortBy(fn ($g) => $g['empno'] === '' ? 'zzzz' : mb_strtolower($g['label']))
            ->values();

        return [
            'groups'  => $groups,
            'slots'   => $slots,
            'total'   => $rows->count(),
            'filters' => [
                'dept'  => $company,
                'empno' => $empno,
                'date'  => $date,
            ],
        ];
    }

    // ข้อความสรุปเงื่อนไขค้นหา (ใช้บนหัว Excel/PDF ของรายงานตามพนักงาน)
    private function employeeFilterSummary(array $filters, int $total): string
    {
        $parts = [];
        $parts[] = 'แผนก: '.($filters['dept'] ?: 'ทุกแผนก');

        if (!empty($filters['empno'])) {
            $parts[] = 'พนักงาน: '.$filters['empno'];
        }

        $d = $filters['date'] ? \Carbon\Carbon::parse($filters['date'])->format('d/m/Y') : '-';
        $parts[] = 'วันที่: '.$d;
        $parts[] = 'พบ '.number_format($total).' รายการ';

        return implode('   |   ', $parts);
    }

    // Export PDF — ฟอร์ม time-grid เหมือนกระดาษ (A4 แนวนอน)
    public function employeePdf(Request $request)
    {
        ['groups' => $groups, 'slots' => $slots, 'total' => $total, 'filters' => $filters] = $this->buildEmployeeReport($request);

        $html = view('production-planning.report.partials.employee-pdf', [
            'groups'  => $groups,
            'slots'   => $slots,
            'total'   => $total,
            'dept'    => $filters['dept'] ?: '',
            'date'    => $filters['date'],
            'summary' => $this->employeeFilterSummary($filters, $total),
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L',
            'margin_left'   => 6,
            'margin_right'  => 6,
            'margin_top'    => 8,
            'margin_bottom' => 8,
        ]);
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="report-employee.pdf"',
        ]);
    }

    // Export Excel (.xls) — คงรูปแบบ time-grid เหมือนบนหน้าจอ
    public function employeeExcel(Request $request)
    {
        ['groups' => $groups, 'slots' => $slots, 'total' => $total, 'filters' => $filters] = $this->buildEmployeeReport($request);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายงานผลิตตามพนักงาน');

        // คอลัมน์: A = ป้ายกำกับแถว, B..J = ช่วงเวลา 9 ช่อง
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $lastCol = 'J';

        $sheet->setCellValue('A1', 'รายงานผลิตตามพนักงาน');
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', $this->employeeFilterSummary($filters, $total));
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // หัวตาราง (ช่วงเวลา)
        $r = 4;
        $sheet->setCellValue("A{$r}", '');
        foreach ($slots as $k => $slot) {
            $sheet->setCellValue($cols[$k + 1].$r, $slot['label']);
        }
        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFont()->setBold(true);
        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E9ECEF');
        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $r++;

        $rowLabels = [
            'color'   => 'รหัสสี',
            'qty'     => 'จำนวน',
            'machine' => 'รหัสเครื่อง',
            'method'  => 'วิธีการผลิต',
        ];

        foreach ($groups as $group) {
            // หัวกลุ่มพนักงาน
            $sheet->setCellValue("A{$r}", 'พนักงาน: '.$group['label'].'  ('.$group['job_count'].' รายการ)');
            $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
            $sheet->getStyle("A{$r}")->getFont()->setBold(true);
            $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('DCE7F7');
            $r++;

            foreach ($rowLabels as $key => $label) {
                $sheet->setCellValue("A{$r}", $label);
                foreach ($slots as $k => $slot) {
                    $c = $group['cells'][$k];
                    if ($key === 'qty') {
                        $val = $c['qty'] !== null ? number_format($c['qty'], 2).' KG' : '';
                    } else {
                        $val = implode("\n", $c[$key]);
                    }
                    $sheet->setCellValueExplicit($cols[$k + 1].$r, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getAlignment()->setWrapText(true)->setVertical(
                    \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP
                );
                $r++;
            }

            // 2 แถวเว้นว่างสำหรับเซ็นมือ
            foreach (['ผู้ทวนสอบ/เวลา', 'ผู้ผลิต'] as $label) {
                $sheet->setCellValue("A{$r}", $label);
                $r++;
            }

            // รายการไม่ระบุเวลา (ถ้ามี)
            if (!empty($group['unscheduled'])) {
                foreach ($group['unscheduled'] as $u) {
                    $txt = 'ไม่ระบุเวลา: รหัสสี '.($u['color'] ?: '-')
                        .' | จำนวน '.($u['qty'] !== null ? number_format($u['qty'], 2).' KG' : '-')
                        .' | เครื่อง '.($u['machine'] ?: '-')
                        .' | วิธี '.($u['method'] ?: '-');
                    $sheet->setCellValue("A{$r}", $txt);
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setItalic(true);
                    $r++;
                }
            }

            $r++; // เว้น 1 แถวระหว่างพนักงาน
        }

        if ($total === 0) {
            $sheet->setCellValue("A{$r}", 'ไม่พบข้อมูลตามเงื่อนไขที่เลือก');
            $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getColumnDimension('A')->setWidth(16);
        foreach (array_slice($cols, 1) as $col) {
            $sheet->getColumnDimension($col)->setWidth(14);
        }

        $fileName = 'report-employee-'.now()->format('Ymd-His').'.xls';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet))->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }
}
