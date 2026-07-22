<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\SemiPigment;
use App\Models\Pigment;
use App\Models\Planning;
use App\Models\PlanningHeader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SemiPigmentController extends Controller
{
    /* ===================== หน้า: รออนุมัติ ===================== */

    public function index()
    {
        return view('production-planning.semi-pigment.index');
    }

    /**
     * Query ของรายการ Semi ตามเงื่อนไขค้นหาปัจจุบัน (ข้อความ / สถานะ / ช่วงวันที่)
     * ใช้ร่วมกันระหว่าง datatable และ export excel เพื่อให้ได้ชุดข้อมูลเดียวกันเสมอ
     */
    private function semiListQuery()
    {
        // หน้านี้แสดงเฉพาะ Semi แล้ว (Pigment แยกไปหน้า production.pigment.index)
        // กรองตามสถานะที่เลือก (รออนุมัติ / อนุมัติแล้ว / ไม่อนุมัติ) — ค่าว่าง = ทุกสถานะ
        $status = request('status');

        return $this->baseQuery()
            ->where('type', 'semi')
            ->when(!empty($status), fn ($q) => $q->where('status', $status));
    }

    public function datatable()
    {
        $data = $this->semiListQuery();

        return DataTables::of($data)
            ->addColumn('rownum', fn ($row) => $row->rownum)
            ->addColumn('type_badge', fn ($row) => $this->typeBadge($row))
            ->addColumn('status_badge', fn ($row) => $this->statusBadge($row))
            ->editColumn('order_date', fn ($row) => $row->order_date ? \Carbon\Carbon::parse($row->order_date)->format('d/m/Y') : '-')
            // วันที่ขอ = วันที่สร้างรายการ (created_at)
            ->editColumn('created_at', fn ($row) => $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d/m/Y') : '-')
            ->editColumn('want_date', fn ($row) => $row->want_date ? \Carbon\Carbon::parse($row->want_date)->format('d/m/Y') : '-')
            ->addColumn('action', fn ($row) => $this->actionButtons($row))
            ->rawColumns(['type_badge', 'status_badge', 'action'])
            ->make(true);
    }

    /* ===================== Export Excel ===================== */

    /**
     * Export รายการ Semi เป็นไฟล์ Excel — "ใบขอสั่งทำ SEMI"
     *
     * ใช้ query ชุดเดียวกับ datatable (semiListQuery) จึงได้ข้อมูลตามเงื่อนไขค้นหาที่เลือกอยู่
     * และดึงมาทุกแถว (ไม่แบ่งหน้า) — ต่างจาก datatable ที่ Yajra ตัดตามหน้าที่แสดง
     */
    public function exportExcel()
    {
        $rows = $this->semiListQuery()->get();

        // หัวคอลัมน์ + ชื่อฟิลด์ที่ใช้ดึงค่า (เรียงตามลำดับคอลัมน์ในไฟล์)
        $headers = [
            'วันที่ขอ',
            'Semi No.',
            'วันที่สั่ง',
            'วันที่ต้องการ',
            'แผนกที่สั่ง',
            'ใช้กับ',
            'แม่สี',
            'ยอดคงเหลือ',
            'Lot No.',
            'ยอดใช้ย้อนหลัง 2 เดือน',
            'แผนกที่ใช้',
            'น้ำหนักที่ใช้',
            'ผลิตเพิ่ม',
            'น้ำหนักที่จะผลิต',
            'เลขที่ออกใบแดง',
            'ผลการอนุมัติ',
        ];

        // ตำแหน่งคอลัมน์ที่ต้องจัดรูปแบบเป็นตัวเลขทศนิยม 2 ตำแหน่ง
        $numericColumns = ['H', 'J', 'L', 'M', 'N'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ใบขอสั่งทำ SEMI');

        $lastCol = 'P'; // 16 คอลัมน์ = A..P

        // แถวที่ 1: หัวเรื่อง
        $sheet->setCellValue('A1', 'ใบขอสั่งทำ SEMI');
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
                $row->semi_code,
                $this->excelDate($row->order_date),
                $this->excelDate($row->want_date),
                $row->company,
                $row->itemno,
                $row->primary_color,
                $row->balance,
                $row->lot_no,
                $row->retrospective,
                $row->custno,
                $row->weight_request,
                $row->increase_production,
                $row->weight_production,
                $row->red_bill_code,
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
            foreach (['A', 'C', 'D', 'P'] as $col) {
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

        $fileName = 'ใบขอสั่งทำ_SEMI_'.now()->format('Ymd_His').'.xlsx';

        // ส่งไฟล์ให้ดาวน์โหลดตรงๆ ไม่เขียนลงดิสก์ (ไม่ทิ้งไฟล์ค้างใน public/)
        return response()->streamDownload(function () use ($spreadsheet) {
            (new WriterXlsx($spreadsheet))->save('php://output');
        }, $fileName, [
            'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
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

        $statusLabels = SemiPigment::$statusLabels;
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

    /**
     * ต้นไม้สถานะแผนการผลิตที่สร้างจาก Semi (recursive)
     * - แสดงแผนการผลิต (planning_code, orderno, สถานะการผลิต, วันที่วางแผนผลิต) ที่ได้จาก semi นี้
     * - พร้อม semi_code / primary_color ของ semi
     * - ถ้าใต้แผนนั้นมี semi ย่อย → แสดงซ้อนลงไปเรื่อยๆ (recursive)
     * - รวมถึงตาราง pigment ของแต่ละแผน
     */
    public function planTree(Request $request)
    {
        $sp = SemiPigment::with('result_planning.planning_header')->find($request->id);

        if (!$sp) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการ']);
        }

        $node = $this->buildSemiPlanNode($sp);

        $html = view('production-planning.planning.partials.semi-plan-tree', [
            'node'  => $node,
            'level' => 0,
        ])->render();

        return response()->json(['status' => 200, 'data' => $html]);
    }

    /**
     * สร้างโครงสร้างต้นไม้ของ semi 1 รายการ:
     *  - ข้อมูล semi (semi_code, primary_color, ...)
     *  - แผนการผลิตที่ได้ (result_planning) ถ้ามี
     *  - semi ย่อย + pigment ที่สร้างใต้แผนนั้น (recursive)
     * $depth ป้องกันการวนซ้ำลึกเกินไป (กันข้อมูลอ้างวนกันเป็น loop)
     */
    private function buildSemiPlanNode(SemiPigment $sp, int $depth = 0): array
    {
        $planning = $sp->result_planning;

        $childSemis    = [];
        $childPigments = [];

        if ($planning && $depth < 20) {
            $childSemis = SemiPigment::with('result_planning.planning_header')
                ->where('planning_id', $planning->id)
                ->where('type', 'semi')
                ->orderBy('id')
                ->get()
                ->map(fn (SemiPigment $child) => $this->buildSemiPlanNode($child, $depth + 1))
                ->toArray();

            $childPigments = Pigment::with('result_planning.planning_header')
                ->where('planning_id', $planning->id)
                ->orderBy('id')
                ->get()
                ->map(fn (Pigment $pg) => [
                    'id'             => $pg->id,
                    'order_date'     => $pg->order_date,
                    'want_date'      => $pg->want_date,
                    'custno'         => $pg->custno,
                    'itemno'         => $pg->itemno,
                    'weight_request' => $pg->weight_request,
                    'status'         => $pg->status,
                    'status_label'   => $pg->statusLabel(),
                    'plan_code'      => $pg->result_planning?->planning_header?->planning_code,
                    'plan_status'    => $pg->result_planning?->planning_status,
                ])
                ->toArray();
        }

        return [
            'semi' => [
                'id'            => $sp->id,
                'itemno'        => $sp->itemno,
                'semi_code'     => $sp->semi_code,
                'primary_color' => $sp->primary_color,
                'company'       => $sp->company,
                'status'        => $sp->status,
                'status_label'  => $sp->statusLabel(),
            ],
            'planning' => $planning ? [
                'id'              => $planning->id,
                'planning_code'   => $planning->planning_header?->planning_code,
                'orderno'         => $planning->planning_header?->orderno,
                'planning_status' => $planning->planning_status,
                'inplan'          => $planning->inplan,
            ] : null,
            'child_semis'    => $childSemis,
            'child_pigments' => $childPigments,
        ];
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

        // ค้นหาช่วงวันที่: เลือกฟิลด์ได้เฉพาะ created_at (วันที่ขอ) / order_date (วันที่สั่ง) / want_date (วันที่ต้องการรับ)
        // ใช้ whitelist กัน SQL injection ที่ชื่อคอลัมน์ — ค่าเริ่มต้นตรงกับตัวเลือกแรกของ dropdown ในหน้า index
        $date_field = request('date_field');
        $date_field = in_array($date_field, ['created_at', 'order_date', 'want_date'], true) ? $date_field : 'created_at';
        $date_start = request('date_start');
        $date_end   = request('date_end');

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
            // กรองช่วงวันที่ตามฟิลด์ที่เลือก — ระบุด้านเดียวหรือทั้งช่วงก็ได้
            ->when(!empty($date_start), function ($q) use ($date_field, $date_start) {
                $q->whereDate('tb_semi_pigment.'.$date_field, '>=', $date_start);
            })
            ->when(!empty($date_end), function ($q) use ($date_field, $date_end) {
                $q->whereDate('tb_semi_pigment.'.$date_field, '<=', $date_end);
            })
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
