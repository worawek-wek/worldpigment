<?php

namespace App\Http\Controllers;

use App\Models\Nc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yajra\DataTables\Facades\DataTables;

/**
 * ระบบ Complaint & NCR — 26/11/2569 · ฟอร์มตามผัง Access "NC : ฟอร์ม" (ตัดช่องที่กากบาท) — ตาราง/Excel ยังเป็นชุดจากสเปกข้อ 1–33
 *
 * ตารางหลัก nc (1 แถว = 1 เรื่อง/1 NCR) · ผลตรวจสอบ nc.degree → result · ค่าใช้จ่ายการแก้ไข treat (ผูกด้วย NCRNo)
 * ช่องที่สเปกขีดฆ่า (3 แผนก · 13 ข้อกำหนด ISO · 18 ผู้แก้ไข · 20 ผู้ป้องกัน) + SubjectGRP / DamageNo / damageCOST
 * ไม่อยู่บนฟอร์มแล้ว — store() ไม่แตะ ข้อมูลเดิมยังอยู่
 */
class ComplaintController extends Controller
{
    /**
     * คอลัมน์ข้อความของ nc ที่ฟอร์มเขียน => ความยาวสูงสุด (schema จริง)
     * ฟอร์มตามผัง Access (26/11/2569) — คอลัมน์ที่ไม่อยู่ในฟอร์ม (Dept/shall/CorrBY/ProtBy/SubjectGRP/
     * CauseTypeRem/CauseDate/FollowUp/ProdAlert/CloseDate) store() ไม่แตะ
     */
    private const TEXT_COLUMNS = [
        'NCRNo'      => 15,
        'Subject'    => 200,
        'custno'     => 5,
        'custname'   => 50,
        'ComplainNO' => 10,
        'ProdNo'     => 30,
        'LotNo'      => 50,
        'CauseType'  => 100, // เลือก A–I หรือพิมพ์เอง — ขยายจาก 5 เมื่อ 27/11/2569
        'Cause'      => 200,
        'Correct'    => 200,
        'Protect'    => 200,
        'MGRcomment' => 200,
    ];

    /** ช่องตัวเลขของ treat ที่ฟอร์มเขียน (ส่วนย่อยล่างตามผัง Access) */
    private const TREAT_FORM_MONEY = ['claim', 'TreatRaw'];

    private const LABELS = [
        'ComplainNO' => 'เลขที่ใบร้องเรียน', 'custno' => 'รหัสลูกค้า', 'custname' => 'ชื่อลูกค้า',
        'ProdNo' => 'Product No.', 'LotNo' => 'Lot No.', 'Subject' => 'ปัญหา', 'NCRNo' => 'NCRNo',
        'CauseType' => 'สรุปสาเหตุ', 'CauseTypeRem' => 'ข้อความเพิ่มเติมสรุปสาเหตุ', 'Cause' => 'สาเหตุ',
        'Correct' => 'วิธีแก้ไข', 'Protect' => 'วิธีป้องกัน', 'FollowUp' => 'การติดตามผล',
        'MGRcomment' => 'ความคิดเห็นผู้บริหาร', 'QTY' => 'น้ำหนัก', 'TreatNo' => 'เลขที่ใบแก้ไข',
        'TreatKG' => 'นน.แก้ไข', 'TreatDetail' => 'รายละเอียดการแก้ไข', 'TreatRaw' => 'ค่าวัตถุดิบ',
        'TreatProcess' => 'ค่า process', 'TreatQC' => 'ค่า QC', 'TreatPack' => 'ค่า packing', 'claim' => 'เงิน claim',
    ];

    /** รายงาน Excel 4 แบบ (กล่องสรุปรายงานผลในสเปก) */
    public const EXPORT_TYPES = [
        'date'     => '1) เรียงตามวันที่ออก NCR',
        'product'  => '2) เรียงตามรหัสสินค้า',
        'customer' => '3) เรียงตามรหัสลูกค้า',
        'ncr'      => '4) เฉพาะที่มี NCRNo (เรียงตามวันที่ออก NCR → NCRNo)',
    ];

    public function index()
    {
        return view('complaint.index', [
            'degrees'     => Nc::degreeOptions(),
            'exportTypes' => self::EXPORT_TYPES,
        ]);
    }

