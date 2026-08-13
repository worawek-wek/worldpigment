<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Services\AccessControl;
use App\Models\Product;
use App\Models\Temp;

/**
 * ข้อมูลสินค้า (tb_products) — CRUD — 07/08/2569
 *
 * ยึด pattern เดียวกับ TempController (datatable + modal form + store/edit/delete)
 */
class ProductController extends Controller
{
    public function index()
    {
        return view('product.index');
    }

    public function datatable()
    {
        $products = Product::query()
            ->with('temp')
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('product_code', 'like', "%{$search}%")
                        ->orWhere('resin', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('pack', 'like', "%{$search}%")
                        ->orWhere('batch', 'like', "%{$search}%")
                        ->orWhere('sampling', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc');

        $rownum = 0;

        return DataTables::of($products)
            ->addColumn('rownum', function () use (&$rownum) {
                return ++$rownum;
            })
            ->addColumn('product_code', function ($product) {
                return $product->product_code ?: '-';
            })
            ->addColumn('resin', function ($product) {
                return $product->resin ?: '-';
            })
            ->addColumn('temp_name', function ($product) {
                return $product->temp->Temp1 ?? '-';
            })
            ->addColumn('code', function ($product) {
                return $product->code ?: '-';
            })
            ->addColumn('pack', function ($product) {
                return $product->pack ?: '-';
            })
            ->addColumn('batch', function ($product) {
                return $product->batch ?: '-';
            })
            ->addColumn('sampling', function ($product) {
                return $product->sampling ?: '-';
            })
            ->addColumn('btnaction', function ($product) {
                return '<div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-sm btn-icon btn-warning btn_edit" data-id="'.$product->id.'" title="แก้ไข">
                                <i class="ti ti-pencil ti-sm"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-danger btn_delete" data-id="'.$product->id.'" title="ลบ">
                                <i class="ti ti-trash ti-sm"></i>
                            </button>
                        </div>';
            })
            ->rawColumns(['btnaction'])
            ->make(true);
    }

    public function edit()
    {
        $id = request('id');

        $product = $id ? Product::find($id) : null;

        // ตัวเลือก Temp (เฉพาะที่เปิดใช้งาน) สำหรับ dropdown ในฟอร์ม
        $temps = Temp::select(['id', 'Temp1'])
            ->where('is_active', 'Y')
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $html = view('product.product-form', [
            'product' => $product,
            'temps'   => $temps,
        ])->render();

        return response()->json([
            'status' => 200,
            'data'   => $html,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required|string|max:255',
            'resin'        => 'nullable|string|max:255',
            'temp_id'      => 'nullable|integer|exists:temp,id',
            'code'         => 'nullable|string|max:255',
            'pack'         => 'nullable|string|max:255',
            'batch'        => 'nullable|string|max:255',
            'sampling'     => 'nullable|string|max:255',
        ], [
            'product_code.required' => 'กรุณากรอกรหัสสินค้า',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ]);
        }

        $userId = AccessControl::currentAccount()?->id;

        $data = [
            'product_code' => $request->product_code,
            'resin'        => $request->resin,
            'temp_id'      => $request->temp_id ?: null,
            'code'         => $request->code,
            'pack'         => $request->pack,
            'batch'        => $request->batch,
            'sampling'     => $request->sampling,
            'updated_by'   => $userId,
        ];

        if (!$request->id) {
            $data['created_by'] = $userId;
        }

        Product::updateOrCreate(
            ['id' => $request->id],
            $data
        );

        return response()->json([
            'status'  => 200,
            'message' => $request->id ? 'แก้ไขข้อมูลสำเร็จ' : 'เพิ่มข้อมูลสำเร็จ',
        ]);
    }

    // ลบรายการ
    public function destroy(Request $request)
    {
        $product = Product::find($request->id);

        if (!$product) {
            return response()->json([
                'status'  => 404,
                'message' => 'ไม่พบข้อมูลที่ต้องการลบ',
            ]);
        }

        $product->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'ลบข้อมูลสำเร็จ',
        ]);
    }
}
