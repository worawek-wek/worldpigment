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
        'letterhead',   // หัวกระดาษที่พิมพ์ (WPI/WPC/WH)
        // ── section หมายเหตุ (คอลัมน์ใหม่) ──
        'resin_price_note', 'delivery_place', 'delivery_term', 'remark_lang',
    ];

    // ─── คอลัมน์วันที่ใน qmast (input type=date ส่งมาเป็น Y-m-d) ───
    private const DATE_FIELDS = ['Qdate', 'ValidFrom', 'Validto', 'Revisedate'];

    // ชนิดสินค้าตระกูล masterbatch → preset เริ่มต้นแบบขั้นบันได (นอกนั้นถือเป็น compound)
    private const MB_FAMILY = ['MB', 'MP', 'RB', 'PO', 'SBR'];

    public function index(Request $request)
    {
        $data['page_url'] = 'quotation';
        $data['pdtypes'] = DB::table('pdtype')->get();
        $data['colRegistry'] = $this->colRegistry();  // คลังคอลัมน์ทั้งหมด (ให้ฟอร์มสร้างคอลัมน์)
        // รูปแบบตารางรายการที่เลือกได้ (1.1–2.3) — ฟอร์มใช้ตั้งคอลัมน์ของตารางกรอก
        $data['presets']      = $this->presets();
        $data['formatLabels'] = $this->formatLabels();
        return view('quotation.index', $data);
    }

    /**
     * GET — รายการใบเสนอราคา (qmast) + ชื่อลูกค้าจริงจากตาราง customer
     * (qmast.CustName ของข้อมูลเก่าภาษาไทยเสีย → ใช้ customer.name ผ่าน Custid แทน)
     */
    public function datatable(Request $request)
    {
        // ── การเรียง (state เดียว sort_col/sort_dir ใช้ร่วมกับ dropdown + คลิกหัวตาราง) ──
        // whitelist: key หัวตาราง → คอลัมน์/alias จริง (กัน SQL injection)
        $sortable = [
            'id'         => 'qmast.id',
            'Qdate'      => 'qmast.Qdate',
            'Custid'     => 'qmast.Custid',
            'PDtype'     => 'qmast.PDtype',
            'item_count' => 'agg.item_count',
            'total_net'  => 'agg.total_net',
        ];
        $sortKey = (string) $request->sort_col;
        $sortKey = isset($sortable[$sortKey]) ? $sortKey : 'id';   // default = id (ลำดับการเพิ่ม)
        $sortDir = strtolower((string) $request->sort_dir) === 'asc' ? 'asc' : 'desc';

        // จำนวนรายการ + มูลค่ารวม: ใช้ตารางสรุป (GROUP BY) แล้ว JOIN
        // — คำนวณครั้งเดียว เรียงได้เร็ว (แทน correlated subquery ที่ช้ามากตอน ORDER BY)
        $agg = DB::table('qdetail')
            ->select('Qno')
            ->selectRaw('COUNT(*) as item_count')
            ->selectRaw('COALESCE(SUM(QNet), 0) as total_net')
            ->groupBy('Qno');

        $results = Qmast::query()
            ->leftJoin('customer as c', 'qmast.Custid', '=', 'c.code')
            ->leftJoinSub($agg, 'agg', 'agg.Qno', '=', 'qmast.Qno')
            ->select('qmast.*', 'c.name as cust_name', 'c.nameEN as cust_nameEN')
            ->selectRaw('COALESCE(agg.item_count, 0) as item_count')
            ->selectRaw('COALESCE(agg.total_net, 0) as total_net')
            ->orderBy($sortable[$sortKey], $sortDir);
        // เรียงรองด้วย id ให้ลำดับคงที่เมื่อค่าคอลัมน์หลักซ้ำกัน
        if ($sortable[$sortKey] !== 'qmast.id') {
            $results->orderBy('qmast.id', 'desc');
        }

        $this->applyFilters($results, $request);

        $limit = @$request['limit'] ?: 15;
        $results = $results->paginate($limit);

        $data['list_data'] = $results;
        $data['sort_col']  = $sortKey;
        $data['sort_dir']  = $sortDir;
        return view('quotation.table', $data);
    }

    /**
     * GET — รายชื่อลูกค้าทั้งหมดที่เคยมีใบเสนอราคา (ขั้นแรกของ flow ดูประวัติ)
     * คืน HTML partial ใส่ modal — กดลูกค้า 1 ราย → เปิดประวัติใบของรายนั้นต่อ
     */
    public function customers()
    {
        $list = Qmast::query()
            ->leftJoin('customer as c', 'qmast.Custid', '=', 'c.code')
            ->whereRaw("TRIM(COALESCE(qmast.Custid, '')) <> ''")
            ->selectRaw('TRIM(qmast.Custid) as custid')
            ->selectRaw('MAX(c.name) as name, MAX(c.nameEN) as nameEN')
            ->selectRaw('COUNT(*) as qcount, MAX(qmast.Qdate) as last_date')
            ->groupBy(DB::raw('TRIM(qmast.Custid)'))
            ->orderByDesc('last_date')
            ->get();

        return view('quotation.customers', compact('list'));
    }

    /**
     * GET — ประวัติใบเสนอราคาทั้งหมดของลูกค้า 1 ราย (ระบุด้วย Custid)
     * คืน HTML partial ใส่ modal — ในรายการกดดูใบแต่ละใบต่อได้
     */
    public function history(Request $request)
    {
        $custid = trim((string) $request->query('custid', ''));
        if ($custid === '') {
            return response('ไม่พบรหัสลูกค้า', 404);
        }

        // ชื่อลูกค้าจริงจากตาราง customer (qmast.CustName เก่าภาษาไทยเสีย)
        $cust = DB::table('customer')->where('code', $custid)->first(['name', 'nameEN']);

        // ใบเสนอราคาทั้งหมดของลูกค้า + จำนวนรายการ/มูลค่ารวม (เหมือน datatable)
        $list = Qmast::query()
            ->whereRaw('TRIM(Custid) = ?', [$custid])
            ->select('qmast.*')
            ->selectRaw('(SELECT COUNT(*) FROM qdetail d WHERE d.Qno = qmast.Qno) as item_count')
            ->selectRaw('(SELECT COALESCE(SUM(d.QNet),0) FROM qdetail d WHERE d.Qno = qmast.Qno) as total_net')
            ->orderByDesc('qmast.Qdate')
            ->orderByDesc('qmast.Qno')
            ->get();

        return view('quotation.history', compact('custid', 'cust', 'list'));
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
     * GET — ค้นข้อมูลสินค้าจากรหัส (Qitemno) จากใบเสนอราคาเก่า
     * คืนชื่อ + ราคา + รายละเอียดแยกย่อย (qdetail_ext) ที่ตรงกับคอลัมน์ในตาราง
     * ให้ฟอร์มเติมช่องที่ยังว่างอัตโนมัติ
     */
    public function itemLookup(Request $request)
    {
        $code = trim((string) $request->query('code', ''));
        if ($code === '') {
            return response()->json(['found' => false]);
        }

        // ชื่อสินค้า = color ล่าสุดจาก testmain (จับคู่ด้วย CodeNo)
        $name = DB::table('testmain')
            ->whereRaw('TRIM(CodeNo) = ?', [$code])
            ->whereNotNull('color')->where('color', '<>', '')
            ->orderByDesc('id')
            ->value('color');

        // แถวราคาล่าสุด (มีราคาจริง) ของรหัสนี้ — ใช้ดึงราคา + ผูก breakdown
        $priceRow = DB::table('qdetail')
            ->whereRaw('TRIM(Qitemno) = ?', [$code])
            ->whereNotNull('QNet')
            ->orderByDesc('Qseq')
            ->first();

        if ($name === null && !$priceRow) {
            return response()->json(['found' => false]);
        }

        $cells = [];
        if ($name !== null && trim($name) !== '') {
            $cells['name'] = $name;
        }

        if ($priceRow) {
            $ext = DB::table('qdetail_ext')
                ->whereRaw('TRIM(Qno) = ?', [trim($priceRow->Qno)])
                ->where('Qseq', $priceRow->Qseq)
                ->first();

            $cells['price_kg']  = ($ext->price_kg  ?? null) ?? $priceRow->QPrice;
            $cells['price_vat'] = ($ext->price_vat ?? null) ?? $priceRow->QNet;

            // รายละเอียดแยกย่อย (ตรงกับคอลัมน์ในตาราง) จาก qdetail_ext
            if ($ext) {
                foreach (['delivery_qty', 'resin_price', 'process_fee', 'pigment_price', 'loss_pct',
                          'cur_process_fee', 'new_process_fee', 'cur_pigment_price', 'new_pigment_price',
                          'new_price', 'remark'] as $k) {
                    if ($ext->$k !== null && $ext->$k !== '') {
                        $cells[$k] = $ext->$k;
                    }
                }
            }
        }

        // ตัดค่าว่างออก
        $cells = array_filter($cells, fn ($v) => $v !== null && $v !== '');

        return response()->json(['found' => !empty($cells), 'cells' => $cells]);
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
            'header'     => $data['header'],
            'cust'       => $data['cust'],
            'items'      => $data['items'],
            'col_config' => $data['colConfig'],
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
     * POST — ตัวอย่างพิมพ์จากฟอร์มที่ "ยังไม่บันทึก"
     * ประกอบข้อมูลจาก request แล้ว render view เดียวกับพิมพ์จริง (quotation.print)
     * โดยไม่แตะ DB (มี lookup pdtype อ่านอย่างเดียวเท่านั้น) — หน้าตาตรงกับพิมพ์จริง 100%
     */
    public function printPreview(Request $request)
    {
        $items = $this->parseItemsPayload($request);

        // หัวใบจากฟอร์ม — เติม key ตาม QMAST_COLUMNS ให้ครบ (กัน undefined property ใน view)
        $headerArr = $this->extractHeader($request);
        $header = (object) array_merge(array_fill_keys(self::QMAST_COLUMNS, null), $headerArr);
        // คอลัมน์ที่แสดง — ตามรูปแบบที่เลือก หรือ derive จากรายการที่กรอก (เหมือนตอนบันทึก)
        $header->col_config = json_encode($this->buildColConfig($request, $items), JSON_UNESCAPED_UNICODE);

        // สร้างรายการสำหรับ view: cells (map แบน key => value) จากค่าที่ฟอร์มส่งมา
        // ข้ามแถวว่างทั้งบรรทัด (ไม่มีทั้งรหัสและชื่อ) เหมือน saveItems
        $reg = $this->colRegistry();
        $itemsForView = [];
        foreach ($items as $it) {
            if (trim((string) ($it['code'] ?? '')) === '' && trim((string) ($it['name'] ?? '')) === '') {
                continue;
            }
            $cells = [];
            foreach ($reg as $k => $meta) {
                $cells[$k] = $it[$k] ?? null;
            }
            $itemsForView[] = (object) ['cells' => $cells];
        }

        // ชื่อลูกค้า/อังกฤษ ใช้ค่าจากฟอร์มโดยตรง (ไม่ query DB)
        $cust = (object) [
            'name'   => $request->input('CustName'),
            'nameEN' => $request->input('Engname'),
        ];

        // ชนิดสินค้า — lookup อ่านอย่างเดียว (ไม่มีก็ fallback เป็นรหัส PDtype ใน view)
        $pdtype = $header->PDtype
            ? DB::table('pdtype')->where('PDType', $header->PDtype)->first()
            : null;

        $colConfig = $this->resolveColConfig($header);
        $revisionKeys = ['cur_process_fee', 'new_process_fee', 'cur_pigment_price', 'new_pigment_price', 'new_price'];
        $isRevision = false;
        foreach ($colConfig as $c) {
            if (in_array($c['key'], $revisionKeys, true)) { $isRevision = true; break; }
        }

        $otherNotes = [];
        if (!empty($header->other_notes)) {
            $decoded = json_decode($header->other_notes, true);
            if (is_array($decoded)) {
                $otherNotes = $decoded;
            }
        }

        return view('quotation.print', [
            'header'      => $header,
            'cust'        => $cust,
            'items'       => $itemsForView,
            'pdtype'      => $pdtype,
            'isRevision'  => $isRevision,
            'colConfig'   => $colConfig,
            'colRegistry' => $reg,
            'otherNotes'  => $otherNotes,
            'empName'     => $this->empName($header->EmpID ?? null), // ชื่อผู้เสนอราคา (จากรหัสพนักงาน)
        ]);
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

            $items = $this->parseItemsPayload($request);

            $header = $this->extractHeader($request);
            $header['Qno'] = $qno;
            $header['col_config'] = json_encode($this->buildColConfig($request, $items), JSON_UNESCAPED_UNICODE);
            // timestamps (ใช้ Query Builder → ตั้งเอง; Eloquent ไม่ได้จัดการให้)
            $header['created_at'] = $header['updated_at'] = now();
            DB::table('qmast')->insert($header);

            $this->saveItems($qno, $items);

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

            $items = $this->parseItemsPayload($request);

            $header = $this->extractHeader($request);
            unset($header['Qno']);   // ไม่ให้เปลี่ยนเลขที่ (เป็น key ทางธุรกิจ)
            $header['col_config'] = json_encode($this->buildColConfig($request, $items), JSON_UNESCAPED_UNICODE);
            $header['updated_at'] = now();   // timestamps (Query Builder → ตั้งเอง)
            DB::table('qmast')->whereRaw('TRIM(Qno) = ?', [$qno])->update($header);

            // แทนที่รายการทั้งชุด: ลบของเดิม (รวมราคาแยกย่อย) แล้ว insert ใหม่
            DB::table('qdetail')->whereRaw('TRIM(Qno) = ?', [$qno])->delete();
            DB::table('qdetail_ext')->whereRaw('TRIM(Qno) = ?', [$qno])->delete();
            $this->saveItems($qno, $items);

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
            DB::table('qdetail_ext')->whereRaw('TRIM(Qno) = ?', [$qno])->delete();
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
     * เลือก preset เริ่มต้นของใบเก่าที่ยังไม่มี col_config — derive จาก PDtype
     * (ตระกูล MB → 1.1, นอกนั้น compound → 1.2)
     */
    private function resolveFormat($header): string
    {
        $pd = trim((string) $header->PDtype);
        return in_array($pd, self::MB_FAMILY, true) ? '1.1' : '1.2';
    }

    /**
     * โหลดใบเสนอราคา 1 ใบ (หัว + ชื่อลูกค้าจริง + รายการ + ราคาแยกย่อย) สำหรับ show/edit/print
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

        // แนบราคาแยกย่อย (qdetail_ext) + สร้างค่าแบน (cells) ต่อรายการ
        $ext = DB::table('qdetail_ext')
            ->whereRaw('TRIM(Qno) = ?', [$qno])
            ->get()->keyBy('Qseq');
        foreach ($items as $it) {
            $it->ext = $ext->get($it->Qseq);
            $it->cells = $this->itemCells($it);
        }

        $pdtype = DB::table('pdtype')->where('PDType', $row->PDtype)->first();

        $colConfig = $this->resolveColConfig($row);

        // ประเภทจดหมาย = "ปรับราคา" ถ้าใบนี้มีคอลัมน์ราคาปัจจุบัน/ปัจจุบัน-ใหม่ ไม่งั้น "เสนอราคา"
        $revisionKeys = ['cur_process_fee', 'new_process_fee', 'cur_pigment_price', 'new_pigment_price', 'new_price'];
        $isRevision = false;
        foreach ($colConfig as $c) {
            if (in_array($c['key'], $revisionKeys, true)) { $isRevision = true; break; }
        }

        // หมายเหตุอื่น (JSON array) → array ให้ view วนแสดง
        $otherNotes = [];
        if (!empty($row->other_notes)) {
            $decoded = json_decode($row->other_notes, true);
            if (is_array($decoded)) {
                $otherNotes = $decoded;
            }
        }

        return [
            'header'      => $row,
            'cust'        => $cust,
            'items'       => $items,
            'pdtype'      => $pdtype,
            'isRevision'  => $isRevision,                     // true = แจ้งปรับราคา
            'colConfig'   => $colConfig,                      // คอลัมน์ที่แสดง
            'colRegistry' => $this->colRegistry(),
            'otherNotes'  => $otherNotes,                     // หมายเหตุอื่น (section หมายเหตุ)
            'empName'     => $this->empName($row->EmpID ?? null), // ชื่อผู้เสนอราคา (จากรหัสพนักงาน)
        ];
    }

    /**
     * ชื่อพนักงานผู้เสนอราคา — lookup จากรหัสพนักงาน (qmast.EmpID → emp.empno)
     * EmpID เก็บเป็น int ส่วน emp.empno เป็น varchar ที่เก็บเลขรหัส (เช่น 9013 = "9013")
     * ไม่พบ/ว่าง → คืน null (view จะ fallback เป็นคำว่า "ผู้เสนอราคา")
     */
    private function empName($empId): ?string
    {
        $empId = trim((string) $empId);
        if ($empId === '') {
            return null;
        }
        $e = DB::table('emp')->where('empno', $empId)->first(['empname', 'empsur']);
        if (!$e) {
            return null;
        }
        $name = trim(($e->empname ?? '') . ' ' . ($e->empsur ?? ''));
        return $name !== '' ? $name : null;
    }

    /**
     * คลังคอลัมน์ทั้งหมด (master registry) — key => [label, num, suffix?]
     */
    private function colRegistry(): array
    {
        return [
            'code'              => ['label' => 'รหัสสินค้า',            'num' => false],
            'name'              => ['label' => 'ชื่อสินค้า',            'num' => false],
            'delivery_qty'      => ['label' => 'น้ำหนักส่งครั้งละ (กก)',     'num' => false],
            'resin_price'       => ['label' => 'ราคาเม็ดพลาสติก',       'num' => true],
            'process_fee'       => ['label' => 'ค่าผลิตและค่าแม่สี',        'num' => true],
            'pigment_price'     => ['label' => 'ค่าแม่สี',                 'num' => true],
            'loss_pct'          => ['label' => '% สูญเสีย',             'num' => true, 'suffix' => '%'],
            // ── ค่าผลิต/ค่าแม่สี แบบปรับราคา (ปัจจุบัน | ใหม่) ── กล่องเน้นเฉพาะกลุ่มนี้
            'cur_process_fee'   => ['label' => 'ค่าผลิต (ปัจจุบัน)',     'num' => true],
            'cur_pigment_price' => ['label' => 'ค่าแม่สี (ปัจจุบัน)',       'num' => true],
            'new_process_fee'   => ['label' => 'ค่าผลิต (ใหม่)',        'num' => true],
            'new_pigment_price' => ['label' => 'ค่าแม่สี (ใหม่)',          'num' => true],
            // ── ราคา (ส่วนราคา แยกต่างหาก) — ปัจจุบัน/ใหม่ ──
            'price_kg'          => ['label' => 'ราคา (บาท/กก) ปัจจุบัน',  'num' => true],
            'new_price'         => ['label' => 'ราคา (บาท/กก) ใหม่',     'num' => true],
            'price_vat'         => ['label' => 'รวม VAT (บาท/กก)',      'num' => true],
            'remark'            => ['label' => 'หมายเหตุ',             'num' => false],
        ];
    }

    /**
     * ชื่อกำกับของแต่ละรูปแบบตาราง (ให้ผู้ใช้เลือกใน dropdown)
     * 1.x = ใบเสนอราคา, 2.x = ใบขอปรับราคา (มีคอลัมน์ ปัจจุบัน/ใหม่)
     */
    private function formatLabels(): array
    {
        return [
            '1.1' => 'ราคาต่อ กก. (ตามน้ำหนักส่ง)',
            '1.2' => 'เม็ดพลาสติก + ค่าผลิตและค่าแม่สี',
            '1.3' => 'ค่าผลิต + ค่าแม่สี (แยกกัน)',
            '1.4' => 'เม็ดพลาสติก + ค่าผลิตฯ + % สูญเสีย',
            '2.1' => 'ราคา ปัจจุบัน/ใหม่',
            '2.2' => 'ค่าผลิตและค่าแม่สี ปัจจุบัน/ใหม่',
            '2.3' => 'ค่าผลิต + ค่าแม่สี ปัจจุบัน/ใหม่',
        ];
    }

    /**
     * Preset คอลัมน์ของ 7 รูปแบบ (+ '' = อัตโนมัติ/ทั่วไป)
     * ใช้เป็น "จุดเริ่มต้น" — ผู้ใช้ปรับ (เพิ่ม/ลบ/สลับ/แก้ชื่อ) เองต่อใบได้
     */
    private function presets(): array
    {
        $c = fn ($k, $l) => ['key' => $k, 'label' => $l];
        return [
            ''    => [$c('code','รหัสสินค้า'), $c('name','ชื่อสินค้า'), $c('price_kg','ราคา (บาท/กก)'), $c('price_vat','ราคารวมภาษี')],
            '1.1' => [$c('code','สินค้า'), $c('name','รายละเอียด'), $c('delivery_qty','น้ำหนักส่งครั้งละ (กก)'), $c('price_kg','ราคา (บาท/กก)'), $c('price_vat','รวม Vat (บาท/กก)'), $c('remark','หมายเหตุ')],
            '2.1' => [$c('code','สินค้า'), $c('name','รายละเอียด'), $c('delivery_qty','น้ำหนักส่งครั้งละ (กก)'), $c('price_kg','ราคา (บาท/กก) ปัจจุบัน'), $c('new_price','ราคา (บาท/กก) ใหม่'), $c('price_vat','รวม Vat ใหม่'), $c('remark','หมายเหตุ')],
            '1.2' => [$c('code','สินค้า'), $c('name','รายละเอียด'), $c('delivery_qty','น้ำหนักส่งครั้งละ (กก)'), $c('resin_price','ราคาเม็ดพลาสติก'), $c('process_fee','ค่าผลิตและค่าแม่สี'), $c('price_kg','ราคา (บาท/กก)'), $c('price_vat','รวม Vat (บาท/กก)')],
            // 2.2 ตามแบบฟอร์มลูกค้า: มีแค่ ค่าผลิตและค่าสี ปัจจุบัน|ใหม่ — ไม่มีเม็ดพลาสติก/ราคา/VAT
            '2.2' => [$c('code','สินค้า'), $c('name','รายละเอียด'), $c('delivery_qty','น้ำหนักส่งครั้งละ (กก)'), $c('cur_process_fee','ค่าผลิตและค่าสี ปัจจุบัน'), $c('new_process_fee','ค่าผลิตและค่าสี ใหม่')],
            '1.3' => [$c('code','สินค้า'), $c('name','รายละเอียด'), $c('delivery_qty','น้ำหนักส่งครั้งละ (กก)'), $c('process_fee','ค่าผลิต'), $c('pigment_price','ค่าแม่สี'), $c('price_kg','ราคา (บาท/กก)'), $c('price_vat','รวม Vat (บาท/กก)')],
            // 2.3 ตามแบบฟอร์มลูกค้า: ปิดท้ายด้วย "รวมราคา" ของฝั่งใหม่ (= ค่าผลิตใหม่ + ค่าแม่สีใหม่) — ไม่มีคอลัมน์ VAT
            '2.3' => [$c('code','สินค้า'), $c('name','รายละเอียด'), $c('delivery_qty','น้ำหนักส่งครั้งละ (กก)'), $c('cur_process_fee','ค่าผลิต ปัจจุบัน'), $c('cur_pigment_price','ค่าแม่สี ปัจจุบัน'), $c('new_process_fee','ค่าผลิต ใหม่'), $c('new_pigment_price','ค่าแม่สี ใหม่'), $c('price_kg','รวมราคา (บาท/กก) ใหม่')],
            '1.4' => [$c('code','สินค้า'), $c('name','รายละเอียด'), $c('delivery_qty','น้ำหนักส่งครั้งละ (กก)'), $c('resin_price','ราคาเม็ดพลาสติก'), $c('process_fee','ค่าผลิตและค่าแม่สี'), $c('loss_pct','% สูญเสีย'), $c('price_kg','ราคา (บาท/กก)'), $c('price_vat','รวม Vat (บาท/กก)')],
        ];
    }

    /**
     * คอลัมน์ที่จะแสดงจริงของใบนี้ — ใช้ col_config ที่ผู้ใช้บันทึกไว้ก่อน
     * ถ้าไม่มี → ใช้ preset ตามรูปแบบที่ derive ได้
     */
    private function resolveColConfig($header): array
    {
        $reg = $this->colRegistry();
        if (!empty($header->col_config)) {
            $decoded = json_decode($header->col_config, true);
            if (is_array($decoded) && $decoded) {
                $out = [];
                foreach ($decoded as $c) {
                    if (empty($c['key']) || !isset($reg[$c['key']])) continue;
                    $label = $c['label'] ?? $reg[$c['key']]['label'];
                    $out[] = [
                        'key'      => $c['key'],
                        'label'    => $label,
                        'label_en' => $this->labelEn($c['key'], $label),
                    ];
                }
                if ($out) return $out;
            }
        }
        $presets = $this->presets();
        $cols = $presets[$this->resolveFormat($header)] ?? $presets[''];
        // แนบ label_en ให้ preset ด้วย (กันกรณีใบเก่าที่ไม่มี col_config)
        return array_map(fn ($c) => $c + ['label_en' => $this->labelEn($c['key'], $c['label'])], $cols);
    }

    /**
     * แปลชื่อหัวคอลัมน์ ไทย → อังกฤษ สำหรับใบเสนอราคาภาษาอังกฤษ (remark_lang = 'en')
     *
     * แปลตาม "ข้อความ label ไทย" เป็นหลัก เพราะคีย์เดียวกันสื่อความต่างกันตามรูปแบบตาราง
     * (เช่น process_fee = "ค่าผลิต" หรือ "ค่าผลิตและค่าแม่สี") — ถ้าไม่พบใน map
     * จึง fallback เป็นคำแปลตามคีย์ และสุดท้าย fallback เป็น label ไทยเดิม
     */
    private function labelEn(string $key, string $thaiLabel): string
    {
        // แปลตามข้อความ label ไทย (ครอบคลุมทุก preset)
        $byLabel = [
            'รหัสสินค้า'                 => 'Product',
            'สินค้า'                     => 'Product',
            'ชื่อสินค้า'                  => 'Description',
            'รายละเอียด'                 => 'Description',
            'น้ำหนักส่งครั้งละ (กก)'      => 'Delivery Quantity (kg)',
            'ราคาเม็ดพลาสติก'            => 'Resin Price',
            'ค่าผลิตและค่าแม่สี'          => 'Process&Pigment Fee',
            'ค่าผลิต'                    => 'Process Fee',
            'ค่าแม่สี'                   => 'Pigment Price',
            '% สูญเสีย'                  => '% Loss',
            'ราคา (บาท/กก)'              => 'Price (THB/kg)',
            'ราคา (บาท/กก) ปัจจุบัน'      => 'Price (THB/kg) Current',
            'ราคา (บาท/กก) ใหม่'         => 'Price (THB/kg) New',
            'รวม Vat (บาท/กก)'          => 'Incld VAT (THB/Kg)',
            'รวม Vat ใหม่'              => 'Incld VAT (THB/Kg) New',
            'ราคารวมภาษี'                => 'Incld VAT (THB/Kg)',
            'ค่าผลิตฯ ปัจจุบัน'           => 'Process&Pigment Fee Current',
            'ค่าผลิตฯ ใหม่'              => 'Process&Pigment Fee New',
            'ค่าผลิตและค่าสี ปัจจุบัน'      => 'Process&Pigment Fee Current',
            'ค่าผลิตและค่าสี ใหม่'         => 'Process&Pigment Fee New',
            'รวมราคา (บาท/กก) ใหม่'       => 'Total Price (THB/kg) New',
            'ค่าผลิต ปัจจุบัน'            => 'Process Fee Current',
            'ค่าผลิต ใหม่'               => 'Process Fee New',
            'ค่าแม่สี ปัจจุบัน'            => 'Pigment Price Current',
            'ค่าแม่สี ใหม่'               => 'Pigment Price New',
            'หมายเหตุ'                   => 'Remarks',
        ];

        // fallback ตามคีย์ (กรณี label ถูกผู้ใช้แก้จนไม่ตรง map ด้านบน)
        $byKey = [
            'code'              => 'Product',
            'name'              => 'Description',
            'delivery_qty'      => 'Delivery Quantity (kg)',
            'resin_price'       => 'Resin Price',
            'process_fee'       => 'Process Fee',
            'pigment_price'     => 'Pigment Price',
            'loss_pct'          => '% Loss',
            'cur_process_fee'   => 'Process Fee Current',
            'cur_pigment_price' => 'Pigment Price Current',
            'new_process_fee'   => 'Process Fee New',
            'new_pigment_price' => 'Pigment Price New',
            'price_kg'          => 'Price (THB/kg)',
            'new_price'         => 'Price (THB/kg) New',
            'price_vat'         => 'Incld VAT (THB/Kg)',
            'remark'            => 'Remarks',
        ];

        $t = trim($thaiLabel);
        return $byLabel[$t] ?? $byKey[$key] ?? $thaiLabel;
    }

    /**
     * รวมค่าของ 1 รายการเป็น map แบน (key => value) ตาม registry
     */
    private function itemCells($it): array
    {
        $e = $it->ext ?? null;
        return [
            'code'              => $it->Qitemno,
            'name'              => $it->Qdesc,
            'delivery_qty'      => $e->delivery_qty ?? null,
            'resin_price'       => $e->resin_price ?? null,
            'process_fee'       => $e->process_fee ?? null,
            'pigment_price'     => $e->pigment_price ?? null,
            'loss_pct'          => $e->loss_pct ?? null,
            'price_kg'          => $e->price_kg  ?? $it->QPrice,
            'cur_process_fee'   => $e->cur_process_fee ?? null,
            'cur_pigment_price' => $e->cur_pigment_price ?? null,
            'new_process_fee'   => $e->new_process_fee ?? null,
            'new_pigment_price' => $e->new_pigment_price ?? null,
            'new_price'         => $e->new_price ?? null,
            'price_vat'         => $e->price_vat ?? $it->QNet,
            'remark'            => $e->remark ?? null,
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

        // หมายเหตุอื่น (เพิ่มได้ไม่จำกัด) — เก็บเป็น JSON array; ตัดบรรทัดว่างออก
        // ตั้งค่าเสมอ (แม้เป็น null) เพื่อให้ตอน update สามารถล้างหมายเหตุที่ลบออกได้
        $notes = array_values(array_filter(
            array_map(fn ($v) => trim((string) $v), (array) $request->input('other_notes', [])),
            fn ($v) => $v !== ''
        ));
        $header['other_notes'] = $notes ? json_encode($notes, JSON_UNESCAPED_UNICODE) : null;

        return $header;
    }

    // ─── field ราคาแยกย่อยใน qdetail_ext (คอลัมน์แปรตามรูปแบบใบ) ───
    private const EXT_NUM_FIELDS = [
        'resin_price', 'process_fee', 'pigment_price', 'loss_pct', 'price_kg', 'price_vat',
        'cur_process_fee', 'new_process_fee', 'cur_pigment_price', 'new_pigment_price',
        'new_price',
    ];

    /**
     * คอลัมน์ที่จะแสดงใน PDF ของใบนี้ — ตัดสินจากรูปแบบตารางที่ผู้ใช้เลือกในฟอร์ม (col_format)
     *
     *  - เลือกรูปแบบ (1.1–2.3) → ใช้คอลัมน์ตาม preset นั้นตรง ๆ (คงคอลัมน์ไว้ครบแม้บางช่องยังว่าง
     *    เพราะผู้ใช้จงใจเลือกหน้าตาตารางแบบนั้น)
     *  - ไม่เลือก (ค่าเริ่มต้น) → พฤติกรรมเดิม: derive จากคอลัมน์ที่มีคนกรอกจริง
     *
     * ทั้งสองทางเคารพ checkbox "แสดงรหัสสินค้าใน PDF" เหมือนกัน
     */
    private function buildColConfig(Request $request, array $items): array
    {
        $showCode = $request->boolean('show_code');
        $format   = trim((string) $request->input('col_format', ''));
        $presets  = $this->presets();

        if ($format === '' || !isset($presets[$format])) {
            return $this->deriveColConfig($items, $showCode);
        }

        $cols = $presets[$format];
        if (!$showCode) {
            $cols = array_values(array_filter($cols, fn ($c) => $c['key'] !== 'code'));
        }

        return $cols;
    }

    /**
     * คอลัมน์ที่จะแสดง = เฉพาะคอลัมน์ที่มีการกรอกอย่างน้อย 1 แถว (ตามลำดับใน registry)
     * → ช่องไหนไม่มีใครกรอกเลย ถูกตัดออกอัตโนมัติ
     *
     * $showCode = ผู้ใช้เลือกจากฟอร์มว่าจะให้ "รหัสสินค้า" ขึ้นใน PDF ไหม
     * (รหัสต้องกรอกไว้เพื่อใช้ค้นหาชื่อ/ราคา แต่บางใบไม่อยากโชว์รหัสให้ลูกค้าเห็น)
     */
    private function deriveColConfig(array $items, bool $showCode = true): array
    {
        $reg = $this->colRegistry();
        $used = [];
        foreach ($items as $it) {
            foreach ($reg as $k => $meta) {
                if (isset($used[$k])) continue;
                $v = $it[$k] ?? null;
                if ($v !== null && trim((string) $v) !== '') {
                    $used[$k] = true;
                }
            }
        }
        // ไม่ติ๊ก "แสดงรหัสสินค้าใน PDF" → ตัดคอลัมน์รหัสออก แม้จะกรอกไว้ก็ตาม
        if (!$showCode) {
            unset($used['code']);
        }
        $out = [];
        foreach ($reg as $k => $meta) {
            if (isset($used[$k])) {
                $out[] = ['key' => $k, 'label' => $meta['label']];
            }
        }
        return $out;
    }

    /**
     * บันทึกรายการสินค้า (qdetail + qdetail_ext) — Qseq เป็นเลข running ทั้งระบบ (max+1 ต่อแถว)
     * รับ items ที่ parse แล้ว (array) ; ข้ามแถวว่างเปล่า
     */
    private function saveItems(string $qno, array $items): void
    {
        $seq = (int) DB::table('qdetail')->max('Qseq');

        $base = [];
        $ext  = [];
        foreach ($items as $it) {
            $code = trim((string) ($it['code'] ?? ''));
            $name = trim((string) ($it['name'] ?? ''));
            if ($code === '' && $name === '') {
                continue;   // แถวว่างทั้งบรรทัด → ข้าม
            }
            $seq++;

            // qdetail (ราคาหลัก): QPrice = ราคาสุดท้าย (เสนอ/ใหม่), QNet = รวมภาษี, oldprice = ราคาปัจจุบัน
            $base[] = [
                'Qno'      => $qno,
                'Qseq'     => $seq,
                'Qitemno'  => $code !== '' ? $code : null,
                'Qdesc'    => $name !== '' ? $name : null,
                'oldprice' => $this->num($it['price_kg'] ?? null),                      // ราคาปัจจุบัน
                'QPrice'   => $this->num($it['new_price'] ?? ($it['price_kg'] ?? null)), // ราคาใหม่ (ไม่มี = ปัจจุบัน)
                'QNet'     => $this->num($it['price_vat'] ?? null),
            ];

            // qdetail_ext (ราคาแยกย่อย): เก็บเฉพาะถ้ามี field แยกย่อยอย่างน้อย 1
            $extRow = ['Qno' => $qno, 'Qseq' => $seq];
            $has = false;
            $dq = trim((string) ($it['delivery_qty'] ?? ''));
            $rm = trim((string) ($it['remark'] ?? ''));
            if ($dq !== '') { $extRow['delivery_qty'] = $dq; $has = true; }
            if ($rm !== '') { $extRow['remark'] = $rm; $has = true; }
            foreach (self::EXT_NUM_FIELDS as $f) {
                $v = $this->num($it[$f] ?? null);
                if ($v !== null) { $extRow[$f] = $v; $has = true; }
            }
            if ($has) {
                $ext[] = $extRow;
            }
        }

        if ($base) DB::table('qdetail')->insert($base);

        if ($ext) {
            // insert หลายแถวในคำสั่งเดียว → ทุกแถวต้องมีคอลัมน์ชุดเดียวกัน
            // (Laravel ยึดคอลัมน์จากแถวแรก ถ้าแถวอื่นมี key ไม่ครบ จะได้ SQL ที่จำนวนค่าไม่ตรงคอลัมน์)
            // $extRow ด้านบนใส่เฉพาะ key ที่มีค่า → เติมช่องที่ขาดเป็น null ให้ครบก่อน
            $template = array_fill_keys(
                array_merge(['Qno', 'Qseq', 'delivery_qty', 'remark'], self::EXT_NUM_FIELDS),
                null
            );
            $ext = array_map(fn ($row) => array_merge($template, $row), $ext);

            DB::table('qdetail_ext')->insert($ext);
        }
    }

    /**
     * แปลง payload รายการ — รับ items_json (คอลัมน์แปรตามรูปแบบ) เป็นหลัก
     * fallback: array แยก field แบบเดิม (item_code[] ...)
     */
    private function parseItemsPayload(Request $request): array
    {
        if ($request->filled('items_json')) {
            $decoded = json_decode($request->items_json, true);
            return is_array($decoded) ? $decoded : [];
        }
        // fallback รูปแบบเดิม
        $codes = (array) $request->input('item_code', []);
        $names = (array) $request->input('item_name', []);
        $olds  = (array) $request->input('item_old_price', []);
        $news  = (array) $request->input('item_new_price', []);
        $tots  = (array) $request->input('item_total_price', []);
        $out = [];
        foreach ($names as $i => $nm) {
            $out[] = [
                'code' => $codes[$i] ?? '', 'name' => $nm,
                'oldprice' => $olds[$i] ?? null, 'price_kg' => $news[$i] ?? null, 'price_vat' => $tots[$i] ?? null,
            ];
        }
        return $out;
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