    // ───────────────────────── ตารางรายการ ─────────────────────────

    /** เงื่อนไขกรองร่วม (ตาราง + Excel) */
    private function filtered(Request $request)
    {
        return DB::table('nc')
            ->when(trim((string) $request->search) !== '', function ($q) use ($request) {
                $s = '%'.trim((string) $request->search).'%';
                $q->where(function ($w) use ($s) {
                    foreach (['NCRNo', 'ComplainNO', 'Subject', 'custno', 'custname', 'ProdNo', 'LotNo'] as $col) {
                        $w->orWhere("nc.$col", 'like', $s);
                    }
                });
            })
            ->when($request->degree, function ($q, $degree) {
                if ($degree === '__none') {
                    $q->where(fn ($w) => $w->whereNull('nc.degree')->orWhere('nc.degree', ''));
                } else {
                    $q->where('nc.degree', $degree);
                }
            })
            ->when(in_array($request->offspec, ['0', '1'], true), fn ($q) => $q->where('nc.OffSpec', (int) $request->offspec))
            ->when($this->parseDate($request->date_from), fn ($q, $d) => $q->where('nc.NCRDate', '>=', $d.' 00:00:00'))
            ->when($this->parseDate($request->date_to), fn ($q, $d) => $q->where('nc.NCRDate', '<=', $d.' 23:59:59'));
    }

