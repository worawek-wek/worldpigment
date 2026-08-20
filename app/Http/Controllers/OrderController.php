<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Morder;
use App\Models\SubOrder;
use App\Services\ProductPriceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * ใบสั่งซื้อ (O-Order) — แปลงมาจากฟอร์ม Access "บันทึกคำสั่งซื้อ"
 *
 * ตารางที่ใช้ (legacy ทั้งหมด อ่านอย่างเดียวในเฟสนี้):
 *   morder    หัวใบสั่งซื้อ (PK = Orderno)
 *   suborder  รายการในใบสั่งซื้อ (PK = Runno + Orderno)
 *   orderrun  เลขรันของแต่ละประเภทใบสั่ง
 *   naddress  สถานที่ส่งของลูกค้าแต่ละราย (Custno + DVpoint)
 *   ordrem    ข้อความหมายเหตุสำเร็จรูป (dropdown ในตารางรายการ)
 *   customer  ข้อมูลลูกค้า + ค่าตั้งต้นของ RP / CER / PO / MSDS
 *   c_type    ประเภทอุตสาหกรรมของลูกค้า (ปุ่ม itype บนฟอร์มเดิม)
 *   uprice / appvreq / zcustprice / pdprice   ข้อมูลราคาที่โชว์บนฟอร์ม
 */
class OrderController extends Controller
{
    /**
     * ประเภทใบสั่งซื้อ = ตัวอักษร 2 ตัวหน้าของเลขที่ใบสั่ง (radio บนหัวฟอร์ม)
     * ค่า = คอลัมน์เลขรันใน orderrun ที่ประเภทนั้นใช้ (หลายประเภทใช้ตัวรันร่วมกัน เช่น CM/CI ใช้ c)
     */
    private const ORDER_TYPES = [
        'CM' => 'c',  'CI' => 'c',
        'HM' => 'h',  'HI' => 'h',
        'WM' => 'w',  'WI' => 'w',
        'CE' => 'ce', 'CR' => 'CR',
        'HE' => 'he', 'HR' => 'HR',
        'WE' => 'we', 'WR' => 'WR',
    ];

    /** ผังปุ่มบนฟอร์ม Access — 2 แถว แถวละ 6 ประเภท (เรียงตามภาพต้นฉบับ) */
    private const TYPE_ROWS = [
        ['CM', 'CI', 'HM', 'HI', 'WM', 'WI'],
        ['CE', 'CR', 'HE', 'HR', 'WE', 'WR'],
    ];

    /** คอลัมน์ checkbox ของ Access เก็บ -1 = ติ๊ก, 0/NULL = ไม่ติ๊ก */
    private static function checked($value): bool
    {
        return (int) $value !== 0 && $value !== null;
    }

    // ─────────────────────────────────────────────────────────────
    //  หน้าหลัก + ตารางรายการ
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $data['page_url']  = 'order';
        $data['type_rows'] = self::TYPE_ROWS;

        // "ผลิตที่" — ค่าที่เคยใช้จริงใน morder (MB / DB / SPP / CP)
        $data['companies'] = DB::table('morder')
            ->whereRaw("TRIM(COALESCE(Company, '')) <> ''")
            ->distinct()
            ->orderBy('Company')
            ->pluck('Company');

        // ข้อความหมายเหตุสำเร็จรูปของตารางรายการ
        $data['remarks'] = DB::table('ordrem')->orderBy('rem')->pluck('rem');

        // ค่า "รหัส" (suborder.nold) ที่เคยใช้จริงในระบบ — ใช้เป็นตัวเลือกในตารางรายการ
        $data['nold_options'] = DB::table('suborder')
            ->whereRaw("TRIM(COALESCE(nold, '')) <> ''")
            ->distinct()
            ->orderBy('nold')
            ->pluck('nold');

        // ผู้บันทึก = พนักงานที่ล็อกอินอยู่ (เติมให้อัตโนมัติเมื่อเปิดใบใหม่)
        $data['current_emp'] = optional(Auth::user())->empno;

