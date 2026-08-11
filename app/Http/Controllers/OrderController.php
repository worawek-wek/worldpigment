<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Morder;
use App\Models\SubOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'price'     => $this->priceData($order->Custno, $itemno),
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
        return response()->json(
            $this->priceData($request->query('custno'), $request->query('itemno'))
        );
    }

    /**
     * กล่องราคาบนฟอร์ม — ดึงจากหลายตารางตามฟอร์ม Access เดิม
     *   fixed_price  ราคาที่กำหนดไว้      uprice.PRICE           (ราคาตกลงกับลูกค้ารายนี้)
     *   price1/2/3   ราคาช่อง 1/2/3       appvreq (ใบขออนุมัติราคาล่าสุด, แบ่งตามช่วงน้ำหนัก)
     *   appv_price   ราคาอนุมัติ          appvreq.price
     *   valid_to     ยืนราคาถึง           zcustprice.enddate
     *   cost_price   ราคาทุน              pdprice.Price
     */
    private function priceData($custno, $itemno): array
    {
        $custno = trim((string) $custno);
        $itemno = trim((string) $itemno);

        $empty = [
            'fixed_price' => null, 'price1' => null, 'price2' => null, 'price3' => null,
            'appv_price'  => null, 'appv'   => null, 'valid_to' => null,
            'cost_price'  => null, 'remark' => null,
        ];

        if ($custno === '' || $itemno === '') {
            return $empty;
        }

        // ราคาที่ตกลงไว้กับลูกค้ารายนี้
        $uprice = DB::table('uprice')
            ->where('CustNo', $custno)
            ->where('ITEMNO', $itemno)
            ->orderByDesc('DATE')
            ->first(['PRICE', 'REM2']);

        // ใบขออนุมัติราคาล่าสุดของคู่นี้ (price1/2/3 = ราคาตามช่วงน้ำหนักสั่ง)
        $appv = DB::table('appvreq')
            ->where('custno', $custno)
            ->where('itemno', $itemno)
            ->orderByDesc('ReqDate')
            ->first(['price', 'price1', 'price2', 'price3', 'Appv']);

        // วันสิ้นสุดการยืนราคา
        $zcust = DB::table('zcustprice')
            ->where('custno', $custno)
            ->where('colorno', $itemno)
            ->first(['exprice', 'enddate']);

        // ราคาทุนของสินค้า
        $cost = DB::table('pdprice')->where('PdCode', $itemno)->value('Price');

        return [
            'fixed_price' => $uprice->PRICE ?? null,
            'price1'      => $appv->price1 ?? null,
            'price2'      => $appv->price2 ?? null,
            'price3'      => $appv->price3 ?? null,
            'appv_price'  => $appv->price ?? null,
            'appv'        => isset($appv->Appv) ? self::checked($appv->Appv) : null,
            'valid_to'    => $zcust->enddate ?? null,
            'cost_price'  => $cost,
            'remark'      => $uprice->REM2 ?? null,
        ];
    }

    /**
     * GET — เลขที่ใบสั่งถัดไปของประเภทที่เลือก (ปุ่ม "เพิ่มใบสั่งซื้อใหม่")
     *   ?type=WM  →  { orderno: "WM24564" }
     *
     * เลขรันเก็บใน orderrun (1 แถว หลายคอลัมน์ แยกตามประเภท) — เฟสนี้แค่ "อ่านมาโชว์"
     * ยังไม่เดินเลข เพราะการเดินเลขต้องทำพร้อมตอนบันทึกจริง
     */
    public function nextOrderno(Request $request)
    {
        $type = strtoupper(trim((string) $request->query('type', '')));
        if (!isset(self::ORDER_TYPES[$type])) {
            return response()->json(['found' => false]);
        }

        $column = self::ORDER_TYPES[$type];
        $run    = DB::table('orderrun')->first();
        $next   = $run ? (int) ($run->{$column} ?? 0) : 0;

        return response()->json([
            'found'   => true,
            'type'    => $type,
            'run'     => $next,
            'orderno' => $next ? $type . $next : null,
        ]);
    }
}