    public function datatable(Request $request)
    {
        $query = $this->filtered($request)
            ->leftJoin('result', 'result.status', '=', 'nc.degree')
            ->select([
                'nc.id', 'nc.NCRDate', 'nc.ComplainNO', 'nc.custno', 'nc.custname', 'nc.ProdNo', 'nc.LotNo',
                'nc.Subject', 'nc.degree', 'nc.NCRNo', 'nc.CauseType', 'nc.Cause', 'nc.Correct',
                'nc.Protect', 'nc.OffSpec', 'result.rem as degree_name',
            ]);
        // ไม่ hard-code orderBy — ลำดับเริ่มต้น (วันที่รับเรื่อง ใหม่→เก่า) ตั้งใน DataTables ฝั่งจอ

        $text = fn ($v, $w = 220) => $v
            ? '<div class="nc-clip" style="max-width:'.$w.'px" title="'.e($v).'">'.e($v).'</div>'
            : '<span class="text-muted">-</span>';

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('NCRDate', fn ($r) => $this->fmtDate($r->NCRDate) ?: '-')
            ->editColumn('ComplainNO', fn ($r) => $r->ComplainNO ?: '-')
            ->addColumn('customer', fn ($r) => (!$r->custno && !$r->custname) ? '-'
                : '<div class="nc-clip" style="max-width:200px" title="'.e($r->custname).'">'.e($r->custname ?: '-').'</div><small class="text-muted">'.e($r->custno).'</small>')
            ->editColumn('ProdNo', fn ($r) => $r->ProdNo ?: '-')
            ->editColumn('LotNo', fn ($r) => $r->LotNo ?: '-')
            ->editColumn('Subject', fn ($r) => $text($r->Subject, 260))
            ->addColumn('degree_label', function ($r) {
                if (!$r->degree) {
                    return '<span class="badge bg-label-warning">รอผล</span>';
                }

                return '<span class="badge '.(Nc::DEGREE_BADGES[$r->degree] ?? 'bg-label-secondary').'">'.e($r->degree_name ?: $r->degree).'</span>'
                    .($r->OffSpec ? '<div><span class="badge bg-danger mt-1">Off Spec</span></div>' : '');
            })
            ->editColumn('NCRNo', fn ($r) => Nc::isRealNcrNo($r->NCRNo) ? '<span class="fw-medium">'.e($r->NCRNo).'</span>' : '-')
            ->editColumn('CauseType', fn ($r) => $r->CauseType
                ? '<span class="fw-medium">'.e($r->CauseType).'</span>'
                : '-')
            ->editColumn('Cause', fn ($r) => $text($r->Cause))
            ->editColumn('Correct', fn ($r) => $text($r->Correct))
            ->editColumn('Protect', fn ($r) => $text($r->Protect))
            ->addColumn('btnaction', fn ($r) => '<div class="d-flex justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-icon btn-warning btn_edit" data-id="'.$r->id.'" title="แก้ไข"><i class="ti ti-pencil ti-sm"></i></button>
                    <button type="button" class="btn btn-sm btn-icon btn-danger btn_delete" data-id="'.$r->id.'" title="ลบ"><i class="ti ti-trash ti-sm"></i></button>
                </div>')
            ->orderColumn('customer', 'nc.custname $1')
            ->orderColumn('degree_label', 'nc.degree $1')
            ->rawColumns(['customer', 'Subject', 'degree_label', 'NCRNo', 'CauseType', 'Cause', 'Correct', 'Protect', 'btnaction'])
            ->make(true);
    }

    // ───────────────────────── ฟอร์ม ─────────────────────────

    public function edit()
    {
        $nc = request('id') ? Nc::find(request('id')) : null;

        $treat = ($nc && Nc::isRealNcrNo($nc->NCRNo))
            ? DB::table('treat')->where('NCRNo', trim($nc->NCRNo))->first()
            : null;

        $html = view('complaint.form', [
            'nc'         => $nc,
            'treat'      => $treat,
            'degrees'    => Nc::degreeOptions(),
            'causeTypes' => Nc::causeTypeOptions(),
            'prodName'   => $nc ? $this->productName($nc->ProdNo, $nc->custno) : null,
        ])->render();

        return response()->json(['status' => 200, 'data' => $html]);
    }

    /** ข้อ 4 — กรอกรหัสลูกค้า → ชื่อลูกค้า */
    public function customerLookup(string $code)
    {
        $c = DB::table('customer')->where('code', trim($code))->first(['code', 'name']);

        return response()->json(['status' => $c ? 200 : 404, 'customer' => $c]);
    }

    /** ข้อ 5 — พิมพ์ชื่อลูกค้า → รายชื่อที่ตรง (ให้ฟอร์มเลือกแล้วเติมรหัส) */
    public function customerSearch(Request $request)
    {
        // ใช้ทั้งช่องรหัสและช่องชื่อ (รายการแนะนำ 26/11/2569) — ค้นรหัสขึ้นต้นด้วย / ชื่อมีคำนี้
        // รหัสที่ตรงพอดีขึ้นก่อน → รหัสขึ้นต้นด้วยคำค้น → ชื่อ
        $q = trim((string) $request->q);
        if ($q === '' || (!ctype_digit($q) && mb_strlen($q) < 2)) {
            return response()->json(['status' => 200, 'items' => []]);
        }

        $items = DB::table('customer')
            ->where(fn ($w) => $w->where('code', 'like', "{$q}%")->orWhere('name', 'like', "%{$q}%"))
            ->orderByRaw('code = ? DESC', [$q])
            ->orderByRaw('code LIKE ? DESC', ["{$q}%"])
            ->orderBy('code')
            ->limit(15)
            ->get(['code', 'name']);

        return response()->json(['status' => 200, 'items' => $items]);
    }

    /**
     * รหัสสินค้า → รายการแนะนำ (26/11/2569) — ที่มา = uprice (ตาราง "กำหนดราคา")
     * ตรวจแล้ว: รหัสสินค้าใน nc ตั้งแต่ปี 2024 มีใน uprice 144/150 รหัส (มีชื่อ Label 133) — tb_products มีแค่ 10 แถว ใช้ไม่ได้
     */
    public function productSearch(Request $request)
    {
        $q = trim((string) $request->q);
        if ($q === '') {
            return response()->json(['status' => 200, 'items' => []]);
        }

        $items = DB::table('uprice')
            ->selectRaw("ITEMNO as code, MAX(NULLIF(TRIM(Label), '')) as name")
            ->where('ITEMNO', 'like', "{$q}%")
            ->groupBy('ITEMNO')
            ->orderByRaw('ITEMNO = ? DESC', [$q])
            ->orderBy('ITEMNO')
            ->limit(15)
            ->get();

        return response()->json(['status' => 200, 'items' => $items]);
    }

    /** ชื่อสินค้าของรหัสนี้ (แสดงอย่างเดียว ไม่บันทึก) */
    public function productLookup(Request $request)
    {
        $code = trim((string) $request->code);

        return response()->json([
            'status' => 200,
            'found'  => $code !== '' && DB::table('uprice')->where('ITEMNO', $code)->exists(),
            'name'   => $this->productName($code, $request->custno),
        ]);
    }

    /**
     * ชื่อสินค้า = uprice.Label แถวล่าสุดที่มีชื่อ — ของลูกค้ารายนี้ก่อน (เบอร์เดียวกันคนละลูกค้า Label ต่างกันได้)
     * ไม่มีค่อยใช้ของลูกค้าใดก็ได้ (กติกาเดียวกับ OrderController::itemLookup)
     */
    private function productName(?string $code, ?string $custno): ?string
    {
        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }

        $base = fn () => DB::table('uprice')->where('ITEMNO', $code)
            ->whereNotNull('Label')->whereRaw("TRIM(Label) <> ''")
            ->orderByDesc('DATE')->orderByDesc('id');

        $custno = trim((string) $custno);
        $label  = $custno !== '' ? $base()->where('CustNo', $custno)->value('Label') : null;

        return $label ?? $base()->value('Label');
    }

