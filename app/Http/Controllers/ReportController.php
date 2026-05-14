<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Room;
use App\Models\Contract;
use App\Models\Position;
use App\Models\Branch;
use App\Models\Receipt;
use App\Models\RentBill;
use App\Models\PaymentList;
use App\Models\Occupancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

DB::beginTransaction();

class ReportController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function view_overview(Request $request)
    {
        $data['page_url'] = 'report/view-overview';
        
        return view('report/report-viewOverview', $data);
    }
    public function view_overview_datatable(Request $request)
    {
        $results = Receipt::orderBy('rooms.id','ASC')
                                ->join('rent_bills', 'receipts.ref_rent_bill_id', '=', 'rent_bills.id')
                                ->join('renters', 'receipts.ref_renter_id', '=', 'renters.id')
                                ->join('rooms', 'receipts.ref_room_id', '=', 'rooms.id')
                                ->join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
                                ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
                                ->where('buildings.ref_branch_id', session("branch_id"))
                                ->where('rent_bills.ref_type_id', 1)
                                ->where('rent_bills.ref_status_id', 5)
                                ->distinct('rooms.id')
                                ->select('receipts.*','rent_bills.water_amount','rent_bills.electricity_amount', 'renters.prefix' , DB::raw('CONCAT(renters.name, " ", COALESCE(renters.surname, "")) as renter_name'), 'rooms.name as room_name', 'rooms.id as room_id', 'rooms.rent', 'renters.phone');
        
        if (!empty($request->month) && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
            [$year, $month] = explode('-', $request->month);
            $results = $results->where('rent_bills.year', $year)
                            ->where('rent_bills.month', $month);
        }

        $limit = $request->limit ?? 15;

        $results = $results->paginate($limit);

        $data['list_data'] = $results->appends(request()->query());
        $data['query'] = request()->query();
        $data['query']['limit'] = $limit;

        $data['list_data'] = $results;

        return view('report/report-viewOverview-table', $data);
    }
    // รายงานบิลค่าเช่า borrow
    public function borrow(Request $request)
    {
        $data['page_url'] = 'report/rent-bill';


        return view('report/report-borrow', $data);
    }
    public function borrow_datatable(Request $request)
    {
        // $results = Receipt::orderBy('rooms.id','ASC')
        //                         ->join('rent_bills', 'receipts.ref_rent_bill_id', '=', 'rent_bills.id')
        //                         ->join('renters', 'receipts.ref_renter_id', '=', 'renters.id')
        //                         ->join('rooms', 'receipts.ref_room_id', '=', 'rooms.id')
        //                         ->join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
        //                         ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
        //                         ->where('buildings.ref_branch_id', session("branch_id"))
        //                         ->where('rent_bills.ref_type_id', 1)
        //                         ->whereIn('rent_bills.ref_status_id', [2, 5])
        //                         ->distinct('receipts.id')
        //                         ->select('receipts.*','rent_bills.water_amount','rent_bills.electricity_amount', 'renters.prefix' , DB::raw('CONCAT(renters.name, " ", COALESCE(renters.surname, "")) as renter_name'), 'rooms.name as room_name', 'rooms.id as room_id', 'rooms.rent', 'renters.phone');
        
        // if (!empty($request->month) && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
        //     [$year, $month] = explode('-', $request->month);
        //     $results = $results->where('rent_bills.year', $year)
        //                     ->where('rent_bills.month', $month);
        // }

        // $limit = $request->limit ?? 15;

        // $results = $results->paginate($limit);

        // $data['list_data'] = $results->appends(request()->query());
        // $data['query'] = request()->query();
        // $data['query'] = request()->query();
        $data['query']['limit'] = '$limit';

        // $data['list_data'] = $results;

        return view('report/report-borrow-table', $data);
    }
    // รายงานบิลค่าเช่า return
    public function return(Request $request)
    {
        $data['page_url'] = 'report/rent-bill';


        return view('report/report-return', $data);
    }
    public function return_datatable(Request $request)
    {
        // $results = Receipt::orderBy('rooms.id','ASC')
        //                         ->join('rent_bills', 'receipts.ref_rent_bill_id', '=', 'rent_bills.id')
        //                         ->join('renters', 'receipts.ref_renter_id', '=', 'renters.id')
        //                         ->join('rooms', 'receipts.ref_room_id', '=', 'rooms.id')
        //                         ->join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
        //                         ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
        //                         ->where('buildings.ref_branch_id', session("branch_id"))
        //                         ->where('rent_bills.ref_type_id', 1)
        //                         ->whereIn('rent_bills.ref_status_id', [2, 5])
        //                         ->distinct('receipts.id')
        //                         ->select('receipts.*','rent_bills.water_amount','rent_bills.electricity_amount', 'renters.prefix' , DB::raw('CONCAT(renters.name, " ", COALESCE(renters.surname, "")) as renter_name'), 'rooms.name as room_name', 'rooms.id as room_id', 'rooms.rent', 'renters.phone');
        
        // if (!empty($request->month) && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
        //     [$year, $month] = explode('-', $request->month);
        //     $results = $results->where('rent_bills.year', $year)
        //                     ->where('rent_bills.month', $month);
        // }

        // $limit = $request->limit ?? 15;

        // $results = $results->paginate($limit);

        // $data['list_data'] = $results->appends(request()->query());
        // $data['query'] = request()->query();
        // $data['query'] = request()->query();
        $data['query']['limit'] = '$limit';

        // $data['list_data'] = $results;

        return view('report/report-return-table', $data);
    }
    public function return_summary(Request $request)
    {
        $paid = Receipt::with('payment_list') // เพื่อ preload
                            ->join('rent_bills', 'receipts.ref_rent_bill_id', '=', 'rent_bills.id')
                            ->where('rent_bills.ref_status_id', 5)
                            ->where('rent_bills.ref_type_id', 1)
                            ->select('receipts.*');
        
        if (!empty($request->month) && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
            [$year, $month] = explode('-', $request->month);
            $paid = $paid->where('rent_bills.year', $year)
                            ->where('rent_bills.month', $month);
        }                    
        $paid = $paid->get()->sum(function($receipt) {
                                return $receipt->total_amount;
                            });                    
        // return $overdue = $paid->get()->sum->total_amount;

        ////////////////////////////////////////////////////////////////

        $overdue = RentBill::with('payment_list')
                            ->where('ref_status_id', "!=", 3)
                            ->where('ref_type_id', 1);
                            
        if (!empty($request->month) && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
            [$year, $month] = explode('-', $request->month);
            $overdue = $overdue->where('rent_bills.year', $year)
                            ->where('rent_bills.month', $month);
        }                    
            $overdue = $overdue->get()
                            ->sum(function($rent_bill) {
                                return $rent_bill->total_amount;
                            });

        $data['paid'] = number_format($paid).' บาท';
        $data['overdue'] = number_format($overdue-$paid).' บาท';

        $transfer = $this->summary(session("branch_id"), $month, $year)['transfer'];
        $cash = $this->summary(session("branch_id"), $month, $year)['cash'];
        $cash_wait_for_confirm = $this->summary(session("branch_id"), $month, $year)['cash_wait_for_confirm'];
        
        $total_all = $transfer + $cash + $cash_wait_for_confirm;

        if($total_all > 0){
            $percent_transfer = $total_all > 0 ? ($transfer / $total_all) * 100 : 0;
            $percent_cash    = $total_all > 0 ? (($cash+$cash_wait_for_confirm) / $total_all) * 100 : 0;
            // $percent_cash_wait_for_confirm   = 100 - ($percent_transfer + $percent_cash);
        }

        $data['transfer'] = $transfer;
        $data['cash'] = $cash;
        $data['cash_wait_for_confirm'] = $cash_wait_for_confirm;

        $data['percent_transfer'] = number_format($percent_transfer ?? 0);
        $data['percent_cash'] = number_format($percent_cash ?? 0);
        $data['percent_cash_wait_for_confirm'] = number_format($percent_cash_wait_for_confirm ?? 0);
        
        // foreach ($values as $value) {
        //         $percent = ($value / $total) * 100;
        //         echo number_format($value, 2) . " = " . number_format($percent, 2) . "%<br>";
        //     }

        return view('report/report-header', $data);
    }
    public function move_in(Request $request)
    {
        $data['page_url'] = 'report/move-in';
        $data['sum'] = $this->summary_calculate();
        $data['sum_room'] = Room::whereHas('floor.building', function ($query) {
                                        $query->where('ref_branch_id', session("branch_id"));
                                    })->where('status', 2)->count();

        return view('report/report-moveIn', $data);
    }
    public function move_in_datatable(Request $request)
    {
        $results = Occupancy::orderBy('id','DESC');
        
        if(@$request->month_from){
            
            $monthFrom = $request->month_from;
            $monthTo   = $request->month_to;

            $results = $results->whereBetween('check_in_date', [$monthFrom, $monthTo]);
            // $results = $results->whereBetween('receipts.created_at', [$startDate, $endDate]);
        }

        $limit = $request->limit ?? 15;

        $results = $results->paginate($limit);

        $data['list_data'] = $results->appends(request()->query());
        $data['query'] = request()->query();
        $data['query']['limit'] = $limit;

        $data['list_data'] = $results;

        return view('report/report-moveIn-table', $data);
    }
    public function income(Request $request)
    {
        // return Receipt::whereNot('ref_status_id', 0)->get();
        
        $data['page_url'] = 'report/income';
        $data['sum'] = $this->summary_calculate();
        $data['sum_room'] = Room::whereHas('floor.building', function ($query) {
                                        $query->where('ref_branch_id', session("branch_id"));
                                    })->where('status', 2)->count();

        return view('report/report-income', $data);
    }
    public function income_datatable(Request $request)
    {
        $date = $request->date;
        $data['query'] = request()->query();
        $summary = PaymentList::selectRaw("
                                            -- TOTAL
                                            SUM(price) as total_all,

                                            -- Revenue
                                            SUM(CASE 
                                                WHEN title LIKE '%ค่าเช่าห้อง (Room rate)%' OR title LIKE '%ค่าห้อง%' 
                                                THEN price ELSE 0 END) as total_rent_room,

                                            SUM(CASE 
                                                WHEN title LIKE '%ค่าน้ำ%' 
                                                THEN price ELSE 0 END) as water,

                                            SUM(CASE 
                                                WHEN title LIKE '%ค่าไฟฟ้า%' 
                                                THEN price ELSE 0 END) as electricity,

                                            SUM(CASE 
                                                WHEN title LIKE '%ค่าทำความสะอาด%' 
                                                THEN price ELSE 0 END) as cleaning,

                                            SUM(CASE 
                                                WHEN title NOT LIKE '%ค่าเช่าห้อง (Room rate)%'
                                                AND title NOT LIKE '%ค่าห้อง%'
                                                AND title NOT LIKE '%ค่าน้ำ%'
                                                AND title NOT LIKE '%ค่าไฟฟ้า%'
                                                AND title NOT LIKE '%ค่าทำความสะอาด%'
                                                THEN price ELSE 0 END) as other,

                                            -- Payment Method 🔥
                                            SUM(CASE 
                                                WHEN receipts.payment_channel = 1 THEN price ELSE 0 END) as cash,

                                            SUM(CASE 
                                                WHEN receipts.payment_channel = 2 THEN price ELSE 0 END) as transfer,

                                            SUM(CASE 
                                                WHEN receipts.payment_channel = 3 THEN price ELSE 0 END) as credit,

                                            SUM(CASE 
                                                WHEN receipts.payment_channel > 3 THEN price ELSE 0 END) as pay_other
                                        ")
                                        ->join('receipts', 'receipts.id', '=', 'payment_lists.ref_payment_id')
                                        ->where('payment_lists.document_type', 2)
                                        ->where('payment_lists.discount', 0)
                                        ->where('receipts.ref_status_id', '!=', 0)
                                        ->whereDate('receipts.created_at', $date)
                                        ->first();

        $data += [
                    'total_all'       => $summary->total_all ?? 0,
                    'total_rent_room' => $summary->total_rent_room ?? 0,
                    'water'           => $summary->water ?? 0,
                    'electricity'     => $summary->electricity ?? 0,
                    'cleaning'        => $summary->cleaning ?? 0,
                    'other'           => $summary->other ?? 0,

                    'cash'     => $summary->cash ?? 0,
                    'transfer' => $summary->transfer ?? 0,
                    'credit'   => $summary->credit ?? 0,
                    'pay_other'=> $summary->pay_other ?? 0,
                ];

        return view('report/report-income-table', $data);
    }
    public function tax_invoice(Request $request)
    {
        $data['page_url'] = 'report/tax-invoice';
        $data['sum'] = $this->summary_calculate();
        $data['room'] = Room::orderBy('ref_floor_id','ASC')
                                    ->orderBy('name', 'ASC')
                                    ->whereHas('floor.building', function ($query) {
                                        $query->where('ref_branch_id', session("branch_id"));
                                    })
                                    ->get();
        
        return view('report/report-tax-invoice', $data);
    }
    public function get_invoice_by_room($room_id) // ดึงแค่บิลค่าเช่าเท่านั้น
    {
        $invoice = RentBill::selectRaw("
                                                    *,
                                                    CASE 
                                                        WHEN ref_type_id = 1 THEN 'ค่าห้อง'
                                                        ELSE ''
                                                    END as type_name
                                                ")
                                                ->where('ref_room_id', $room_id)
                                                ->where('ref_type_id', 1)
                                                ->get();
        return $invoice;
    }
    public function tax_invoice_datatable(Request $request)
    {
        $date = $request->date;
        $data['query'] = request()->query();
        $data['invoice'] = RentBill::find($request->ref_rent_bill_id);
        $data['branch'] = Branch::find(session("branch_id"));
        
        return view('report/report-tax-invoice-table', $data);
    }
    public function move_out_datatable(Request $request)
    {
        $results = Receipt::orderBy('id','DESC')
                            ->where('ref_status_id', 5)
                            ->where('ref_type_id', 7)
                            ->where('move_out_type', 1)
                            ->whereHas('room.floor.building', function ($query) {
                                $query->where('ref_branch_id', session("branch_id"));
                            });

        if (!empty($request->month) && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
            [$year, $month] = explode('-', $request->month);
            $results = $results->whereYear('created_at', $year)
                                ->whereMonth('created_at', $month);
        }

        $limit = $request->limit ?? 15;

        $results = $results->paginate($limit);

        $data['list_data'] = $results->appends(request()->query());
        $data['query'] = request()->query();
        $data['query']['limit'] = $limit;

        $data['list_data'] = $results;

        return view('report/report-moveOut-table', $data);
    }
    public function move_out(Request $request)
    {
        $data['page_url'] = 'report/move-out';

        $data['all_room'] = Receipt::orderBy('id','ASC')
                                    ->where('ref_status_id', 5)
                                    ->where('ref_type_id', 7)
                                    ->whereHas('room.floor.building', function ($query) {
                                        $query->where('ref_branch_id', session("branch_id"));
                                    })
                                    ->count();

        $yod_kun = Receipt::orderBy('id','ASC')
                                    ->where('ref_status_id', 5)
                                    ->where('ref_type_id', 7)
                                    ->whereHas('room.floor.building', function ($query) {
                                        $query->where('ref_branch_id', session('branch_id'));
                                    })
                                    ->get()
                                    ->filter(function ($r) {
                                        return $r->total_amount > 0;
                                    });

        $data['yod_kun'] = $yod_kun->sum('total_amount');

        $keb = Receipt::orderBy('id','ASC')
                                    ->where('ref_status_id', 5)
                                    ->where('ref_type_id', 7)
                                    ->whereHas('room.floor.building', function ($query) {
                                        $query->where('ref_branch_id', session('branch_id'));
                                    })
                                    ->get()
                                    ->filter(function ($r) {
                                        return $r->total_amount < 0;
                                    });

        $data['keb'] = $keb->sum('total_amount');

        return view('report/report-moveOut', $data);
    }
    public function badDebt(Request $request)
    {
        $data['count_room'] = RentBill::orderBy('rooms.name', 'ASC')
                                        ->join('room_for_rents', function ($join) {
                                            $join->on('rent_bills.ref_room_for_rent_id', '=', 'room_for_rents.id')
                                                ->where('room_for_rents.status', 0); // ใส่เงื่อนไขเพิ่มตรงนี้
                                        })
                                        ->join('rooms', 'rent_bills.ref_room_id', '=', 'rooms.id')
                                        ->join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
                                        ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
                                        ->where('buildings.ref_branch_id', session("branch_id"))
                                        ->where('rent_bills.ref_type_id', 1)
                                        ->where('rent_bills.ref_status_id', '!=', 5)
                                        ->where('room_for_rents.move_out_type', 2)
                                        ->distinct('rent_bills.id')
                                        ->count('rent_bills.id');
                                        
        $data['totalAmount'] = RentBill::orderBy('rooms.name', 'ASC')
                                        ->join('room_for_rents', function ($join) {
                                            $join->on('rent_bills.ref_room_for_rent_id', '=', 'room_for_rents.id')
                                                ->where('room_for_rents.status', 0); // ใส่เงื่อนไขเพิ่มตรงนี้
                                        })
                                        ->join('rooms', 'rent_bills.ref_room_id', '=', 'rooms.id')
                                        ->join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
                                        ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
                                        ->where('buildings.ref_branch_id', session("branch_id"))
                                        ->where('rent_bills.ref_type_id', 1)
                                        ->where('rent_bills.ref_status_id', '!=', 5)
                                        ->where('room_for_rents.move_out_type', 2)
                                        ->select(
                                            'rent_bills.*',
                                            'room_for_rents.payment_method as payment_method',
                                            'room_for_rents.date_stay as date_stay',
                                            'rooms.name as room_name',
                                            'rooms.rent'
                                        )
                                        ->with('payment_list') // โหลดความสัมพันธ์เพื่อใช้ accessor
                                        ->get()
                                        ->sum(function ($bill) {
                                            return $bill->total_amount; // ใช้ accessor ที่คุณเขียนไว้
                                        });
                                        
        $data['all_room'] = Receipt::orderBy('id','ASC')
                                    ->where('ref_status_id', 5)
                                    ->where('ref_type_id', 7)
                                    ->where('move_out_type', 2)
                                    ->whereHas('room.floor.building', function ($query) {
                                        $query->where('ref_branch_id', session("branch_id"));
                                    })
                                    ->count();

        $yod_kun = Receipt::orderBy('id','ASC')
                                    ->where('ref_status_id', 5)
                                    ->where('ref_type_id', 7)
                                    ->where('move_out_type', 2)
                                    ->whereHas('room.floor.building', function ($query) {
                                        $query->where('ref_branch_id', session('branch_id'));
                                    })
                                    ->get()
                                    ->filter(function ($r) {
                                        return $r->total_amount > 0;
                                    });

        $data['yod_kun'] = $yod_kun->sum('total_amount');

        $keb = Receipt::orderBy('id','ASC')
                                    ->where('ref_status_id', 5)
                                    ->where('ref_type_id', 7)
                                    ->where('move_out_type', 2)
                                    ->whereHas('room.floor.building', function ($query) {
                                        $query->where('ref_branch_id', session('branch_id'));
                                    })
                                    ->get()
                                    ->filter(function ($r) {
                                        return $r->total_amount < 0;
                                    });

        $data['keb'] = $keb->sum('total_amount');


        $data['page_url'] = 'report/bad-debt';
        return view('report/report-badDebt', $data);
    }
    public function badDebt_datatable(Request $request)
    {
        $results = Receipt::orderBy('id','DESC')
                            ->where('ref_status_id', 5)
                            ->where('ref_type_id', 7)
                            ->where('move_out_type', 2)
                            ->whereHas('room.floor.building', function ($query) {
                                $query->where('ref_branch_id', session("branch_id"));
                            });

        if (!empty($request->month) && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
            [$year, $month] = explode('-', $request->month);
            $results = $results->whereYear('created_at', $year)
                                ->whereMonth('created_at', $month);
        }

        $limit = $request->limit ?? 15;

        $results = $results->paginate($limit);

        $data['list_data'] = $results->appends(request()->query());
        $data['query'] = request()->query();
        $data['query']['limit'] = $limit;

        $data['list_data'] = $results;
        
        return view('report/report-badDebt-table', $data);
    }
        public function view_overview_excel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // สร้างหัวตาราง 2 แถว
        $sheet->mergeCells('A1:A2')->setCellValue('A1', 'ห้อง');
        $sheet->mergeCells('B1:H1')->setCellValue('B1', 'บิลค่าเช่าห้อง');
        $sheet->mergeCells('I1:J1')->setCellValue('I1', 'บิลจองห้อง');
        $sheet->mergeCells('K1:L1')->setCellValue('K1', 'บิลเงินประกัน');
        $sheet->mergeCells('M1:N1')->setCellValue('M1', 'บิลย้ายออก');
        $sheet->mergeCells('O1:O2')->setCellValue('O1', 'คืนเงินประกัน');

        $sheet->setCellValue('B2', 'ค่าเช่าห้อง');
        $sheet->setCellValue('C2', 'ค่าน้ำ');
        $sheet->setCellValue('D2', 'ค่าไฟ');
        $sheet->setCellValue('E2', 'ค่าที่จอดรถยนต์');
        $sheet->setCellValue('F2', 'ค่าที่จอดรถมอเตอร์ไซค์');
        $sheet->setCellValue('G2', 'ส่วนกลาง');
        $sheet->setCellValue('H2', 'ค่าไฟเกิน');
        $sheet->setCellValue('I2', 'ค่ามัดจำการจอง');
        $sheet->setCellValue('J2', 'คืนมัดจำการจอง');
        $sheet->setCellValue('K2', 'ค่าประกันห้อง');
        $sheet->setCellValue('L2', 'หักค่ามัดจำจอง');
        $sheet->setCellValue('M2', 'ค่าน้ำ');
        $sheet->setCellValue('N2', 'ค่าไฟ');

        // สไตล์หัวตาราง
        $sheet->getStyle('A1:O2')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
            'font' => ['bold' => true],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(25);
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ดึงข้อมูลจากฐานข้อมูล
        $results = Receipt::orderBy('rooms.id','ASC')
            ->join('rent_bills', 'receipts.ref_rent_bill_id', '=', 'rent_bills.id')
            ->join('renters', 'receipts.ref_renter_id', '=', 'renters.id')
            ->join('rooms', 'receipts.ref_room_id', '=', 'rooms.id')
            ->join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
            ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
            ->where('buildings.ref_branch_id', session("branch_id"))
            ->where('rent_bills.ref_type_id', 1)
            ->where('rent_bills.ref_status_id', 5)
            ->distinct('rooms.id')
            ->select(
                'receipts.*',
                'rent_bills.water_amount',
                'rent_bills.electricity_amount',
                'renters.prefix',
                DB::raw('CONCAT(renters.name, " ", COALESCE(renters.surname, "")) as renter_name'),
                'rooms.name as room_name',
                'rooms.id as room_id',
                'rooms.rent',
                'renters.phone'
            );

        if (!empty($request->month) && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
            [$year, $month] = explode('-', $request->month);
            $results = $results->where('rent_bills.year', $year)
                            ->where('rent_bills.month', $month);
        }

        $results = $results->get();

        // ใส่ข้อมูลเริ่มที่แถว 3
        $rowIndex = 3;
        foreach ($results as $row) {
            $sheet->fromArray([
                $row->room_name,
                $row->invoice->payment_rent_room->price == 0 ? "0" : number_format($row->invoice->payment_rent_room->price),
                $row->water_amount == 0 ? "0" : number_format($row->water_amount),
                $row->electricity_amount == 0 ? "0" : number_format($row->electricity_amount),
                number_format($row->invoice->payment_car_parking_fee->price ?? 0), // ค่าที่จอดรถยนต์
                number_format($row->invoice->payment_motorcycle_parking_fee->price ?? 0), // ค่าที่จอดรถมอเตอร์ไซค์
                "0", // ส่วนกลาง
                "0", // ค่าไฟเกิน
                "0", // ค่ามัดจำการจอง
                "0", // คืนมัดจำ
                "0", // ค่าประกันห้อง
                "0", // หักค่ามัดจำจอง
                "0", // ค่าน้ำย้ายออก
                "0", // ค่าไฟย้ายออก
                "0"  // คืนเงินประกัน
            ], null, 'A' . $rowIndex++);
        }

        // สร้างไฟล์
        $fileName = "รายงานจองรายเดือน_" . date('m-Y', strtotime($request->month)) . ".xlsx";
        $filePath = public_path("upload/export_excel/{$fileName}");

        $writer = new WriterXlsx($spreadsheet);
        $writer->save($filePath);

        return redirect("upload/export_excel/{$fileName}");
    }
    public function badDebt_export_excel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $results = RentBill::orderBy('rooms.name', 'ASC')
                            ->join('room_for_rents', function ($join) {
                                $join->on('rent_bills.ref_room_for_rent_id', '=', 'room_for_rents.id')
                                    ->where('room_for_rents.status', 0); // ใส่เงื่อนไขเพิ่มตรงนี้
                            })
                            ->join('rooms', 'rent_bills.ref_room_id', '=', 'rooms.id')
                            ->join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
                            ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
                            ->where('buildings.ref_branch_id', session("branch_id"))
                            ->where('rent_bills.ref_type_id', 1)
                            ->where('rent_bills.ref_status_id', '!=', 5)
                            ->where('room_for_rents.move_out_type', 2)
                            ->select(
                                'rent_bills.*',
                                'room_for_rents.payment_method as payment_method',
                                'room_for_rents.date_stay as date_stay',
                                'rooms.name as room_name',
                                'rooms.rent'
                            )
                            ->with('payment_water')
                            ->distinct('rent_bills.id');

        if (!empty($request->month) && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
            [$year, $month] = explode('-', $request->month);
            $results = $results->where('rent_bills.year', $year)
                            ->where('rent_bills.month', $month);
        }


        $results = $results->get();
        
        $data = 
        [
            [
                'รายงานหนี้สูญ วันที่ '.date('d/m/Y')
            ],
            [
                "ห้อง",
                "รอบบิล",
                "ค่าเช่าห้อง",
                "ค่าน้ำ",
                "ค่าไฟ",
                "ชำระแล้ว",
                "คืนเงินประกัน",
                "แจ้งหนี้โดย",
                "วันที่",
            ]
        ];
        foreach($results as $key=>$row){
            $data[] = [
                        $key+1,
                        $row->room_name,
                        $row->month.'/'.$row->year,
                        $row->rent,
                        $row->payment_water->price,
                        $row->payment_electricity->price,
                        0,
                        0,
                        date('d/m/Y',strtotime($row->created_at)),
            ];
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data);
        $sheet->getStyle(
            'A1:' . 
            $sheet->getHighestColumn() . 
            $sheet->getHighestRow()
        )->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $writer = new WriterXlsx($spreadsheet);
        $writer->save("upload/export_excel/รายงานหนี้สูญ.xlsx");
        return redirect("upload/export_excel/รายงานหนี้สูญ.xlsx");
    }
    public function monthly_booking(Request $request)
    {
        // $receipt = Receipt:;
        $data['page_url'] = 'report/monthly-booking';
        return view('report/report-monthlyBooking', $data);
    }
    public function monthly_booking_datatable(Request $request)
    {
        $results = Occupancy::orderBy(
                                    DB::table('room_for_rents')
                                        ->select('renters.booking_date')
                                        ->join('renters', 'renters.id', '=', 'room_for_rents.ref_renter_id')
                                        ->whereColumn('room_for_rents.ref_occupancy_id', 'occupancys.id'),
                                    'asc'
                                            );

                        // ตรวจสอบว่า $request->month มีค่าและอยู่ในรูปแบบที่ถูกต้อง
        if (!empty($request->month) && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
            [$year, $month] = explode('-', $request->month);
            $results = $results->whereHas('room_for_rent.renter', function ($query) use ($year, $month) {
                                    $query->whereYear('booking_date', $year)
                                        ->whereMonth('booking_date', $month);
                                });
        }
        $reserveQuery = clone $results;
        $cancelQuery  = clone $results;

        $data['reserve'] = $reserveQuery->count();
        $data['cancel']  = $cancelQuery->where('status', 3)->count();
                        // จัดการเรื่อง limit
        $limit = $request->limit ?? 15;

        $results = $results->paginate($limit);

        $data['list_data'] = $results->appends(request()->query());
        $data['query'] = request()->query();
        $data['query']['limit'] = $limit;

        $data['list_data'] = $results;
        
        return view('report/report-monthlyBooking-table', $data);
    }
    
    public function rent_bill_excel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $results = Receipt::orderBy('rooms.id','ASC')
                                ->join('rent_bills', 'receipts.ref_rent_bill_id', '=', 'rent_bills.id')
                                ->join('renters', 'receipts.ref_renter_id', '=', 'renters.id')
                                ->join('rooms', 'receipts.ref_room_id', '=', 'rooms.id')
                                ->join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
                                ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
                                ->where('buildings.ref_branch_id', session("branch_id"))
                                ->where('rent_bills.ref_type_id', 1)
                                ->whereIn('rent_bills.ref_status_id', [2, 5])
                                ->distinct('receipts.id')
                                ->select('receipts.*','rent_bills.water_amount','rent_bills.electricity_amount', 'renters.prefix' , DB::raw('CONCAT(renters.name, " ", COALESCE(renters.surname, "")) as renter_name'), 'rooms.name as room_name', 'rooms.id as room_id', 'rooms.rent', 'renters.phone');
        
        if (!empty($request->month) && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
            [$year, $month] = explode('-', $request->month);
            $results = $results->where('rent_bills.year', $year)
                            ->where('rent_bills.month', $month);
        }

        $results = $results->get();
        
        $data = 
        [
            [
                "ห้อง",
                "ชื่อผู้เช่า",
                "เลขที่ใบเสร็จ",
                "วันที่รับชำระ",
                "ช่องทาง",
                "รับชำระโดย",
                "ค่าห้องเช่า",
                "ค่าน้ำ",
                "ค่าไฟ",
                "ค่าที่จอด รถยนต์",
                "ค่าที่จอด รถมอเตอร์ไซค์",
                "ส่วนกลาง",
                "ส่วนลด",
                "รวม",
                "สถานะ"
            ]
        ];
        foreach($results as $key => $row){
            $data[] = [
                        $row->room_name,
                        $row->renter_name,
                        $row->receipt_number,
                        date("d/m/Y" , strtotime($row->payment_date)),
                        $row->payment_method == 1? "เงินสด" : "โอนเงิน",
                        $row->user->name,
                        $row->payment_rent_room->price,
                        $row->water_amount,
                        $row->electricity_amount,
                        '-',
                        '-',
                        '-',
                        @$row->payment_discount_total_amount ?? 0,
                        $row->total_amount,
                        "ชำระแล้ว"
            ];
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data);
        $sheet->getStyle(
            'A1:' . 
            $sheet->getHighestColumn() . 
            $sheet->getHighestRow()
        )->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $writer = new WriterXlsx($spreadsheet);
        $writer->save("upload/export_excel/รายงานบิลค่าเช่า".date('m-Y', strtotime($request->month)).".xlsx");
        return redirect("upload/export_excel/รายงานบิลค่าเช่า".date('m-Y', strtotime($request->month)).".xlsx");
    }
    public function move_in_excel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $results = Occupancy::orderBy('id','DESC');
        
        if(@$request->month_from){
            
            $monthFrom = $request->month_from;
            $monthTo   = $request->month_to;

            $results = $results->whereBetween('check_in_date', [$monthFrom, $monthTo]);
            // $results = $results->whereBetween('receipts.created_at', [$startDate, $endDate]);
        }
        $results = $results->get();
        
        $data = 
        [
            [
                "ห้อง",
                "ผู้เช่า",
                "รูปแบบ",
                "วันที่เข้าพัก",
                "วันที่สิ้นสุดสัญญา"
            ]
        ];
        foreach($results as $key => $row){
            $data[] = [
                        $row->room->name,
                        $row->contract->renter->fullName(),
                        $row->booking_type == 1 ? "รายวัน" : "รายเดือน",
                        date('d/m/Y',strtotime($row->contract->contract_date)),
                        date('d/m/Y',strtotime($row->contract->contract_end_date))
            ];
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data);
        $sheet->getStyle(
            'A1:' . 
            $sheet->getHighestColumn() . 
            $sheet->getHighestRow()
        )->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $writer = new WriterXlsx($spreadsheet);
        $writer->save("upload/export_excel/รายงานย้ายเข้า-".date('d-m-Y', strtotime($request->month_from))."-".date('d-m-Y', strtotime($request->month_to)).".xlsx");
        return redirect("upload/export_excel/รายงานย้ายเข้า-".date('d-m-Y', strtotime($request->month_from))."-".date('d-m-Y', strtotime($request->month_to)).".xlsx");
    }
    public function monthly_booking_excel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $results = Receipt::orderBy('rooms.name', 'ASC')
                            ->join('renters', 'receipts.ref_renter_id', '=', 'renters.id')
                            ->join('rooms', 'receipts.ref_room_id', '=', 'rooms.id')
                            ->join('room_for_rents', function($join) {
                                $join->on('receipts.ref_room_id', '=', 'room_for_rents.ref_room_id')
                                    ->whereColumn('receipts.ref_renter_id', '=', 'room_for_rents.ref_renter_id');
                            })
                            ->join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
                            ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
                            ->where('buildings.ref_branch_id', session("branch_id"))
                            ->where('receipts.ref_type_id', 3)
                            ->select(
                                'receipts.*',
                                'renters.prefix',
                                DB::raw('CONCAT(renters.name, " ", COALESCE(renters.surname, "")) as renter_name'),
                                'renters.booking_date',
                                'room_for_rents.payment_method as payment_method',
                                'room_for_rents.date_stay as date_stay',
                                'rooms.name as room_name',
                                'rooms.rent'
                            )
                            ->distinct('receipts.id');

                        // ตรวจสอบว่า $request->month มีค่าและอยู่ในรูปแบบที่ถูกต้อง
        if (!empty($request->month) && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
            [$year, $month] = explode('-', $request->month);
            $results = $results->whereYear('renters.booking_date', $year)
                            ->whereMonth('renters.booking_date', $month);
        }

        $results = $results->get();
        
        $data = 
        [
            [
                "ห้อง",
                "ชื่อผู้จอง",
                "หมายเลขการจอง",
                "วันที่จอง",
                "วันที่เข้าพัก",
                "ช่องทาง",
                "รับจองโดย",
                "ค่ามัดจำ",
                "รวม",
                "สถานะ"
            ]
        ];
        foreach($results as $key => $row){
            $data[] = [
                        $row->room_name,
                        $row->renter_name,
                        $row->receipt_number,
                        date("d/m/Y" , strtotime($row->booking_date)),
                        date("d/m/Y" , strtotime($row->date_stay)),
                        $row->payment_method == 1? "เงินสด" : "โอนเงิน",
                        $row->user->name,
                        $row->amount,
                        $row->amount,
                        $row->จองแล้ว
            ];
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data);
        $sheet->getStyle(
            'A1:' . 
            $sheet->getHighestColumn() . 
            $sheet->getHighestRow()
        )->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $writer = new WriterXlsx($spreadsheet);
        $writer->save("upload/export_excel/รายงานจองรายเดือน".date('m-Y', strtotime($request->month)).".xlsx");
        return redirect("upload/export_excel/รายงานจองรายเดือน".date('m-Y', strtotime($request->month)).".xlsx");
    }
}
