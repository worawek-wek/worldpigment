<?php

namespace App\Http\Controllers;

use App\Models\ColorRate;
use App\Models\ColorRateHistory;
use App\Models\Emp;
use App\Services\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

/**
 * จัดการกลุ่มราคา (master) — ตาราง `zcolorrate` (19/09/2569)
 *
 * โครงมิเรอร์จาก QcStatusController (หน้ารายการ DataTables serverSide + modal ฟอร์ม)
 * แต่ต่างที่สำคัญ: ตารางนี้เป็น legacy MyISAM **ไม่มี `id`** — คีย์คือ `colorno` (varchar 12)
 *   ⇒ ฟอร์มส่ง colorno เดิมมาในช่อง `id` เพื่อบอกว่ากำลังแก้แถวไหน
 *   ⇒ ตอนแก้ไข **ห้ามเปลี่ยนรหัสสินค้า** (เป็น PK) ถ้าพิมพ์ผิดต้องลบแล้วเพิ่มใหม่
 *
 * ค่าในตารางนี้ถูกใช้เป็น "ราคาขั้นต่ำ" ของด่านราคาในใบสั่งซื้อ
 * (OrderController::priceData คู่กับ colorRateFloor)
 */
class ColorRateController extends Controller
{
    public function index()
    {
        return view('color-rate.index', [
            'groups' => PriceApprovalController::priceGroups(),
        ]);
    }

    public function datatable()
    {
        $items = ColorRate::select(['colorno', 'rate_A', 'rate_B', 'rate_C', 'RDate'])
            ->when(request('search'), function ($q, $search) {
                $q->where('colorno', 'like', "%{$search}%");
            })
            // ตัวกรองวันที่ปรับราคา (RDate) แบบช่วง — รับ d/m/Y · ค่าแปลงไม่ได้ = ไม่กรอง (15/10/2569)
            ->when($this->parseDate(request('date_from')), function ($q, $from) {
                $q->where('RDate', '>=', $from);
            })
            ->when($this->parseDate(request('date_to')), function ($q, $to) {
                $q->where('RDate', '<=', substr($to, 0, 10).' 23:59:59');
            })
            // ลำดับเริ่มต้น = รหัสสินค้า (เติมเฉพาะตอนผู้ใช้ยังไม่คลิก sort คอลัมน์อื่น)
            ->when(empty(request('order')), function ($q) {
                $q->orderBy('colorno', 'asc');
            });

        $rownum = 0;

        return DataTables::of($items)
            ->addColumn('rownum', function () use (&$rownum) {
                return ++$rownum;
            })
            ->editColumn('rate_A', function ($i) {
                return $this->money($i->rate_A);
            })
            ->editColumn('rate_B', function ($i) {
                return $this->money($i->rate_B);
            })
            ->editColumn('rate_C', function ($i) {
                return $this->money($i->rate_C);
            })
            ->editColumn('RDate', function ($i) {
                return $i->RDate ? date('d/m/Y', strtotime($i->RDate)) : '-';
            })
            ->addColumn('btnaction', function ($item) {
                $key = e($item->colorno);

                return '<div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-sm btn-icon btn-info waves-effect btn_history" data-id="'.$key.'" title="ประวัติการแก้ไข">
                                <i class="ti ti-history ti-sm"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-warning btn_edit" data-id="'.$key.'" title="แก้ไข">
                                <i class="ti ti-pencil ti-sm"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-danger btn_delete" data-id="'.$key.'" title="ลบ">
                                <i class="ti ti-trash ti-sm"></i>
                            </button>
                        </div>';
            })
            ->rawColumns(['btnaction'])
            ->make(true);
    }

    public function edit()
    {
        $id = trim((string) request('id'));

        $item = $id !== '' ? ColorRate::find($id) : null;

        $html = view('color-rate.color-rate-form', [
            'item'   => $item,
            'groups' => PriceApprovalController::priceGroups(),
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html,
        ]);
    }

    public function store(Request $request)
    {
        /* ถอดคอมมาออกจากช่องราคา **ก่อน** validate (19/09/2569)
           ช่องกรอกใช้ class js-comma ⇒ ค่าที่เห็นคือ '1,234.50' และฝั่งจอเรียก stripCommaFields()
           ให้ก่อน serialize อยู่แล้ว — แต่ถ้าพึ่ง JS อย่างเดียว เวลายิงตรง/JS ไม่ทำงาน
           กฎ `numeric` จะตีกลับด้วยข้อความ "ต้องเป็นตัวเลข" ทั้งที่ผู้ใช้กรอกถูก */
        $request->merge([
            'rate_A' => $this->stripComma($request->input('rate_A')),
            'rate_B' => $this->stripComma($request->input('rate_B')),
            'rate_C' => $this->stripComma($request->input('rate_C')),
        ]);

        $validator = Validator::make($request->all(), [
            'colorno' => 'required|string|max:12',
            'rate_A'  => 'nullable|numeric|min:0',
            'rate_B'  => 'nullable|numeric|min:0',
            'rate_C'  => 'nullable|numeric|min:0',
        ], [
            'colorno.required' => 'กรุณากรอกรหัสสินค้า',
            'colorno.max'      => 'รหัสสินค้ายาวเกิน 12 ตัวอักษร',
            'rate_A.numeric'   => 'ราคากลุ่ม A ต้องเป็นตัวเลข',
            'rate_B.numeric'   => 'ราคากลุ่ม B ต้องเป็นตัวเลข',
            'rate_C.numeric'   => 'ราคากลุ่ม C ต้องเป็นตัวเลข',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ]);
        }

