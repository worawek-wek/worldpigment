<?php

namespace App\Http\Controllers;

use App\Models\PriceRule;
use App\Models\Saleinfo;
use App\Services\ProductPriceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * กำหนดราคา (ราคาสินค้าต่อลูกค้า)
 *
 * เขียนลงตาราง `tb_saleinfo` — ชื่อคอลัมน์ยึดตาม `uprice` ของเดิม
 * (`uprice` = ข้อมูลจอเก่าของลูกค้า ใช้อ่านอย่างเดียว ห้ามเขียนทับ)
 * แถบข้อมูลลูกค้าดึงจากตาราง `customer` ผ่าน CustNo → code
 *
 * ยังไม่ทำ: ราคา 1/2/3 (DB tier) + ค่าสี/%สี — รอสรุปสูตรกับลูกค้า
 */
class SaleinfoController extends Controller
{
    /** คอลัมน์ที่รับจากฟอร์มได้ */
    private const COLUMNS = [
        'CustNo', 'st_code', 'ITEMNO', 'DATE', 'NotifyDate', 'MOQ', 'PRICE',
        'REM1', 'PackRem', 'Label', 'Author',
        // 'REM2',    // ปิดไว้ — เลิกใช้ช่อง "ประวัติการปรับราคา" แบบข้อความ (มีตารางประวัติแทนแล้ว)
        // 'NoAcp',   // ปิดไว้ก่อน — รอลูกค้ายืนยันความหมาย (ดู extractForm)
    ];

    public function index(Request $request)
    {
        $data['page_url'] = 'saleinfo';

        return view('saleinfo.index', $data);
    }

    /**
     * GET — รายการราคา (tb_saleinfo) + ชื่อ/เงื่อนไขลูกค้าจากตาราง customer
     */
    public function datatable(Request $request)
    {
        $results = Saleinfo::query()
            ->leftJoin('customer as c', 'tb_saleinfo.CustNo', '=', 'c.code')
            ->select('tb_saleinfo.*', 'c.name as custname', 'c.term as term')
            ->orderByDesc('tb_saleinfo.id');

        $this->applyFilters($results, $request);

        $limit = @$request['limit'] ?: 15;

        $data['list_data'] = $results->paginate($limit);

        return view('saleinfo.table', $data);
    }