    public function store(Request $request)
    {
        $nc = $request->id ? Nc::find($request->id) : null;
        if ($request->id && !$nc) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการที่ต้องการแก้ไข']);
        }

        // ถอดคอมมา (ช่อง js-comma) ก่อนตรวจ
        foreach (['QTY', 'TreatKG', ...self::TREAT_FORM_MONEY] as $f) {
            if ($request->has($f)) {
                $request->merge([$f => str_replace(',', '', (string) $request->input($f))]);
            }
        }

        $rules = [
            'NCRDate'     => 'required',
            'degree'      => ['nullable', Rule::in(array_keys(Nc::degreeOptions()))],
            // สรุปสาเหตุ (CauseType) เลือกจากรายการ A–I หรือพิมพ์เองได้ (27/11/2569) — ตรวจแค่ความยาวใน TEXT_COLUMNS
            'QTY'         => 'nullable|numeric',
            'TreatKG'     => 'nullable|numeric',
            'TreatNo'     => 'nullable|string|max:15',
        ];
        foreach (self::TEXT_COLUMNS as $col => $len) {
            $rules[$col] = $rules[$col] ?? "nullable|string|max:$len";
        }
        foreach (self::TREAT_FORM_MONEY as $col) {
            $rules[$col] = 'nullable|numeric';
        }

        $validator = Validator::make($request->all(), $rules, [
            'NCRDate.required' => 'กรุณาระบุวันที่ออก NCR',
            'degree.in'        => 'ผลไม่ถูกต้อง',
            '*.max'            => ':attribute ยาวเกิน :max ตัวอักษร',
            '*.numeric'        => ':attribute ต้องเป็นตัวเลข',
        ], self::LABELS);

        if ($validator->fails()) {
            return response()->json(['status' => 422, 'message' => $validator->errors()->first()]);
        }

        $date = $this->parseDate($request->NCRDate);
        if (!$date) {
            return response()->json(['status' => 422, 'message' => 'รูปแบบวันที่ออก NCR ไม่ถูกต้อง']);
        }

        $data = ['NCRDate' => $date.' 00:00:00'];
        foreach (array_keys(self::TEXT_COLUMNS) as $col) {
            $v = trim((string) $request->input($col));
            $data[$col] = $v === '' ? null : $v;
        }
        // ไม่มี NCR ให้เป็น "-"
        if (!Nc::isRealNcrNo($data['NCRNo'])) {
            $data['NCRNo'] = '-';
        }
        $data['degree']    = $request->degree ?: null;
        $data['QTY']       = $request->QTY === null || $request->QTY === '' ? null : round((float) $request->QTY, 2);
        $data['OffSpec']   = $request->has('OffSpec') ? 1 : 0;

        // NCR No. ห้ามซ้ำ — ตรวจเฉพาะเลขจริง และเฉพาะตอนเพิ่มใหม่/เปลี่ยนเลข (ข้อมูลเดิมซ้ำอยู่ 9 เลข)
        $newNo = $data['NCRNo'];
        $oldNo = $nc ? trim((string) $nc->NCRNo) : null;
        if (Nc::isRealNcrNo($newNo) && $newNo !== $oldNo) {
            $dup = Nc::whereRaw('TRIM(NCRNo) = ?', [$newNo])->when($nc, fn ($q) => $q->where('id', '<>', $nc->id))->exists();
            if ($dup) {
                return response()->json(['status' => 422, 'message' => "NCR / CAR No. {$newNo} มีอยู่แล้ว"]);
            }
        }