        $original = trim((string) $request->input('id'));       // '' = เพิ่มใหม่
        $colorno  = trim((string) $request->input('colorno'));

        // เพิ่มใหม่ — กันรหัสซ้ำเอง (colorno เป็น PK, insert ซ้ำจะได้ SQL error ดิบ ๆ)
        if ($original === '' && ColorRate::where('colorno', $colorno)->exists()) {
            return response()->json([
                'status'  => 422,
                'message' => 'รหัสสินค้า '.$colorno.' มีอยู่ในตารางกลุ่มราคาแล้ว',
            ]);
        }

        $data = [
            'rate_A' => $this->numOrNull($request->input('rate_A')),
            'rate_B' => $this->numOrNull($request->input('rate_B')),
            'rate_C' => $this->numOrNull($request->input('rate_C')),
            // ไม่กรอก = วันนี้ (ตัดเวลาทิ้งให้ตรงกับข้อมูลเดิมที่เป็น 00:00:00 ทุกแถว)
            'RDate'  => $this->parseDate($request->input('RDate')) ?? now()->startOfDay(),
        ];

        if ($original === '') {
            $item = ColorRate::create(['colorno' => $colorno] + $data);
            $this->logHistory('create', $colorno, null, $item);
        } else {
            // แก้ไข — ยึด colorno เดิมเสมอ (ช่องบนฟอร์มเป็น readonly อยู่แล้ว)
            $item = ColorRate::find($original);
            if (!$item) {
                return response()->json(['status' => 404, 'message' => 'ไม่พบรหัสสินค้า '.$original]);
            }
            $before = clone $item;   // ค่าก่อนแก้ — เก็บลงประวัติ
            $item->forceFill($data)->save();
            $this->logHistory('update', $original, $before, $item);
        }

