<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Morder;
use App\Models\PlanningHeader;
use App\Models\Planning;
use App\Models\Department;
use App\Models\PlanningStatus;

class OrderController extends Controller
{
    public function index()
    {
        $departments = Department::where('is_active', 'Y')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return view('production-planning.order.index', [
            'departments' => $departments,
        ]);
    }

    public function datatable()
    {
        $data = $this->dataQuery();

        return DataTables::of($data)
            // # ใช้ DT_RowIndex จาก addIndexColumn() — เรียงตามลำดับที่แสดงจริง (รองรับ sort + pagination)
            ->addIndexColumn()
            ->editColumn('Mdate', fn ($row) => $row->Mdate ? \Carbon\Carbon::parse($row->Mdate)->format('d/m/Y') : '-')
            // รหัสสินค้า (Itemno) ของ suborder ทั้งหมดในออเดอร์ — ตัดค่าซ้ำ/ว่างทิ้ง แล้วคั่นด้วย ", "
            ->addColumn('itemno_list', function($row) {
                $items = $row->suborders
                    ->pluck('Itemno')
                    ->map(fn ($v) => trim((string) $v))
                    ->filter()
                    ->unique()
                    ->values();

                return $items->isEmpty() ? '-' : $items->implode(', ');
            })
            ->editColumn('stock_total', fn ($row) => number_format((float) $row->stock_total, 2))
            ->editColumn('production_total', fn ($row) => number_format((float) $row->production_total, 2))
            ->addColumn('plan_badge', function($row) {
                if ($row->has_plan) {
                    return '<span class="badge bg-label-success">สร้างแล้ว</span>';
                }
                return '<span class="badge bg-label-secondary">ยังไม่สร้าง</span>';
            })
            ->addColumn('btnedit', function($row) {
                $btn_view = '<button class="btn btn-sm btn-icon btn-label-primary me-2 btn_view" data-orderno="'.$row->Orderno.'" title="ดูรายละเอียด">
                    <i class="ti ti-eye ti-sm"></i>
                </button>';

                if ($row->has_plan) {
                    $btn_edit = '<button class="btn btn-sm btn-icon btn-label-secondary btn_edit" data-orderno="'.$row->Orderno.'" title="สร้างแผนการผลิตแล้ว" disabled>
                        <i class="ti ti-checks ti-sm"></i>
                    </button>';
                } else {
                    $btn_edit = '<button class="btn btn-sm btn-icon btn-label-warning btn_edit" data-orderno="'.$row->Orderno.'" title="สร้างแผนการผลิต">
                        <i class="ti ti-download ti-sm"></i>
                    </button>';
                }

                return $btn_view.$btn_edit;
            })
            ->rawColumns(['plan_badge', 'btnedit']) // 👈 บอกให้ column นี้ render HTML
            ->make(true);
    }

