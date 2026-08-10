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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls as WriterXls;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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

    /**
     * Query ของรายการ Pigment ตามเงื่อนไขค้นหาปัจจุบัน (ข้อความ / สถานะ / ช่วงวันที่)
     * ใช้ร่วมกันระหว่าง datatable และ export excel เพื่อให้ได้ชุดข้อมูลเดียวกันเสมอ
     */
    private function pigmentListQuery()
    {
        // กรองตามสถานะที่เลือก (รออนุมัติ / อนุมัติแล้ว / ไม่อนุมัติ) — ค่าว่าง = ทุกสถานะ
        $status = request('status');

        return $this->baseQuery()
            ->when(!empty($status), fn ($q) => $q->where('status', $status));
    }

    public function datatable()
    {
        $data = $this->pigmentListQuery();

        return DataTables::of($data)
            // # ใช้ DT_RowIndex จาก addIndexColumn() — เรียงตามลำดับที่แสดงจริง (รองรับ sort + pagination)
            ->addIndexColumn()
            ->addColumn('status_badge', fn ($row) => $this->statusBadge($row))
            // วันที่ขอ = วันที่สร้างรายการ (created_at)
            ->editColumn('created_at', fn ($row) => $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d/m/Y') : '-')
            ->editColumn('order_date', fn ($row) => $row->order_date ? \Carbon\Carbon::parse($row->order_date)->format('d/m/Y') : '-')
            ->editColumn('want_date', fn ($row) => $row->want_date ? \Carbon\Carbon::parse($row->want_date)->format('d/m/Y') : '-')
            ->addColumn('action', fn ($row) => $this->actionButtons($row))
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    /* ===================== Export Excel ===================== */

    /**
     * Export รายการ Pigment เป็นไฟล์ Excel — "ใบขอสั่ง PIGMENT"
     *
     * ใช้ query ชุดเดียวกับ datatable (pigmentListQuery) จึงได้ข้อมูลตามเงื่อนไขค้นหาที่เลือกอยู่
     * และดึงมาทุกแถว (ไม่แบ่งหน้า) — ต่างจาก datatable ที่ Yajra ตัดตามหน้าที่แสดง
     */
    public function exportExcel()
    {
        // export เรียงใหม่→เก่าตาม id เสมอ (baseQuery ไม่ได้ orderBy แล้ว จึงระบุที่นี่)
        $rows = $this->pigmentListQuery()->orderBy('tb_pigment.id', 'desc')->get();

        $headers = [
            'วันที่ขอ',
            'Item No.',
            'วันที่สั่ง',
            'วันที่ต้องการ',
            'แผนกที่สั่ง',
            'ใช้กับ Order',
            'ยอดคงเหลือ',
            'ยอดใช้ย้อนหลัง 2 เดือน',
            'น้ำหนักที่ใช้',
            'น้ำหนักที่จะสั่ง',
            'ผลการอนุมัติ',
        ];

        // ตำแหน่งคอลัมน์ที่ต้องจัดรูปแบบเป็นตัวเลขทศนิยม 2 ตำแหน่ง
        $numericColumns = ['G', 'H', 'I', 'J'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ใบขอสั่ง PIGMENT');

        $lastCol = 'K'; // 11 คอลัมน์ = A..K

        // แถวที่ 1: หัวเรื่อง
        $sheet->setCellValue('A1', 'ใบขอสั่ง PIGMENT');
        $sheet->mergeCells('A1:'.$lastCol.'1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // แถวที่ 2: เงื่อนไขที่ใช้ค้นหา + วันเวลาที่ export (ให้ตรวจสอบย้อนหลังได้ว่าไฟล์มาจากเงื่อนไขไหน)
        $sheet->setCellValue('A2', $this->exportConditionText().' | พิมพ์เมื่อ '.now()->format('d/m/Y H:i'));
        $sheet->mergeCells('A2:'.$lastCol.'2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setARGB('FF666666');

        // แถวที่ 3: หัวตาราง
        $headerRow = 3;
        $sheet->fromArray($headers, null, 'A'.$headerRow);
        $sheet->getStyle('A'.$headerRow.':'.$lastCol.$headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$headerRow.':'.$lastCol.$headerRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');
        $sheet->getStyle('A'.$headerRow.':'.$lastCol.$headerRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        // แถวข้อมูล
        $rowIndex = $headerRow + 1;
        foreach ($rows as $row) {
            $sheet->fromArray([
                $this->excelDate($row->created_at),
                $row->itemno,
                $this->excelDate($row->order_date),
                $this->excelDate($row->want_date),
                $row->custno,
                $row->orderno,
                $row->balance,
                $row->retrospective,
                $row->weight_request,
                $row->weight_production,
                $row->statusLabel(),
            ], null, 'A'.$rowIndex);

            $rowIndex++;
        }

        $lastRow = $rowIndex - 1;

        // ตีเส้นตาราง + จัดรูปแบบ เฉพาะเมื่อมีข้อมูลอย่างน้อย 1 แถว
        if ($lastRow >= $headerRow + 1) {
            $dataRange = 'A'.$headerRow.':'.$lastCol.$lastRow;

            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            // วันที่ + ผลการอนุมัติ จัดกึ่งกลาง
            foreach (['A', 'C', 'D', 'K'] as $col) {
                $sheet->getStyle($col.($headerRow + 1).':'.$col.$lastRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // คอลัมน์ตัวเลข แสดงทศนิยม 2 ตำแหน่ง ชิดขวา
            foreach ($numericColumns as $col) {
                $range = $col.($headerRow + 1).':'.$col.$lastRow;
                $sheet->getStyle($range)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        } else {
            $sheet->getStyle('A'.$headerRow.':'.$lastCol.$headerRow)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
        }

        // ปรับความกว้างคอลัมน์อัตโนมัติ
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->freezePane('A'.($headerRow + 1));

        $fileName = 'ใบขอสั่ง_PIGMENT_'.now()->format('Ymd_His').'.xls';

        // ส่งไฟล์ให้ดาวน์โหลดตรงๆ ไม่เขียนลงดิสก์ (ไม่ทิ้งไฟล์ค้างใน public/)
        return response()->streamDownload(function () use ($spreadsheet) {
            (new WriterXls($spreadsheet))->save('php://output');
        }, $fileName, [
            'Content-Type'  => 'application/vnd.ms-excel',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * แปลงค่าวันที่เป็น d/m/Y สำหรับใส่ในไฟล์ Excel (ค่าว่างคืนสตริงว่าง)
     */
    private function excelDate($value): string
    {
        return $value ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '';
    }

    /**
     * ข้อความสรุปเงื่อนไขค้นหาที่ใช้ export (แสดงใต้หัวเรื่องในไฟล์ Excel)
     */
    private function exportConditionText(): string
    {
        $parts = [];

        if (request()->filled('search')) {
            $parts[] = 'คำค้นหา: '.request('search');
        }

        $statusLabels = Pigment::$statusLabels;
        $parts[] = 'สถานะ: '.($statusLabels[request('status')] ?? 'ทุกสถานะ');

        if (request()->filled('date_start') || request()->filled('date_end')) {
            $fieldLabels = [
                'created_at' => 'วันที่ขอ',
                'order_date' => 'วันที่สั่ง',
                'want_date'  => 'วันที่ต้องการรับ',
            ];
            $field = $fieldLabels[request('date_field')] ?? $fieldLabels['created_at'];
            $start = request('date_start') ? $this->excelDate(request('date_start')) : '-';
            $end   = request('date_end') ? $this->excelDate(request('date_end')) : '-';

            $parts[] = $field.': '.$start.' ถึง '.$end;
        }

        return implode('  |  ', $parts);
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

        // ค้นหาช่วงวันที่: เลือกฟิลด์ได้เฉพาะ created_at (วันที่ขอ) / order_date (วันที่สั่ง) / want_date (วันที่ต้องการรับ)
        // ใช้ whitelist กัน SQL injection ที่ชื่อคอลัมน์ — ค่าเริ่มต้นตรงกับตัวเลือกแรกของ dropdown ในหน้า index
        $date_field = request('date_field');
        $date_field = in_array($date_field, ['created_at', 'order_date', 'want_date'], true) ? $date_field : 'created_at';
        $date_start = request('date_start');
        $date_end   = request('date_end');

        return Pigment::query()
            ->select(['tb_pigment.*'])
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('itemno', 'LIKE', '%'.$search.'%')
                      ->orWhere('custno', 'LIKE', '%'.$search.'%')
                      ->orWhere('orderno', 'LIKE', '%'.$search.'%');
                });
            })
            // กรองช่วงวันที่ตามฟิลด์ที่เลือก — ระบุด้านเดียวหรือทั้งช่วงก็ได้
            ->when(!empty($date_start), function ($q) use ($date_field, $date_start) {
                $q->whereDate('tb_pigment.'.$date_field, '>=', $date_start);
            })
            ->when(!empty($date_end), function ($q) use ($date_field, $date_end) {
                $q->whereDate('tb_pigment.'.$date_field, '<=', $date_end);
            });
        // หมายเหตุ: ไม่ hard-code orderBy ที่นี่แล้ว — ให้ Yajra จัดการเรียงตามที่คลิกหัวคอลัมน์ในหน้า datatable
        // (ลำดับเริ่มต้นกำหนดเป็น default order ใน DataTables; ส่วน export Excel ใส่ orderBy id desc เองใน exportExcel())
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
