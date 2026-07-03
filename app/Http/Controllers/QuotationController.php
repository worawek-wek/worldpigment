<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Qmast;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    // ─── คอลัมน์หัวใบเสนอราคา (qmast) ที่รับจากฟอร์มได้ ───
    private const QMAST_COLUMNS = [
        'Qno', 'Qdate', 'PDtype', 'exam', 'EmpID', 'Custid', 'CustName',
        'Qremark', 'ValidFrom', 'Validto', 'Term', 'Engname', 'LeadTime', 'Revisedate',
    ];

    // ─── คอลัมน์วันที่ใน qmast (input type=date ส่งมาเป็น Y-m-d) ───
    private const DATE_FIELDS = ['Qdate', 'ValidFrom', 'Validto', 'Revisedate'];

    public function index(Request $request)
    {
        $data['page_url'] = 'quotation';
        // ชนิดสินค้า (pdtype) สำหรับ filter + form — PDHead1 (ไทย) เพี้ยน จึงใช้ PDHead1E (อังกฤษ) เป็นคำอธิบาย
        $data['pdtypes'] = DB::table('pdtype')->get();
        return view('quotation.index', $data);
    }

    /**
     * GET — รายการใบเสนอราคา (qmast) + ชื่อลูกค้าจริงจากตาราง customer
     * (qmast.CustName ของข้อมูลเก่าภาษาไทยเสีย → ใช้ customer.name ผ่าน Custid แทน)
     */
    public function datatable(Request $request)
    {
        $results = Qmast::query()
            ->leftJoin('customer as c', 'qmast.Custid', '=', 'c.code')
            ->select('qmast.*', 'c.name as cust_name', 'c.nameEN as cust_nameEN')
            // จำนวนรายการ + มูลค่ารวม จาก qdetail
            ->selectRaw('(SELECT COUNT(*) FROM qdetail d WHERE d.Qno = qmast.Qno) as item_count')
            ->selectRaw('(SELECT COALESCE(SUM(d.QNet),0) FROM qdetail d WHERE d.Qno = qmast.Qno) as total_net')
            ->orderByDesc('qmast.Qdate')
            ->orderByDesc('qmast.Qno');

        $this->applyFilters($results, $request);

        $limit = @$request['limit'] ?: 15;
        $results = $results->paginate($limit);

        $data['list_data'] = $results;
        return view('quotation.table', $data);
    }

    /**
     * GET — ค้นชื่อลูกค้าจากรหัส (Custid) ในตาราง customer
     * คืน name (ไทย) + nameEN (อังกฤษ) ให้ฟอร์มเติมอัตโนมัติ
     */
    public function customerLookup($code)
    {
        $cust = DB::table('customer')->where('code', $code)->first(['name', 'nameEN', 'term']);

        if (!$cust) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'  => true,
            'name'   => $cust->name,
            'nameEN' => $cust->nameEN,
            'term'   => $cust->term,
        ]);
    }

    /**
     * GET — เดาเลขที่ใบเสนอราคาถัดไป (แก้ไขได้ในฟอร์ม)
     * รูปแบบ = <prefix><ปีพ.ศ. 2 หลัก><running 4 หลัก> เช่น WH690325
     * หา running มากสุดของ prefix+ปีเดียวกัน แล้ว +1
     */
    public function nextQno(Request $request)
    {
        $prefix = strtoupper(trim($request->query('prefix', 'WH')));
        // ปี พ.ศ. 2 หลัก (2026 → 2569 → "69")
        $yy = str_pad((Carbon::now()->year + 543) % 100, 2, '0', STR_PAD_LEFT);
        $head = $prefix . $yy;

        $max = (int) DB::table('qmast')
            ->whereRaw('TRIM(Qno) LIKE ?', [$head . '%'])
            ->selectRaw('MAX(CAST(SUBSTRING(TRIM(Qno), ?) AS UNSIGNED)) as m', [strlen($head) + 1])
            ->value('m');

        $next = $head . str_pad($max + 1, 4, '0', STR_PAD_LEFT);
        return response()->json(['qno' => $next]);
    }

    /**
     * GET — รายละเอียดใบเสนอราคา 1 ใบ (อ่านอย่างเดียว) เป็น HTML ใส่ modal
     */
    public function show(Request $request)
    {
        $row = $this->findByQno($request->query('qno', ''));
        if (!$row) {
            return response('ไม่พบข้อมูล', 404);
        }

        $data = $this->loadQuotation($row);
        return view('quotation.show', $data);
    }

    /**
     * GET — ดึงข้อมูลใบเสนอราคา (หัว + รายการ) เป็น JSON ให้ฟอร์มแก้ไขเติม
     */
    public function edit(Request $request)
    {
        $row = $this->findByQno($request->query('qno', ''));
        if (!$row) {
            return response()->json(['error' => 'not_found'], 404);
        }

        $data = $this->loadQuotation($row);
        return response()->json([
            'header' => $data['header'],
            'cust'   => $data['cust'],
            'items'  => $data['items'],
        ]);
    }

    /**
     * GET — หน้าพิมพ์ใบเสนอราคา
     */
    public function print(Request $request)
    {
        $row = $this->findByQno($request->query('qno', ''));
        if (!$row) {
            return response('ไม่พบข้อมูล', 404);
        }

        $data = $this->loadQuotation($row);
        return view('quotation.print', $data);
    }

    /**
     * POST — สร้างใบเสนอราคาใหม่ (qmast + qdetail)
     */
    public function insert(Request $request)
    {
        $qno = trim((string) $request->Qno);
        if ($qno === '') {
            return response()->json(['error' => 'เลขที่ใบเสนอราคาห้ามว่าง'], 422);
        }
        if ($this->qnoExists($qno)) {
            return response()->json(['error' => "เลขที่ใบเสนอราคา {$qno} มีอยู่แล้ว"], 422);
        }

        try {
            DB::beginTransaction();

            $header = $this->extractHeader($request);
            $header['Qno'] = $qno;
            DB::table('qmast')->insert($header);

            $this->saveItems($qno, $request);

            DB::commit();
            return response()->json(['ok' => true, 'qno' => $qno]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST — อัพเดทใบเสนอราคา (ระบุด้วย qno เดิม; รายการแทนที่ทั้งชุด)
     */
    public function update(Request $request)
    {
        $row = $this->findByQno($request->qno);
        if (!$row) {
            return response()->json(['error' => 'not_found'], 404);
        }
        $qno = trim((string) $row->Qno);

        try {
            DB::beginTransaction();

            $header = $this->extractHeader($request);
            unset($header['Qno']);   // ไม่ให้เปลี่ยนเลขที่ (เป็น key)
            DB::table('qmast')->whereRaw('TRIM(Qno) = ?', [$qno])->update($header);

            // แทนที่รายการทั้งชุด: ลบของเดิม แล้ว insert ใหม่
            DB::table('qdetail')->whereRaw('TRIM(Qno) = ?', [$qno])->delete();
            $this->saveItems($qno, $request);

            DB::commit();
            return response()->json(['ok' => true, 'qno' => $qno]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST — ลบใบเสนอราคา (หัว + รายการ)
     */
    public function destroy(Request $request)
    {
        $row = $this->findByQno($request->qno);
        if (!$row) {
            return response()->json(['error' => 'not_found'], 404);
        }
        $qno = trim((string) $row->Qno);

        try {
            DB::beginTransaction();
            DB::table('qdetail')->whereRaw('TRIM(Qno) = ?', [$qno])->delete();
            DB::table('qmast')->whereRaw('TRIM(Qno) = ?', [$qno])->delete();
            DB::commit();
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ────────────────────────────────────────────────────────────────
    //  Helpers
    // ────────────────────────────────────────────────────────────────

    /**
     * ค้น qmast จาก Qno (เทียบแบบ trim เพราะข้อมูลเก่าบางตัวมีช่องว่างนำหน้า)
     */
    private function findByQno(?string $qno)
    {
        $qno = trim((string) $qno);
        if ($qno === '') {
            return null;
        }
        return Qmast::whereRaw('TRIM(Qno) = ?', [$qno])->first();
    }

    private function qnoExists(string $qno): bool
    {
        return DB::table('qmast')->whereRaw('TRIM(Qno) = ?', [trim($qno)])->exists();
    }

    /**
     * โหลดใบเสนอราคา 1 ใบ (หัว + ชื่อลูกค้าจริง + รายการ) สำหรับ show/edit/print
     */
    private function loadQuotation($row): array
    {
        $qno = trim((string) $row->Qno);

        $cust = DB::table('customer')
            ->where('code', $row->Custid)
            ->first(['name', 'nameEN']);

        $items = DB::table('qdetail')
            ->whereRaw('TRIM(Qno) = ?', [$qno])
            ->orderBy('Qseq')
            ->get();

        $pdtype = DB::table('pdtype')->where('PDType', $row->PDtype)->first();

        return [
            'header' => $row,
            'cust'   => $cust,
            'items'  => $items,
            'pdtype' => $pdtype,
        ];
    }

    /**
     * ดึง + แปลงค่าหัวใบจากฟอร์ม (เฉพาะคอลัมน์ที่มีจริง)
     */
    private function extractHeader(Request $request): array
    {
        $header = collect($request->only(self::QMAST_COLUMNS))
            ->map(fn ($v) => is_string($v) ? trim($v) : $v)
            ->reject(fn ($v) => $v === null || $v === '')
            ->toArray();

        // checkbox พร้อมตัวอย่าง → 0/1
        $header['exam'] = $request->boolean('exam') ? 1 : 0;

        // EmpID เป็น int
        if (isset($header['EmpID'])) {
            $header['EmpID'] = (int) $header['EmpID'];
        }

        // แปลงวันที่ Y-m-d → datetime
        foreach (self::DATE_FIELDS as $field) {
            if (!empty($header[$field])) {
                $header[$field] = $this->parseDate($header[$field]);
            }
        }

        return $header;
    }

    /**
     * บันทึกรายการสินค้า (qdetail) — Qseq เป็นเลข running ทั้งระบบ (max+1 ต่อแถว)
     * ข้ามแถวที่ว่างเปล่าทั้งหมด
     */
    private function saveItems(string $qno, Request $request): void
    {
        $codes  = (array) $request->input('item_code', []);
        $names  = (array) $request->input('item_name', []);
        $olds   = (array) $request->input('item_old_price', []);
        $news   = (array) $request->input('item_new_price', []);
        $totals = (array) $request->input('item_total_price', []);

        $seq = (int) DB::table('qdetail')->max('Qseq');

        $rows = [];
        foreach ($names as $i => $name) {
            $code = trim((string) ($codes[$i] ?? ''));
            $name = trim((string) $name);
            // แถวว่างทั้งบรรทัด → ข้าม
            if ($code === '' && $name === '') {
                continue;
            }
            $rows[] = [
                'Qno'      => $qno,
                'Qseq'     => ++$seq,
                'Qitemno'  => $code !== '' ? $code : null,
                'Qdesc'    => $name !== '' ? $name : null,
                'oldprice' => $this->num($olds[$i] ?? null),
                'QPrice'   => $this->num($news[$i] ?? null),
                'QNet'     => $this->num($totals[$i] ?? null),
            ];
        }

        if ($rows) {
            DB::table('qdetail')->insert($rows);
        }
    }

    private function num($v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        return (float) str_replace(',', '', (string) $v);
    }

    private function parseDate(?string $input): ?string
    {
        if (empty($input)) return null;
        try { return Carbon::createFromFormat('Y-m-d', $input)->format('Y-m-d'); } catch (\Exception $e) {}
        try { return Carbon::createFromFormat('d/m/Y', $input)->format('Y-m-d'); } catch (\Exception $e) {}
        try { return Carbon::parse($input)->format('Y-m-d'); } catch (\Exception $e) {}
        return null;
    }

    private function applyFilters($query, Request $request): void
    {
        if (@$request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('qmast.Qno', 'LIKE', "%{$s}%")
                  ->orWhere('qmast.Custid', 'LIKE', "%{$s}%")
                  ->orWhere('c.name', 'LIKE', "%{$s}%");
            });
        }
        if (@$request->product_type) {
            $query->where('qmast.PDtype', $request->product_type);
        }
        if (@$request->date_from) {
            $d = $this->parseDate($request->date_from);
            if ($d) $query->whereDate('qmast.Qdate', '>=', $d);
        }
        if (@$request->date_to) {
            $d = $this->parseDate($request->date_to);
            if ($d) $query->whereDate('qmast.Qdate', '<=', $d);
        }
    }
}
