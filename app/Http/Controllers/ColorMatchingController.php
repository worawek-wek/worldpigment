<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Testmain;
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

    public function index(Request $request)
    {
        $data['page_url'] = 'color-matching';
        return view('color-matching.index', $data);
    }

    public function datatable(Request $request)
    {
        $results = Testmain::orderByDesc('TestDate')->orderByDesc('SendNo');
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

            $row->fill($payload)->save();

            DB::commit();
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE — ลบ testmain row
     */
    public function delete($sendno)
    {
        try {
            DB::beginTransaction();

            $row = Testmain::where('SendNo', $sendno)->first();
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

        return $payload;
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
        if (@$request->test_date) {
            $query->whereDate('TestDate', $request->test_date);
        }
    }
}
