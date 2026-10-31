<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Testmain;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColorMatchingController extends Controller
{
    // ─── Columns ที่ testmain มีอยู่จริง (ใช้กรอง form input ก่อน mass-assign) ───
    private const TESTMAIN_COLUMNS = [
        'SendNo', 'TestDate', 'TestType', 'TestDesc', 'Testno', 'Type_Work', 'Model',
        'custno', 'custname', 'custnameEN', 'sale', 'CodeNo', 'color', 'CustPigment', 'STD',
        'lotno', 'Adj', 'Resp', 'TyResp', 'Respdate', 'Wage', 'remark',
        'DsendT', 'Mems', 'TNname', 'rptno', 'pop', 'cancel', 'CancalRes',
        'TNDate', 'PHR', 'ResinMatch', 'startdate', 'SampleDate', 'ReadyDate',
        'RminWating', 'ColorMatcher', 'MI', 'Density', 'VR', 'Hardness',
        'ColorChar', 'ApprovedLot', 'ChemSafety', 'Brand',
        'ZnstEBS', // คำนวณ Znst+EBS ร่วมในชิ้นงาน (20/10/2569)
    ];

    // ─── 4 ช่องที่ต่างกันในแต่ละใบ เมื่อสร้างใบส่ง ต.ย. หลายใบพร้อมกัน (ตาราง "รายการตัวอย่าง", 29/10/2569) ───
    private const SD_ITEM_FIELDS = ['TestDesc', 'ResinMatch', 'PHR', 'lotno'];

    // ─── Field ที่เป็นวันที่ — flatpickr ส่งมาเป็น d/m/Y ต้อง parse ก่อน save ───
    private const DATE_FIELDS = [
        'TestDate', 'Respdate', 'DsendT', 'TNDate',
        'startdate', 'SampleDate', 'ReadyDate',
    ];

    public function index(Request $request)
    {
        $data['page_url'] = 'color-matching';
        // ตัวเลือก dropdown ดึงจากค่าที่เคยบันทึกจริงใน testmain (distinct) — list โตตามข้อมูลที่เพิ่ม
        // ใช้ร่วมกันทั้ง modal (CM/SD) และ filter หน้าหลัก ผ่าน @include
        $data['options'] = [
            'Type_Work' => $this->distinctOptions('Type_Work'),
            'Model'     => $this->distinctOptions('Model'),
            'pop'       => $this->distinctOptions('pop'),
            'Adj'       => $this->distinctOptions('Adj'),
            'ColorChar'  => $this->distinctOptions('ColorChar'),
            // ChemSafety เก็บได้หลายค่าในคอลัมน์เดียว → แตกออกเป็นตัวเลือกรายตัว (29/08/2569)
            'ChemSafety' => $this->distinctMultiOptions('ChemSafety'),
        ];
        // รหัสพนักงานขายที่เคยมีใน testmain — ตัวเลือกของรายงานการเบิกตัวอย่าง (ตามพนักงานขาย) (29/10/2569)
        $data['sale_codes'] = DB::table('testmain')
            ->whereRaw("TRIM(sale) <> ''")
            ->distinct()
            ->pluck(DB::raw('TRIM(sale) as sale'))
            ->sort(fn ($a, $b) => strnatcasecmp($a, $b))
            ->values();
        return view('color-matching.index', $data);
    }

    /**
     * ค่า distinct ของ column หนึ่งใน testmain (ตัด null/ค่าว่างออก) สำหรับเติม <option>
     */
    private function distinctOptions(string $column): array
    {
        return Testmain::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->all();
    }

    /**
     * เหมือน distinctOptions() แต่ใช้กับคอลัมน์ที่เก็บ "หลายค่าในช่องเดียว" (คั่นด้วย , )
     * — แตกออกเป็นตัวเลือกรายตัว ไม่งั้นดรอปดาวน์จะได้ option ที่เป็นค่ารวม (29/08/2569)
     */
    private function distinctMultiOptions(string $column): array
    {
        $values = [];
        foreach ($this->distinctOptions($column) as $stored) {
            foreach (self::splitMulti($stored) as $one) {
                $values[$one] = true;
            }
        }
        $values = array_keys($values);
        sort($values, SORT_NATURAL | SORT_FLAG_CASE);

        return $values;
    }

    /**
     * แตกค่าที่เก็บรวมกัน (", ") ออกเป็น array — ตัดค่าว่าง/ซ้ำทิ้ง
     */
    public static function splitMulti(?string $stored): array
    {
        if ($stored === null || trim($stored) === '') {
            return [];
        }

        $parts = array_map('trim', explode(',', $stored));

        return array_values(array_unique(array_filter($parts, fn ($v) => $v !== '')));
    }

    /**
     * รวมค่าหลายตัวจาก select multiple → string เดียวสำหรับคอลัมน์ legacy (คั่นด้วย ", ")
     *
     * - ตัดคอมมาในแต่ละค่าทิ้ง (ผู้ใช้พิมพ์ tag เองได้) ไม่งั้นตอนอ่านกลับจะแตกผิดตำแหน่ง
     * - ตัดความยาวให้พอดีคอลัมน์ (ChemSafety = varchar(255))
     */
    private function joinMulti($input, int $maxLen = 255): string
    {
        $values = is_array($input) ? $input : [$input];
        $clean  = [];

        foreach ($values as $v) {
            $v = trim(str_replace(',', ' ', (string) $v));
            $v = preg_replace('/\s+/u', ' ', $v);
            if ($v !== '' && !in_array($v, $clean, true)) {
                $clean[] = $v;
            }
        }

        $joined = implode(', ', $clean);

        return mb_strlen($joined) > $maxLen ? rtrim(mb_substr($joined, 0, $maxLen)) : $joined;
    }

    public function datatable(Request $request)
    {
        // ── เรียงลำดับโดยคลิกหัวตาราง ──
        // whitelist คอลัมน์ที่เรียงได้ (กัน SQL injection) — map หัวตาราง → คอลัมน์จริงใน DB
        // 'status' = คอลัมน์เสมือน (คำนวณจาก cancel/RminWating/Testno) → เรียงด้วย CASE
        $sortable = ['id', 'DsendT', 'custno', 'Type_Work', 'color', 'STD', 'lotno', 'status'];
        $sortCol  = in_array((string) $request->sort_col, $sortable, true) ? (string) $request->sort_col : 'id';
        // ทิศทาง: desc = มาก→น้อย (default, id desc = ใหม่สุดอยู่บน), asc = น้อย→มาก
        $sortDir  = strtolower((string) $request->sort_dir) === 'asc' ? 'asc' : 'desc';

        if ($sortCol === 'status') {
            // จัดอันดับสถานะให้ตรงลำดับ logic ใน table.blade (1=กำลังเทียบสี → 4=ยกเลิก)
            $statusRank = "CASE
                WHEN cancel = 1 THEN 4
                WHEN (RminWating IS NOT NULL AND TRIM(RminWating) <> '') THEN 2
                WHEN (Testno IS NULL OR TRIM(Testno) = '') THEN 1
                ELSE 3 END";
            $results = Testmain::orderByRaw("$statusRank $sortDir");
        } else {
            $results = Testmain::orderBy($sortCol, $sortDir);
        }
        // เรียงรองด้วย id เพื่อให้ลำดับคงที่เมื่อค่าคอลัมน์หลักซ้ำกัน
        if ($sortCol !== 'id') {
            $results->orderBy('id', 'desc');
        }
        $this->applyFilters($results, $request);

        $limit = 15;
        if (@$request['limit']) {
            $limit = $request['limit'];
        }

        $results = $results->paginate($limit);

        // จำนวนใบส่ง ต.ย. (SD) ของแต่ละใบนำส่งเทียบสี (CM) ในหน้านี้ (28/10/2569)
        // เงื่อนไขเดียวกับ relatedSd ใน detail() — SendNo เดียวกัน + มี Testno
        $cmSendNos = $results->getCollection()
            ->filter(fn ($r) => trim((string) $r->Testno) === '' && trim((string) $r->SendNo) !== '')
            ->pluck('SendNo')->unique()->values();
        $sdCounts = $cmSendNos->isEmpty() ? collect() : Testmain::whereIn('SendNo', $cmSendNos)
            ->whereNotNull('Testno')->where('Testno', '!=', '')
            ->groupBy('SendNo')
            ->selectRaw('SendNo, COUNT(*) AS cnt')
            ->pluck('cnt', 'SendNo');
        $results->getCollection()->each(function ($r) use ($sdCounts) {
            $r->sd_count = (int) ($sdCounts[$r->SendNo] ?? 0);
        });

        $data['list_data'] = $results->appends(request()->query());
        $data['query'] = request()->query();
        $data['query']['limit'] = $limit;

        $data['list_data'] = $results;
        $data['sort_col']  = $sortCol;
        $data['sort_dir']  = $sortDir;

        return view('color-matching.table', $data);
    }

    public function getSummary(Request $request)
    {
        $base = Testmain::query();

        // ⚠ TODO: ยังไม่ชัวร์ว่า summary จะแสดงตัวเลขตาม filter หรือเป็น total ทั้งระบบ
        // ถ้าจะให้สอดคล้องกับ filter ค่อย uncomment บรรทัดนี้
        // $this->applyFilters($base, $request);

        $data['summary'] = [
            'total'         => (clone $base)->count(),
            'waiting'       => (clone $base)->whereNotNull('RminWating')->where('RminWating', '!=', '')->count(),
            'matching'      => (clone $base)->whereNull('Testno')->where(function ($q) {
                                    $q->whereNull('cancel')->orWhere('cancel', 0);
                                })->count(),
            'sent_customer' => (clone $base)->whereNotNull('Testno')->where('Testno', '!=', '')->count(),
        ];

        return view('color-matching.summary', $data);
    }

    /**
     * GET — แสดงรายละเอียด testmain 1 row (อ่านอย่างเดียว) เป็น HTML ใส่ modal
     * CM กับ SD แสดงคนละชุดข้อมูล (แยกใน detail.blade.php ด้วย Testno)
     */
    public function detail($id)
    {
        $row = Testmain::find($id);

        if (!$row) {
            return response('ไม่พบข้อมูล', 404);
        }

        // ถ้าเป็นใบนำส่งเทียบสี (CM = ไม่มี Testno) → ดึงใบส่ง ต.ย. (SD) ที่อ้างอิง SendNo เดียวกัน
        $relatedSd = collect();
        $isCM = empty(trim((string) $row->Testno));
        if ($isCM && !empty($row->SendNo)) {
            $relatedSd = Testmain::where('SendNo', $row->SendNo)
                ->whereNotNull('Testno')->where('Testno', '!=', '')
                ->orderByDesc('id')
                ->get();
        }

        return view('color-matching.detail', compact('row', 'relatedSd'));
    }

    /**
     * GET — ดึงข้อมูล testmain 1 row (ส่งกลับเป็น JSON ให้ form modal เติม)
     */
    public function edit($id)
    {
        $row = Testmain::find($id);

        if (!$row) {
            return response()->json(['error' => 'not_found'], 404);
        }

        // ใบส่ง ต.ย. อื่นของใบเทียบสีเดียวกัน — ฟอร์มใช้ถามว่าจะแก้ใบเดียวหรือทุกใบ (29/10/2569)
        return response()->json($row->toArray() + [
            'sd_siblings' => $this->sdSiblingsQuery($row)->count(),
        ]);
    }

    /**
     * ใบส่ง ต.ย. อื่น (ไม่รวมตัวเอง) ที่อ้างอิงใบเทียบสีเดียวกัน = SendNo เดียวกัน + มี Testno
     * ใบที่ไม่ใช่ SD หรือไม่มี SendNo → ไม่มีพี่น้อง
     */
    private function sdSiblingsQuery(Testmain $row)
    {
        $sendNo = trim((string) $row->SendNo);
        $isSd   = trim((string) $row->Testno) !== '';

        return Testmain::query()
            ->when(!$isSd || $sendNo === '', fn ($q) => $q->whereRaw('1 = 0'))
            ->where('SendNo', $sendNo)
            ->whereNotNull('Testno')->where('Testno', '!=', '')
            ->where('id', '!=', $row->id);
    }

    /**
     * GET — ค้น record เทียบสี (CM) จากเลขที่ใบนำส่ง (SendNo)
     * ใช้ในฟอร์มใบส่ง ต.ย. เมื่อพิมพ์เลขอ้างอิง → ดึงข้อมูลที่ตรงกันมาเติม
     */
    public function lookupBySendNo(Request $request)
    {
        // รับผ่าน query string (?sendno=) เพราะ SendNo มี "/" — Apache บล็อก %2F ใน path
        $sendno = $request->query('sendno', '');
        if ($sendno === '') {
            return response()->json(['found' => false]);
        }

        // SendNo ซ้ำได้ → เลือก record CM (ไม่มี Testno) ก่อน, ไม่งั้น fallback แถวแรก
        $row = Testmain::where('SendNo', $sendno)
            ->where(function ($q) {
                $q->whereNull('Testno')->orWhere('Testno', '');
            })
            ->first()
            ?? Testmain::where('SendNo', $sendno)->first();

        if (!$row) {
            return response()->json(['found' => false]);
        }

        return response()->json(['found' => true] + $row->toArray());
    }

    /**
     * GET — ค้นชื่อลูกค้าจากรหัส (custno) ในตาราง customer
     * คืน name (ไทย) + nameEN (อังกฤษ) ให้ฟอร์มเติมอัตโนมัติ
     */
    public function customerLookup($code)
    {
        $cust = DB::table('customer')
            ->where('code', $code)
            ->first(['name', 'nameEN']);

        if (!$cust) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'  => true,
            'name'   => $cust->name,
            'nameEN' => $cust->nameEN,
        ]);
    }

    /**
     * ถ้าชื่อพนักงานที่กรอกยังไม่มีใน emp.empname → สร้างพนักงานใหม่ (gen empno)
     * เก็บเฉพาะชื่อต้น (empname) ตามที่ฟอร์มกรอก (ปกติไม่กรอกนามสกุล)
     */
    private function ensureEmployee(?string $name): void
    {
        $name = trim((string) $name);
        if ($name === '') return;

        if (DB::table('emp')->where('empname', $name)->exists()) return;

        // gen empno ใหม่ (varchar(4)) = เลขมากสุด + 1
        $maxNo = (int) DB::table('emp')->max(DB::raw('CAST(empno AS UNSIGNED)'));
        DB::table('emp')->insert([
            'empno'   => (string) ($maxNo + 1),
            'empname' => $name,
        ]);
    }

    /**
     * POST — สร้าง testmain row ใหม่
     */
    public function insert(Request $request)
    {
        try {
            DB::beginTransaction();

            $payload = $this->extractPayload($request);

            // SendNo จำเป็น แต่ "ซ้ำได้" (1 CM → หลาย SD ใช้ SendNo เดียวกัน); PK จริงคือ id
            if (empty($payload['SendNo'])) {
                return response()->json(['error' => 'SendNo required'], 422);
            }

            // ชื่อพนักงานที่กรอก (ผู้รับเอกสาร/Color Matcher) ถ้าใหม่ → สร้างใน emp
            $this->ensureEmployee($request->TNname);
            $this->ensureEmployee($request->ColorMatcher);

            // ใบส่ง ต.ย. หลายแถว (29/10/2569) — 1 แถวในตาราง "รายการตัวอย่าง" = 1 ใบ
            // ข้อมูลอื่นเหมือนกันทุกใบ ต่างกันแค่ 4 ช่องใน SD_ITEM_FIELDS · ไม่มี sd_items = สร้างใบเดียวตามเดิม
            $items = $this->sdItems($request);

            // Lot No. ต้องไม่ซ้ำ — ทั้งกับใบที่มีอยู่แล้ว และระหว่างแถวที่สร้างพร้อมกัน (29/10/2569)
            $lots = empty($items)
                ? [(string) ($payload['lotno'] ?? '')]
                : array_column($items, 'lotno');
            if ($dupMsg = $this->lotDuplicateError($lots)) {
                DB::rollBack();
                return response()->json(['error' => $dupMsg, 'lot_duplicate' => true], 422);
            }

            if (empty($items)) {
                Testmain::create($payload);
                $created = 1;
            } else {
                foreach ($items as $item) {
                    $row = $payload;
                    foreach (self::SD_ITEM_FIELDS as $field) {
                        // ช่องว่างในแถวนั้น = ไม่ใส่ค่า (ไม่ยืมค่าจากแถวแรกที่อยู่ใน $payload)
                        if ($item[$field] === '') {
                            unset($row[$field]);
                        } else {
                            $row[$field] = $item[$field];
                        }
                    }
                    Testmain::create($row);
                }
                $created = count($items);
            }

            DB::commit();
            return response()->json(['ok' => true, 'created' => $created]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * อ่าน sd_items (JSON จากตาราง "รายการตัวอย่าง" ของฟอร์ม SD) → array ของแถวที่กรอกอย่างน้อย 1 ช่อง
     * ความยาวตัดตามขนาดคอลัมน์ testmain (TestDesc 60 · ResinMatch 30 · lotno 20) · PHR ต้องเป็นตัวเลข
     */
    private function sdItems(Request $request): array
    {
        $raw = json_decode((string) $request->input('sd_items', ''), true);
        if (!is_array($raw)) return [];

        $maxLen = ['TestDesc' => 60, 'ResinMatch' => 30, 'lotno' => 20];
        $items  = [];
        foreach ($raw as $r) {
            if (!is_array($r)) continue;
            $item = [];
            foreach (self::SD_ITEM_FIELDS as $field) {
                $v = trim((string) ($r[$field] ?? ''));
                if ($field === 'PHR') {
                    $v = is_numeric($v) ? $v : '';
                } elseif (isset($maxLen[$field])) {
                    $v = mb_substr($v, 0, $maxLen[$field]);
                }
                $item[$field] = $v;
            }
            if (implode('', $item) !== '') $items[] = $item;
        }
        return $items;
    }

    /**
     * ตรวจ Lot No. ซ้ำ (29/10/2569) — คืนข้อความ error หรือ null ถ้าไม่ซ้ำ
     * เทียบแบบ TRIM (collation ของคอลัมน์ไม่สนตัวพิมพ์เล็ก/ใหญ่) · ค่าว่างไม่นับ
     * $excludeId = id ของใบที่กำลังแก้ไข (ไม่นับตัวเอง)
     */
    private function lotDuplicateError(array $lots, ?int $excludeId = null): ?string
    {
        $lots = array_values(array_filter(array_map(fn ($l) => trim((string) $l), $lots), fn ($l) => $l !== ''));
        if (empty($lots)) return null;

        // ซ้ำกันเองในชุดที่ส่งมา
        $seen = [];
        foreach ($lots as $l) {
            $k = mb_strtolower($l);
            if (isset($seen[$k])) return "Lot No. \"{$l}\" กรอกซ้ำกันมากกว่า 1 แถว";
            $seen[$k] = true;
        }

        // ซ้ำกับใบที่มีอยู่แล้ว
        $used = $this->lotsInUse($lots, $excludeId);
        if (!empty($used)) {
            return 'Lot No. นี้มีอยู่แล้ว: ' . implode(', ', $used);
        }
        return null;
    }

    /**
     * Lot No. ใน $lots ที่มีใบอื่นใช้อยู่แล้ว (แนบเลขที่ใบส่ง ต.ย. ของใบนั้นให้รู้ว่าซ้ำกับใบไหน)
     */
    private function lotsInUse(array $lots, ?int $excludeId = null): array
    {
        $placeholders = implode(',', array_fill(0, count($lots), '?'));
        return DB::table('testmain')
            ->whereRaw("TRIM(lotno) IN ({$placeholders})", $lots)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->get(['lotno', 'Testno'])
            ->map(fn ($r) => trim($r->lotno) . (trim((string) $r->Testno) !== '' ? " (ใบ {$r->Testno})" : ''))
            ->unique()->values()->all();
    }

    /**
     * GET — ตรวจ Lot No. ซ้ำ ให้ฟอร์มใบส่ง ต.ย. เช็คตอนกรอก (29/10/2569)
     * ?lotno=…&exclude_id=… (id ของใบที่กำลังแก้ไข)
     */
    public function checkLot(Request $request)
    {
        $lot = trim((string) $request->query('lotno', ''));
        if ($lot === '') return response()->json(['exists' => false]);

        $excludeId = (int) $request->query('exclude_id') ?: null;
        $used = $this->lotsInUse([$lot], $excludeId);
        return response()->json(['exists' => !empty($used), 'used' => $used]);
    }

    /**
     * POST — อัพเดท testmain row (ระบุด้วย SendNo เดิม)
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $row = Testmain::find($id);
            if (!$row) {
                return response()->json(['error' => 'not_found'], 404);
            }

            $payload = $this->extractPayload($request);

            // Lot No. ต้องไม่ซ้ำกับใบอื่น — ตรวจเฉพาะตอนเปลี่ยนค่า (29/10/2569)
            $newLot = trim((string) ($payload['lotno'] ?? ''));
            if ($newLot !== '' && $newLot !== trim((string) $row->lotno)) {
                if ($dupMsg = $this->lotDuplicateError([$newLot], (int) $row->id)) {
                    DB::rollBack();
                    return response()->json(['error' => $dupMsg, 'lot_duplicate' => true], 422);
                }
            }

            // ชื่อพนักงานที่กรอก (ผู้รับเอกสาร/Color Matcher) ถ้าใหม่ → สร้างใน emp
            $this->ensureEmployee($request->TNname);
            $this->ensureEmployee($request->ColorMatcher);

            // หาใบพี่น้องจาก SendNo "เดิม" ก่อนบันทึก (เผื่อผู้ใช้แก้ SendNo ในฟอร์ม)
            $applyAll = $request->boolean('apply_all');
            $siblings = $applyAll ? $this->sdSiblingsQuery($row)->get() : collect();

            $row->fill($payload)->save();

            // แก้ทุกใบของใบเทียบสีนี้ (29/10/2569) — ใช้ค่าชุดเดียวกัน ยกเว้น 4 ช่องที่เป็นของแต่ละใบ
            // (SD_ITEM_FIELDS: รายละเอียด / Resin / PHR / Lot No.) ⇒ ไม่ทับกัน
            $shared = collect($payload)->except(self::SD_ITEM_FIELDS)->all();
            foreach ($siblings as $sib) {
                $sib->fill($shared)->save();
            }

            DB::commit();
            return response()->json(['ok' => true, 'updated' => 1 + $siblings->count()]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST — บันทึกผลการทดสอบตัวอย่างสี ของใบส่ง ต.ย. 1 ใบ (10/08/2569)
     *
     * แตะเฉพาะ 3 คอลัมน์: TyResp (ตัวเลือก) / Resp (ข้อความระบุ) / Respdate (วันที่ทราบผล)
     * ไม่ใช้ update() ปกติ เพราะ extractPayload() ตัดค่าว่างทิ้ง (ล้างผลไม่ได้)
     * และบังคับเซ็ต cancel = 0 ทุกครั้ง ซึ่งจะไปล้างสถานะยกเลิกของใบนั้น
     */
    public function saveResult(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $row = Testmain::find($id);
            if (!$row) {
                return response()->json(['error' => 'not_found'], 404);
            }

            // ตัวเลือกที่ยอมรับ = key ใน config เท่านั้น (0, 9, A–H) + ค่าว่าง = ยังไม่ระบุ
            //
            // ⚠ ต้องใช้ array_key_exists ไม่ใช่ in_array(..., strict) เพราะ PHP แปลง key
            // ที่เป็น "ตัวเลขในรูป string" ให้เป็น int อัตโนมัติ — '0'/'9' ใน config
            // จะกลายเป็น int 0/9 ทำให้เทียบแบบ strict กับ '9' ที่ส่งมาจากฟอร์มไม่ผ่าน
            $allowed = config('color_matching.test_result_options', []);
            $tyResp  = trim((string) $request->input('TyResp', ''));
            if ($tyResp !== '' && !array_key_exists($tyResp, $allowed)) {
                return response()->json(['error' => 'ผลการทดสอบไม่ถูกต้อง'], 422);
            }

            $row->TyResp = $tyResp;
            // Resp เป็น varchar(30) — ตัดความยาวกันเกิน (นับเป็นตัวอักษร ไม่ใช่ byte)
            $row->Resp     = mb_substr(trim((string) $request->input('Resp', '')), 0, 30);
            $row->Respdate = $this->parseDate($request->input('Respdate'));

            $row->save();

            DB::commit();
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST — ลบ testmain row (ระบุด้วย id auto-increment)
     * ลบเฉพาะแถวที่ระบุ (1 row) — ไม่ลบ SD/CM ที่ใช้ SendNo เดียวกันตามไปด้วย
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $row = Testmain::find($id);
            if (!$row) {
                return response()->json(['error' => 'not_found'], 404);
            }

            $row->delete();

            DB::commit();
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ดึงเฉพาะ field ที่มีอยู่จริงใน testmain + แปลง type พิเศษ
     */
    private function extractPayload(Request $request): array
    {
        $payload = collect($request->only(self::TESTMAIN_COLUMNS))
            ->reject(fn ($v) => $v === null || $v === '')
            ->toArray();

        // checkbox cancel → 0/1
        $payload['cancel'] = $request->has('cancel') && $request->cancel ? 1 : 0;

        // ChemSafety = select multiple (ส่งมาเป็น array) → เก็บรวมในคอลัมน์เดียวคั่นด้วย ", " (29/08/2569)
        // เซ็ตหลัง reject ค่าว่าง เพราะช่องนี้ต้อง "ล้างค่า" ได้ด้วย (ฟอร์มส่ง key มาเสมอผ่าน hidden)
        // ฟอร์มที่ไม่มีช่องนี้ (modal-sd) จะไม่ส่ง key มา → ไม่แตะค่าเดิม
        if ($request->has('ChemSafety')) {
            $payload['ChemSafety'] = $this->joinMulti($request->input('ChemSafety'));
        }

        // ถ้ามี powder_color จาก modal-sd แต่ไม่มี color (เพราะ user กรอกใน SD) → ใช้ powder_color
        if ($request->filled('powder_color') && empty($payload['color'])) {
            $payload['color'] = $request->powder_color;
        }

        // แปลง date fields จาก 'd/m/Y' (flatpickr) → 'Y-m-d' (MySQL)
        foreach (self::DATE_FIELDS as $field) {
            if (!empty($payload[$field])) {
                $payload[$field] = $this->parseDate($payload[$field]);
            }
        }

        return $payload;
    }

    /**
     * Parse 'd/m/Y' (จาก flatpickr) → 'Y-m-d' ปลอดภัยแม้รูปแบบ input ไม่แน่นอน
     */
    private function parseDate(?string $input): ?string
    {
        if (empty($input)) return null;

        // ลอง 'd/m/Y' ก่อน เพราะเป็น default ของ flatpickr ในระบบ
        try { return Carbon::createFromFormat('d/m/Y', $input)->format('Y-m-d'); } catch (\Exception $e) {}
        // fallback ถ้าค่ามาเป็น 'Y-m-d' อยู่แล้ว
        try { return Carbon::createFromFormat('Y-m-d', $input)->format('Y-m-d'); } catch (\Exception $e) {}
        // fallback สุดท้าย — Carbon parse() เดาเอง
        try { return Carbon::parse($input)->format('Y-m-d'); } catch (\Exception $e) {}

        return null;
    }

    /**
     * Filter logic ที่ใช้ทั้ง datatable() และ getSummary()
     */
    private function applyFilters($query, Request $request): void
    {
        if (@$request->search) {
            $query->where(function ($q) use ($request) {
                $s = $request->search;
                $q->where('SendNo', 'LIKE', "%{$s}%")
                  ->orWhere('Testno', 'LIKE', "%{$s}%")
                  ->orWhere('custno', 'LIKE', "%{$s}%")
                  ->orWhere('custname', 'LIKE', "%{$s}%")
                  ->orWhere('color', 'LIKE', "%{$s}%");
            });
        }
        // ประเภทเอกสาร (checkbox) — CM=ไม่มี Testno, SD=มี Testno
        // ติ๊กทั้งคู่ → แสดงทั้งหมด; ติ๊กข้างเดียว → กรองชนิดนั้น; ไม่ติ๊กเลย → ไม่พบ
        // 3 ตัวเลือกไม่ซ้อนกัน (29/10/2569): show_sd = ใบส่ง ต.ย. ที่มีใบเทียบสี (มีแถว CM ที่ SendNo เดียวกัน)
        // · show_sd_orphan = ใบส่ง ต.ย. ที่ไม่มีใบเทียบสี · ติ๊กทั้งคู่ = ใบส่ง ต.ย. ทั้งหมด · ติ๊กครบ 3 = ไม่กรอง
        $showCm     = $request->filled('show_cm');
        $showSd     = $request->filled('show_sd');
        $showOrphan = $request->filled('show_sd_orphan');
        if (!$showCm && !$showSd && !$showOrphan) {
            $query->whereRaw('1 = 0');                       // ไม่ติ๊กเลย → ไม่พบข้อมูล
        } elseif (!($showCm && $showSd && $showOrphan)) {
            // มีแถว CM (ไม่มี Testno) ที่ SendNo เดียวกัน
            $hasCm = function ($e) {
                $e->from('testmain as cm')
                  ->whereColumn('cm.SendNo', 'testmain.SendNo')
                  ->where(function ($c) {
                      $c->whereNull('cm.Testno')->orWhere('cm.Testno', '');
                  });
            };
            $query->where(function ($q) use ($showCm, $showSd, $showOrphan, $hasCm) {
                if ($showCm) {                               // CM = ไม่มี Testno
                    $q->orWhere(function ($w) {
                        $w->whereNull('Testno')->orWhere('Testno', '');
                    });
                }
                if ($showSd || $showOrphan) {                // SD = มี Testno
                    $q->orWhere(function ($w) use ($showSd, $showOrphan, $hasCm) {
                        $w->whereNotNull('Testno')->where('Testno', '!=', '');
                        if ($showSd && !$showOrphan) $w->whereExists($hasCm);
                        if ($showOrphan && !$showSd) $w->whereNotExists($hasCm);
                    });
                }
            });
        }

        if (@$request->job_type) {
            $query->where('Type_Work', $request->job_type);
        }
        if (@$request->revision) {
            $query->where('Adj', $request->revision);
        }
        if (@$request->std) {
            $query->where('STD', 'LIKE', "%{$request->std}%");
        }
        if (@$request->color) {
            $query->where('color', 'LIKE', "%{$request->color}%");
        }
        if (@$request->resin) {
            $query->where('ResinMatch', 'LIKE', "%{$request->resin}%");
        }
        if (@$request->lot) {
            $query->where('lotno', 'LIKE', "%{$request->lot}%");
        }
        // ช่วงวันที่ส่งเทียบสี (ตั้งแต่/ถึง) — ใช้ column DsendT
        if (@$request->test_date_from) {
            $d = $this->parseDate($request->test_date_from);
            if ($d) {
                $query->whereDate('DsendT', '>=', $d);
            }
        }
        if (@$request->test_date_to) {
            $d = $this->parseDate($request->test_date_to);
            if ($d) {
                $query->whereDate('DsendT', '<=', $d);
            }
        }
    }

    /**
     * รายงาน "ใบส่งตัวอย่างเทียบสี" ตามรหัสลูกค้า + ช่วงวันที่ (29/10/2569) — ผังตามกระดาษระบบเดิม
     * ปุ่มบนหัวหน้า /color-matching → #sampleReportModal → PDF A4 แนวนอน
     *
     * แถว = ใบส่ง ต.ย. (มี Testno) + ใบนำส่งเทียบสีที่ยังไม่มีใบส่ง ต.ย. (CM ที่ไม่มีแถว SD ที่ SendNo เดียวกัน)
     * ช่วงวันที่เทียบกับ TestDate (วันที่ใบส่ง ต.ย.) — CM ที่ยังไม่มี TestDate ใช้ DsendT แทน
     * จำนวน (กรัม) = Wage วางลงคอลัมน์ตาม TestType (1 CP · 2 สีผง · 3 สีเม็ด · 4 Pigment)
     */
    public function sampleReportPdf(Request $request)
    {
        [$custno, $from, $to] = $this->customerReportParams($request);

        $rows = DB::table('testmain as t')
            ->where('t.custno', $custno)
            ->where(function ($q) {
                $q->where(function ($w) {                        // ใบส่ง ต.ย.
                    $w->whereNotNull('t.Testno')->where('t.Testno', '!=', '');
                })->orWhere(function ($w) {                      // CM ที่ยังไม่มีใบส่ง ต.ย.
                    $w->where(function ($c) {
                        $c->whereNull('t.Testno')->orWhere('t.Testno', '');
                    })->whereNotExists(function ($e) {
                        $e->from('testmain as sd')
                          ->whereColumn('sd.SendNo', 't.SendNo')
                          ->whereNotNull('sd.Testno')->where('sd.Testno', '!=', '');
                    });
                });
            })
            ->whereRaw('DATE(COALESCE(t.TestDate, t.DsendT)) BETWEEN ? AND ?', [$from, $to])
            ->orderBy('t.SendNo')
            ->orderBy('t.Testno')
            ->orderBy('t.id')
            ->get([
                't.SendNo', 't.TNDate', 't.Testno', 't.TestDesc', 't.ResinMatch', 't.PHR', 't.lotno',
                't.STD', 't.Wage', 't.TestType', 't.CodeNo', 't.cancel',
            ]);

        $html = view('color-matching.sample-report-pdf', [
            'rows'     => $rows,
            'custno'   => $custno,
            'customer' => DB::table('customer')->where('code', $custno)->first(['code', 'name', 'sale']),
            'from'     => $from,
            'to'       => $to,
        ])->render();

        return $this->customerReportPdfResponse($html, 'sample-report-' . $custno);
    }

    /**
     * รายงาน "การเบิกตัวอย่าง (ตามรหัสลูกค้า)" + ช่วงวันที่ (29/10/2569) — ผังตามกระดาษระบบเดิม
     * ช่องกรอกชุดเดียวกับ sampleReportPdf() (#sampleReportModal ปุ่มที่ 2)
     *
     * แถว = ใบส่ง ต.ย. (มี Testno = เลขที่ใบเบิก) ที่ TestDate (วันที่เบิก) อยู่ในช่วง · เรียงตามวันที่เบิก
     * Sale ที่เบิก = testmain.sale · จำนวน (กรัม) = Wage ลงคอลัมน์ตาม TestType
     */
    public function sampleWithdrawPdf(Request $request)
    {
        [$custno, $from, $to] = $this->customerReportParams($request);

        $rows = DB::table('testmain as t')
            ->where('t.custno', $custno)
            ->whereNotNull('t.Testno')->where('t.Testno', '!=', '')
            ->whereRaw('DATE(t.TestDate) BETWEEN ? AND ?', [$from, $to])
            ->orderBy('t.TestDate')
            ->orderBy('t.Testno')
            ->orderBy('t.id')
            ->get([
                't.TestDate', 't.Testno', 't.TestDesc', 't.ResinMatch', 't.PHR', 't.STD', 't.sale',
                't.lotno', 't.Wage', 't.TestType', 't.CodeNo',
            ]);

        $html = view('color-matching.sample-withdraw-pdf', [
            'rows'     => $rows,
            'custno'   => $custno,
            'customer' => DB::table('customer')->where('code', $custno)->first(['code', 'name', 'sale']),
            'from'     => $from,
            'to'       => $to,
        ])->render();

        return $this->customerReportPdfResponse($html, 'sample-withdraw-' . $custno);
    }

    /**
     * เงื่อนไขของรายงานตามรหัสลูกค้า → [custno, from Y-m-d, to Y-m-d] · ไม่กรอกวันที่ = ต้นปี → วันนี้
     */
    private function customerReportParams(Request $request): array
    {
        $custno = trim((string) $request->query('custno', ''));
        if ($custno === '') {
            abort(422, 'กรุณาระบุรหัสลูกค้า');
        }

        return array_merge([$custno], $this->reportDateRange($request));
    }

    /**
     * ช่วงวันที่ของรายงาน (date_from / date_to แบบ d/m/Y) → [from Y-m-d, to Y-m-d] · ไม่กรอก = ต้นปี → วันนี้
     */
    private function reportDateRange(Request $request): array
    {
        $from = $this->parseDate($request->query('date_from')) ?? now()->startOfYear()->format('Y-m-d');
        $to   = $this->parseDate($request->query('date_to')) ?? now()->format('Y-m-d');
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    /**
     * รายงาน "การเบิกตัวอย่าง (ตามพนักงานขาย)" + ช่วงวันที่ (29/10/2569) — ผังตามกระดาษระบบเดิม
     * ปุ่มบนหัวหน้า /color-matching → #saleReportModal → PDF A4 แนวตั้ง
     *
     * แถว = ใบส่ง ต.ย. (มี Testno) ที่ testmain.sale = รหัสที่กรอก และ TestDate (วันที่เบิก) อยู่ในช่วง
     * จัดกลุ่มตามลูกค้า (เรียงรหัส) → ในกลุ่มเรียงวันที่เบิก · จำนวน (กรัม) = Wage ลงคอลัมน์ CP/สีผง/สีเม็ด ตาม TestType
     */
    public function saleWithdrawPdf(Request $request)
    {
        $sale = trim((string) $request->query('sale', ''));
        if ($sale === '') {
            abort(422, 'กรุณาระบุรหัสพนักงานขาย');
        }
        [$from, $to] = $this->reportDateRange($request);

        $rows = DB::table('testmain as t')
            ->leftJoin('customer as c', 'c.code', '=', 't.custno')
            ->whereRaw('TRIM(t.sale) = ?', [$sale])
            ->whereNotNull('t.Testno')->where('t.Testno', '!=', '')
            ->whereRaw('DATE(t.TestDate) BETWEEN ? AND ?', [$from, $to])
            ->orderBy('t.custno')
            ->orderBy('t.TestDate')
            ->orderBy('t.id')
            ->get([
                't.custno', 't.custname', 'c.name as cust_name',
                't.Testno', 't.TestDate', 't.TestDesc', 't.ResinMatch', 't.PHR', 't.lotno',
                't.Wage', 't.TestType', 't.CodeNo',
            ]);

        // ลำดับมาจาก SQL แล้ว — groupBy คงลำดับเดิม
        $groups = $rows->groupBy(fn ($r) => trim((string) $r->custno))
            ->map(fn ($items, $custno) => [
                'custno' => (string) $custno,
                'name'   => trim((string) ($items->first()->cust_name ?: $items->first()->custname)),
                'items'  => $items->values(),
            ])
            ->values();

        $html = view('color-matching.sale-withdraw-pdf', [
            'groups' => $groups,
            'sale'   => $sale,
            'from'   => $from,
            'to'     => $to,
        ])->render();

        return $this->customerReportPdfResponse($html, 'sale-withdraw-' . $sale, 'A4');
    }

    /**
     * รายงาน "บันทึกใบนำส่งตัวอย่างเทียบสี" ตามช่วงวันที่ส่งเทียบสี (29/10/2569) — ผังตามกระดาษระบบเดิม
     * ปุ่มบนหัวหน้า /color-matching → #sendReportModal → PDF A4 แนวนอน
     *
     * 1 แถว = 1 ใบนำส่ง (SendNo) ที่ DsendT อยู่ในช่วง
     *   - มีแถว CM (ไม่มี Testno) → ใช้แถว CM · Lot / รหัสสินค้า / วันที่รับตัวอย่าง รวบจากใบส่ง ต.ย. ที่ SendNo เดียวกัน
     *   - ไม่มีแถว CM (ข้อมูลเก่า — ใบส่ง ต.ย. เก็บทั้งชุดในแถวเดียว) → ใช้แถวใบส่ง ต.ย. นั้น
     */
    public function sendRecordPdf(Request $request)
    {
        [$from, $to] = $this->reportDateRange($request);

        $records = $this->sendNoRecords($from, $to);

        $html = view('color-matching.send-record-pdf', [
            'rows' => $records,
            'from' => $from,
            'to'   => $to,
        ])->render();

        return $this->customerReportPdfResponse($html, 'send-record');
    }

    /**
     * รายงาน "เทียบสี ส่งตัวอย่าง ระหว่างวันที่ … ถึงวันที่ …" (29/10/2569) — ผังตามกระดาษระบบเดิม
     * ปุ่มบนหัวหน้า /color-matching → #matchReportModal → PDF A4 แนวนอน
     * ข้อมูลชุดเดียวกับ sendRecordPdf() (1 แถว = 1 SendNo ที่ DsendT อยู่ในช่วง) แต่เรียงตาม รหัสเซลล์ → ลูกค้า → วันที่ส่ง
     */
    public function matchReportPdf(Request $request)
    {
        [$from, $to] = $this->reportDateRange($request);

        $records = $this->sendNoRecords($from, $to)
            ->sort(function ($a, $b) {
                return strnatcasecmp(trim((string) $a->sale), trim((string) $b->sale))
                    ?: strcmp(trim((string) $a->custno), trim((string) $b->custno))
                    ?: strcmp((string) $a->DsendT, (string) $b->DsendT)
                    ?: strcmp((string) $a->SendNo, (string) $b->SendNo);
            })
            ->values();

        $html = view('color-matching.match-report-pdf', [
            'rows' => $records,
            'from' => $from,
            'to'   => $to,
        ])->render();

        return $this->customerReportPdfResponse($html, 'color-matching-report', 'A4-L', 22);
    }

    /**
     * ใบนำส่งเทียบสีในช่วง DsendT → 1 record ต่อ 1 SendNo (เรียง DsendT → SendNo) — ใช้ร่วม sendRecordPdf() / matchReportPdf()
     *   - มีแถว CM (ไม่มี Testno) → ใช้แถว CM · Lot / รหัสสินค้า (คั่น ", ") / วันที่รับตัวอย่าง / วันที่เบิก (เก่าสุด)
     *     รวบจากใบส่ง ต.ย. ที่ SendNo เดียวกัน
     *   - ไม่มีแถว CM (ข้อมูลเก่า — ใบส่ง ต.ย. เก็บทั้งชุดในแถวเดียว) → ใช้แถวใบส่ง ต.ย. นั้น
     */
    private function sendNoRecords(string $from, string $to)
    {
        $rows = DB::table('testmain as t')
            ->leftJoin('customer as c', 'c.code', '=', 't.custno')
            ->whereRaw('DATE(t.DsendT) BETWEEN ? AND ?', [$from, $to])
            ->orderBy('t.DsendT')
            ->orderBy('t.SendNo')
            ->orderBy('t.id')
            ->get([
                't.id', 't.SendNo', 't.Testno', 't.DsendT', 't.sale', 't.custno', 't.custname', 'c.name as cust_name',
                't.color', 't.TestDesc', 't.STD', 't.Type_Work', 't.Model', 't.pop', 't.TNDate', 't.TNname',
                't.SampleDate', 't.TestDate', 't.CodeNo', 't.lotno', 't.ResinMatch', 't.Mems',
            ]);

        $isCm = fn ($r) => trim((string) $r->Testno) === '';
        $join = fn ($c, $field) => $c->pluck($field)->map(fn ($v) => trim((string) $v))->filter()->unique()->implode(', ');

        return $rows->groupBy(fn ($r) => (string) $r->SendNo)
            ->map(function ($items) use ($isCm, $join) {
                $cm = $items->first($isCm);
                if (!$cm) {
                    return $items->first();                    // ข้อมูลเก่า — แถวเดียวมีครบ
                }
                $sd = $items->reject($isCm);
                $rec = clone $cm;
                $rec->lotno      = $join($sd, 'lotno') ?: $cm->lotno;
                $rec->CodeNo     = $join($sd, 'CodeNo') ?: $cm->CodeNo;
                $rec->SampleDate = $sd->pluck('SampleDate')->filter()->min() ?: $cm->SampleDate;
                $rec->TestDate   = $sd->pluck('TestDate')->filter()->min();   // CM ไม่มีวันที่เบิกของตัวเอง
                return $rec;
            })
            ->values();
    }

    /**
     * รายงาน "เทียบสีค้างสะสม จนถึง ส่งตัวอย่างวันที่ …" (29/10/2569) — ผังตามกระดาษระบบเดิม
     * ปุ่มบนหัวหน้า /color-matching → #pendingReportModal (ถึงวันที่ อย่างเดียว) → PDF A4 แนวนอน
     *
     * ค้าง = ใบนำส่งเทียบสี (CM — ไม่มี Testno) ที่ DsendT ≤ วันที่ที่กรอก · ยังไม่มีใบส่ง ต.ย. ที่ SendNo เดียวกัน
     *        · ไม่ถูกยกเลิก (cancel = 0 / NULL) · เรียงตามเลขที่ใบนำส่ง
     */
    public function pendingPdf(Request $request)
    {
        $to = $this->parseDate($request->query('date_to')) ?? now()->format('Y-m-d');

        $rows = DB::table('testmain as t')
            ->leftJoin('customer as c', 'c.code', '=', 't.custno')
            ->where(function ($c) {
                $c->whereNull('t.Testno')->orWhere('t.Testno', '');
            })
            ->where(function ($c) {
                $c->whereNull('t.cancel')->orWhere('t.cancel', 0);
            })
            ->whereNotExists(function ($e) {
                $e->from('testmain as sd')
                  ->whereColumn('sd.SendNo', 't.SendNo')
                  ->whereNotNull('sd.Testno')->where('sd.Testno', '!=', '');
            })
            ->whereRaw('DATE(t.DsendT) <= ?', [$to])
            ->orderBy('t.SendNo')
            ->orderBy('t.id')
            ->get([
                't.SendNo', 't.sale', 't.custno', 't.custname', 'c.name as cust_name', 't.DsendT',
                't.color', 't.STD', 't.Type_Work', 't.Model', 't.Mems', 't.TNDate',
            ]);

        $html = view('color-matching.pending-pdf', [
            'rows' => $rows,
            'to'   => $to,
        ])->render();

        return $this->customerReportPdfResponse($html, 'color-matching-pending', 'A4-L', 20);
    }

    /**
     * รายงาน "บันทึกติดตามผลการเทียบสี แผนก MK" ตามช่วงวันที่เบิกตัวอย่าง (30/10/2569) — ผังตามกระดาษระบบเดิม MK01-LB03.00
     * ปุ่ม "รายงาน" บนหัวหน้า /color-matching → เลือก "บันทึกติดตามผลการเทียบสี" → PDF A4 แนวนอน
     *
     * แถว = ใบส่ง ต.ย. (มี Testno) ที่ TestDate (วันที่เบิก) อยู่ในช่วง · ทุกเซลส์
     * จัดกลุ่ม เซลส์ (TRIM(sale) เรียงแบบตัวเลข, ว่างไว้ท้ายสุด) → ลูกค้า → วันที่เบิก · 1 เซลส์ = ขึ้นหน้าใหม่
     * ประเภทงาน / นำไปทำชิ้นงาน = Type_Work / Model ของใบส่ง ต.ย. — ว่าง = ใช้ของใบนำส่ง (CM) ที่ SendNo เดียวกัน
     */
    public function followUpPdf(Request $request)
    {
        [$from, $to] = $this->reportDateRange($request);

        $rows = DB::table('testmain as t')
            ->leftJoin('customer as c', 'c.code', '=', 't.custno')
            ->whereNotNull('t.Testno')->where('t.Testno', '!=', '')
            ->whereRaw('DATE(t.TestDate) BETWEEN ? AND ?', [$from, $to])
            ->orderBy('t.custno')
            ->orderBy('t.TestDate')
            ->orderBy('t.id')
            ->get([
                't.sale', 't.custno', 't.custname', 'c.name as cust_name',
                't.TestDate', 't.Testno', 't.TestDesc', 't.ResinMatch', 't.PHR', 't.SendNo', 't.lotno',
                't.Wage', 't.Type_Work', 't.Model', 't.CodeNo', 't.TyResp', 't.Resp',
            ]);

        // ประเภทงาน / ชิ้นงาน จากใบนำส่ง (CM) — ใบส่ง ต.ย. ที่สร้างจากระบบใหม่มักไม่ได้กรอก 2 ช่องนี้
        $sendNos = $rows->pluck('SendNo')->map(fn ($v) => trim((string) $v))->filter()->unique()->values();
        $cm = $sendNos->isEmpty() ? collect() : DB::table('testmain')
            ->whereIn('SendNo', $sendNos)
            ->where(fn ($q) => $q->whereNull('Testno')->orWhere('Testno', ''))
            ->get(['SendNo', 'Type_Work', 'Model'])
            ->keyBy(fn ($r) => trim((string) $r->SendNo));
        foreach ($rows as $r) {
            $src = $cm->get(trim((string) $r->SendNo));
            if ($src) {
                if (trim((string) $r->Type_Work) === '') $r->Type_Work = $src->Type_Work;
                if (trim((string) $r->Model) === '')     $r->Model     = $src->Model;
            }
        }

        $saleKey = fn ($r) => trim((string) $r->sale);
        $sections = $rows->groupBy($saleKey)
            ->sortKeysUsing(function ($a, $b) {
                if ($a === '' || $b === '') return ($a === '') <=> ($b === '');   // ไม่ระบุเซลส์ไว้ท้ายสุด
                return strnatcasecmp($a, $b);
            })
            ->map(fn ($items, $sale) => (object) [
                'sale'      => (string) $sale,
                'count'     => $items->count(),
                'new_codes' => $items->filter(fn ($r) => trim((string) $r->CodeNo) !== '')->count(),
                // ลำดับลูกค้า/วันที่มาจาก SQL แล้ว — groupBy คงลำดับเดิม
                'customers' => $items->groupBy(fn ($r) => trim((string) $r->custno))
                    ->map(fn ($list) => (object) [
                        'name'  => trim((string) ($list->first()->cust_name ?: $list->first()->custname)),
                        'items' => $list->values(),
                    ])->values(),
            ])
            ->values();

        $html = view('color-matching.follow-up-pdf', [
            'sections' => $sections,
            'total'    => $rows->count(),
            'newCodes' => $sections->sum('new_codes'),
            'reasons'  => collect(config('color_matching.test_result_options', []))
                ->filter(fn ($o) => ($o['group'] ?? '') === 'reject'),
            'from'     => $from,
            'to'       => $to,
        ])->render();

        ini_set('pcre.backtrack_limit', '10000000');   // ช่วงยาว ๆ HTML เกิน limit ของ mPDF ได้

        return $this->customerReportPdfResponse($html, 'color-matching-follow-up', 'A4-L', 22, 16);
    }

    /**
     * mPDF สำหรับรายงานตัวอย่าง (ค่าเริ่มต้น A4 แนวนอน) — หัวรายงานอยู่ใน htmlpageheader ของ blade
     */
    private function customerReportPdfResponse(string $html, string $name, string $format = 'A4-L', int $marginTop = 30, int $marginBottom = 10)
    {
        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => $format,
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => $marginTop,   // เผื่อที่ให้หัวรายงาน (htmlpageheader ใน blade)
            'margin_bottom' => $marginBottom,   // รายงานที่มี htmlpagefooter ส่งค่ามากขึ้น
            'margin_header' => 8,
        ]);
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . preg_replace('/[^A-Za-z0-9_-]/', '', $name) . '-' . now()->format('Ymd') . '.pdf"',
        ]);
    }
}
