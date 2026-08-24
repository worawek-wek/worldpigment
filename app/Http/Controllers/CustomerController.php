<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * ฐานข้อมูลลูกค้า (เมนู C-ฐานข้อมูลลูกค้า, /customer) — 24/08/2569
 *
 * แปลงผังมาจากฟอร์ม Access "ข้อมูลลูกค้า" โดยตรง
 *
 * ตารางที่ใช้ (legacy ทั้งหมด — MyISAM ไม่รองรับ transaction):
 *   customer  ข้อมูลลูกค้า (PK = code)
 *   contact   ผู้ติดต่อของลูกค้า (PK = code + contactname) — ตารางล่างของฟอร์มเดิม
 *   naddress  สถานที่ส่งของลูกค้า (PK = Custno + DVpoint) — ใบสั่งซื้อเอาไปใช้เป็น dropdown
 *   engname   ชื่อลูกค้าภาษาอังกฤษ (PK = code) — ที่เก็บจริงของชื่ออังกฤษ (customer.nameEN แทบไม่มีข้อมูล)
 *   c_type    ประเภทอุตสาหกรรมของลูกค้า (ช่อง "ประเภทลูกค้า")
 *   emp       ใช้แปลง "รหัสผู้ขาย" (customer.sale = emp.supno) เป็นชื่อพนักงานขาย
 */
class CustomerController extends Controller
{
    /** whitelist การเรียง: key หัวตาราง → คอลัมน์จริง (กัน SQL injection) */
    private const SORTABLE = [
        'code' => 'customer.code',
        'name' => 'customer.name',
        'city' => 'customer.city',
        'type' => 'customer.type',
        'sale' => 'customer.sale',
        'term' => 'customer.term',
    ];

    /** คอลัมน์ checkbox ของ Access เก็บ -1 = ติ๊ก, 0/NULL = ไม่ติ๊ก */
    public static function checked($value): bool
    {
        return $value !== null && (int) $value !== 0;
    }