        return response()->json([
            'status'  => 200,
            'message' => $original === '' ? 'เพิ่มข้อมูลสำเร็จ' : 'แก้ไขข้อมูลสำเร็จ',
        ]);
    }

    public function destroy(Request $request)
    {
        $item = ColorRate::find(trim((string) $request->input('id')));

        if (!$item) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบข้อมูลที่ต้องการลบ',
            ]);
        }

        $this->logHistory('delete', $item->colorno, $item, null);
        $item->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'ลบข้อมูลสำเร็จ',
        ]);
    }

    /**
     * ตารางกลุ่มราคาแบบอ่านอย่างเดียว — ใช้กับปุ่ม "ดูกลุ่มราคา" ในหน้ากำหนดราคา (/saleinfo)
     * และหน้าใบสั่งซื้อ (/order) — คืน HTML ให้ฝั่งจอยัดลง modal ได้เลย
     *
     * ⚠ route name เป็น `colorratelookup.*` **โดยตั้งใจ** ไม่ใช่ `colorrate.*`
     *   เพราะ CheckAccess คุมสิทธิ์ด้วย namespace ของ route name เทียบกับเมนู —
     *   ถ้าใช้ `colorrate.*` คนที่เข้าเมนู Order / กำหนดราคา ได้ แต่ไม่ได้ติ๊กเมนู
     *   "จัดการกลุ่มราคา" จะกดปุ่มนี้แล้วได้ 403
     *   namespace ที่ไม่ผูกกับเมนูใน config/menu.php จะ pass-through (เหลือด่าน auth)
     */
    public function lookup()
    {
        $search = trim((string) request('search'));

        $rows = ColorRate::select(['colorno', 'rate_A', 'rate_B', 'rate_C', 'RDate'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where('colorno', 'like', "%{$search}%");
            })
            ->orderBy('colorno')
            ->get();

        return response()->json([
            'status' => 200,
            'count'  => $rows->count(),
            'data'   => view('color-rate.lookup-table', ['rows' => $rows])->render(),
        ]);
    }

    /** จำนวนแถวสูงสุดของ "ประวัติทั้งหมด" */
    private const HISTORY_LIMIT = 500;

    /**
     * ประวัติการแก้ไขกลุ่มราคา (15/10/2569) — คืน HTML ให้ยัดลง modal
     * ส่ง colorno = เฉพาะรหัสนั้น (ปุ่มในแถว) · ไม่ส่ง = ทุกรหัส (ปุ่มบนหัวหน้า — รวมรหัสที่ถูกลบไปแล้ว)
     * ค้นได้ด้วย search (LIKE รหัสสินค้า) · จำกัด HISTORY_LIMIT แถวล่าสุด
     */
    public function history()
    {
        $colorno = trim((string) request('colorno'));
        $search  = trim((string) request('search'));

        $q = ColorRateHistory::query()
            ->when($colorno !== '', function ($q) use ($colorno) {
                $q->where('colorno', $colorno);
            })
            ->when($colorno === '' && $search !== '', function ($q) use ($search) {
                $q->where('colorno', 'like', "%{$search}%");
            });

        $total = (clone $q)->count();
        $rows  = $q->orderByDesc('changed_at')->orderByDesc('id')
            ->limit(self::HISTORY_LIMIT)
            ->get();

        return response()->json([
            'status' => 200,
            'count'  => $total,
            'data'   => view('color-rate.history-table', [
                'rows'    => $rows,
                'total'   => $total,
                'limit'   => self::HISTORY_LIMIT,
                'single'  => $colorno !== '',
                'actions' => ColorRateHistory::ACTIONS,
            ])->render(),
        ]);
    }

    // ─────────── ตัวช่วย ───────────

    /**
     * บันทึกประวัติ 1 แถว — $before / $after เป็น ColorRate (null = ไม่มี: เพิ่มใหม่ไม่มี before, ลบไม่มี after)
     * แก้ไขแต่ค่าไม่เปลี่ยนเลย = ไม่บันทึก (กันประวัติรกจากการกดบันทึกเฉย ๆ)
     * ⚠ zcolorrate เป็น MyISAM ⇒ ไม่มีทรานแซกชันครอบ ถ้าบันทึกประวัติพังหลังเขียนราคาแล้ว ราคาไม่ถูกย้อน
     */
    private function logHistory(string $action, string $colorno, ?ColorRate $before, ?ColorRate $after): void
    {
        $pick = function (?ColorRate $r, string $prefix) {
            return [
                $prefix.'rate_A' => $r ? $this->numOrNull($r->rate_A) : null,
                $prefix.'rate_B' => $r ? $this->numOrNull($r->rate_B) : null,
                $prefix.'rate_C' => $r ? $this->numOrNull($r->rate_C) : null,
                $prefix.'RDate'  => ($r && $r->RDate) ? date('Y-m-d H:i:s', strtotime($r->RDate)) : null,
            ];
        };

        $old = $pick($before, 'old_');
        $new = $pick($after, 'new_');

        if ($action === 'update' && array_values($old) === array_values($new)) {
            return;
        }

        // ผู้ทำ — ระบบมีบัญชี 2 แบบ (emp = พนักงาน, user = admin) · เก็บชื่อ ณ ตอนนั้นไว้ด้วย
        $account = AccessControl::currentAccount();
        $byType = $by = $byName = null;
        if ($account instanceof Emp) {
            $byType = 'emp';
            $by     = $account->empno;
            $byName = trim($account->empname.' '.$account->empsur) ?: $account->empno;
        } elseif ($account) {
            $byType = 'user';
            $by     = (string) $account->id;
            $byName = $account->name;
        }

        ColorRateHistory::create([
            'colorno'         => $colorno,
            'action'          => $action,
            'changed_by_type' => $byType,
            'changed_by'      => $by,
            'changed_by_name' => $byName !== null ? mb_substr($byName, 0, 150) : null,
            'changed_at'      => now(),
        ] + $old + $new);
    }

    /** จัดรูปแบบเงินสำหรับแสดงในตาราง (ว่าง = ขีด) */
    private function money($v): string
    {
        return ($v === null || $v === '') ? '-' : number_format((float) $v, 2);
    }

    /** ตัดคอมมาคั่นหลักพันออก (ช่องกรอกใช้ class js-comma) — คืนเป็นสตริงเสมอ */
    private function stripComma($v): string
    {
        return str_replace(',', '', trim((string) $v));
    }

    /** ค่าว่าง → null, ไม่งั้นเป็น float */
    private function numOrNull($v): ?float
    {
        $v = $this->stripComma($v);

        return ($v === '' || !is_numeric($v)) ? null : (float) $v;
    }

    /** รับ d/m/Y (หรือ Y-m-d) แล้วคืน 'Y-m-d 00:00:00' · แปลงไม่ได้ = null */
    private function parseDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        foreach (['d/m/Y', 'Y-m-d'] as $fmt) {
            $d = \DateTime::createFromFormat($fmt, $value);
            if ($d && $d->format($fmt) === $value) {
                return $d->format('Y-m-d').' 00:00:00';
            }
        }

        return null;
    }
}