    /**
     * GET — ข้อมูลลูกค้าจากรหัส (ให้ฟอร์มเติมแถบข้อมูลลูกค้าอัตโนมัติ)
     */
    public function customerLookup($code)
    {
        $cust = DB::table('customer')
            ->where('code', $code)
            ->first(['code', 'name', 'nameEN', 'road', 'term']);

        if (!$cust) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'code'   => $cust->code,
            'name'   => $cust->name ?: $cust->nameEN,
            'road'   => $cust->road,
            'term'   => $cust->term,
        ]);
    }

    /**
     * ตารางสำเนาจากไฟล์ Access — ชื่อแท็บ → ชื่อตารางบน MySQL + คอลัมน์ที่ใช้ค้นหา
     *
     * (ดู migration create_access_mirror_tables — ข้อมูลถูกคัดลอกมาจาก formula_2000.mdb)
     */
    private const ACCESS_TABS = [
        'compo'   => ['table' => 'access_compo',   'search' => ['PdCode', 'PdCodes', 'TestNo', 'ChangeNo']],
        'pdprice' => ['table' => 'access_pdprice', 'search' => ['PdCode']],
        'testmai' => ['table' => 'access_testmai', 'search' => ['TestNo', 'Lotno', 'TDecs', 'CCode', 'CName', 'Resin', 'Matcher']],
    ];

    /**
     * GET — ข้อมูลดิบของตารางที่ยกมาจากไฟล์ Access (อ่านอย่างเดียว)
     *
     * ใช้ให้ลูกค้าตรวจว่าข้อมูลที่ย้ายขึ้น server มาครบ/ตรงกับไฟล์เดิมไหม
     * คืน HTML partial ให้ AJAX เอาไปแปะ (รูปแบบเดียวกับ datatable ของหน้านี้)
     */
    public function accessData(Request $request)
    {
        $tab = $request->query('tab', 'pdprice');
        if (!isset(self::ACCESS_TABS[$tab])) {
            $tab = 'pdprice';
        }

        $config = self::ACCESS_TABS[$tab];
        $query  = DB::table($config['table'])->orderBy('id');   // เรียงตาม id = ลำดับเดิมในไฟล์ Access

        $search = trim((string) $request->query('access_search'));
        if ($search !== '') {
            $query->where(function ($q) use ($config, $search) {
                foreach ($config['search'] as $col) {
                    $q->orWhere($col, 'LIKE', "%{$search}%");
                }
            });
        }

        $limit = (int) $request->query('access_limit', 15);
        $limit = in_array($limit, [15, 50, 100], true) ? $limit : 15;

        // ไม่ append query เดิมเข้า URL ของเลขหน้า — ฝั่ง JS ส่ง tab/คำค้น/limit มาให้ทุกครั้งอยู่แล้ว
        // ถ้า append ด้วยจะได้ query ซ้ำสองชุดใน URL
        // withPath: ผูก URL ของเลขหน้ากับ path ของ request นี้ตรง ๆ ไม่พึ่ง path resolver ส่วนกลาง
        $data['tab']       = $tab;
        $data['list_data'] = $query->paginate($limit)->withPath($request->url());

        return view('saleinfo.access-table', $data);
    }

    /**
     * GET — ค้นหาราคาสินค้า (modal "New Price")
     *
     * ราคาทุนมาจากตาราง `access_pdprice` (สำเนาของ PdPrice ในไฟล์ Access)
     * แล้วคิดราคาขายตามตารางเงื่อนไขของลูกค้าใน `config/product_price.php`
     *   ราคาขาย 1 = Price × คูณ ÷ หาร + บวก → ราคาขาย 2 = 1 × 1.14 → ราคาขาย 3 = 2 × 1.30
     */
    public function priceLookup(Request $request, ProductPriceService $prices)
    {
        $code = trim((string) $request->query('code'));

        try {
            return response()->json($prices->lookup($code));
        } catch (\Throwable $e) {
            // อ่านตารางราคาทุนไม่ได้ (ยังไม่ได้ migrate / ยังไม่ได้ import ข้อมูล)
            // — บอกให้รู้ ไม่ใช่โชว์ราคา 0 ให้เข้าใจผิด
            return response()->json([
                'found'      => false,
                'code'       => $code,
                'base_price' => null,
                'rule'       => null,
                'prices'     => null,
                'reason'     => 'อ่านข้อมูลราคาทุนไม่ได้: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET — ตารางเงื่อนไขราคา (JSON) สำหรับ modal "ตั้งค่าเงื่อนไขราคา" (10/08/2569)
     *
     * โครงเงื่อนไข (ชื่อ/ตัวขึ้นต้น/ตัวลงท้าย) มาจาก config อ่านอย่างเดียว
     * ส่วน คูณ/หาร/บวก คือค่าที่แก้ได้ — ถ้าเคยแก้แล้วจะเป็นค่าจาก tb_price_rule
     */
    public function priceRules(ProductPriceService $prices)
    {
        $rows = array_map(function ($rule) {
            return [
                'key'        => $rule['key'] ?? null,
                'label'      => $rule['label'] ?? '',
                'prefix'     => implode(', ', $rule['prefix'] ?? []),
                'suffix'     => implode(', ', $rule['suffix'] ?? []),
                'suffix_pos' => $rule['suffix_pos'] ?? null,
                'mul'        => (float) $rule['mul'],
                'div'        => (float) $rule['div'],
                'add'        => (float) $rule['add'],
                'default'    => $rule['default'],
                'is_custom'  => $rule['is_custom'],
            ];
        }, $prices->rules());

        // แถวที่ไม่มี key ตั้งค่าจากหน้าจอไม่ได้ (ผูกกับ tb_price_rule ไม่ได้) — กันไว้ให้เห็นชัด
        $rows = array_values(array_filter($rows, fn ($r) => $r['key'] !== null));

        return response()->json(['rows' => $rows]);
    }

    /**
     * POST — บันทึกค่า คูณ/หาร/บวก ที่ผู้ใช้แก้ (10/08/2569)
     *
     * รับมาทีละหลายแถว: rules[<key>][mul|div|add]
     * แถวไหนค่าตรงกับค่าตั้งต้นใน config → ลบ override ทิ้ง ให้กลับไปใช้ค่าตั้งต้น
     */
    public function priceRulesUpdate(Request $request, ProductPriceService $prices)
    {
        $input = $request->input('rules', []);

        if (!is_array($input) || $input === []) {
            return response()->json(['error' => 'ไม่มีข้อมูลที่จะบันทึก'], 422);
        }

        // เทียบกับ config เสมอ — กันไม่ให้ยัด key แปลกปลอมเข้าตาราง
        $defaults = [];
        foreach (config('product_price.rules', []) as $rule) {
            if (isset($rule['key'])) {
                $defaults[$rule['key']] = $rule;
            }
        }

        $errors = [];
        $clean  = [];

        foreach ($input as $key => $values) {
            if (!isset($defaults[$key])) {
                continue;   // key ที่ไม่มีใน config — ข้ามไปเงียบ ๆ
            }

            $label = $defaults[$key]['label'];
            $mul   = $this->numberOrNull($values['mul'] ?? null);
            $div   = $this->numberOrNull($values['div'] ?? null);
            $add   = $this->numberOrNull($values['add'] ?? null);

            if ($mul === null || $div === null || $add === null) {
                $errors[] = "\"{$label}\" — ต้องกรอกตัวเลขให้ครบทั้ง คูณ / หาร / บวก";
                continue;
            }

            // แถว 0/0/0 คือแถวที่ตั้งใจปิดไว้ (Pigment/เคมี) — ยอมให้เป็น 0 ทั้งชุดได้
            // แต่ห้ามหารด้วย 0 ทั้งที่ตัวคูณไม่เป็น 0 เพราะจะพังตอนคำนวณ
            if ((float) $div == 0.0 && ((float) $mul != 0.0 || (float) $add != 0.0)) {
                $errors[] = "\"{$label}\" — ช่องหารเป็น 0 ไม่ได้";
                continue;
            }

            $clean[$key] = ['mul' => $mul, 'div' => $div, 'add' => $add];
        }

        if ($errors) {
            return response()->json(['error' => implode("\n", $errors)], 422);
        }

        $user    = auth()->user()?->name ?: auth()->user()?->username;
        $changed = 0;

        DB::transaction(function () use ($clean, $defaults, $user, &$changed) {
            foreach ($clean as $key => $values) {
                $default = $defaults[$key];

                $isDefault = (float) $values['mul'] == (float) $default['mul']
                    && (float) $values['div'] == (float) $default['div']
                    && (float) $values['add'] == (float) $default['add'];

                if ($isDefault) {
                    $changed += PriceRule::where('rule_key', $key)->delete();
                    continue;
                }

                $row = PriceRule::firstOrNew(['rule_key' => $key]);

                if ((float) $row->mul == (float) $values['mul']
                    && (float) $row->div == (float) $values['div']
                    && (float) $row->add == (float) $values['add']
                    && $row->exists) {
                    continue;   // ไม่มีอะไรเปลี่ยน
                }

                $row->fill($values);
                $row->updated_by = $user ?: null;
                $row->save();
                $changed++;
            }
        });

        return response()->json(['saved' => true, 'changed' => $changed]);
    }

    /** แปลงค่าที่กรอกเป็นตัวเลข — ว่าง/ไม่ใช่ตัวเลข = null ให้ผู้เรียกไปแจ้ง error เอง */
    private function numberOrNull($value): ?float
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    /**
     * GET — อ่านราคา 1 รายการ (JSON) สำหรับเติมฟอร์มโหมดแก้ไข
     */
    public function edit($id)
    {
        $row = Saleinfo::find($id);
        if (!$row) {
            return response()->json(['error' => 'not_found'], 404);
        }

        // ฟอร์มใช้ flatpickr d/m/Y → แปลงให้ตรงรูปแบบก่อนส่งกลับ
        $data = $row->toArray();
        $data['DATE']       = $row->DATE ? Carbon::parse($row->DATE)->format('d/m/Y') : '';
        $data['NotifyDate'] = $row->NotifyDate ? Carbon::parse($row->NotifyDate)->format('d/m/Y') : '';

        return response()->json(['found' => true, 'data' => $data]);
    }

    /**
     * GET — ประวัติการปรับราคาของคู่ลูกค้า/สินค้า (JSON)
     *
     * ใช้เติมตาราง "ประวัติการปรับราคา" ในฟอร์ม เมื่อกรอกรหัสลูกค้า + รหัสสินค้า
     * ตรงกับที่เคยบันทึกไว้ — เรียงจากปรับล่าสุดไปเก่าสุด
     */
    public function history(Request $request)
    {
        $custno = trim((string) $request->query('custno'));
        $itemno = trim((string) $request->query('itemno'));

        if ($custno === '' || $itemno === '') {
            return response()->json(['found' => false, 'rows' => []]);
        }

        $query = Saleinfo::query()
            ->whereRaw('TRIM(CustNo) = ?', [$custno])
            ->whereRaw('TRIM(ITEMNO) = ?', [$itemno]);

        // แก้ไขอยู่ → ไม่ต้องแสดงแถวตัวเองในประวัติ
        if ($request->filled('exclude_id')) {
            $query->where('id', '!=', (int) $request->query('exclude_id'));
        }

        $rows = $query
            ->orderByRaw('COALESCE(NotifyDate, DATE, AuthDate) DESC')
            ->orderByDesc('id')
            ->get();

        $out = $rows->map(function ($r) {
            return [
                'id'         => $r->id,
                'NotifyDate' => $r->NotifyDate ? Carbon::parse($r->NotifyDate)->format('d/m/Y') : '',
                'DATE'       => $r->DATE ? Carbon::parse($r->DATE)->format('d/m/Y') : '',
                'ITEMNO'     => $r->ITEMNO,
                'MOQ'        => $r->MOQ,
                'PRICE'      => $r->PRICE,
                'REM1'       => $r->REM1,
            ];
        });

        return response()->json(['found' => $out->isNotEmpty(), 'rows' => $out]);
    }

    /**
     * POST — เพิ่มราคาใหม่
     */
    public function insert(Request $request)
    {
        $error = $this->validateForm($request);
        if ($error) {
            return response()->json(['error' => $error], 422);
        }

        // แต่ละครั้งที่กำหนด/ปรับราคา = 1 แถว → เก็บเป็นประวัติการปรับราคา
        // (ไม่เช็คซ้ำคู่ลูกค้า/สินค้า เพราะต้องการให้มีได้หลายแถวต่อคู่)
        try {
            $row = $this->extractForm($request);
            $row['AuthDate'] = now();   // เวลาที่บันทึกราคานี้

            $saleinfo = Saleinfo::create($row);

            return response()->json(['ok' => true, 'id' => $saleinfo->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST — แก้ไขราคาเดิม (ระบุด้วย id)
     */
    public function update(Request $request)
    {
        $row = Saleinfo::find($request->id);
        if (!$row) {
            return response()->json(['error' => 'not_found'], 404);
        }

        $error = $this->validateForm($request);
        if ($error) {
            return response()->json(['error' => $error], 422);
        }

        try {
            $data = $this->extractForm($request);
            $data['AuthDate'] = now();   // เวลาที่แก้ราคาล่าสุด

            $row->update($data);

            return response()->json(['ok' => true, 'id' => $row->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST — ลบราคา (ระบุด้วย id)
     */
    public function destroy(Request $request)
    {
        $row = Saleinfo::find($request->id);
        if (!$row) {
            return response()->json(['error' => 'not_found'], 404);
        }

        try {
            $row->delete();

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ────────────────────────────────────────────────────────────────
    //  Helpers
    // ────────────────────────────────────────────────────────────────

    /**
     * ตรวจช่องบังคับ (ตรงกับ required ในฟอร์ม) — คืนข้อความ error หรือ null ถ้าผ่าน
     */
    private function validateForm(Request $request): ?string
    {
        if (trim((string) $request->CustNo) === '') {
            return 'รหัสลูกค้าห้ามว่าง';
        }
        if (trim((string) $request->st_code) === '') {
            return 'ชื่อสินค้าห้ามว่าง';
        }
        if ($request->PRICE === null || $request->PRICE === '') {
            return 'ราคาห้ามว่าง';
        }
        if (!is_numeric($request->PRICE)) {
            return 'ราคาต้องเป็นตัวเลข';
        }

        return null;
    }

    /**
     * ดึงเฉพาะคอลัมน์ที่รับได้จากฟอร์ม + แปลงชนิดข้อมูลให้ตรงกับตาราง
     */
    private function extractForm(Request $request): array
    {
        $row = [];
        foreach (self::COLUMNS as $col) {
            $row[$col] = $request->input($col);
        }

        $row['CustNo']  = trim((string) $row['CustNo']);
        $row['st_code'] = trim((string) $row['st_code']);
        // รหัสสินค้าไม่ได้กรอก → ใช้ชื่อสินค้าแทน (ในข้อมูลเก่าสองช่องนี้มักตรงกัน)
        $row['ITEMNO']  = trim((string) $row['ITEMNO']) ?: $row['st_code'];

        $row['DATE']       = $this->parseDate($row['DATE']);
        $row['NotifyDate'] = $this->parseDate($row['NotifyDate']);
        $row['PRICE']      = $row['PRICE'] !== null && $row['PRICE'] !== '' ? (float) $row['PRICE'] : null;
        $row['MOQ']        = $row['MOQ']   !== null && $row['MOQ']   !== '' ? (float) $row['MOQ']   : null;

        // NoAcp ปิดไว้ก่อน — ช่องในฟอร์มถูกคอมเมนต์ไว้ ค่าที่บันทึกจะเป็น default 0 ของตาราง
        // เปิดใช้เมื่อลูกค้ายืนยันความหมายแล้ว (คืน 'NoAcp' เข้า COLUMNS ด้วย):
        // $row['NoAcp'] = $request->boolean('NoAcp') ? 1 : 0;   // checkbox ไม่ติ๊ก = ไม่ส่งค่ามา

        return $row;
    }

    /**
     * รับได้ทั้ง d/m/Y (flatpickr) และ Y-m-d — คืน Y-m-d หรือ null
     */
    private function parseDate(?string $input): ?string
    {
        if (empty($input)) return null;
        try { return Carbon::createFromFormat('d/m/Y', $input)->format('Y-m-d'); } catch (\Exception $e) {}
        try { return Carbon::createFromFormat('Y-m-d', $input)->format('Y-m-d'); } catch (\Exception $e) {}
        try { return Carbon::parse($input)->format('Y-m-d'); } catch (\Exception $e) {}

        return null;
    }

    private function applyFilters($query, Request $request): void
    {
        if (@$request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('tb_saleinfo.CustNo', 'LIKE', "%{$s}%")
                  ->orWhere('tb_saleinfo.st_code', 'LIKE', "%{$s}%")
                  ->orWhere('tb_saleinfo.ITEMNO', 'LIKE', "%{$s}%")
                  ->orWhere('c.name', 'LIKE', "%{$s}%");
            });
        }
        if (@$request->date_from) {
            $d = $this->parseDate($request->date_from);
            if ($d) $query->whereDate('tb_saleinfo.DATE', '>=', $d);
        }
        if (@$request->date_to) {
            $d = $this->parseDate($request->date_to);
            if ($d) $query->whereDate('tb_saleinfo.DATE', '<=', $d);
        }
    }
}
