<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * กำหนดราคา (ราคาสินค้าต่อลูกค้า)
 *
 * ⚠ ตอนนี้เป็น UI อย่างเดียว — ข้อมูลทั้งหมดเป็น mock ยังไม่ต่อ DB
 *   ตารางปลายทางที่ตั้งใจไว้คือ `uprice` (CustNo, st_code, ITEMNO, DATE, PRICE,
 *   REM1, REM2, PackRem, Label, Author, AuthDate, NoAcp, Black)
 *   และ join `customer` (code, name, road, term, cashdisc) สำหรับแถบข้อมูลลูกค้า
 */
class SaleinfoController extends Controller
{
    public function index(Request $request)
    {
        $data['page_url']  = 'saleinfo';
        $data['list_data'] = $this->paginateMock($request);

        return view('saleinfo.index', $data);
    }

    /**
     * ตารางรายการ (โหลดผ่าน AJAX) — คืน HTML partial เหมือนโมดูลอื่น
     */
    public function datatable(Request $request)
    {
        $data['list_data'] = $this->paginateMock($request);

        return view('saleinfo.table', $data);
    }

    /**
     * ข้อมูลจำลอง — โครงคอลัมน์ตาม uprice + ชื่อลูกค้าจาก customer
     * TODO: แทนที่ด้วย query จริงจากตาราง uprice เมื่อสรุปคีย์/เงื่อนไขกับลูกค้าแล้ว
     */
    private function mockRows(): Collection
    {
        $rows = [
            ['41008', 'CP8462B',  'CP8462B',  '2004-03-04', 46.72,  'บริษัท ฮอนด้า เทรดดิ้ง เอเชีย จำกัด', '30 วัน 0 %', 'PP AZ 864 (NH- 361L)', 'เฉพาะเบอร์คิดราคาพิเศษ เช็คราคาก่อนเปิด ORDER', 'ไม่มีแม่สี 1103019'],
            ['41008', 'CP8462G',  'CP8462G',  '2019-06-11', 52.10,  'บริษัท ฮอนด้า เทรดดิ้ง เอเชีย จำกัด', '30 วัน 0 %', 'PP AZ 864 (NH- 120P)', '',                                              'ไม่มีแม่สี 1103019'],
            ['00001', 'VM5A112R', 'VM5A112R', '2025-05-05', 68.00,  'บริษัท วี.เอ็ม. พลาสติก จำกัด',       '60 วัน 0 %', '[PMS 2387U]',           'VM BLUE-R [L-680403-1/97 15RRVR807CL=10PHR]',   ''],
            ['00002', '1108030',  '1108030',  '2007-09-07', 400.00, 'บริษัท สยามคัลเลอร์ จำกัด',           'เงินสด',      '',                      'ราคาพิเศษเฉพาะลอต',                              ''],
            ['30215', 'AB7710W',  'AB7710W',  '2023-11-20', 118.50, 'บริษัท ไทยโพลิเมอร์ อุตสาหกรรม จำกัด', '45 วัน 2 %', 'ABS WHITE 9003',        '',                                              'อนุมัติโดยฝ่ายขาย'],
            ['30215', 'AB7710K',  'AB7710K',  '2024-02-14', 97.25,  'บริษัท ไทยโพลิเมอร์ อุตสาหกรรม จำกัด', '45 วัน 2 %', 'ABS BLACK 9005',        'ปรับราคาตามต้นทุนเม็ดพลาสติก',                   ''],
        ];

        return collect($rows)->map(fn (array $r, int $i) => (object) [
            'id'        => $i + 1,
            'CustNo'    => $r[0],
            'st_code'   => $r[1],
            'ITEMNO'    => $r[2],
            'DATE'      => $r[3],
            'PRICE'     => $r[4],
            'custname'  => $r[5],
            'term'      => $r[6],
            'PackRem'   => $r[7],
            'Label'     => $r[7],
            'REM1'      => $r[8],
            'REM2'      => '',
            'Author'    => $r[9],
            'AuthDate'  => $r[3],
            'NoAcp'     => 0,
        ]);
    }

    /**
     * ตัดหน้า mock ให้ใช้กับ layout/pagination ตัวเดิมได้
     */
    private function paginateMock(Request $request): LengthAwarePaginator
    {
        $rows    = $this->mockRows();
        $perPage = (int) $request->input('limit', 15) ?: 15;
        $page    = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => url('saleinfo/datatable')]
        );
    }
}
