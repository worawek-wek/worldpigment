<?php

namespace App\Http\Controllers;

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
     * GET — Test Price (JSON) : ข้อมูลของใบเทส 1 ใบ + ราคาที่คำนวณได้ (25/08/2569)
     *
     * คีย์ค้น = **Test No. หรือ Lot Test** (กรอกช่องไหนก็ได้ ทั้งคู่ไม่ซ้ำกันในทางปฏิบัติ)
     * ส่วน Customer เป็นตัวกรองเสริม — ช่องที่ผู้ใช้ไม่ได้กรอก จอจะเติมให้เองจากใบที่ค้นเจอ
     *
     * จอนี้อ่านอย่างเดียว ไม่มีการบันทึก — ประกอบจาก 3 ตาราง:
     *   `access_testmai` (สำเนา TestMai ของไฟล์ Access)  = ตัวใบเทส (ลูกค้า / Lot / Sample / Resin / ต้นทุนสูตร)
     *   `access_compo`   (สำเนา Compo)                    = เบอร์ที่ตั้งให้สูตรนี้ (PdCode) → ช่อง "ตั้งเบอร์เป็น"
     *   `customer`                                        = ชื่อลูกค้าภาษาไทย (`access_testmai.CName` เป็น "?" ถาวร)
     *
     * ราคา 1/2/3 + DB ใช้เครื่องคิดราคาตัวเดียวกับทั้งระบบ (ProductPriceService)
     * โดย **ต้นทุน = `access_testmai.TNet`** (= ผลรวม CNet ของสูตรในใบนั้น ตรวจกับข้อมูลจริงแล้ว)
     * ส่วนรหัสที่ใช้เลือก "เงื่อนไข" คือเบอร์ที่ตั้งไว้ — เบอร์ใหม่ที่ยังไม่มีใน `access_pdprice`
     * จึงยังคิดราคาได้ (ต่างจากจอค้นหาราคาสินค้าที่ต้องมีรหัสในตารางราคาทุนก่อน)
     */
    public function testPrice(Request $request, ProductPriceService $prices)
    {
        $custno  = trim((string) $request->query('customer'));
        $testno  = trim((string) $request->query('testno'));
        $lottest = trim((string) $request->query('lottest'));

        // ชื่อลูกค้าโชว์ได้ตั้งแต่ยังไม่ระบุใบเทส (ผู้ใช้กรอกรหัสลูกค้าก่อนเป็นปกติ)
        $customer = $custno === ''
            ? null
            : DB::table('customer')->select('code', 'name')->where('code', $custno)->first();

        if ($testno === '' && $lottest === '') {
            return response()->json([
                'found'    => false,
                'customer' => $customer,
                'reason'   => 'ระบุ Test No. หรือ Lot Test เพื่อค้นข้อมูลใบเทส',
            ]);
        }

        // Test No. กับ Lot Test เป็น "คีย์ค้น" เท่าเทียมกัน — กรอกช่องไหนก็ได้ (Customer เป็นตัวกรองเสริม)
        // ค้นแบบตรงตัวก่อน ไม่เจอค่อยค้นแบบใกล้เคียง (LIKE) — จอนี้อ่านอย่างเดียว
        // และผู้ใช้มักพิมพ์เลขที่/Lot มาไม่ครบท่อน จึงยอมให้หลวมได้
        $build = function (bool $loose) use ($testno, $lottest, $custno) {
            $q = DB::table('access_testmai');

            if ($testno !== '') {
                $loose
                    ? $q->where('TestNo', 'LIKE', '%' . $testno . '%')
                    : $q->whereRaw('TRIM(TestNo) = ?', [$testno]);
            }
            if ($lottest !== '') {
                $loose
                    ? $q->where('Lotno', 'LIKE', '%' . $lottest . '%')
                    : $q->whereRaw('TRIM(Lotno) = ?', [$lottest]);
            }
            if ($custno !== '') {
                $q->whereRaw('TRIM(CCode) = ?', [$custno]);
            }

            return $q;
        };

        $query = $build(false);
        $loose = false;

        if (!(clone $query)->exists()) {
            $query = $build(true);
            $loose = true;
        }

        // Lot เดียวมีได้หลายใบเทส → เอาใบล่าสุด แล้วส่งรายการที่เหลือไปให้จอบอกผู้ใช้ด้วย
        $rows    = (clone $query)->orderByDesc('Tdate')->orderByDesc('id')->limit(20)->get();
        $matches = (clone $query)->count();
        $row     = $rows->first();

        if (!$row) {
            return response()->json([
                'found'    => false,
                'customer' => $customer,
                'reason'   => 'ไม่พบใบเทสที่ตรงกับที่ค้น',
            ]);
        }

        // ชื่อลูกค้าของใบที่เจอ (กรณีผู้ใช้ไม่ได้กรอกรหัสลูกค้ามา)
        if (!$customer) {
            $customer = DB::table('customer')->select('code', 'name')
                ->whereRaw('TRIM(code) = ?', [trim((string) $row->CCode)])->first();
        }

        // "ตั้งเบอร์เป็น" = เบอร์ที่ผูกกับสูตรของใบเทสนี้ (1 ใบอาจตั้งได้หลายเบอร์ → คั่นด้วย ", ")
        $codes = DB::table('access_compo')
            ->whereRaw('TRIM(TestNo) = ?', [trim((string) $row->TestNo)])
            ->distinct()->orderBy('PdCode')->pluck('PdCode')
            ->map(fn ($c) => trim((string) $c))->filter()->values();

        $setcode = $codes->implode(', ');
        $base    = (float) $row->TNet;

        // คิดราคาจากเบอร์แรก (ปกติมีเบอร์เดียว) — ยังไม่ตั้งเบอร์ = เลือกเงื่อนไขไม่ได้
        if ($codes->isEmpty()) {
            $calc = [
                'found' => false, 'code' => null, 'base_price' => round($base, 2),
                'rule'  => null,  'prices' => null,
                'reason' => 'ใบเทสนี้ยังไม่ได้ตั้งเบอร์ — เลือกเงื่อนไขราคาไม่ได้',
            ];
        } else {
            $calc = $prices->quote($codes->first(), $base);
        }

        return response()->json([
            'found'    => true,
            'matches'  => $matches,
            // Test No. ทุกใบที่เข้าเงื่อนไข (ใบแรก = ใบที่กำลังแสดง) ให้จอเลือกดูใบอื่นต่อได้
            'testnos'  => $rows->map(fn ($r) => trim((string) $r->TestNo))->values(),
            'loose'    => $loose,   // true = ไม่เจอ Lot แบบตรงตัว เลยค้นแบบใกล้เคียงให้
            'customer' => $customer ?: ['code' => $row->CCode, 'name' => $row->CName],
            'setcode'  => $setcode,
            'test'     => [
                'testno'      => trim((string) $row->TestNo),
                'testdate'    => $row->Tdate ? Carbon::parse($row->Tdate)->format('d/m/Y') : '',
                'lotno'       => trim((string) $row->Lotno),
                'sample'      => $row->TDecs,      // รายละเอียดตัวอย่างที่เทส
                'cust_resin'  => $row->CResin,     // Resin ที่ลูกค้าใช้
                'resin_match' => $row->Resin,      // Resin ที่ใช้ตอน match
                'tnet'        => round($base, 2),  // ต้นทุนสูตร (= ผลรวม CNet ของ access_compo)
                'matcher'     => $row->Matcher,
            ],
            'calc'     => $calc,
            'reason'   => null,
        ]);
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