        return view('order.index', $data);
    }

    /**
     * GET — ตารางรายการใบสั่งซื้อ (HTML partial ใส่ #table-data)
     */
    public function datatable(Request $request)
    {
        // whitelist การเรียง: key หัวตาราง → คอลัมน์/alias จริง (กัน SQL injection)
        $sortable = [
            'Mdate'      => 'morder.Mdate',
            'Orderno'    => 'morder.Orderno',
            'Custno'     => 'morder.Custno',
            'Company'    => 'morder.Company',
            'item_count' => 'agg.item_count',
            'total_prod' => 'agg.total_prod',
        ];
        $sortKey = (string) $request->sort_col;
        $sortKey = isset($sortable[$sortKey]) ? $sortKey : 'Mdate';   // default = วันที่สั่ง
        $sortDir = strtolower((string) $request->sort_dir) === 'asc' ? 'asc' : 'desc';

        // สรุปรายการต่อ 1 ใบสั่ง — คำนวณครั้งเดียวแล้ว join (เร็วกว่า correlated subquery ตอน ORDER BY)
        $agg = DB::table('suborder')
            ->select('Orderno')
            ->selectRaw('COUNT(*) as item_count')
            ->selectRaw('COALESCE(SUM(Production), 0) as total_prod')
            ->selectRaw('COALESCE(SUM(Stock), 0) as total_stock')
            ->selectRaw('MIN(senddate) as first_senddate')
            ->selectRaw("GROUP_CONCAT(DISTINCT NULLIF(TRIM(Itemno), '') SEPARATOR ', ') as itemno_list")
            ->groupBy('Orderno');

        $results = Morder::query()
            ->leftJoin('customer as c', 'morder.Custno', '=', 'c.code')
            ->leftJoinSub($agg, 'agg', 'agg.Orderno', '=', 'morder.Orderno')
            ->select('morder.*', 'c.name as cust_name')
            ->selectRaw('COALESCE(agg.item_count, 0) as item_count')
            ->selectRaw('COALESCE(agg.total_prod, 0) as total_prod')
            ->selectRaw('COALESCE(agg.total_stock, 0) as total_stock')
            ->addSelect('agg.first_senddate', 'agg.itemno_list')
            ->orderBy($sortable[$sortKey], $sortDir);

        // เรียงรองด้วยเลขที่ใบสั่ง ให้ลำดับคงที่เมื่อค่าคอลัมน์หลักซ้ำกัน
        if ($sortable[$sortKey] !== 'morder.Orderno') {
            $results->orderBy('morder.Orderno', 'desc');
        }

        $this->applyFilters($results, $request);

        $limit = $request->input('limit') ?: 15;
        $results = $results->paginate($limit);

        $data['list_data'] = $results;
        $data['sort_col']  = $sortKey;
        $data['sort_dir']  = $sortDir;

        return view('order.table', $data);
    }

    /** ตัวกรองของหน้ารายการ */
    private function applyFilters($query, Request $request): void
    {
        // ค้นหารวมช่องเดียว: เลขที่ใบสั่ง / P.O. / รหัสลูกค้า / ชื่อลูกค้า / รหัสสินค้าในใบ
        if ($search = trim((string) $request->input('search'))) {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('morder.Orderno', 'like', $like)
                    ->orWhere('morder.PO', 'like', $like)
                    ->orWhere('morder.Custno', 'like', $like)
                    ->orWhere('morder.Custname', 'like', $like)
                    ->orWhere('c.name', 'like', $like)
                    ->orWhereExists(function ($sub) use ($like) {
                        $sub->from('suborder')
                            ->whereColumn('suborder.Orderno', 'morder.Orderno')
                            ->where('suborder.Itemno', 'like', $like);
                    });
            });
        }

        // ประเภทใบสั่ง = 2 ตัวอักษรหน้าเลขที่
        $type = strtoupper((string) $request->input('order_type'));
        if (isset(self::ORDER_TYPES[$type])) {
            $query->where('morder.Orderno', 'like', $type . '%');
        }

        // ผลิตที่
        if ($company = trim((string) $request->input('company'))) {
            $query->where('morder.Company', $company);
        }

        // ช่วงวันที่สั่ง (ช่องกรอกเป็น d/m/Y จาก flatpickr)
        if ($from = $this->parseDate($request->input('date_from'))) {
            $query->whereDate('morder.Mdate', '>=', $from);
        }
        if ($to = $this->parseDate($request->input('date_to'))) {
            $query->whereDate('morder.Mdate', '<=', $to);
        }
    }

    /** แปลงวันที่จากช่องกรอก d/m/Y → Y-m-d (ค่าว่าง/รูปแบบผิด = null) */
    private function parseDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $value, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        if (preg_match('#^\d{4}-\d{2}-\d{2}#', $value)) {
            return substr($value, 0, 10);
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────
    //  ฟอร์มบันทึกใบสั่งซื้อ
    // ─────────────────────────────────────────────────────────────

    /**
     * GET — ข้อมูลทั้งใบสำหรับเติมฟอร์ม (JSON)
     *   ?orderno=WM23946
     */
    public function form(Request $request)
    {
        $orderno = trim((string) $request->query('orderno', ''));
        if ($orderno === '') {
            return response()->json(['found' => false]);
        }

        $order = Morder::where('Orderno', $orderno)->first();
        if (!$order) {
            return response()->json(['found' => false]);
        }

        $items = SubOrder::where('Orderno', $orderno)
            ->orderBy('Runno')
            ->get();

        // รหัสสินค้าหลักของใบ (ฟอร์มเดิมโชว์ช่องเดียวมุมขวาบน) = รหัสแรกที่พบในรายการ
        $itemno = optional($items->first(function ($r) {
            return trim((string) $r->Itemno) !== '';
        }))->Itemno;

        return response()->json([
            'found'     => true,
            'order'     => $this->orderPayload($order),
            'items'     => $items->map(fn ($r) => $this->itemPayload($r))->values(),
            'customer'  => $this->customerPayload($order->Custno),
            'dvpoints'  => $this->dvpoints($order->Custno),
            'itemno'    => $itemno,
            'price'     => $this->priceData($order->Custno, $itemno, $order->netqty),
        ]);
    }

    /** หัวใบสั่งซื้อ → รูปแบบที่ฟอร์มใช้ (แปลง checkbox แบบ Access, ตัดเวลาออกจากวันที่) */
    private function orderPayload(Morder $order): array
    {
        return [
            'Orderno'  => $order->Orderno,
            'type'     => strtoupper(substr((string) $order->Orderno, 0, 2)),
            'Mdate'    => $order->Mdate,
            'Company'  => $order->Company,
            'PO'       => $order->PO,
            'Custno'   => $order->Custno,
            'Custname' => $order->Custname,
            'Emp'      => $order->Emp,
            'supno'    => $order->supno,
            'DVpoint'  => $order->DVpoint,
            'RsvNo'    => $order->RsvNo,
            'netqty'   => $order->netqty,
            'price'    => $order->price,
            // กรณีสั่งทำสต๊อก
            'sendend'  => $order->sendend,
            'SendCust' => $order->SendCust,
            'HMStore'  => $order->HMStore,
            'sendmth'  => $order->sendmth,
            // checkbox (Access เก็บ -1 = ติ๊ก)
            'Send'     => self::checked($order->Send),
            'RP'       => self::checked($order->RP),
            'Spec'     => self::checked($order->Spec),
            'Cer'      => self::checked($order->Cer),
            'MSDS'     => self::checked($order->MSDS),
        ];
    }

    /** 1 รายการในตารางล่างของฟอร์ม */
    private function itemPayload(SubOrder $row): array
    {
        return [
            'Runno'      => $row->Runno,
            'Itemno'     => $row->Itemno,
            'prodname'   => $row->prodname,
            'Lotno'      => $row->Lotno,
            'Stock'      => $row->Stock,
            'Production' => $row->Production,
            'custwant'   => $row->custwant,
            'senddate'   => $row->senddate,
            'EndP'       => $row->EndP,
            'DVDate'     => $row->DVDate,
            'outno'      => $row->outno,
            'Remark'     => $row->Remark,
        ];
    }

    /**
     * GET — ค้นลูกค้าจากรหัส (เติมชื่อ + สถานที่ส่ง + ค่าตั้งต้น RP/CER บนฟอร์ม)
     */
    public function customerLookup($code)
    {
        $cust = $this->customerPayload($code);

        if (!$cust) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'    => true,
            'customer' => $cust,
            'dvpoints' => $this->dvpoints($code),
        ]);
    }

    /** ข้อมูลลูกค้า + ค่าตั้งต้นของ checkbox บนฟอร์ม (null ถ้าไม่พบ) */
    private function customerPayload($code): ?array
    {
        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }

        $cust = DB::table('customer')->where('code', $code)->first();
        if (!$cust) {
            return null;
        }

        // ประเภทอุตสาหกรรมของลูกค้า (ปุ่ม itype บนฟอร์ม Access)
        $typeName = $cust->type
            ? DB::table('c_type')->where('type', $cust->type)->value('t_namee')
            : null;

        return [
            'code'      => $cust->code,
            'name'      => $cust->name,
            'nameEN'    => $cust->nameEN,
            'sale'      => $cust->sale,     // รหัสผู้ขายประจำลูกค้า → เติมช่อง "รหัสผู้ขาย" (morder.supno)
            'term'      => $cust->term,
            'type'      => $cust->type,
            'type_name' => $typeName,
            'RP'        => self::checked($cust->RP),
            'CER'       => self::checked($cust->CER),
            'PO'        => self::checked($cust->PO),
            'MSDS'      => self::checked($cust->MSDS),
        ];
    }

    /** สถานที่ส่งของลูกค้ารายนี้ (dropdown "สถานที่ส่ง") */
    private function dvpoints($custno): array
    {
        $custno = trim((string) $custno);
        if ($custno === '') {
            return [];
        }

        return DB::table('naddress')
            ->where('Custno', $custno)
            ->orderBy('DVpoint')
            ->pluck('DVpoint')
            ->all();
    }

    /**
     * GET — ข้อมูลราคาของคู่ (ลูกค้า, รหัสสินค้า) สำหรับกล่องราคาบนฟอร์ม
     *   ?custno=41008&itemno=CP-WPIN[NH900L]
     */
    public function priceInfo(Request $request)
    {
        return response()->json($this->priceData(
            $request->query('custno'),
            $request->query('itemno'),
            $request->query('weight')
        ));
    }

    /**
     * กล่องราคาบนฟอร์ม
     *
     * ราคา 1/2/3 มาจาก **ระบบกำหนดราคา** (`ProductPriceService` ตัวเดียวกับที่หน้า
     * "ค้นหาราคาสินค้า" ใน /saleinfo ใช้) — คำนวณจากราคาทุนใน `access_pdprice`
     * ตามเงื่อนไขที่จับคู่ด้วยตัวขึ้นต้นรหัส: ราคา1 = ทุน × mul ÷ div + add,
     * ราคา2 = ราคา1 × 1.14, ราคา3 = ราคา2 × 1.30 (ตัวคูณอยู่ที่ `product_price.tier`)
     * → **อ้างอิงจากรหัสสินค้าอย่างเดียว ไม่เกี่ยวกับลูกค้า**
     *
     *   fixed_price  ราคาที่กำหนดไว้      = ราคาขาย 1
     *   price2       ราคาช่อง 2           = ราคาขาย 2
     *   min_price    ราคาต้องไม่ต่ำกว่า    = ราคาขาย 2
     *   cost_price   ราคาทุน              = ราคาทุนที่ใช้คำนวณ
     *
     * ส่วนที่ผูกกับลูกค้า (ใช้ custno ด้วย):
     *   appv_price   ราคาอนุมัติ          appvreq.price
     *   valid_to     ยืนราคาถึง           zcustprice.enddate
     *
     * หมายเหตุ: "ราคาขาย" (morder.price) ผู้ใช้พิมพ์เอง ไม่ได้ดึงมาจากที่นี่
     */
    private function priceData($custno, $itemno, $weight = null): array
    {
        $custno = trim((string) $custno);
        $itemno = trim((string) $itemno);

        $empty = [
            'found'       => false, 'message' => null, 'others' => [],
            'fixed_price' => null, 'fixed_src' => null,
            'price1'      => null, 'price2' => null, 'price3' => null,
            'appv_price'  => null, 'appv'   => null, 'valid_to' => null,
            'cost_price'  => null, 'remark' => null,
            'group'       => null, 'min_price' => null,
        ];

        // ต้องมีรหัสสินค้าเป็นอย่างน้อย — ราคา 1/2/3 คำนวณจากรหัสสินค้าล้วน ไม่ต้องรู้ลูกค้า
        if ($itemno === '') {
            return $empty + ['message' => 'ยังไม่ได้กรอกรหัสสินค้าในตารางรายการ'];
        }

        // ราคาที่ตกลงไว้กับลูกค้ารายนี้
        $uprice = $custno === '' ? null : DB::table('uprice')
            ->where('CustNo', $custno)
            ->where('ITEMNO', $itemno)
            ->orderByDesc('DATE')
            ->first(['PRICE', 'REM2']);

        // ใบขออนุมัติราคาล่าสุดของคู่นี้ (ใช้เฉพาะ "ราคาอนุมัติ")
        $appv = $custno === '' ? null : DB::table('appvreq')
            ->where('custno', $custno)
            ->where('itemno', $itemno)
            ->orderByDesc('ReqDate')
            ->first(['price', 'price1', 'price2', 'price3', 'Appv']);

        // วันสิ้นสุดการยืนราคา
        $zcust = $custno === '' ? null : DB::table('zcustprice')
            ->where('custno', $custno)
            ->where('colorno', $itemno)
            ->first(['exprice', 'enddate']);

        // ── ราคา 1/2/3 จากระบบกำหนดราคา (ตัวเดียวกับหน้า "ค้นหาราคาสินค้า" ใน /saleinfo) ──
        $calc   = app(ProductPriceService::class)->lookup($itemno);
        $prices = $calc['prices'] ?? null;

        // กลุ่มราคาตามปริมาณที่สั่ง (A ≥1,000 / B ≥500 / C ต่ำกว่า 500) — แสดงประกอบเฉย ๆ
        $group = PriceApprovalController::groupOf($weight);

        // คำนวณไม่ได้ → บอกเหตุผลที่ระบบกำหนดราคาให้มา (ไม่พบราคาทุน / ไม่มีเงื่อนไขรองรับ ฯลฯ)
        $message = $prices ? null : ($calc['reason'] ?? 'คำนวณราคาไม่ได้');

        return [
            'found'       => (bool) $prices,
            'message'     => $message,
            'others'      => [],
            'group'       => $group ? $group['group'] : null,
            'group_label' => $group ? $group['label'] : null,
            // ราคาที่กำหนดไว้ = ราคาขาย 1 · ราคาช่อง 2 / ราคาต้องไม่ต่ำกว่า = ราคาขาย 2
            'fixed_price' => $prices['price_1'] ?? null,
            'price2'      => $prices['price_2'] ?? null,
            'min_price'   => $prices['price_2'] ?? null,
            'price1'      => $prices['price_1'] ?? null,
            'price3'      => $prices['price_3'] ?? null,
            // ที่มาของราคา — ราคาทุน + เงื่อนไขที่จับคู่ได้ + สูตร (โชว์ใต้กล่องราคา)
            'cost_price'  => $calc['base_price'] ?? null,
            'rule_label'  => $calc['rule']['label'] ?? null,
            'formula'     => isset($calc['rule'])
                ? '× ' . $calc['rule']['mul'] . ' ÷ ' . $calc['rule']['div'] . ' + ' . $calc['rule']['add']
                : null,
            // ส่วนที่ผูกกับลูกค้า
            'appv_price'  => $appv->price ?? null,
            'appv'        => isset($appv->Appv) ? self::checked($appv->Appv) : null,
            'valid_to'    => $zcust->enddate ?? null,
            'remark'      => $uprice->REM2 ?? null,
        ];
    }

    /**
     * GET — เลขที่ใบสั่งถัดไปของประเภทที่เลือก (ปุ่ม "เพิ่มใบสั่งซื้อใหม่")
     *   ?type=WM  →  { orderno: "WM24565" }
     *
     * ค่าใน orderrun คือ "เลขล่าสุดที่ใช้ไปแล้ว" → เลขถัดไป = ค่านั้น + 1
     * แล้วข้ามเลขที่มีใบสั่งอยู่จริง (เผื่อค่าใน orderrun ไม่ตรงกับข้อมูลจริง)
     *
     * เป็นแค่ "เลขที่คาดว่าจะได้" ไว้โชว์บนฟอร์ม — ยังไม่เดินเลขจริง
     * การเดินเลขจริงต้องทำตอนบันทึก ซึ่งยังไม่เปิดใช้งานในเฟสนี้
     */
    public function nextOrderno(Request $request)
    {
        $type = strtoupper(trim((string) $request->query('type', '')));
        if (!isset(self::ORDER_TYPES[$type])) {
            return response()->json(['found' => false]);
        }

        $column   = self::ORDER_TYPES[$type];
        $run      = DB::table('orderrun')->first();
        // ประเภทอื่นที่ใช้เลขรันคอลัมน์เดียวกัน (เช่น CM กับ CI ใช้ c ร่วมกัน) — ต้องไม่ชนกัน
        $siblings = array_keys(self::ORDER_TYPES, $column, true);

        $next = $run ? (int) ($run->{$column} ?? 0) : 0;
        do {
            $next++;
            $taken = DB::table('morder')
                ->whereIn('Orderno', array_map(fn ($p) => $p . $next, $siblings))
                ->exists();
        } while ($taken);

        return response()->json([
            'found'   => true,
            'type'    => $type,
            'run'     => $next,
            'orderno' => $type . $next,
        ]);
    }

    /**
     * GET — ค้นข้อมูลสินค้าจากรหัสที่กรอกในตารางรายการ
     *   ?itemno=CP8F247B
     *
     * คืนชื่อสินค้าไว้เติมให้อัตโนมัติ + คำเตือน "ต้อง Match ใหม่"
     * (ฟอร์มเดิมขึ้นแถบแดง "สีที่สั่งซื้อล่าสุดเกิน 3 ปี จะต้อง Match ใหม่")
     */
    public function itemLookup(Request $request)
    {
        $itemno = trim((string) $request->query('itemno', ''));
        if ($itemno === '') {
            return response()->json(['found' => false]);
        }

        // ชื่อสินค้า — ใช้ชื่อที่เคยบันทึกไว้ในใบสั่งก่อนหน้าเป็นหลัก ไม่มีค่อยดูจากตารางราคา
        $prodname = DB::table('suborder')
            ->where('Itemno', $itemno)
            ->whereRaw("TRIM(COALESCE(prodname, '')) <> ''")
            ->orderByDesc('Runno')
            ->value('prodname');

        if (!$prodname) {
            $up = DB::table('uprice')
                ->where('ITEMNO', $itemno)
                ->orderByDesc('DATE')
                ->first(['Label', 'PackRem', 'st_code']);
            $prodname = $up->Label ?? $up->PackRem ?? $up->st_code ?? null;
        }

        // วันที่สั่งซื้อล่าสุดของเบอร์นี้ (ทุกลูกค้า — การ Match เป็นเรื่องของสูตรสี ไม่ผูกกับลูกค้า)
        $lastDate = DB::table('suborder')
            ->join('morder', 'morder.Orderno', '=', 'suborder.Orderno')
            ->where('suborder.Itemno', $itemno)
            ->max('morder.Mdate');

        return response()->json([
            'found'           => (bool) ($prodname || $lastDate),
            'itemno'          => $itemno,
            'prodname'        => $prodname,
            'last_order_date' => $lastDate,
            // เกิน 3 ปี (หรือไม่เคยสั่งเลย) → ต้อง Match ใหม่
            'need_match'      => !$lastDate || Carbon::parse($lastDate)->lt(Carbon::now()->subYears(3)),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  บันทึกใบสั่งซื้อ
    // ─────────────────────────────────────────────────────────────

    /**
     * POST — บันทึกใบสั่งซื้อ (สร้างใหม่ / แก้ไขใบเดิม)
     *
     * insert: จองเลขที่ใบสั่งจาก orderrun ในทรานแซกชันเดียวกับการบันทึก
     *         และปล่อย appv ว่างไว้ → ใบจะไปเข้าคิวอนุมัติราคาเอง (ดู OrderApprovalController)
     * update: ไม่แตะเลขที่ใบสั่ง, วันที่เปิดใบ และสถานะอนุมัติ (appv / appvDT)
     */
    public function save(Request $request)
    {
        $mode = $request->input('mode') === 'update' ? 'update' : 'insert';

        $rules = [
            'Custno' => 'required|string|max:10',
            'items'  => 'required|array|min:1',
        ];
        $rules[$mode === 'insert' ? 'order_type' : 'Orderno'] = 'required|string';

        $validator = Validator::make($request->all(), $rules, [
            'Custno.required'     => 'ต้องระบุรหัสลูกค้า',
            'items.required'      => 'ต้องมีรายการสินค้าอย่างน้อย 1 รายการ',
            'items.min'           => 'ต้องมีรายการสินค้าอย่างน้อย 1 รายการ',
            'order_type.required' => 'ต้องเลือกประเภทใบสั่ง',
            'Orderno.required'    => 'ไม่พบเลขที่ใบสั่งที่จะแก้ไข',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $custno = trim((string) $request->input('Custno'));
        $cust   = DB::table('customer')->where('code', $custno)->first(['code', 'name']);
        if (!$cust) {
            return response()->json(['status' => false, 'message' => 'ไม่พบรหัสลูกค้า ' . $custno], 422);
        }

        // แถวที่ยังไม่กรอกรหัสสินค้า = แถวเปล่า ข้ามไป
        $items = array_values(array_filter(
            (array) $request->input('items', []),
            fn ($row) => trim((string) ($row['Itemno'] ?? '')) !== ''
        ));
        if (empty($items)) {
            return response()->json(['status' => false, 'message' => 'ต้องกรอกรหัสสินค้าอย่างน้อย 1 รายการ'], 422);
        }

        $type = strtoupper(trim((string) $request->input('order_type')));
        if ($mode === 'insert' && !isset(self::ORDER_TYPES[$type])) {
            return response()->json(['status' => false, 'message' => 'ประเภทใบสั่งไม่ถูกต้อง'], 422);
        }

        try {
            $orderno = DB::transaction(function () use ($request, $mode, $type, $custno, $cust, $items) {
                if ($mode === 'insert') {
                    $orderno = $this->allocateOrderno($type);
                    DB::table('morder')->insert(
                        ['Orderno' => $orderno] + $this->headerPayload($request, $custno, $cust)
                    );
                } else {
                    $orderno = trim((string) $request->input('Orderno'));
                    if (!DB::table('morder')->where('Orderno', $orderno)->exists()) {
                        throw new \RuntimeException('ไม่พบใบสั่งซื้อเลขที่ ' . $orderno);
                    }
                    DB::table('morder')->where('Orderno', $orderno)
                        ->update($this->headerPayload($request, $custno, $cust));
                }

                $this->syncItems($orderno, $items);

                return $orderno;
            });
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'บันทึกไม่สำเร็จ: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => $mode === 'insert' ? 'บันทึกใบสั่งซื้อใหม่เรียบร้อย' : 'แก้ไขใบสั่งซื้อเรียบร้อย',
            'orderno' => $orderno,
        ]);
    }

    /** ค่าที่จะเขียนลง morder (ใช้ร่วมทั้ง insert / update) */
    private function headerPayload(Request $request, string $custno, $cust): array
    {
        return [
            // วันที่เปิดใบ — ฟอร์มตั้งค่าปัจจุบันให้ แต่ผู้ใช้แก้ได้ (ว่าง/รูปแบบผิด = ใช้เวลาปัจจุบัน)
            'Mdate'    => $this->parseDateTime($request->input('Mdate')) ?? now(),
            'Company'  => $this->nullIfBlank($request->input('Company')),
            'PO'       => $this->nullIfBlank($request->input('PO')),
            'Custno'   => $custno,
            'Custname' => $cust->name,
            'Emp'      => $this->nullIfBlank($request->input('Emp')),
            'supno'    => $this->nullIfBlank($request->input('supno')),
            'DVpoint'  => $this->nullIfBlank($request->input('DVpoint')),
            'RsvNo'    => $this->nullIfBlank($request->input('RsvNo')),
            'netqty'   => $this->numOrNull($request->input('netqty')),
            'price'    => $this->numOrNull($request->input('price')),
            // กรณีสั่งทำสต๊อก
            'sendend'  => $this->parseDate($request->input('sendend')),
            'SendCust' => (int) $this->numOrNull($request->input('SendCust')),
            'HMStore'  => (float) $this->numOrNull($request->input('HMStore')),
            'sendmth'  => $this->numOrNull($request->input('sendmth')),
            // checkbox — Access เก็บ -1 = ติ๊ก
            'Send'     => $this->flag($request->input('Send')),
            'RP'       => $this->flag($request->input('RP')),
            'Spec'     => $this->flag($request->input('Spec')),
            'Cer'      => $this->flag($request->input('Cer')),
            'MSDS'     => $this->flag($request->input('MSDS')),
        ];
    }

    /**
     * เขียนรายการลง suborder แบบเทียบกับของเดิม:
     * แถวที่มี Runno = แก้ไข · แถวใหม่ = เพิ่ม · แถวที่หายไปจากฟอร์ม = ลบ
     * (คง Runno เดิมไว้ ไม่ลบทั้งใบแล้วใส่ใหม่ เพราะ Runno เป็นเลขอ้างอิงของระบบเดิม)
     */
    private function syncItems(string $orderno, array $items): void
    {
        $keep = [];

        foreach ($items as $row) {
            $data = [
                'Itemno'     => trim((string) ($row['Itemno'] ?? '')),
                'nold'       => $this->nullIfBlank($row['nold'] ?? null),
                'prodname'   => $this->nullIfBlank($row['prodname'] ?? null),
                'Lotno'      => $this->nullIfBlank($row['Lotno'] ?? null),
                'Stock'      => (float) $this->numOrNull($row['Stock'] ?? null),
                'Production' => (float) $this->numOrNull($row['Production'] ?? null),
                'custwant'   => $this->parseDate($row['custwant'] ?? null),
                'senddate'   => $this->parseDate($row['senddate'] ?? null),
                'EndP'       => $this->parseDate($row['EndP'] ?? null),
                'DVDate'     => $this->parseDate($row['DVDate'] ?? null),
                'outno'      => $this->nullIfBlank($row['outno'] ?? null),
                'Remark'     => $this->nullIfBlank($row['Remark'] ?? null),
            ];

            $runno = (int) ($row['Runno'] ?? 0);
            $isOld = $runno > 0 && DB::table('suborder')
                ->where('Orderno', $orderno)->where('Runno', $runno)->exists();

            if ($isOld) {
                DB::table('suborder')->where('Orderno', $orderno)->where('Runno', $runno)->update($data);
                $keep[] = $runno;
            } else {
                $data['Orderno'] = $orderno;
                $keep[] = DB::table('suborder')->insertGetId($data);
            }
        }

        // แถวที่ผู้ใช้ลบออกจากฟอร์ม
        DB::table('suborder')
            ->where('Orderno', $orderno)
            ->whereNotIn('Runno', $keep)
            ->delete();
    }

    /**
     * จองเลขที่ใบสั่งของประเภทที่เลือก — ต้องเรียกภายใน transaction เท่านั้น
     *
     * ล็อกแถว orderrun ไว้ก่อน กันสองคนกดบันทึกพร้อมกันแล้วได้เลขซ้ำ
     * แล้วเดินเลขต่อจากค่าล่าสุด ข้ามเลขที่มีใบสั่งอยู่จริง (เผื่อค่าใน orderrun ไม่ตรงกับข้อมูลจริง)
     */
    private function allocateOrderno(string $type): string
    {
        $column = self::ORDER_TYPES[$type];

        $run = DB::table('orderrun')->lockForUpdate()->first();
        if (!$run) {
            throw new \RuntimeException('ไม่พบข้อมูลเลขรันในตาราง orderrun');
        }

        $siblings = array_keys(self::ORDER_TYPES, $column, true);

        $next = (int) ($run->{$column} ?? 0);
        do {
            $next++;
            $taken = DB::table('morder')
                ->whereIn('Orderno', array_map(fn ($p) => $p . $next, $siblings))
                ->exists();
        } while ($taken);

        DB::table('orderrun')->update([$column => $next]);

        return $type . $next;
    }

    /**
     * แปลงวันที่+เวลาจากช่องกรอก (flatpickr ส่งมาเป็น d/m/Y H:i) → Y-m-d H:i:s
     * รับรูปแบบไม่มีเวลา (d/m/Y) และรูปแบบของ DB (Y-m-d H:i:s) ได้ด้วย
     */
    private function parseDateTime($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})(?:[ T](\d{1,2}):(\d{2})(?::(\d{2}))?)?$#', $value, $m)) {
            return sprintf(
                '%04d-%02d-%02d %02d:%02d:%02d',
                $m[3], $m[2], $m[1], $m[4] ?? 0, $m[5] ?? 0, $m[6] ?? 0
            );
        }

        if (preg_match('#^\d{4}-\d{2}-\d{2}#', $value)) {
            return substr($value . ' 00:00:00', 0, 19);
        }

        return null;
    }

    /** ค่าว่าง → null (กันช่องว่างกลายเป็นสตริงว่างใน DB) */
    private function nullIfBlank($value)
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /** ตัวเลขจากฟอร์ม (ผ่าน stripCommaFields มาแล้ว) — ว่าง/ไม่ใช่ตัวเลข = null */
    private function numOrNull($value)
    {
        $value = str_replace(',', '', trim((string) $value));

        return ($value === '' || !is_numeric($value)) ? null : (float) $value;
    }

    /** checkbox บนฟอร์ม → รูปแบบของ Access (-1 = ติ๊ก, 0 = ไม่ติ๊ก) */
    private function flag($value): int
    {
        return in_array((string) $value, ['1', 'true', 'on', '-1'], true) ? -1 : 0;
    }
}
