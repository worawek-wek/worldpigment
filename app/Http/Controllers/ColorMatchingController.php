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
        'ColorChar', 'ApprovedLot',
    ];

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
            'ColorChar' => $this->distinctOptions('ColorChar'),
        ];
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

        return response()->json($row);
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

            Testmain::create($payload);

            DB::commit();
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
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

            // ชื่อพนักงานที่กรอก (ผู้รับเอกสาร/Color Matcher) ถ้าใหม่ → สร้างใน emp
            $this->ensureEmployee($request->TNname);
            $this->ensureEmployee($request->ColorMatcher);

            $row->fill($payload)->save();

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
        $showCm = $request->filled('show_cm');
        $showSd = $request->filled('show_sd');
        if (!$showCm && !$showSd) {
            $query->whereRaw('1 = 0');                       // ไม่ติ๊กเลย → ไม่พบข้อมูล
        } elseif ($showCm && !$showSd) {
            $query->where(function ($q) {                    // เฉพาะ CM
                $q->whereNull('Testno')->orWhere('Testno', '');
            });
        } elseif ($showSd && !$showCm) {
            $query->whereNotNull('Testno')->where('Testno', '!=', '');  // เฉพาะ SD
        }
        // ติ๊กทั้งคู่ → ไม่กรอง (แสดงทั้งหมด)

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
}
