<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Testmain;
use Illuminate\Http\Request;

class ColorMatchingController extends Controller
{
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
        // base query
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
     * Apply shared filter logic ที่ใช้ทั้ง datatable() และ summary()
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
