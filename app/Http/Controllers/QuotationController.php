<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Qmast;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $data['page_url'] = 'quotation';
        // ชนิดสินค้า (pdtype) สำหรับ filter + form
        $data['pdtypes'] = DB::table('pdtype')->get();
        return view('quotation.index', $data);
    }

    /**
     * GET — รายการใบเสนอราคา (qmast) + ชื่อลูกค้าจริงจากตาราง customer
     * (qmast.CustName ภาษาไทยเสีย → ใช้ customer.name ผ่าน Custid แทน)
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

    private function parseDate(?string $input): ?string
    {
        if (empty($input)) return null;
        try { return Carbon::createFromFormat('d/m/Y', $input)->format('Y-m-d'); } catch (\Exception $e) {}
        try { return Carbon::createFromFormat('Y-m-d', $input)->format('Y-m-d'); } catch (\Exception $e) {}
        try { return Carbon::parse($input)->format('Y-m-d'); } catch (\Exception $e) {}
        return null;
    }
}