    public function dataQuery()
    {
        $search = request('search');
        $company = request('company');
        $planStatus = request('plan_status');
        // วันที่สั่ง (Mdate) จาก flatpickr รูปแบบ Y-m-d — รูปแบบผิด = ไม่กรอง (26/10/2569)
        $mdate = request('mdate');
        $mdate = (is_string($mdate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $mdate)) ? $mdate : null;

        $data = Morder::select([
                'morder.*',
                DB::raw("EXISTS(
                    SELECT 1 FROM tb_planning_header
                    WHERE tb_planning_header.orderno = morder.Orderno
                      AND tb_planning_header.plan_type = 'ORDER'
                ) AS has_plan"),
                // น้ำหนักรวม S (Stock) / P (Production) ของ suborder ทุกตัวในออเดอร์ (22/10/2569)
                DB::raw("(SELECT COALESCE(SUM(s.Stock), 0) FROM suborder s WHERE s.Orderno = morder.Orderno) AS stock_total"),
                DB::raw("(SELECT COALESCE(SUM(s.Production), 0) FROM suborder s WHERE s.Orderno = morder.Orderno) AS production_total"),
                // คีย์สำหรับ sort คอลัมน์ Item No (26/10/2569) — รหัสสินค้าของออเดอร์ต่อกันตาม Runno
                // ลำดับเดียวกับที่แสดงในคอลัมน์ (eager-load ด้านล่างเรียง Runno เหมือนกัน)
                DB::raw("(SELECT GROUP_CONCAT(TRIM(s.Itemno) ORDER BY s.Runno SEPARATOR ', ') FROM suborder s
                          WHERE s.Orderno = morder.Orderno AND TRIM(s.Itemno) <> '') AS itemno_sort"),
            ])
            // โหลด suborder ล่วงหน้า (เฉพาะคีย์ + Itemno) เพื่อรวมรหัสสินค้าโดยไม่เกิด N+1
            ->with(['suborders' => function ($query) {
                $query->select('Orderno', 'Itemno')->orderBy('Runno');
            }])
            ->where('morder.appv', '-1') // เฉพาะ order ที่อนุมัติแล้ว
            // เงื่อนไขการแสดงผล (22/09/2569): ต้องมี suborder ที่ยังไม่ปิดงาน (EndP เป็นค่าว่าง)
            // และ suborder ตัวนั้นต้องมีน้ำหนักที่ต้องทำ ตามแผนกที่ผลิต:
            //   - Company = DB   → Stock > 0 หรือ Production > 0
            //   - Company ≠ DB   → Production > 0 เท่านั้น (Company ที่เป็น NULL นับเป็น "ไม่ใช่ DB")
            // ใช้ whereHas (EXISTS) กัน row ซ้ำกรณี suborder มีหลายตัว
            ->where(function ($outer) {
                // สาขา DB — Stock หรือ Production มากกว่า 0
                $outer->where(function ($q) {
                    $q->where('morder.Company', 'DB')
                        ->whereHas('suborders', function ($sq) {
                            $sq->whereNull('EndP')
                                ->where(function ($cond) {
                                    $cond->where('Stock', '>', 0)
                                        ->orWhere('Production', '>', 0);
                                });
                        });
                })
                // สาขาไม่ใช่ DB (รวม Company ที่เป็น NULL) — Production มากกว่า 0 เท่านั้น
                ->orWhere(function ($q) {
                    $q->where(function ($c) {
                        $c->where('morder.Company', '!=', 'DB')
                            ->orWhereNull('morder.Company');
                    })
                        ->whereHas('suborders', function ($sq) {
                            $sq->whereNull('EndP')
                                ->where('Production', '>', 0);
                        });
                });
            })
            ->when(!empty($search), function ($query) use ($search) {

                $query->where(function ($query) use ($search) {
                    $query->where('morder.Orderno', 'LIKE', '%'.$search.'%')
                        ->orWhere('morder.Custno', 'LIKE', '%'.$search.'%')
                        ->orWhere('morder.Custname', 'LIKE', '%'.$search.'%')
                        // ค้นรหัสสินค้า (Item No.) ใน suborder ของออเดอร์ (26/10/2569)
                        ->orWhereHas('suborders', function ($sq) use ($search) {
                            $sq->where('Itemno', 'LIKE', '%'.$search.'%');
                        });
                });
            })
            ->when(!empty($company), function ($query) use ($company) {
                $query->where('morder.Company', 'LIKE', '%'.$company.'%');
            })
            // กรองวันที่สั่งทั้งวัน (ช่วงเวลา แทน DATE() เพื่อให้ใช้ index ได้)
            ->when($mdate, function ($query) use ($mdate) {
                $query->where('morder.Mdate', '>=', $mdate.' 00:00:00')
                    ->where('morder.Mdate', '<=', $mdate.' 23:59:59');
            })
            // กรองสถานะแผนการผลิต (26/10/2569): created = สร้างแล้ว / not_created = ยังไม่สร้าง
            // เงื่อนไขเดียวกับ alias has_plan (มี tb_planning_header plan_type ORDER ของออเดอร์นี้)
            ->when(in_array($planStatus, ['created', 'not_created'], true), function ($query) use ($planStatus) {
                $exists = function ($q) {
                    $q->select(DB::raw(1))
                        ->from('tb_planning_header')
                        ->whereColumn('tb_planning_header.orderno', 'morder.Orderno')
                        ->where('tb_planning_header.plan_type', 'ORDER');
                };
                $planStatus === 'created' ? $query->whereExists($exists) : $query->whereNotExists($exists);
            });
        // หมายเหตุ: ไม่ hard-code orderBy ที่นี่แล้ว — ให้ Yajra จัดการเรียงตามที่คลิกหัวคอลัมน์
        // (ลำดับเริ่มต้น Mdate จากมากไปน้อย ถูกกำหนดเป็นค่า default order ในฝั่ง DataTables ของ order/index)

        return $data;
    }

    public function detail()
    {
        $orderno =  request('orderno');

        $order = Morder::where('Orderno', $orderno)->first();

        $html = view('production-planning.order.order-detail',compact('order'))->render();

        return response()->json([
            'status' => 200,
            'data' => $html
        ]);
    }

