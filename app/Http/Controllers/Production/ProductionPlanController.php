<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\PlanningHeader;
use App\Models\Planning;
use App\Models\SemiPigment;
use App\Models\Pigment;
use App\Models\Machine;
use App\Models\PlanningStatus;
use App\Models\Department;
use App\Models\Emp;
use App\Models\ProdMethod;
use App\Models\PlanningProdMethod;
use App\Services\HolidayService;

class ProductionPlanController extends Controller
{
    public function index()
    {
        $departments = Department::where('is_active', 'Y')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return view('production-planning.planning.index', [
            'departments' => $departments,
            // วันหยุด (tb_holiday ที่เปิดใช้งาน) + วันหยุดประจำสัปดาห์ — ให้ JS เตือนตอนเลือกวันหยุด
            // ในช่องวันที่ของ modal "สร้างแผน (Semi)" ที่อยู่บนหน้านี้ (เปิดได้โดยไม่ต้องเปิดฟอร์มแก้ไข Item ก่อน)
            'holidays'    => HolidayService::activeMap(),
            'weekly_off'  => HolidayService::weeklyOff(),
        ]);
    }

    public function datatable()
    {
        $data = $this->dataQuery();

        return DataTables::of($data)
            // # ใช้ DT_RowIndex จาก addIndexColumn() — เรียงตามลำดับที่แสดงจริง (รองรับ sort + pagination)
            ->addIndexColumn()
            // แผนกจริงของ item: ใช้ของ item ก่อน ถ้าว่างจึง fallback ไปที่ header
            ->editColumn('company', fn ($row) => $row->company ?: $row->header_company)
            // Inplan: แสดงวันที่ แล้วขึ้นบรรทัดใหม่แสดงกะการผลิต (work_shift) ถ้ามี — เช่น "กะ A"
            ->editColumn('inplan', function ($row) {
                $date = $row->inplan ? \Carbon\Carbon::parse($row->inplan)->format('d/m/Y') : '-';
                if (!empty($row->work_shift)) {
                    $date .= '<br><span class="badge bg-label-info">กะ '.e($row->work_shift).'</span>';
                }
                return $date;
            })
            ->editColumn('custwant', fn ($row) => $row->custwant ? \Carbon\Carbon::parse($row->custwant)->format('d/m/Y') : '-')
            // วันเวลาที่บรรจุเสร็จ (packing_datetie) — เก็บเป็น datetime แสดง วัน/เดือน/ปี ชั่วโมง:นาที
            ->editColumn('packing_datetie', fn ($row) => $row->packing_datetie ? \Carbon\Carbon::parse($row->packing_datetie)->format('d/m/Y H:i') : '-')
            // เลขที่ใบเบิก: ดึงจาก red_bill_code (ว่าง = แสดง -)
            ->addColumn('red_bill_code', fn ($row) => $row->red_bill_code ?: '-')
            // สถานะภายใน: สถานะของ planning item แถวนี้เอง (planning_status ของแถว)
            // ไม่รวมสถานะของ item อื่นใน header เดียวกัน — item ที่ยังไม่มีสถานะ (เช่น R1/R2 ที่ปิดงานแล้ว)
            // ต้องแสดงเป็นว่าง ไม่ใช่ดึงสถานะของ item อื่น (เช่น R3) มาโชว์
            ->addColumn('inner_status', function ($row) {
                $text = ($row->planning_status !== null && $row->planning_status !== '')
                    ? e($row->planning_status)
                    : '-';

                // บรรทัดที่ 2: สถานะปิดงานของ item แถวนี้ (อ้างอิงคอลัมน์ end_job) — รูปแบบเดียวกับ badge กะ ใน Inplan
                $end_job_badge = ($row->end_job ?? 'N') === 'Y'
                    ? '<span class="badge bg-label-success">ปิดงาน</span>'
                    : '<span class="badge bg-label-warning">ยังไม่ปิดงาน</span>';

                return $text.'<br>'.$end_job_badge;
            })
            ->addColumn('btnedit', function($row) {
                $btn_view = '<button class="btn btn-sm btn-icon btn-info me-2 btn_view" data-planning_id="'.$row->id.'" title ="ลบ">
                    <i class="ti ti-eye text-white ti-sm"></i>
                </button>';
                $btn_edit = '<button class="btn btn-sm btn-icon btn-warning me-2 btn_edit" data-planning_id="'.$row->id.'" title ="แก้ไข">
                    <i class="ti ti-pencil text-white ti-sm"></i>
                </button>';

                // return $btn_view.$btn_edit;
                return $btn_edit;
            })
            // sort คอลัมน์แผนก = ตามแผนกจริงของ item (COALESCE item→header) ให้ตรงกับที่แสดง
            ->orderColumn('company', 'COALESCE(tb_planning.company, tb_planning_header.company) $1')
            ->rawColumns(['inplan', 'inner_status', 'btnedit']) // 👈 บอกให้ column นี้ render HTML
            ->make(true);
    }