    /** ค่าที่จะเขียนกลับลง DB ให้เป็นแบบ Access */
    private static function checkbox($value): int
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? -1 : 0;
    }

    // ─────────────────────────────────────────────────────────────
    //  หน้าหลัก + ตารางรายการ
    // ─────────────────────────────────────────────────────────────

    public function index()
    {
        $data['page_url'] = 'customer';

        // ตัวเลือกของแถบตัวกรอง
        $data['types'] = DB::table('c_type')->orderBy('type')->get();

        $data['sales'] = $this->saleOptions();

        $data['cities'] = DB::table('customer')
            ->whereRaw("TRIM(COALESCE(city, '')) <> ''")
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('customer.index', $data);
    }

    /**
     * GET — ตารางรายการลูกค้า (HTML partial ใส่ #table-data)
     */
    public function datatable(Request $request)
    {
        $sortKey = (string) $request->sort_col;
        $sortKey = isset(self::SORTABLE[$sortKey]) ? $sortKey : 'code';
        $sortDir = strtolower((string) $request->sort_dir) === 'desc' ? 'desc' : 'asc';

        $query = Customer::query()
            ->leftJoin('c_type as ct', 'ct.type', '=', 'customer.type')
            ->leftJoin('engname as en', 'en.code', '=', 'customer.code')
            ->select('customer.*', 'ct.t_namee as type_name')
            ->selectRaw("NULLIF(TRIM(COALESCE(NULLIF(TRIM(en.name), ''), customer.nameEN, '')), '') as name_en")
            ->selectSub(
                DB::table('contact')->selectRaw('COUNT(*)')->whereColumn('contact.code', 'customer.code'),
                'contact_count'
            )
            ->orderBy(self::SORTABLE[$sortKey], $sortDir);

        // เรียงรองด้วยรหัสลูกค้า ให้ลำดับคงที่เมื่อค่าคอลัมน์หลักซ้ำกัน
        if ($sortKey !== 'code') {
            $query->orderBy('customer.code', 'asc');
        }

        $this->applyFilters($query, $request);

        $limit = $request->input('limit') ?: 15;

        $data['list_data'] = $query->paginate($limit);
        $data['sort_col']  = $sortKey;
        $data['sort_dir']  = $sortDir;

        return view('customer.table', $data);
    }

    /** ตัวกรองของหน้ารายการ */
    private function applyFilters($query, Request $request): void
    {
        // ค้นหารวมช่องเดียว: รหัส / ชื่อไทย / ชื่ออังกฤษ / ชื่อเล่น / โทร / แฟกซ์ / เลขผู้เสียภาษี / ที่อยู่
        if ($search = trim((string) $request->input('search'))) {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('customer.code', 'like', $like)
                    ->orWhere('customer.name', 'like', $like)
                    ->orWhere('customer.nameEN', 'like', $like)
                    ->orWhere('en.name', 'like', $like)
                    ->orWhere('customer.nickname', 'like', $like)
                    ->orWhere('customer.tel', 'like', $like)
                    ->orWhere('customer.fax', 'like', $like)
                    ->orWhere('customer.taxid', 'like', $like)
                    ->orWhere('customer.amphur', 'like', $like)
                    ->orWhere('customer.road', 'like', $like);
            });
        }

        // ประเภทลูกค้า
        if (trim((string) $request->input('type')) !== '') {
            $query->where('customer.type', (int) $request->input('type'));
        }

        // รหัสผู้ขาย
        if (($sale = trim((string) $request->input('sale'))) !== '') {
            $query->where('customer.sale', $sale);
        }

        // จังหวัด
        if (($city = trim((string) $request->input('city'))) !== '') {
            $query->where('customer.city', $city);
        }

        // สถานะ Blacklist — ค่า -1 คือ "ติด Blacklist" ตาม convention ของ Access
        // (ค่าอื่นที่พบใน DB เช่น -3 / 2 / 5 ยังไม่ทราบความหมาย จึงไม่เอามาเป็นตัวเลือก)
        $black = (string) $request->input('black');
        if ($black === 'Y') {
            $query->where('customer.black', -1);
        } elseif ($black === 'N') {
            $query->where(function ($q) {
                $q->whereNull('customer.black')->orWhere('customer.black', '<>', -1);
            });
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  ฟอร์มข้อมูลลูกค้า (modal)
    // ─────────────────────────────────────────────────────────────

    /**
     * GET — คืน HTML ของฟอร์ม (ไม่ส่ง code = เพิ่มลูกค้าใหม่)
     */
    public function form(Request $request)
    {
        $code = trim((string) $request->input('code'));

        $customer = $code === '' ? null : Customer::find($code);

        if ($code !== '' && !$customer) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบข้อมูลลูกค้ารหัส ' . $code,
            ]);
        }

        $contacts = $customer
            ? DB::table('contact')->where('code', $customer->code)->orderBy('contactname')->get()
            : collect();

        $dvpoints = $customer
            ? DB::table('naddress')->where('Custno', $customer->code)->orderBy('DVpoint')->pluck('DVpoint')
            : collect();

        // ชื่ออังกฤษ: ที่เก็บหลักคือ engname — ถ้าไม่มีค่อยใช้ customer.nameEN
        $nameEn = null;
        if ($customer) {
            $nameEn = DB::table('engname')->where('code', $customer->code)->value('name');
            if (trim((string) $nameEn) === '') {
                $nameEn = $customer->nameEN;
            }
        }

        $html = view('customer.customer-form', [
            'customer'  => $customer,
            'contacts'  => $contacts,
            'dvpoints'  => $dvpoints,
            'name_en'   => $nameEn,
            'types'     => DB::table('c_type')->orderBy('type')->get(),
            'sales'     => $this->saleOptions(),
            'sale_name' => $customer ? $this->saleName($customer->sale) : null,
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html,
        ]);
    }

    /**
     * POST — บันทึกข้อมูลลูกค้า (เพิ่มใหม่ / แก้ไข)
     *
     * ⚠ ตาราง legacy เป็น MyISAM → ไม่มี transaction จริง
     *   ถ้าพังกลางคันจะเหลือข้อมูลค้างครึ่ง ๆ (customer บันทึกแล้ว แต่ contact ยังไม่ครบ)
     *   จึงบันทึกหัวข้อมูลลูกค้าให้ผ่านก่อน แล้วค่อยไล่บันทึกตารางลูก
     */
    public function save(Request $request)
    {
        $mode = $request->input('mode') === 'insert' ? 'insert' : 'update';
        $code = strtoupper(trim((string) $request->input('code')));

        $rules = [
            'code'      => ['required', 'string', 'max:6', 'regex:/^[A-Za-z0-9\-]+$/'],
            'name'      => ['required', 'string', 'max:70'],
            'name_en'   => ['nullable', 'string', 'max:60'],
            'no'        => ['nullable', 'string', 'max:40'],
            'road'      => ['nullable', 'string', 'max:65'],
            'amphur'    => ['nullable', 'string', 'max:40'],
            'city'      => ['nullable', 'string', 'max:20'],
            'zip'       => ['nullable', 'string', 'max:6'],
            'tel'       => ['nullable', 'string', 'max:23'],
            'fax'       => ['nullable', 'string', 'max:12'],
            'term'      => ['nullable', 'string', 'max:15'],
            'cashdisc'  => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sale'      => ['nullable', 'string', 'max:10'],
            'type'      => ['nullable', 'integer'],
            'remark'    => ['nullable', 'string', 'max:50'],
            'condition' => ['nullable', 'string', 'max:50'],
            'taxid'     => ['nullable', 'string', 'max:50'],
            'branch'    => ['nullable', 'string', 'max:15'],
            'legal'     => ['nullable', 'string', 'max:50'],
            'copyinv'   => ['nullable', 'string', 'max:20'],
            'nickname'  => ['nullable', 'string', 'max:30'],
            'custtime'  => ['nullable', 'string', 'max:20'],
            'cashchq'   => ['nullable', 'string', 'max:20'],
            'cust_desc' => ['nullable', 'string', 'max:50'],

            'contacts'               => ['nullable', 'array'],
            'contacts.*.contactname' => ['nullable', 'string', 'max:20'],
            'contacts.*.position'    => ['nullable', 'string', 'max:20'],
            'contacts.*.tel'         => ['nullable', 'string', 'max:30'],
            'contacts.*.fax'         => ['nullable', 'string', 'max:20'],
            'contacts.*.remark'      => ['nullable', 'string', 'max:30'],

            'dvpoints'   => ['nullable', 'array'],
            'dvpoints.*' => ['nullable', 'string', 'max:20'],
        ];

        $messages = [
            'code.required'    => 'กรุณากรอกรหัสลูกค้า',
            'code.max'         => 'รหัสลูกค้ายาวได้ไม่เกิน 6 ตัวอักษร',
            'code.regex'       => 'รหัสลูกค้าใช้ได้เฉพาะตัวอักษรภาษาอังกฤษ ตัวเลข และเครื่องหมาย -',
            'name.required'    => 'กรุณากรอกชื่อลูกค้า',
            'name.max'         => 'ชื่อลูกค้ายาวได้ไม่เกิน 70 ตัวอักษร',
            'cashdisc.numeric' => 'ส่วนลดต้องเป็นตัวเลข',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $existing = Customer::find($code);

        if ($mode === 'insert' && $existing) {
            return response()->json([
                'status'  => 422,
                'message' => 'รหัสลูกค้า ' . $code . ' ถูกใช้ไปแล้ว (' . $existing->name . ')',
            ], 422);
        }

        if ($mode === 'update' && !$existing) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบข้อมูลลูกค้ารหัส ' . $code,
            ], 404);
        }

        // ─── หัวข้อมูลลูกค้า ───────────────────────────────────────
        // เขียนเฉพาะคอลัมน์ที่ฟอร์มดูแล — คอลัมน์อื่น (black/blackdate/blackrem/zone/DV/flprt)
        // ไม่ถูกแตะ เพราะยังไม่ทราบความหมายหรือถูกจัดการจากที่อื่น
        $payload = [
            'name'      => $this->nn($request->input('name')),
            'no'        => $this->nn($request->input('no')),
            'road'      => $this->nn($request->input('road')),
            'amphur'    => $this->nn($request->input('amphur')),
            'city'      => $this->nn($request->input('city')),
            'zip'       => $this->nn($request->input('zip')),
            'tel'       => $this->nn($request->input('tel')),
            'fax'       => $this->nn($request->input('fax')),
            'term'      => $this->nn($request->input('term')),
            'cashdisc'  => $request->filled('cashdisc') ? (int) $request->input('cashdisc') : null,
            'sale'      => $this->nn($request->input('sale')),
            'type'      => $request->filled('type') ? (int) $request->input('type') : null,
            'remark'    => $this->nn($request->input('remark')),
            'condition' => $this->nn($request->input('condition')),
            'taxid'     => $this->nn($request->input('taxid')),
            'Branch'    => $this->nn($request->input('branch')),
            'legal'     => $this->nn($request->input('legal')),
            'CopyINV'   => $this->nn($request->input('copyinv')),
            'nickname'  => $this->nn($request->input('nickname')),
            'custTime'  => $this->nn($request->input('custtime')),
            'CashChq'   => $this->nn($request->input('cashchq')),
            'cust_desc' => $this->nn($request->input('cust_desc')),
            'nameEN'    => $this->nn($request->input('name_en')),
            'RP'        => self::checkbox($request->input('rp')),
            'CER'       => self::checkbox($request->input('cer')),
            'PO'        => self::checkbox($request->input('po')),
            'MSDS'      => self::checkbox($request->input('msds')),
        ];

        if ($existing) {
            DB::table('customer')->where('code', $code)->update($payload);
        } else {
            DB::table('customer')->insert($payload + ['code' => $code]);
        }

        // ─── ตารางลูก ────────────────────────────────────────────
        $this->syncContacts($code, (array) $request->input('contacts', []));
        $this->syncDeliveryPoints($code, (array) $request->input('dvpoints', []));
        $this->syncEngName($code, $this->nn($request->input('name_en')));

        return response()->json([
            'status'  => 200,
            'code'    => $code,
            'message' => $existing ? 'บันทึกการแก้ไขเรียบร้อย' : 'เพิ่มลูกค้าใหม่เรียบร้อย',
        ]);
    }

    /**
     * POST — ลบลูกค้า
     *
     * ลบได้เฉพาะลูกค้าที่ยังไม่มีธุรกรรมผูกอยู่ (ใบสั่งซื้อ / ใบเสนอราคา / เทียบสี / ราคา / แผนผลิต)
     * เพราะตาราง legacy ไม่มี foreign key — ถ้าลบทิ้ง ข้อมูลเก่าจะกลายเป็นเอกสารไร้เจ้าของ
     */
    public function destroy(Request $request)
    {
        $code = trim((string) $request->input('code'));

        $customer = $code === '' ? null : Customer::find($code);

        if (!$customer) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบข้อมูลลูกค้าที่ต้องการลบ',
            ], 404);
        }

        $used = $this->relatedCounts($code);

        if ($used) {
            return response()->json([
                'status'  => 422,
                'message' => 'ลบไม่ได้ — ลูกค้ารายนี้มีข้อมูลผูกอยู่: ' . implode(', ', $used),
            ], 422);
        }

        DB::table('customer')->where('code', $code)->delete();
        DB::table('contact')->where('code', $code)->delete();
        DB::table('naddress')->where('Custno', $code)->delete();
        DB::table('engname')->where('code', $code)->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'ลบข้อมูลลูกค้าเรียบร้อย',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  ตัวช่วย
    // ─────────────────────────────────────────────────────────────

    /** trim แล้วคืน null ถ้าว่าง (ตาราง legacy เก็บทั้ง '' และ NULL ปนกัน) */
    private function nn($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * บันทึกผู้ติดต่อ — PK เป็น (code + contactname) และไม่มี id
     * จึงลบของลูกค้ารายนั้นทิ้งทั้งหมดแล้ว insert ใหม่ตามที่กรอกในฟอร์ม
     */
    private function syncContacts(string $code, array $rows): void
    {
        $insert = [];
        $seen   = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row['contactname'] ?? ''));

            // แถวที่ยังไม่กรอกชื่อผู้ติดต่อ = แถวเปล่า (ชื่อเป็นส่วนหนึ่งของ PK จึงว่างไม่ได้)
            if ($name === '') {
                continue;
            }

            // ชื่อซ้ำในฟอร์มเดียวกันเก็บได้แถวเดียว (PK ซ้ำไม่ได้)
            if (isset($seen[$name])) {
                continue;
            }
            $seen[$name] = true;

            $insert[] = [
                'code'        => $code,
                'contactname' => $name,
                'position'    => $this->nn($row['position'] ?? null),
                'tel'         => $this->nn($row['tel'] ?? null),
                'fax'         => $this->nn($row['fax'] ?? null),
                'remark'      => $this->nn($row['remark'] ?? null),
            ];
        }

        DB::table('contact')->where('code', $code)->delete();

        if ($insert) {
            DB::table('contact')->insert($insert);
        }
    }

    /** บันทึกสถานที่ส่ง (naddress) — PK = Custno + DVpoint เช่นเดียวกัน */
    private function syncDeliveryPoints(string $code, array $points): void
    {
        $insert = [];
        $seen   = [];

        foreach ($points as $point) {
            $point = trim((string) $point);

            if ($point === '' || isset($seen[$point])) {
                continue;
            }
            $seen[$point] = true;

            $insert[] = ['Custno' => $code, 'DVpoint' => $point];
        }

        DB::table('naddress')->where('Custno', $code)->delete();

        if ($insert) {
            DB::table('naddress')->insert($insert);
        }
    }

    /** ชื่อภาษาอังกฤษเก็บที่ตาราง engname (customer.nameEN เขียนคู่กันไว้ให้หน้าใบเสนอราคาที่อ่านคอลัมน์นั้น) */
    private function syncEngName(string $code, ?string $name): void
    {
        if ($name === null) {
            DB::table('engname')->where('code', $code)->delete();

            return;
        }

        $exists = DB::table('engname')->where('code', $code)->exists();

        if ($exists) {
            DB::table('engname')->where('code', $code)->update(['name' => $name]);
        } else {
            DB::table('engname')->insert(['code' => $code, 'name' => $name]);
        }
    }

    /** ธุรกรรมที่อ้างถึงลูกค้ารายนี้ — ใช้กันการลบ */
    private function relatedCounts(string $code): array
    {
        $checks = [
            'ใบสั่งซื้อ'       => ['morder', 'Custno'],
            'ใบเสนอราคา'      => ['qmast', 'Custid'],
            'ใบเทียบสี'       => ['testmain', 'custno'],
            'ราคาที่อนุมัติ'   => ['zcustprice', 'custno'],
            'ใบขออนุมัติราคา'  => ['appvreq', 'custno'],
            'แผนการผลิต'      => ['tb_planning_header', 'custno'],
        ];

        $used = [];

        foreach ($checks as $label => [$table, $column]) {
            $count = DB::table($table)->where($column, $code)->count();

            if ($count > 0) {
                $used[] = $label . ' ' . number_format($count) . ' รายการ';
            }
        }

        return $used;
    }

    /**
     * ตัวเลือก "รหัสผู้ขาย" — ค่าที่เคยใช้จริงใน customer.sale
     * แนบชื่อพนักงานให้ถ้าจับคู่ emp.supno ได้ (พนักงานขายเก่าหลายรหัสไม่มีใน emp แล้ว)
     */
    private function saleOptions()
    {
        $names = DB::table('emp')
            ->whereRaw("TRIM(COALESCE(supno, '')) <> ''")
            ->pluck(DB::raw("TRIM(CONCAT(COALESCE(empname, ''), ' ', COALESCE(empsur, '')))"), 'supno');

        return DB::table('customer')
            ->whereRaw("TRIM(COALESCE(sale, '')) <> ''")
            ->distinct()
            ->orderByRaw('CAST(sale AS UNSIGNED), sale')
            ->pluck('sale')
            ->map(function ($sale) use ($names) {
                $name = trim((string) ($names[$sale] ?? ''));

                return [
                    'sale'  => $sale,
                    'label' => $name === '' ? $sale : $sale . ' — ' . $name,
                ];
            });
    }

    /** ชื่อพนักงานขายจากรหัส (customer.sale = emp.supno) */
    private function saleName($sale): ?string
    {
        $sale = trim((string) $sale);

        if ($sale === '') {
            return null;
        }

        $emp = DB::table('emp')->where('supno', $sale)->first(['empname', 'empsur']);

        if (!$emp) {
            return null;
        }

        return trim(($emp->empname ?? '') . ' ' . ($emp->empsur ?? '')) ?: null;
    }
}