        // ลบเลข NCR ออก (กลายเป็น "-") ทั้งที่มีใบแก้ไข/ค่าใช้จ่ายผูกอยู่ = ข้อมูล treat จะหลุดจากรายการ → ไม่ยอม
        if (Nc::isRealNcrNo($oldNo) && !Nc::isRealNcrNo($newNo)
            && DB::table('treat')->where('NCRNo', $oldNo)->exists()
            && !Nc::whereRaw('TRIM(NCRNo) = ?', [$oldNo])->where('id', '<>', $nc->id)->exists()) {
            return response()->json(['status' => 422, 'message' => "NCR {$oldNo} มีใบแก้ไข / ค่าใช้จ่ายผูกอยู่ จึงล้างเลข NCR ไม่ได้"]);
        }

        $treatData   = $this->treatPayload($request);
        $hasTreatVal = collect($treatData)->contains(fn ($v) => $v !== null && $v !== 0.0 && $v !== '');

        if ($hasTreatVal && !Nc::isRealNcrNo($newNo)) {
            return response()->json(['status' => 422, 'message' => 'บันทึกส่วนการแก้ไข / ค่าใช้จ่ายได้เมื่อมี NCR / CAR No. แล้วเท่านั้น']);
        }
        if ($hasTreatVal && mb_strlen($newNo) > 10) {
            // treat.NCRNo เป็น varchar(10) แคบกว่า nc.NCRNo (15)
            return response()->json(['status' => 422, 'message' => 'NCR / CAR No. ยาวเกิน 10 ตัวอักษร จึงบันทึกค่าใช้จ่ายการแก้ไขไม่ได้']);
        }

        DB::transaction(function () use ($nc, $data, $newNo, $oldNo, $treatData, $hasTreatVal) {
            if ($nc) {
                $nc->update($data);
            } else {
                Nc::create($data);
            }

            // เปลี่ยนเลข NCR → ย้ายแถว treat ตามไป (ถ้าเลขใหม่ยังไม่มี treat และไม่มี nc อื่นใช้เลขเดิมอยู่)
            if (Nc::isRealNcrNo($oldNo) && $oldNo !== $newNo && Nc::isRealNcrNo($newNo)
                && !DB::table('treat')->where('NCRNo', $newNo)->exists()
                && !Nc::whereRaw('TRIM(NCRNo) = ?', [$oldNo])->exists()) {
                DB::table('treat')->where('NCRNo', $oldNo)->update(['NCRNo' => $newNo]);
            }

            if (Nc::isRealNcrNo($newNo)) {
                if (DB::table('treat')->where('NCRNo', $newNo)->exists()) {
                    DB::table('treat')->where('NCRNo', $newNo)->update($treatData);
                } elseif ($hasTreatVal) {
                    DB::table('treat')->insert(['NCRNo' => $newNo] + $treatData);
                }
            }
        });