    public function convertplanning()
    {
        $orderno =  request('orderno');

        $order = Morder::where('Orderno', $orderno)->first();
        $check_plan = PlanningHeader::where('orderno', $orderno)
            ->where('plan_type', 'ORDER')
            ->first();

        if(!is_null($check_plan)){
            return response()->json([
                'status' => 500,
                'message' => 'มีการสร้าง Order นี้แล้ว'
            ]);
        }

        // ดึง custwant ของ suborder ทุกตัว (ตัดค่าว่างทิ้ง) เพื่อหาวันที่น้อยสุด/หลังสุด
        $custwants = $order->suborders
            ->pluck('custwant')
            ->filter(fn ($date) => !empty($date))
            ->values();

        // หมายเหตุ header: รวม Remark ของ suborder ทุกตัว (ตัดค่าว่าง แต่เก็บทุกหมายเหตุแม้ซ้ำกัน) คั่นด้วย ", " เมื่อมีมากกว่า 1 รายการ
        $header_remark = $order->suborders
            ->pluck('Remark')
            ->map(fn ($r) => trim((string) $r))
            ->filter()
            ->implode(', ');

        $data_planning_header = [
            'planning_code' => $order->Orderno,
            'mdate' => $order->Mdate,
            'company' => $order->Company,
            'orderno' => $order->Orderno,
            'custno' => $order->Custno,
            'saleno' => $order->supno,
            'netqty' => $order->netqty,
            'custwant' => $custwants->max() ?: null, // วันที่หลังสุดจาก suborder.custwant
            'remark' => $header_remark ?: null,       // หมายเหตุจาก suborder (คั่นด้วย , ถ้ามีหลายรายการ)
            'plan_type' => 'ORDER',
        ];

        // red_bill_code ต้องไม่ซ้ำกันทุก item (unique index บน tb_planning.red_bill_code, 14/09/2569)
        // ออเดอร์ที่มีหลาย suborder จึง stamp orderno เปล่า ๆ ให้ทุก item ไม่ได้ (จะชนกัน)
        // → มีหลาย item: ต่อท้าย /R1, /R2, ... ตาม convention เลขใบเบิกจริง (เช่น HE1576/R1..R3)
        //   มี item เดียว: ใช้ orderno เปล่าเหมือนเดิม
        $suborders   = $order->suborders;
        $multi_items = $suborders->count() > 1;

        // ขายสีสต๊อค (22/10/2569): แผนก DB + รายการที่ Stock > 0 และ Production = 0
        // → สถานะ = สถานะบรรจุ (is_packing='Y') ของแผนก DB, หมายเหตุวางแผน = "ขายสีสต๊อค"
        // ไม่มีสถานะบรรจุของแผนก DB = สถานะว่างตามเดิม
        $is_db_order    = trim((string) $order->Company) === 'DB';
        $packing_status = $is_db_order ? $this->packingStatusName('DB') : null;

        // ครอบ transaction: ถ้า item ใดชน unique (เช่น orderno ถูกใช้ที่อื่นแล้ว) ให้ rollback ทั้งชุด
        // (tb_planning / tb_planning_header เป็น InnoDB จึง rollback ได้จริง)
        DB::beginTransaction();
        try {
            $planning_header = PlanningHeader::create($data_planning_header);

            $seq = 1;
            foreach ($suborders as $suborder) {
                $red_bill = $multi_items
                    ? $order->Orderno . '/R' . $seq   // หลาย item → เติม suffix กันซ้ำ
                    : $order->Orderno;                // item เดียว → เลข Order เปล่า

                $planning = [
                    'planning_header_id' => $planning_header->id,
                    'itemno'             => $suborder->Itemno,
                    'plan_type'          => 'ORDER',
                    'mdate'              => $order->Mdate ?: null,
                    'custwant'           => $suborder->custwant ?: null,
                    'senddate'           => $suborder->senddate ?: null, // กำหนดส่งทบทวนจาก suborder → tb_planning.senddate
                    'remark'             => $suborder->Remark ?: null,
                    'red_bill_code'      => $red_bill, // เลขที่ Order (+ /Rn ถ้าหลาย item) → เลขที่ใบเบิก
                    'quantity'           => $order->netqty, // น้ำหนักสั่งตาม Order
                ];

                $is_stock_sale = $is_db_order
                    && (float) $suborder->Stock > 0
                    && (float) $suborder->Production == 0;

                if ($is_stock_sale) {
                    $planning['planning_remark'] = 'ขายสีสต๊อค';
                    if ($packing_status) {
                        $planning['planning_status'] = $packing_status;
                    }
                }

                Planning::create($planning);
                $seq++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'สร้างแผนไม่สำเร็จ (เลขที่ใบเบิกอาจซ้ำ): ' . $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Success'
        ]);
    }

    // ชื่อสถานะบรรจุ (is_packing='Y') ตัวแรกตามลำดับของแผนก — ไม่มี = null
    private function packingStatusName(string $company): ?string
    {
        $dept_id = Department::where('name', $company)->value('id');
        if (!$dept_id) {
            return null;
        }

        return PlanningStatus::where('dept', $dept_id)
            ->where('is_active', 'Y')
            ->where('is_packing', 'Y')
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->value('name');
    }
}
