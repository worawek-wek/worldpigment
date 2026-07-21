<?php

namespace App\Http\Controllers;

use App\Models\Saleinfo;
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
