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
        'custno', 'custname', 'sale', 'CodeNo', 'color', 'STD',
        'lotno', 'Adj', 'Resp', 'TyResp', 'Respdate', 'Wage', 'remark',
        'DsendT', 'Mems', 'TNname', 'rptno', 'pop', 'cancel', 'CancalRes',
        'TNDate', 'PHR', 'ResinMatch', 'startdate', 'SampleDate', 'ReadyDate',
        'RminWating', 'ColorMatcher', 'MI', 'Density', 'VR', 'Hardness',
    ];

    // ─── Field ที่เป็นวันที่ — flatpickr ส่งมาเป็น d/m/Y ต้อง parse ก่อน save ───
    private const DATE_FIELDS = [
        'TestDate', 'Respdate', 'DsendT', 'TNDate',
        'startdate', 'SampleDate', 'ReadyDate',
    ];

    public function index(Request $request)
    {
        $data['page_url'] = 'color-matching';
        return view('color-matching.index', $data);
    }

    public function datatable(Request $request)
    {
        $results = Testmain::orderByDesc('DsendT')->orderByDesc('SendNo');
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
     * GET — ดึงข้อมูล testmain 1 row (ส่งกลับเป็น JSON ให้ form modal เติม)
     */
    public function edit($sendno)
    {
        $row = Testmain::where('SendNo', $sendno)->first();

        if (!$row) {
            return response()->json(['error' => 'not_found'], 404);
        }

        return response()->json($row);
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

            // PK ต้องไม่ซ้ำ
            if (empty($payload['SendNo'])) {
                return response()->json(['error' => 'SendNo required'], 422);
            }
            if (Testmain::where('SendNo', $payload['SendNo'])->exists()) {
                return response()->json(['error' => 'duplicate_send_no'], 422);
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
    public function update(Request $request, $sendno)
    {
        try {
            DB::beginTransaction();

            $row = Testmain::where('SendNo', $sendno)->first();
            if (!$row) {
                return response()->json(['error' => 'not_found'], 404);
            }

            $payload = $this->extractPayload($request);

            // ถ้า user เปลี่ยน SendNo ใน form → handle rename (เลี่ยงไว้ตอนนี้ — ใช้ค่าเดิม)
            $payload['SendNo'] = $sendno;

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
        if (@$request->job_type) {
            $query->where('Type_Work', $request->job_type);
        }
        if (@$request->revision) {
            $query->where('Adj', $request->revision);
        }
        if (@$request->std) {
            $query->where('STD', 'LIKE', "%{$request->std}%");
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