    public function dataQuery()
    {
        $search = request('search');
        $company = request('company');
        $planning_status = request('planning_status');

        // สถานะปิดงาน (end_job): 'all' = ทั้งหมด (ไม่กรอง), 'Y' = ปิดงาน, 'N' = ยังไม่ปิดงาน
        // ค่าอื่น/ไม่ได้ส่งมา = ใช้ค่าเริ่มต้น 'N' (ยังไม่ปิดงาน)
        $end_job = request('end_job', 'N');
        $end_job = in_array($end_job, ['all', 'Y', 'N'], true) ? $end_job : 'N';

        // ค้นหาช่วงวันที่: เลือกฟิลด์ได้เฉพาะ inplan / custwant (whitelist กัน SQL injection ที่ชื่อคอลัมน์)
        $date_field = request('date_field');
        $date_field = in_array($date_field, ['inplan', 'custwant'], true) ? $date_field : 'inplan';
        $date_start = request('date_start');
        $date_end   = request('date_end');

        // ค้นหาตามวันเวลาบรรจุเสร็จ (แยกเป็นแถวของตัวเอง): ระบุวันที่บรรจุ + ช่วงเวลาเริ่ม–สิ้นสุดในวันนั้น
        $packing_date       = request('packing_date');
        $packing_time_start = request('packing_time_start');
        $packing_time_end   = request('packing_time_end');
        $has_packing_filter = !empty($packing_date);

        $data = Planning::
            leftJoin('tb_planning_header', 'tb_planning_header.id', '=', 'tb_planning.planning_header_id')
            ->select([
                'tb_planning.*', // มีคอลัมน์ company (ของ item) อยู่แล้ว
                // แผนกของ header ดึงมาเป็นชื่อแยก (header_company) กันชนกับ tb_planning.company
                // เมื่อ Yajra ห่อ query เป็น subquery นับจำนวนแถว MySQL ห้ามมีชื่อคอลัมน์ซ้ำใน derived table
                'tb_planning_header.company as header_company',
                'tb_planning_header.orderno as orderno',
                'tb_planning_header.planning_code as planning_code',
                'tb_planning_header.mdate as header_mdate',
            ])
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('tb_planning.machine_no', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning.itemno', 'LIKE', '%'.$search.'%')
                        // เลขที่ใบเบิกออกใบแดง (Red Bill)
                        ->orWhere('tb_planning.red_bill_code', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning_header.orderno', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning_header.planning_code', 'LIKE', '%'.$search.'%')
                        ->orWhere('tb_planning_header.custno', 'LIKE', '%'.$search.'%')
                        // ค้นหาจากพนักงานผู้รับผิดชอบ: รหัสพนักงาน หรือ ชื่อ/นามสกุล (ตาราง emp อ้างด้วย tb_planning.empno)
                        ->orWhere('tb_planning.empno', 'LIKE', '%'.$search.'%')
                        ->orWhereExists(function ($sub) use ($search) {
                            $sub->select(DB::raw(1))->from('emp')
                                ->whereColumn('emp.empno', 'tb_planning.empno')
                                ->where(function ($q) use ($search) {
                                    $q->where('emp.empname', 'LIKE', '%'.$search.'%')
                                        ->orWhere('emp.empsur', 'LIKE', '%'.$search.'%')
                                        // รองรับการพิมพ์ "ชื่อ นามสกุล" ติดกันเป็นข้อความเดียว
                                        ->orWhereRaw("CONCAT(emp.empname, ' ', emp.empsur) LIKE ?", ['%'.$search.'%']);
                                });
                        });
                });
            })
             ->when(!empty($company), function ($query) use ($company) {
                // กรองด้วยแผนกจริงของ item (item ก่อน แล้ว fallback header)
                $query->whereRaw('COALESCE(tb_planning.company, tb_planning_header.company) = ?', [$company]);
            })
            // กรองด้วยสถานะการวางแผน — สถานะผูกกับแผนก (ค่าที่ส่งมาเป็นชื่อสถานะของแผนกที่เลือก)
            ->when(!empty($planning_status), function ($query) use ($planning_status) {
                $query->where('tb_planning.planning_status', $planning_status);
            })
            // กรองด้วยสถานะปิดงาน — 'ยังไม่ปิดงาน' นับรวมค่า NULL/ว่าง ด้วย
            ->when($end_job === 'Y', function ($query) {
                $query->where('tb_planning.end_job', 'Y');
            })
            ->when($end_job === 'N', function ($query) {
                $query->where(function ($query) {
                    $query->where('tb_planning.end_job', '!=', 'Y')
                        ->orWhereNull('tb_planning.end_job');
                });
            })
            // กรองช่วงวันที่ตามฟิลด์ที่เลือก (inplan / custwant) — ระบุด้านเดียวหรือทั้งช่วงก็ได้
            ->when(!empty($date_start), function ($query) use ($date_field, $date_start) {
                $query->whereDate('tb_planning.'.$date_field, '>=', $date_start);
            })
            ->when(!empty($date_end), function ($query) use ($date_field, $date_end) {
                $query->whereDate('tb_planning.'.$date_field, '<=', $date_end);
            })
            // กรองตามวันเวลาบรรจุเสร็จ: เฉพาะวันที่บรรจุที่เลือก และถ้าระบุช่วงเวลาก็กรองตามเวลาในวันนั้น
            ->when($has_packing_filter, function ($query) use ($packing_date, $packing_time_start, $packing_time_end) {
                $query->whereDate('tb_planning.packing_datetie', '=', $packing_date);
                if (!empty($packing_time_start)) {
                    $query->whereTime('tb_planning.packing_datetie', '>=', $packing_time_start);
                }
                if (!empty($packing_time_end)) {
                    $query->whereTime('tb_planning.packing_datetie', '<=', $packing_time_end);
                }
            })
            // ลำดับเริ่มต้น (เฉพาะเมื่อผู้ใช้ยังไม่คลิก sort หัวคอลัมน์ = ไม่มี order[] จาก DataTables):
            //   ปกติเรียงตาม id ล่าสุด; ถ้าค้นตามวันเวลาบรรจุเสร็จให้เรียงตามวันเวลานั้นก่อน แล้วตามด้วย id
            // เมื่อผู้ใช้คลิก sort (มี order[]) → ปล่อยให้ Yajra จัดการ ไม่ใส่ default ทับ (ไม่งั้น sort ไม่มีผล)
            ->when(empty(request('order')), function ($query) use ($has_packing_filter) {
                if ($has_packing_filter) {
                    $query->orderBy('tb_planning.packing_datetie', 'desc');
                }
                $query->orderBy('tb_planning.id', 'desc');
            });

        // $data = $data->get();
        // foreach($data as $value){
        //     dd($value->planning_header);
        // }

        return $data;
    }

    // ประกอบข้อมูลสำหรับ export (Excel/PDF) — ดึงตาม dataQuery() (เงื่อนไขค้นหาชุดเดียวกับตาราง)
    // แล้วเติมค่าที่คำนวณฝั่ง PHP ให้ตรงกับที่ datatable() แสดง: company จริง (item→header) และ inner_status
    // ไม่ส่ง order[] มา จึงเรียงตาม default ของ dataQuery() (id ล่าสุด / packing เมื่อกรอง)
    private function buildReportRows()
    {
        $rows = $this->dataQuery()->get();

        foreach ($rows as $row) {
            // แผนกจริง: ของ item ก่อน ถ้าว่างจึง fallback ไปที่ header
            $row->company_display = $row->company ?: $row->header_company;

            // สถานะภายใน: สถานะของ item แถวนี้เอง (ไม่รวมของ item อื่นใน header เดียวกัน)
            $row->inner_status_text = ($row->planning_status !== null && $row->planning_status !== '')
                ? $row->planning_status
                : '-';
            $row->end_job_label = ($row->end_job ?? 'N') === 'Y' ? 'ปิดงาน' : 'ยังไม่ปิดงาน';
        }

        return $rows;
    }

    // ป้ายชื่อคอลัมน์สำหรับไฟล์ export (ใช้ร่วม Excel/PDF)
    private function reportHeaders(): array
    {
        return [
            '#', 'Orderno', 'เลขที่ใบเบิก', 'Company', 'Inplan', 'Custwant',
            'วันเวลาบรรจุเสร็จ', 'Itemno', 'Quantity', 'MachineNo', 'สถานะภายใน',
        ];
    }

    public function excel(Request $request)
    {
        $rows = $this->buildReportRows();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('วางแผนการผลิต');

        $headers = $this->reportHeaders();
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
        $lastCol = 'K';

        // หัวรายงาน
        $sheet->setCellValue('A1', 'รายงานวางแผนการผลิต');
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // แถวหัวตาราง
        $r = 3;
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i].$r, $h);
        }
        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFont()->setBold(true);
        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E9ECEF');
        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getAlignment()->setWrapText(true);
        // Inplan (E) พื้นน้ำเงิน, Custwant (F) พื้นแดง — ให้เหมือนฝั่งเว็บ
        $sheet->getStyle("E{$r}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CFE2FF');
        $sheet->getStyle("E{$r}")->getFont()->getColor()->setRGB('084298');
        $sheet->getStyle("F{$r}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F8D7DA');
        $sheet->getStyle("F{$r}")->getFont()->getColor()->setRGB('842029');

        $rownum = 0;
        $r++;
        foreach ($rows as $row) {
            $inplan   = $row->inplan ? \Carbon\Carbon::parse($row->inplan)->format('d/m/Y') : '-';
            if (!empty($row->work_shift)) {
                $inplan .= ' (กะ '.$row->work_shift.')';
            }
            $custwant = $row->custwant ? \Carbon\Carbon::parse($row->custwant)->format('d/m/Y') : '-';
            $packing  = $row->packing_datetie ? \Carbon\Carbon::parse($row->packing_datetie)->format('d/m/Y H:i') : '-';
            $status   = $row->inner_status_text.' ('.$row->end_job_label.')';

            $sheet->setCellValue("A{$r}", ++$rownum);
            $sheet->setCellValueExplicit("B{$r}", $row->orderno ?: '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$r}", $row->red_bill_code ?: '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("D{$r}", $row->company_display ?: '-');
            $sheet->setCellValue("E{$r}", $inplan);
            $sheet->getStyle("E{$r}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CFE2FF');
            $sheet->getStyle("E{$r}")->getFont()->getColor()->setRGB('084298');
            $sheet->setCellValue("F{$r}", $custwant);
            $sheet->getStyle("F{$r}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F8D7DA');
            $sheet->getStyle("F{$r}")->getFont()->getColor()->setRGB('842029');
            $sheet->setCellValue("G{$r}", $packing);
            $sheet->setCellValueExplicit("H{$r}", $row->itemno ?: '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("I{$r}", $row->quantity !== null ? number_format($row->quantity, 2) : '-');
            $sheet->setCellValueExplicit("J{$r}", $row->machine_no ?: '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("K{$r}", $status);
            $r++;
        }

        if ($rows->isEmpty()) {
            $sheet->setCellValue("A{$r}", 'ไม่พบข้อมูลตามเงื่อนไขที่เลือก');
            $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'planning-'.now()->format('Ymd-His').'.xls';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet))->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function pdf(Request $request)
    {
        $rows = $this->buildReportRows();

        $html = view('production-planning.planning.partials.report-pdf', [
            'rows'    => $rows,
            'headers' => $this->reportHeaders(),
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L', // แนวนอน (คอลัมน์เยอะ)
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
            'Content-Disposition' => 'inline; filename="planning.pdf"',
        ]);
    }

    public function edit()
    {
        $planning_id = request('planning_id');
        $planning_header_id = request('planning_header_id');

        $planning_header = null;

        if ($planning_id) {
            $planning = Planning::find($planning_id);
            if ($planning) {
                $planning_header = PlanningHeader::with('plannings.subHeadersRecursive')
                    ->find($planning->planning_header_id);
            }
        } elseif ($planning_header_id) {
            $planning_header = PlanningHeader::with('plannings.subHeadersRecursive')
                ->find($planning_header_id);
        }

        // เงื่อนไขปิดออเดอร์: end_job ของ item ทั้งต้นไม้ (recursive) ต้องเป็น 'Y' ครบทุกแถว
        $all_jobs_done = $planning_header ? $this->allEndJobsDone($planning_header) : false;

        $html = view('production-planning.planning.planning-form', [
            'planning_header' => $planning_header,
            'all_jobs_done'   => $all_jobs_done,
        ])->render();

        return response()->json([
            'status' => 200,
            'data' => $html
        ]);
    }

    public function editItem()
    {
        $planning_id        = request('planning_id');
        $planning_header_id = request('planning_header_id');

        $planning_item  = null;
        $semi_list      = [];
        $pigment_list   = [];
        $parent_orderno = null;
        $last_itemno    = null;

        $parent_header = null;

        if ($planning_id) {
            $planning_item = Planning::with('planning_header')->find($planning_id);

            if ($planning_item) {
                $parent_header  = $planning_item->planning_header;
                $parent_orderno = $parent_header?->orderno;

                // โหลดทุกรายการพร้อมสถานะ (รออนุมัติ = แก้ไขได้, อนุมัติ/ปฏิเสธ = ล็อกอ่านอย่างเดียว)
                $mapRow = function (SemiPigment $r) {
                    return [
                        'id'                  => $r->id,
                        'company'             => $r->company,
                        'mdate'               => !empty($r->order_date) ? $r->order_date : null,
                        'custwant'            => !empty($r->want_date) ? $r->want_date : null,
                        'custno'              => $r->custno,
                        'itemno'              => $r->itemno,
                        'semi_code'           => $r->semi_code,
                        'primary_color'       => $r->primary_color,
                        'weight_request'      => $r->weight_request,
                        'balance'             => $r->balance,
                        'lot_no'              => $r->lot_no,
                        'retrospective'       => $r->retrospective,
                        'increase_production' => $r->increase_production,
                        'weight_production'   => $r->weight_production,
                        'red_bill_code'       => $r->red_bill_code,
                        'status'              => $r->status,
                        'status_label'        => $r->statusLabel(),
                        // แผนการผลิตที่สร้างจาก semi นี้ (ถ้าอนุมัติแล้ว) — ใช้แสดงสถานะในคอลัมน์ "จัดการ"
                        'result_planning_id'  => $r->result_planning_id,
                        'plan_status'         => $r->result_planning?->planning_status,
                        // สถานะปิดงานของแผนที่สร้างจาก semi นี้ (คอลัมน์ end_job ของ tb_planning)
                        'plan_end_job'        => $r->result_planning?->end_job,
                        // สถานะปิดออเดอร์ของแผน semi (end_order ของ tb_planning_header) — ใช้เป็นเกณฑ์ปิด end_job ใบแม่
                        'plan_end_order'      => $r->result_planning?->planning_header?->end_order,
                        'plan_code'           => $r->result_planning?->planning_header?->planning_code,
                    ];
                };

                // เรียงให้ "รออนุมัติ" อยู่บนสุด แล้วตามด้วยอนุมัติ/ปฏิเสธ
                $orderByStatus = "FIELD(status, 'request', 'approved', 'reject')";

                $semi_list = SemiPigment::with('result_planning.planning_header')
                    ->where('planning_id', $planning_id)
                    ->where('type', 'semi')
                    ->orderByRaw($orderByStatus)
                    ->orderBy('id')
                    ->get()->map($mapRow)->values()->toArray();

                // Pigment แยกเป็นตารางของตัวเอง (tb_pigment) — ไม่มีฟิลด์ semi (company/semi_code/primary_color/lot_no/red_bill/increase)
                $mapPigmentRow = function (Pigment $r) {
                    return [
                        'id'                => $r->id,
                        'mdate'             => !empty($r->order_date) ? $r->order_date : null,
                        'custwant'          => !empty($r->want_date) ? $r->want_date : null,
                        'custno'            => $r->custno,
                        'itemno'            => $r->itemno,
                        'weight_request'    => $r->weight_request,
                        'balance'           => $r->balance,
                        'retrospective'     => $r->retrospective,
                        'weight_production' => $r->weight_production,
                        'status'            => $r->status,
                        'status_label'      => $r->statusLabel(),
                    ];
                };

                $pigment_list = Pigment::where('planning_id', $planning_id)
                    ->orderByRaw($orderByStatus)
                    ->orderBy('id')
                    ->get()->map($mapPigmentRow)->values()->toArray();
            }
        } elseif ($planning_header_id) {
            $parent_header  = PlanningHeader::find($planning_header_id);
            $parent_orderno = $parent_header?->orderno;

            // ตั้งต้น Item No. ให้ item ใหม่ ด้วย itemno ของ item ล่าสุดใน header เดียวกัน
            $last_itemno = Planning::where('planning_header_id', $planning_header_id)
                ->whereNotNull('itemno')
                ->where('itemno', '!=', '')
                ->orderByDesc('id')
                ->value('itemno');
        }

        // แผนกจริงของ item: ใช้แผนกที่ตั้งไว้ที่ item ก่อน ถ้าว่างจึง fallback ไปที่ header
        $item_company = ($planning_item && $planning_item->company)
            ? $planning_item->company
            : $parent_header?->company;

        $machines = Machine::where('dept', $item_company)->get();

        // สถานะ Planning ตามแผนก (company) ของ item — เฉพาะที่เปิดใช้งาน
        // dept ใน tb_planning_status เก็บเป็น id ของ tb_departments จึง map ชื่อแผนก (company) → id ก่อน
        $dept_id = \App\Models\Department::where('name', $item_company)->value('id');
        $planning_statuses = PlanningStatus::where('dept', $dept_id)
            ->where('is_active', 'Y')
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // รายชื่อแผนก (department) สำหรับ dropdown Company ใน modal เพิ่ม Semi/Pigment
        $departments = Department::where('is_active', 'Y')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        // เงื่อนไขปิดงาน (end_job): งาน Semi ของ item นี้ต้องจบงานครบก่อนจึงจะติ๊กได้ (item ใหม่ยังไม่มี semi → true)
        $item_semi_jobs_done = $planning_item ? $this->itemSemiJobsDone($planning_item) : true;

        // พนักงานผู้รับผิดชอบ: รายชื่อพนักงานในแผนก (emp.dept = company ของ item)
        $employees    = $this->employeesByDept($item_company);
        $selected_emp = $planning_item?->empno ? Emp::find($planning_item->empno) : null;

        // สถานะวิธีการผลิต: master (dropdown) + แถวเดิมของ item
        $prod_methods     = ProdMethod::where('is_active', 'Y')
            ->orderBy('sort', 'asc')->orderBy('name', 'asc')
            ->get(['id', 'name']);
        $prod_method_rows = $planning_item ? $planning_item->prodMethods : collect();

        $html = view('production-planning.planning.planning-item-form', [
            'planning_item'      => $planning_item,
            'planning_header_id' => $planning_header_id,
            'parent_header'      => $parent_header,
            'semi_list'          => $semi_list,
            'pigment_list'       => $pigment_list,
            'parent_orderno'     => $parent_orderno,
            'last_itemno'        => $last_itemno,
            'machines' => $machines,
            'planning_statuses' => $planning_statuses,
            'departments' => $departments,
            'item_semi_jobs_done' => $item_semi_jobs_done,
            'employees'    => $employees,
            'selected_emp' => $selected_emp,
            'prod_methods'     => $prod_methods,
            'prod_method_rows' => $prod_method_rows,
            // วันหยุด (tb_holiday ที่เปิดใช้งาน) + วันหยุดประจำสัปดาห์ — ให้ JS เตือนตอนเลือกวันหยุด
            'holidays'         => HolidayService::activeMap(),
            'weekly_off'       => HolidayService::weeklyOff(),
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html
        ]);
    }

    // รายชื่อพนักงานผู้รับผิดชอบตามแผนก (emp.dept = ชื่อแผนก) เฉพาะที่เปิดใช้งาน
    private function employeesByDept(?string $dept)
    {
        if (!$dept) {
            return collect();
        }

        return Emp::where('dept', $dept)
            ->where('is_active', 'Y')
            ->orderBy('empname', 'asc')
            ->get(['empno', 'empname', 'empsur']);
    }

    // คืนตัวเลือกเครื่องจักร + สถานะ Planning + พนักงาน ตามแผนก (company) ที่เลือก
    // ใช้เมื่อผู้ใช้เปลี่ยน dropdown แผนกในโมดัลแก้ไข item เพื่อโหลดชุดตัวเลือกใหม่
    public function deptOptions(Request $request)
    {
        $company = $request->get('company');

        // code = ค่าที่บันทึกลง machine_no, label = รหัส + ความเร็วรอบสำหรับแสดงใน dropdown
        $machines = Machine::where('dept', $company)
            ->get()
            ->map(fn (Machine $m) => [
                'code'  => $m->MBX,
                'label' => $m->displayLabel(),
            ])
            ->values();

        // dept ใน tb_planning_status เก็บเป็น id ของ tb_departments จึง map ชื่อแผนก → id ก่อน
        $dept_id = Department::where('name', $company)->value('id');
        $statuses = PlanningStatus::where('dept', $dept_id)
            ->where('is_active', 'Y')
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->pluck('name')
            ->values();

        $employees = $this->employeesByDept($company)->map(fn ($e) => [
            'empno' => $e->empno,
            'name'  => trim($e->empname.' '.$e->empsur),
        ])->values();

        return response()->json([
            'status'    => 200,
            'machines'  => $machines,
            'statuses'  => $statuses,
            'employees' => $employees,
        ]);
    }

    public function saveItem(Request $request)
    {
        // ช่องตัวเลขในฟอร์มแสดงผลด้วย number_format จึงอาจมีจุลภาคหลักพัน (เช่น "1,250.00")
        // ตัดจุลภาคออกก่อน validate ไม่งั้น rule numeric จะไม่ผ่าน
        foreach (['quantity', 'weight', 'weight_produced', 'weight_packing'] as $numeric_field) {
            if ($request->filled($numeric_field)) {
                $request->merge([
                    $numeric_field => str_replace(',', '', $request->input($numeric_field)),
                ]);
            }
        }

        $validator = Validator::make($request->all(), [
            'planning_header_id' => 'required|exists:tb_planning_header,id',
            'company'            => 'nullable|string|max:255',
            'itemno'             => 'required|string|max:255',
            'quantity'           => 'nullable|numeric|min:0',
            'lot'                => 'nullable|string|max:255',
            'weight'             => 'nullable|numeric|min:0',
            'weight_produced'    => 'nullable|numeric|min:0',
            'weight_packing'     => 'nullable|numeric|min:0',
            'red_bill_code'      => 'nullable|string|max:255',
            'cycles'             => 'nullable|string|max:25',
            'end_job'            => 'nullable|in:Y,N',
            'empno'              => 'nullable|string|max:50|exists:emp,empno',
            'machine_no'         => 'nullable|string|max:255',
            'plan_type'          => 'nullable|string|max:255',
            'planning_status'    => 'nullable|string|max:255',
            'inplan'         => 'nullable|date',
            'work_shift'         => 'nullable|in:A,B,C',
            'start_date'         => 'nullable|date',
            'start_time'         => 'nullable|date_format:H:i',
            'end_date'           => 'nullable|date',
            'end_time'           => 'nullable|date_format:H:i',
            'qc_date'            => 'nullable|date',
            'qc_time'            => 'nullable|string|max:10',
            'qc_status'          => 'nullable|string|max:255',
            'packing_datetie'    => 'nullable|string|max:255',
            'pack_remark'        => 'nullable|string|max:1000',
            'shortage_remark'    => 'nullable|string|max:1000',
            'mdate'              => 'nullable|date',
            'custwant'           => 'nullable|date',
            'senddate'           => 'nullable|date',
            // สถานะวิธีการผลิต (การ์ดสีน้ำเงิน) — array คู่ขนาน
            'prod_method_id'     => 'nullable|array',
            'prod_method_id.*'   => 'nullable|integer|exists:tb_prod_method,id',
            'prod_method_date.*' => 'nullable|date',
            'prod_method_start.*'=> 'nullable|date_format:H:i',
            'prod_method_end.*'  => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ]);
        }

        // ออเดอร์ถูกปิดแล้ว (end_order = Y) → ห้ามเพิ่ม/แก้ไข Planning Item ของ header นี้
        // ตรวจซ้ำฝั่ง server กันการ bypass attribute disabled ฝั่ง client (ทั้งเพิ่มใหม่และแก้ไข)
        $target_header = PlanningHeader::find($request->planning_header_id);
        if ($target_header && ($target_header->end_order ?? 'N') === 'Y') {
            return response()->json([
                'status'  => 422,
                'message' => 'ออเดอร์นี้ถูกปิดแล้ว (End Order) ไม่สามารถเพิ่มหรือแก้ไข Planning ได้',
            ]);
        }

        $fields = $request->only([
            'planning_header_id', 'company', 'itemno', 'quantity', 'lot', 'weight',
            'weight_produced', 'weight_packing', 'red_bill_code', 'cycles', 'end_job', 'empno',
            'machine_no', 'plan_type', 'planning_status', 'inplan', 'work_shift', 'start_date', 'start_time', 'end_date', 'end_time',
            'qc_date', 'qc_time', 'qc_status', 'packing_datetie', 'pack_remark',
            'mdate', 'custwant', 'senddate', 'remark', 'shortage_remark'
        ]);

        // หมายเหตุ: Semi / Pigment ไม่ถูกบันทึกที่นี่อีกต่อไป
        // ย้ายไปบันทึกลงฐานข้อมูลทันทีผ่าน modal เพิ่ม/แก้ไข Semi (SemiPigmentController::entryStore/entryUpdate)

        $planning_id = $request->planning_id;
        $is_update   = !empty($planning_id);

        // เงื่อนไขปิดงาน (end_job = Y): ถ้า item มีคำขอ Semi แผน Semi ทุกใบต้องถูกปิดออเดอร์ (end_order = Y) ก่อน
        // ตรวจซ้ำฝั่ง server กันการ bypass attribute disabled ฝั่ง client
        if (($request->end_job ?? 'N') === 'Y' && $is_update) {
            $item = Planning::find($planning_id);
            if ($item && !$this->itemSemiJobsDone($item)) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'ยังปิดงานไม่ได้ เพราะแผน Semi ยังปิดออเดอร์ (End Order) ไม่ครบทุกใบ',
                ]);
            }
        }

        // ธงบอกว่ามีการปิดออเดอร์ให้อัตโนมัติในรอบนี้หรือไม่ (ใช้ประกอบข้อความตอบกลับ)
        $auto_closed_order = false;

        DB::beginTransaction();
        try {
            // 1) บันทึก / อัปเดต planning หลัก
            if ($is_update) {
                // ถ้าวันที่ส่งสินค้า (senddate) เปลี่ยนไปจากเดิม → เก็บค่าเดิมไว้ใน senddate_log (สะสมต่อท้าย คั่นด้วย comma)
                // และบันทึก "เวลาที่เปลี่ยนล่าสุด" ทับลง senddate_changed_at (ค่าเดียว ใช้สำหรับค้นหา)
                // เก็บเฉพาะตอนที่มีค่าเดิมอยู่แล้ว ($old_send) — ถ้าตอนแรกว่างแล้วเพิ่งใส่ค่าใหม่ จะไม่เก็บ
                $existing = Planning::find($planning_id);
                if ($existing) {
                    $old_send = $existing->senddate ? \Carbon\Carbon::parse($existing->senddate)->format('Y-m-d') : null;
                    $new_send = !empty($fields['senddate']) ? \Carbon\Carbon::parse($fields['senddate'])->format('Y-m-d') : null;
                    if ($old_send && $old_send !== $new_send) {
                        $fields['senddate_log'] = $existing->senddate_log
                            ? $existing->senddate_log . ',' . $old_send
                            : $old_send;

                        $fields['senddate_changed_at'] = now()->format('Y-m-d H:i:s');
                    }

                    // ถ้าย้ายแผนก (company เปลี่ยน) → เครื่องจักร/สถานะเดิมเป็นของแผนกเก่า ล้างทิ้งเพื่อกันค่าที่ไม่ตรงแผนกใหม่
                    // (กันกรณี JS ฝั่งหน้าเว็บไม่ได้ล้างให้)
                    // ⚠ ล้างเฉพาะเมื่อ "เคยมีแผนกเดิมอยู่จริง" แล้วถูกเปลี่ยนไปแผนกอื่นเท่านั้น
                    //   item ที่เพิ่งสร้างจาก Order (convertplanning) มี company = NULL แต่ฟอร์มเติมแผนกจาก header ให้
                    //   ถ้าไม่กันด้วย $old_company !== null การบันทึกครั้งแรกจะถูกเข้าใจผิดว่า "ย้ายแผนก" (NULL → "MB")
                    //   แล้วล้าง machine_no/planning_status ที่ผู้ใช้เพิ่งกรอกทิ้ง (ครั้งที่สองถึงบันทึกได้ เพราะ company ถูกตั้งแล้ว)
                    $old_company = $existing->company ?: null;
                    $new_company = !empty($fields['company']) ? $fields['company'] : null;
                    if ($old_company !== null && $old_company !== $new_company) {
                        $fields['machine_no']      = null;
                        $fields['planning_status'] = null;
                    }
                }

                Planning::where('id', $planning_id)->update($fields);
            } else {
                $created     = Planning::create($fields);
                $planning_id = $created->id;
            }

            // 2) sync สถานะวิธีการผลิต (การ์ดสีน้ำเงิน) — ลบของเดิมแล้ว insert ใหม่จาก array ที่ส่งมา
            $this->syncProdMethods($planning_id, $request);

            // 3) ปิดออเดอร์อัตโนมัติ (auto-close end_order):
            //    เมื่อรอบนี้เป็นการ "ปิดงาน" item (end_job='Y') และทำให้ทุก item ในต้นไม้ของ header
            //    จบงานครบ (allEndJobsDone) → ตั้ง end_order='Y' ให้เอง
            //    - ใช้ predicate เดิม allEndJobsDone ซึ่งเป็นด่านเดียวกับการกดปิดออเดอร์เอง จึงคงเงื่อนไขเดิม
            //    - ครอบคลุมทั้งกรณี header มี planning เดียว และกรณีเหลือ item สุดท้ายที่เพิ่งปิด
            //    - ทำเฉพาะขา "ปิด" เท่านั้น (ไม่ auto-ปลด) และไม่แตะ end_close (ที่ต้องมีหมายเหตุ)
            if (($request->end_job ?? 'N') === 'Y') {
                $header = PlanningHeader::find($fields['planning_header_id']);
                if ($header && ($header->end_order ?? 'N') !== 'Y' && $this->allEndJobsDone($header)) {
                    $header->update(['end_order' => 'Y']);
                    $auto_closed_order = true;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 500,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }

        $message = $is_update ? 'แก้ไขข้อมูล Planning สำเร็จ' : 'เพิ่มข้อมูล Planning สำเร็จ';
        if ($auto_closed_order) {
            $message .= ' และปิดออเดอร์ (End Order) อัตโนมัติแล้ว เพราะทุกรายการจบงานครบ';
        }

        return response()->json([
            'status'             => 200,
            'message'            => $message,
            'planning_header_id' => $fields['planning_header_id'],
            'end_order_auto_closed' => $auto_closed_order,
        ]);
    }

    /**
     * sync สถานะวิธีการผลิต (tb_planning_prod_method) ของ planning หนึ่ง ๆ
     * ลบแถวเดิมทั้งหมดแล้ว insert ใหม่จาก array คู่ขนานที่ส่งมา (ข้ามแถวที่ว่างทั้งหมด)
     * เรียกภายในทรานแซกชันของ saveItem
     */
    private function syncProdMethods($planning_id, Request $request): void
    {
        PlanningProdMethod::where('planning_id', $planning_id)->delete();

        $methods = $request->input('prod_method_id', []);
        $dates   = $request->input('prod_method_date', []);
        $starts  = $request->input('prod_method_start', []);
        $ends    = $request->input('prod_method_end', []);

        $count = max(count($methods), count($dates), count($starts), count($ends));

        for ($i = 0; $i < $count; $i++) {
            $method_id = $methods[$i] ?? null;
            $work_date = $dates[$i]   ?? null;
            $start     = $starts[$i]  ?? null;
            $end       = $ends[$i]    ?? null;

            // ข้ามแถวที่ว่างทั้งหมด
            if (empty($method_id) && empty($work_date) && empty($start) && empty($end)) {
                continue;
            }

            PlanningProdMethod::create([
                'planning_id'    => $planning_id,
                'prod_method_id' => $method_id ?: null,
                'work_date'      => $work_date ?: null,
                'start_time'     => $start ?: null,
                'end_time'       => $end ?: null,
                'sort'           => $i,
            ]);
        }
    }

    /**
     * รวบรวมค่า end_job ของ item ตรงของ header + item ของ sub-header (semi/pigment) ทุกชั้นแบบ recursive
     * (อ้างอิงรูปแบบเดียวกับ OrderPlanController::collectStatuses)
     */
    private function collectEndJobs($plannings, array &$jobs): void
    {
        foreach ($plannings as $p) {
            $jobs[] = $p->end_job;
            foreach ($p->subHeadersRecursive as $header) {
                $this->collectEndJobs($header->planningsRecursive, $jobs);
            }
        }
    }

    /**
     * true เมื่อมี item อย่างน้อย 1 รายการ และ end_job ทุกแถวที่เกี่ยวข้อง (recursive) = 'Y'
     */
    private function allEndJobsDone(PlanningHeader $header): bool
    {
        $header->loadMissing('plannings.subHeadersRecursive');
        $jobs = [];
        $this->collectEndJobs($header->plannings, $jobs);

        return count($jobs) > 0 && collect($jobs)->every(fn ($j) => $j === 'Y');
    }

    /**
     * true เมื่องาน Semi ของ item นี้ "จบงาน" ครบทุกรายการ (ใช้เป็นเงื่อนไขก่อนอนุญาตให้ปิดงาน end_job ของ item)
     * - ถ้า item ไม่มีคำขอ Semi เลย → ไม่มีข้อจำกัด (คืน true)
     *
     * เงื่อนไข (ตรงกับสถานะที่แสดงในคอลัมน์ "จัดการ" ของตาราง Semi ใน modal) — คำขอ Semi ที่ไม่ถูกปฏิเสธ
     * (status != reject) ทุกใบต้อง:
     *   1) "สร้างแผนการผลิตแล้ว" (มี result_planning_id) — ถ้ายังรออนุมัติ/อนุมัติแล้วแต่ยังไม่สร้างแผน → ยังปิดงานไม่ได้
     *   2) แผน Semi ที่สร้าง (tb_planning_header ของ result_planning) ต้อง "ปิดออเดอร์" (end_order = 'Y') แล้ว
     *
     * หมายเหตุ: เมื่อสร้างแผนจาก Semi ระบบสร้าง tb_planning_header (plan_type=semi) ให้ 1 ใบ ซึ่งมี end_order
     * ของตัวเอง — และ end_order = 'Y' ตั้งได้ก็ต่อเมื่อ end_job ของ item ในแผน semi นั้นครบแล้ว (allEndJobsDone)
     * จึงใช้ end_order เป็นสัญญาณ "แผน semi เสร็จสมบูรณ์" ที่ตรงความหมายกว่าการไล่เช็ค end_job รายตัว
     */
    private function itemSemiJobsDone(Planning $item): bool
    {
        $semis = SemiPigment::with('result_planning.planning_header')
            ->where('planning_id', $item->id)
            ->where('type', 'semi')
            ->where('status', '!=', SemiPigment::STATUS_REJECT)
            ->get();

        foreach ($semis as $semi) {
            // (1) ยังไม่ได้สร้างแผนการผลิต (รออนุมัติ / อนุมัติแล้วแต่ยังไม่สร้าง) → ยังปิดงานไม่ได้
            //     result_planning_id ว่าง = NULL หรือ 0 (ให้ตรงกับ !empty() ที่ใช้แสดงผลใน modal)
            if (empty($semi->result_planning_id)) {
                return false;
            }

            // (2) สร้างแผนแล้ว แต่แผน Semi นั้นยังไม่ถูกปิดออเดอร์ (end_order != 'Y') → ยังปิดงานไม่ได้
            $semi_header = $semi->result_planning?->planning_header;
            if (($semi_header->end_order ?? 'N') !== 'Y') {
                return false;
            }
        }

        return true;
    }

    /**
     * บันทึกสถานะปิดออเดอร์ (end_order) ของ PlanningHeader
     * - ปลด (N) ได้ตลอด
     * - ติ๊ก (Y) ได้ต่อเมื่อ end_job ของ item ที่เกี่ยวข้องทั้งต้นไม้เป็น 'Y' ครบ (ตรวจซ้ำฝั่ง server กัน bypass)
     */
    public function saveEndOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'planning_header_id' => 'required|exists:tb_planning_header,id',
            'end_order'          => 'required|in:Y,N',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ]);
        }

        $header = PlanningHeader::find($request->planning_header_id);

        // ตรวจเงื่อนไขเฉพาะตอนจะติ๊กปิดออเดอร์ (Y); การปลด (N) ทำได้เสมอ
        if ($request->end_order === 'Y' && !$this->allEndJobsDone($header)) {
            return response()->json([
                'status'  => 422,
                'message' => 'ยังปิดออเดอร์ไม่ได้ เพราะยังมีรายการที่ยังไม่จบงาน (End Job)',
            ]);
        }

        $header->update(['end_order' => $request->end_order]);

        return response()->json([
            'status'             => 200,
            'message'            => $request->end_order === 'Y' ? 'ปิดออเดอร์สำเร็จ' : 'ยกเลิกการปิดออเดอร์แล้ว',
            'planning_header_id' => $header->id,
        ]);
    }

    /**
     * บันทึกสถานะปิดจบงาน (end_close) + หมายเหตุ (end_close_remark) ของ PlanningHeader
     * - ปิดจบงานได้เลย ไม่ต้องตรวจ End Job (ตามที่ผู้ใช้กำหนด) ต่างจาก end_order ปกติที่มี gate ของมันเอง
     * - end_close และ end_order เคลื่อนไหวพร้อมกัน (lockstep): ติ๊ก Y → end_order = Y, ปลด N → end_order = N
     * - เมื่อ end_close = Y ต้องมีหมายเหตุ (end_close_remark)
     */
    public function saveEndClose(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'planning_header_id' => 'required|exists:tb_planning_header,id',
            'end_close'          => 'required|in:Y,N',
            'end_close_remark'   => 'required_if:end_close,Y|nullable|string|max:1000',
        ], [
            'end_close_remark.required_if' => 'เมื่อปิดจบงาน ต้องระบุหมายเหตุการปิดจบงาน',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ]);
        }

        $header = PlanningHeader::find($request->planning_header_id);

        // ปิดจบงาน (end_close) บังคับให้ปิดออเดอร์ (end_order) ตามเสมอ / ปลดก็ปลดพร้อมกัน
        // end_close_date: ติ๊ก Y → บันทึกวันเวลาที่ปิด (คงเวลาปิดเดิมไว้ถ้ามีอยู่แล้ว) / ปลด N → ล้างค่า
        $header->update([
            'end_close'        => $request->end_close,
            'end_close_remark' => $request->end_close_remark,
            'end_order'        => $request->end_close,
            'end_close_date'   => $request->end_close === 'Y'
                ? ($header->end_close_date ?? now())
                : null,
        ]);

        return response()->json([
            'status'             => 200,
            'message'            => $request->end_close === 'Y' ? 'ปิดจบงานสำเร็จ' : 'ยกเลิกการปิดจบงานแล้ว',
            'planning_header_id' => $header->id,
        ]);
    }
}