        return response()->json(['status' => 200, 'message' => $nc ? 'แก้ไขข้อมูลเรียบร้อย' : 'เพิ่มข้อมูลเรียบร้อย']);
    }

    /** ส่วนย่อยล่างตามผัง Access: เงิน claim · เลขที่ใบแก้ไข · ค่าวัตถุดิบ · น.น.แก้ไข — คอลัมน์อื่นของ treat ไม่แตะ */
    private function treatPayload(Request $request): array
    {
        $num = fn ($v) => $v === null || $v === '' ? 0.0 : round((float) $v, 2);

        $data = [];
        foreach (self::TREAT_FORM_MONEY as $col) {
            $data[$col] = $num($request->input($col));
        }
        $data['TreatKG'] = $num($request->input('TreatKG'));
        $data['TreatNo'] = trim((string) $request->TreatNo) ?: null;

        return $data;
    }

    public function destroy(Request $request)
    {
        $nc = Nc::find($request->id);
        if (!$nc) {
            return response()->json(['status' => 404, 'message' => 'ไม่พบรายการที่ต้องการลบ']);
        }

        DB::transaction(function () use ($nc) {
            $no = trim((string) $nc->NCRNo);
            $nc->delete();

            // ลบค่าใช้จ่ายการแก้ไขตามไป เฉพาะเมื่อไม่มี nc แถวอื่นใช้เลขนี้อยู่
            if (Nc::isRealNcrNo($no) && !Nc::whereRaw('TRIM(NCRNo) = ?', [$no])->exists()) {
                DB::table('treat')->where('NCRNo', $no)->delete();
            }
        });

        return response()->json(['status' => 200, 'message' => 'ลบข้อมูลเรียบร้อย']);
    }

    // ───────────────────────── Export Excel (4 แบบ) ─────────────────────────

    /**
     * คอลัมน์ = ช่องที่อยู่บนฟอร์ม nc (ตามผัง Access) — ตัดช่องที่ไม่อยู่บนฟอร์มออกแล้ว (ผู้ใช้สั่ง 26/11/2569)
     * ลำดับรอง = Complaint No. น้อย → มาก แล้วค่อย "NCR ภายใน" (ค่าที่ไม่ใช่ตัวเลข) ต่อท้าย
     */
    public function exportExcel(Request $request)
    {
        $type = array_key_exists($request->type, self::EXPORT_TYPES) ? $request->type : 'date';

        // ไม่กรองวันที่ = ทุกแถว (~2,400) ใช้หน่วยความจำ > 128M ตอนเขียน xlsx (ทดสอบ 26/11/2569)
        ini_set('memory_limit', '512M');

        $complainOrder = "(nc.ComplainNO REGEXP '^[0-9]+$') DESC, CAST(nc.ComplainNO AS UNSIGNED), nc.ComplainNO";

        $q = $this->filtered($request)
            ->leftJoin('result', 'result.status', '=', 'nc.degree')
            // ส่วนที่แก้ไข (treat) ผูกด้วย NCRNo — เฉพาะเลขจริง (treat มีแถว NCRNo "--" ค้างอยู่ ไม่ให้ไปติดทุกแถวที่ยังไม่ออกเลข)
            ->leftJoin('treat', function ($j) {
                $j->on('treat.NCRNo', '=', DB::raw('TRIM(nc.NCRNo)'))
                  ->whereRaw("TRIM(nc.NCRNo) NOT IN ('', '-', '--')");
            })
            ->select('nc.*', 'result.rem as degree_name',
                'treat.claim as t_claim', 'treat.TreatNo as t_no', 'treat.TreatRaw as t_raw', 'treat.TreatKG as t_kg');

        switch ($type) {
            case 'product':
                $q->orderByRaw("nc.ProdNo IS NULL OR nc.ProdNo = '', nc.ProdNo")->orderByRaw($complainOrder);
                break;
            case 'customer':
                $q->orderByRaw("nc.custno IS NULL OR nc.custno = '', nc.custno")->orderByRaw($complainOrder);
                break;
            case 'ncr':
                $q->whereRaw("TRIM(nc.NCRNo) NOT IN ('', '-', '--')")->whereNotNull('nc.NCRNo')
                  ->orderBy('nc.NCRDate')->orderByRaw('nc.NCRNo');
                break;
            default:
                $q->orderBy('nc.NCRDate')->orderByRaw($complainOrder);
        }
        $q->orderBy('nc.id');

        $rows = $q->get();

        $cols = [
            // [หัวคอลัมน์ (ป้ายเดียวกับฟอร์ม), ค่า, รูปแบบ num|wrap]
            ['เลขที่ใบร้องเรียน',   fn ($r) => $r->ComplainNO],
            ['วันที่ออก NCR',      fn ($r) => $this->fmtDate($r->NCRDate)],
            ['รหัสลูกค้า',         fn ($r) => $r->custno],
            ['ชื่อลูกค้า',          fn ($r) => $r->custname],
            ['รหัสสินค้า',         fn ($r) => $r->ProdNo],
            ['LotNo',            fn ($r) => $r->LotNo],
            ['น้ำหนัก (กก.)',      fn ($r) => $r->QTY === null ? null : (float) $r->QTY, 'num'],
            ['ปัญหา',             fn ($r) => $r->Subject, 'wrap'],
            ['ผล',                fn ($r) => $r->degree ? $r->degree.' = '.($r->degree_name ?? '') : ''],
            ['Off Spec',         fn ($r) => $r->OffSpec ? '✓' : ''],
            ['NCRNo',            fn ($r) => Nc::isRealNcrNo($r->NCRNo) ? $r->NCRNo : '-'],
            ['สรุปสาเหตุ',         fn ($r) => $r->CauseType],
            ['สาเหตุ',            fn ($r) => $r->Cause, 'wrap'],
            ['วิธีแก้ไข',          fn ($r) => $r->Correct, 'wrap'],
            ['วิธีป้องกัน',         fn ($r) => $r->Protect, 'wrap'],
            ['ความเห็น ผู้บริหาร',  fn ($r) => $r->MGRcomment, 'wrap'],
            // ส่วนที่แก้ไข (treat) — ไม่มีแถว = ว่าง
            ['เงิน claim',         fn ($r) => $r->t_claim === null ? null : (float) $r->t_claim, 'num'],
            ['เลขที่ใบแก้ไข',       fn ($r) => $r->t_no],
            ['ค่าวัตถุดิบ',         fn ($r) => $r->t_raw === null ? null : (float) $r->t_raw, 'num'],
            ['น.น.แก้ไข',          fn ($r) => $r->t_kg === null ? null : (float) $r->t_kg, 'num'],
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Complaint NCR');
        $last = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($cols));

        $period = trim(($request->date_from ? 'ตั้งแต่ '.$request->date_from : '').' '.($request->date_to ? 'ถึง '.$request->date_to : ''));
        $sheet->setCellValue('A1', 'รายงาน Complaint & NCR — '.self::EXPORT_TYPES[$type]);
        $sheet->setCellValue('A2', ($period ?: 'ทุกช่วงวันที่').' · พบ '.$rows->count().' รายการ · พิมพ์ '.now()->format('d/m/Y H:i'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        foreach ($cols as $i => $c) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1).'4', $c[0]);
        }
        $sheet->getStyle("A4:{$last}4")->getFont()->setBold(true);
        $sheet->getStyle("A4:{$last}4")->getFill()->setFillType('solid')->getStartColor()->setRGB('DDEBF7');
        $sheet->getStyle("A4:{$last}4")->getAlignment()->setWrapText(true)->setVertical('center');

        $r = 5;
        foreach ($rows as $row) {
            foreach ($cols as $i => $c) {
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1).$r, $c[1]($row));
            }
            $r++;
        }

        foreach ($cols as $i => $c) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            if (($c[2] ?? null) === 'num') {
                $sheet->getStyle("{$letter}5:{$letter}".max(5, $r))->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getColumnDimension($letter)->setWidth(14);
            } elseif (($c[2] ?? null) === 'wrap') {
                $sheet->getColumnDimension($letter)->setWidth(40);
                $sheet->getStyle("{$letter}5:{$letter}".max(5, $r))->getAlignment()->setWrapText(true);
            } else {
                $sheet->getColumnDimension($letter)->setAutoSize(true);
            }
        }
        if ($r > 5) {
            $sheet->getStyle("A4:{$last}".($r - 1))->getBorders()->getAllBorders()->setBorderStyle('thin');
            $sheet->getStyle("A5:{$last}".($r - 1))->getAlignment()->setVertical('top');
        }
        $sheet->freezePane('A5');

        $fileName = 'complaint-ncr-'.$type.'-'.now()->format('Ymd-His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ───────────────────────── helper ─────────────────────────

    /** วันที่รูปแบบ DD/MM/YY ตามสเปก */
    private function fmtDate($v): string
    {
        return $v ? date('d/m/y', strtotime((string) $v)) : '';
    }

    /** d/m/y · d/m/Y · Y-m-d → Y-m-d · แปลงไม่ได้ = null (ปี 2 หลัก = 20yy) */
    private function parseDate($value): ?string
    {
        $value = trim((string) $value);

        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{2}|\d{4})$#', $value, $m)) {
            $y = strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3];

            return checkdate((int) $m[2], (int) $m[1], $y) ? sprintf('%04d-%02d-%02d', $y, $m[2], $m[1]) : null;
        }
        if (preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2})$#', $value, $m)) {
            return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]) : null;
        }

        return null;
    }
}
