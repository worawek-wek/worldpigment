<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LeaveController;
use App\Models\User;
use App\Models\Contract;
use App\Models\Occupancy;
use App\Models\Receipt;
use App\Models\Building;
use App\Models\RentBill;
use App\Models\Setting_bill;
use App\Models\RoomHasService;
use App\Models\PaymentList;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Branch;
use App\Models\Renter;
use App\Models\Province;
use App\Models\District;
use App\Models\IncomeExpenses;
use App\Models\Subdistrict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Mpdf\Mpdf;
use Carbon\Carbon;

DB::beginTransaction();

class PDFController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($receipt_id)
    {
        $data['receipt'] = Receipt::find($receipt_id);

        return view('pdf/index', $data);
    }
    
    public function receipt_all($receipt_all_id)
    {
        // File::cleanDirectory(public_path('pdf')); // ลบไฟล์ทั้งหมด แต่คงโฟลเดอร์ไว้

        $receipt_all_id = explode(',', $receipt_all_id);
        $html = '';
        // return $receipt_all_id;
        foreach($receipt_all_id as $key => $id){
            $last = 0;
            if($key === array_key_last($receipt_all_id)){
                $last = 1;
            }
            $html .= $this->receipt($id, $last)->render();
        }
        return $html;
            // return $pdf;
    }
    
    public function receipt_all_download($receipt_all_id)
    {
        // File::cleanDirectory(public_path('pdf')); // ลบไฟล์ทั้งหมด แต่คงโฟลเดอร์ไว้
        $html = $this->receipt_all($receipt_all_id);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            // 👇 ปิด margin ทุกด้าน
            'margin_left'   => 0,
            'margin_right'  => 0,
            'margin_top'    => 0,
            'margin_bottom' => 0,
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        $mpdf->SetFont('sarabun');
        $mpdf->WriteHTML($html);

        $fileName = 'invoice_' . time() . '.pdf';
        $path = public_path('pdf/' . $fileName);

        $mpdf->Output($path, \Mpdf\Output\Destination::FILE);

            return response()->json([
                'message' => 'PDF ถูกสร้างเรียบร้อย',
                'file_url' => asset('pdf/' . $fileName),
                'html' => $html,
                'file_name' => $fileName
            ]);
    }

    
    public function invoice_all($invoice_all_id)
    {
        // File::cleanDirectory(public_path('pdf')); // ลบไฟล์ทั้งหมด แต่คงโฟลเดอร์ไว้

        $invoice_all_id = explode(',', $invoice_all_id);
        $html = '';
        // return $invoice_all_id;
        foreach($invoice_all_id as $key => $id){
            $last = 0;
            if($key === array_key_last($invoice_all_id)){
                $last = 1;
            }
            $html .= $this->invoice($id, $last)->render();
        }
        return $html;
        
        return response()->json([
            'message' => 'PDF ถูกสร้างเรียบร้อย',
            'file_url' => asset('pdf/' . $fileName),
            'html' => $html,
            'file_name' => $fileName
        ]);
        // return $pdf;
    }

    public function receipt($receipt_id, $last = 0)
    {
        $data['setting_bill'] = Setting_bill::first();
        $receipt = Receipt::find($receipt_id);
        $data['receipt'] = $receipt;
        $data['branch'] = Branch::find(session("branch_id"));
        $data['renter'] = Renter::find($receipt->ref_renter_id);
        $data['amount_thai'] = $this->convertToThaiBaht($receipt->total_amount);
        $data['last'] = $last;

        return view('pdf/receipt', $data);
    }
    public function invoice($invoice_id, $last = 0)
    {
        $data['setting_bill'] = Setting_bill::first();
        
        $invoice = RentBill::find($invoice_id);
        
        $receipt_rent_room_deducted = Receipt::where('ref_contract_id', $invoice->ref_contract_id)->where('payment_channel', 7)->where('ref_type_id', 1)->where('paid_on_checkout', 1)->latest()->first(); // ใบเสร็จค่าเช่าห้อง ที่ชำระโดย หักจากเงินประกัน
        $receipt_rent_room_not_deducted = Receipt::where('ref_contract_id', $invoice->ref_contract_id)->where('payment_channel', '!=', 7)->where('ref_type_id', 1)->where('paid_on_checkout', 1)->latest()->first(); // ใบเสร็จค่าเช่าห้อง ที่ชำระปกติ ไม่ หักจากเงินประกัน

        $receipt_move_out_deducted = Receipt::where('ref_contract_id', $invoice->ref_contract_id)->where('payment_channel', 7)->where('ref_type_id', 4)->latest()->first(); // ใบเสร็จย้ายออก ที่ชำระโดย หักจากเงินประกัน
        $receipt_bad_debt_deducted = Receipt::where('ref_contract_id', $invoice->ref_contract_id)->where('ref_type_id', 5)->latest()->first(); // ใบเสร็จ หนี้สูญ ผู้เช่าหนี
        $receipt_move_out_not_deducted = Receipt::where('ref_contract_id', $invoice->ref_contract_id)->where('payment_channel', '!=', 7)->where('ref_type_id', 4)->latest()->first(); // ใบเสร็จย้ายออก ที่ชำระปกติ ไม่ หักจากเงินประกัน
        
        $invoice_contract = RentBill::where('ref_contract_id', $invoice->ref_contract_id)->where('ref_type_id', 6)->latest()->first(); // บิลเงินประกันคืนผู้เช่า

        $data['invoice'] = $invoice;
        $data['receipt_rent_room_deducted'] = $receipt_rent_room_deducted;
        $data['receipt_rent_room_not_deducted'] = $receipt_rent_room_not_deducted;
        $data['receipt_move_out_deducted'] = $receipt_move_out_deducted;
        $data['receipt_bad_debt_deducted'] = $receipt_bad_debt_deducted;
        $data['receipt_move_out_not_deducted'] = $receipt_move_out_not_deducted;
        $data['invoice_contract'] = $invoice_contract;

        $data['branch'] = Branch::find(session("branch_id"));
        $data['renter'] = Renter::find($invoice->room_for_rent->ref_renter_id);
        $data['amount_thai'] = $this->convertToThaiBaht($invoice->total_amount);
        $data['last'] = $last;

        if(in_array($invoice->ref_type_id, [7])){
        // คำนวนยอดสรุปการย้ายออก เริ่ม
            $receipt_rent_room_deducted_total = $receipt_rent_room_deducted->total_amount ?? 0; // ยอดใบเสร็จค่าเช่าห้อง ที่ชำระโดย หักจากเงินประกัน
            $data['receipt_rent_room_deducted_total'] = $receipt_rent_room_deducted_total;

            if($invoice->receipt[0]->move_out_type == 1){
                $receipt_move_out_deducted_total = $receipt_move_out_deducted->total_amount ?? 0; // ยอดใบเสร็จค่าเช่าห้อง ที่ชำระโดย หักจากเงินประกัน
                $data['receipt_move_out_deducted_total'] = $receipt_move_out_deducted_total;
                $cal = $invoice_contract->total_amount-$receipt_move_out_deducted_total-$receipt_rent_room_deducted_total; // บิลเงินประกันคืนผู้เช่า ลบ ยอดใบเสร็จ ที่ชำระโดย หักจากเงินประกัน
                
                $data['cal'] = $cal;
                $data['amount_thai'] = $this->convertToThaiBaht(abs($cal));
        // คำนวนยอดสรุปการย้ายออก จบ
                return view('pdf/move-out', $data);
            }else{
                $receipt_bad_debt_deducted_total = $receipt_bad_debt_deducted->total_amount ?? 0; // ยอดใบเสร็จค่าเช่าห้อง ที่ชำระโดย หักจากเงินประกัน
                $data['receipt_bad_debt_deducted_total'] = $receipt_bad_debt_deducted_total;
                $cal = $invoice_contract->total_amount-$receipt_bad_debt_deducted_total-$receipt_rent_room_deducted_total; // บิลเงินประกันคืนผู้เช่า ลบ ยอดใบเสร็จ ที่ชำระโดย หักจากเงินประกัน
                
                $data['cal'] = $cal;
                $data['amount_thai'] = $this->convertToThaiBaht(abs($cal));
                return view('pdf/bad-debt', $data);
            }
        }
            return view('pdf/invoice', $data);
    }
    public function move_out_not_yet_recorded($invoice_id)
    {
        $data['setting_bill'] = Setting_bill::first();
        
        $invoice = RentBill::find($invoice_id);
        
        $receipt_rent_room_deducted = Receipt::where('ref_contract_id', $invoice->ref_contract_id)->where('payment_channel', 7)->where('ref_type_id', 1)->where('paid_on_checkout', 1)->latest()->first(); // ใบเสร็จค่าเช่าห้อง ที่ชำระโดย หักจากเงินประกัน
        $receipt_rent_room_not_deducted = Receipt::where('ref_contract_id', $invoice->ref_contract_id)->where('payment_channel', '!=', 7)->where('ref_type_id', 1)->where('paid_on_checkout', 1)->latest()->first(); // ใบเสร็จค่าเช่าห้อง ที่ชำระปกติ ไม่ หักจากเงินประกัน

        $receipt_bad_debt_deducted = Receipt::where('ref_contract_id', $invoice->ref_contract_id)->where('payment_channel', 7)->where('ref_type_id', 4)->latest()->first(); // ใบเสร็จย้ายออก ที่ชำระโดย หักจากเงินประกัน
        $receipt_bad_debt_not_deducted = Receipt::where('ref_contract_id', $invoice->ref_contract_id)->where('payment_channel', '!=', 7)->where('ref_type_id', 4)->latest()->first(); // ใบเสร็จย้ายออก ที่ชำระปกติ ไม่ หักจากเงินประกัน
        
        $invoice_contract = RentBill::where('ref_contract_id', $invoice->ref_contract_id)->where('ref_type_id', 6)->latest()->first(); // บิลเงินประกันคืนผู้เช่า

        $data['invoice'] = $invoice;
        $data['receipt_rent_room_deducted'] = $receipt_rent_room_deducted;
        $data['receipt_rent_room_not_deducted'] = $receipt_rent_room_not_deducted;
        $data['receipt_bad_debt_deducted'] = $receipt_bad_debt_deducted;
        $data['receipt_bad_debt_not_deducted'] = $receipt_bad_debt_not_deducted;
        $data['invoice_contract'] = $invoice_contract;

        $data['branch'] = Branch::find(session("branch_id"));
        $data['renter'] = Renter::find($invoice->room_for_rent->ref_renter_id);
        $data['amount_thai'] = $this->convertToThaiBaht($invoice->total_amount);

        // คำนวนยอดสรุปการย้ายออก เริ่ม
            $receipt_rent_room_deducted_total = $receipt_rent_room_deducted->total_amount ?? 0; // ยอดใบเสร็จค่าเช่าห้อง ที่ชำระโดย หักจากเงินประกัน
            $receipt_bad_debt_deducted_total = $receipt_bad_debt_deducted->total_amount ?? 0; // ยอดใบเสร็จย้ายออก ที่ชำระโดย หักจากเงินประกัน
            $data['receipt_rent_room_deducted_total'] = $receipt_rent_room_deducted_total;
            // $data['receipt_bad_debt_deducted_total'] = $receipt_bad_debt_deducted_total;
            $cal = $invoice_contract->total_amount-$receipt_rent_room_deducted_total-$receipt_bad_debt_deducted_total; // บิลเงินประกันคืนผู้เช่า ลบ ยอดใบเสร็จ ที่ชำระโดย หักจากเงินประกัน
            
            $data['cal'] = $cal;
            $data['amount_thai'] = $this->convertToThaiBaht(abs($cal));
        // คำนวนยอดสรุปการย้ายออก จบ
            return view('pdf/move-out-not-yet-recorded', $data);
    }
    public function overdue_invoice($room_id)
    {
        $data['setting_bill'] = Setting_bill::first();
        $invoice = RentBill::where('ref_room_id', $room_id)->whereIn('ref_status_id', [2, 4, 7])->get();
        $data['invoice'] = $invoice;
        $data['branch'] = Branch::find(session("branch_id"));
        $data['renter'] = Renter::find($invoice[0]->room_for_rent->ref_renter_id);
        foreach($invoice as $inv){
            $amount_thai[$inv->id] = $this->convertToThaiBaht($inv->total_amount);
        }
        $data['amount_thai'] = $amount_thai;

        return view('pdf/invoice-overdue', $data);
    }
    public function overdue_all()
    {
        $rooms = Room::with([
                    'rent_bill' => function ($q) {
                        $q->whereIn('ref_status_id', [2, 4, 5, 7])
                        ->where('ref_type_id', 1)
                        ->with('receipt');
                    }
                ])
                ->whereHas('floor.building', function ($q) {
                    $q->where('ref_branch_id', session('branch_id'));
                })
                ->get(); // ต้อง get ก่อน เพราะใช้ accessor

            // 2️⃣ คำนวณยอดค้างต่อห้อง + กรองเฉพาะที่มียอดค้าง
            $rooms = $rooms->map(function ($room) {

                $totalOverdue = 0;

                foreach ($room->rent_bill as $bill) {
                    $billTotal = $bill->total_amount ?? 0;
                    $receiptTotal = $bill->receipt?->sum('total_amount') ?? 0;

                    $totalOverdue += ($billTotal - $receiptTotal);
                }

                $room->total_overdue = $totalOverdue;

                return $room;

            })->filter(function ($room) {
                return $room->total_overdue > 0;
            })->values(); // รี index ใหม่
        $data['list_data'] = $rooms;
        
        return view('pdf/invoice-overdue-all', $data);
    }
    // พิมพ์หลายห้อง
    public function invoice_many($invoice_id)
    {
        
        // $invoice = RentBill::first();
        $data['setting_bill'] = Setting_bill::first();
        $invoice_many = Rentbill::whereHas('room.floor.building', function ($query) {
                                    $query->where('ref_branch_id', session("branch_id"));
                                })
                                ->where('ref_type_id', 1)
                                ->where('ref_status_id', 7)
                                ->with('room_for_rent.renter')->get();
        $data['branch'] = Branch::find(session("branch_id"));
        foreach ($invoice_many as $invoice) {
            $invoice->thai_total_amount = $this->convertToThaiBaht($invoice->total_amount);
        }
        $data['invoice_many'] = $invoice_many;

        // $data['renter'] = Renter::find($invoice->room_for_rent->ref_renter_id);
        // $data['amount_thai'] = $this->convertToThaiBaht($invoice->total_amount);

        return view('pdf/invoice-many', $data);

        $invoice_id = RentBill::with('room_for_rent')->with('payment_list')->get();
        $html_pdf = '';
        $data = [];
        foreach($invoice_id as $key => $id){
            if(!@$id->room_for_rent){
                continue;
            }
            if(!@$id->payment_list || count($id->payment_list) == 0){
                continue;
            }
            
            $data[]['invoice'] = $id;
            $data[]['branch'] = Branch::find(session("branch_id"));
            $data[]['renter'] = Renter::find($id->room_for_rent->ref_renter_id);
            $data[]['amount_thai'] = $this->convertToThaiBaht($id->total_amount);


            // $html_pdf .= $this->invoice($id->id).'<div style="page-break-before: always;"></div>';
            // if($key == 1){
            //     break;
            // }
        }
        // return $data;
        return view('pdf/invoice-many', ['data' => $data])->render();

        return $html_pdf;
    }
    public function invoice_bill($invoice_id)
    {
        $results = RentBill::orderBy('rent_bills.id','DESC')
                                ->join('room_for_rents', 'rent_bills.ref_room_for_rent_id', '=', 'room_for_rents.id')
                                ->join('renters', 'room_for_rents.ref_renter_id', '=', 'renters.id')
                                ->join('rooms', 'room_for_rents.ref_room_id', '=', 'rooms.id')
                                ->join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
                                ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
                                ->where('buildings.ref_branch_id', session("branch_id"))
                                ->where('rent_bills.ref_type_id', 1)
                                ->distinct('rent_bills.id')
                                ->select('rent_bills.*', 'renters.prefix' , DB::raw('CONCAT(renters.name, " ", COALESCE(renters.surname, "")) as renter_name'), 'rooms.name as room_name', 'rooms.rent')
                                ->get();
                                
        $data['list_data'] = $results;

        return view('pdf/invoice-all', $data);
    }
    // พิมพ์ ใบสรุปรายรับรายจ่าย
    public function income_expenses_all(Request $request, $invoice_id)
    {
        $results = IncomeExpenses::where('ref_branch_id', session("branch_id"))->orderBy('id','DESC');
        
        if(@$request->from_month || $request->to_month){
            $from_month = "2000-01";
            if(@$request->from_month){
                $from_month = $request->from_month;
            }
            $results = $results->whereRaw("DATE_FORMAT(date, '%Y-%m') BETWEEN ? AND ?", [$from_month, $request->to_month]);
        }


        $results = $results->get(); 

        $data = $this->summary_calculate();
        
        $thaiMonths = [
            'January' => 'มกราคม',
            'February' => 'กุมภาพันธ์',
            'March' => 'มีนาคม',
            'April' => 'เมษายน',
            'May' => 'พฤษภาคม',
            'June' => 'มิถุนายน',
            'July' => 'กรกฎาคม',
            'August' => 'สิงหาคม',
            'September' => 'กันยายน',
            'October' => 'ตุลาคม',
            'November' => 'พฤศจิกายน',
            'December' => 'ธันวาคม',
        ];

        // ถ้าไม่มีค่า ให้กำหนด default
        $fromMonth = $request->from_month ?? '2025-01';
        $toMonth = $request->to_month ?? date('Y-m'); // default เป็นเดือนปัจจุบัน

        $from = Carbon::createFromFormat('Y-m', $fromMonth);
        $to = Carbon::createFromFormat('Y-m', $toMonth);

        $fromText = $thaiMonths[$from->format('F')] . '/' . $from->format('Y');
        $toText = $thaiMonths[$to->format('F')] . '/' . $to->format('Y');

        $data['month_to'] = "$fromText - $toText";
        $data['name_branch'] = Branch::find(session("branch_id"))->name;
        $data['list_data'] = $results;
        return view('pdf/income-expenses-all', $data);
    }

    public function checkCarPDF($status)
    {
        $data['name_branch'] = Branch::find(session("branch_id"))->name;
        
        $results = Room::whereHas('floor.building', function ($query) {
                            $query->where('ref_branch_id', session("branch_id"));
                        });
        // กำหนดสถานะตาม status
        if ($status == '0') {
            // ผู้เช่าเก่า
            $roomStatus = [0];
            $roomForRentStatus = [0];
        } else {
            // ผู้เช่าปัจจุบัน
            $roomStatus = [2];
            $roomForRentStatus = [1];
            $results = $results->whereIn('status', $roomStatus);
        }
        
        $results = $results->whereHas('room_for_rent_s', function($query) use ($roomForRentStatus) {
                            $query->whereIn('status', $roomForRentStatus);
                        })
                        ->whereHas('room_for_rent_s.renter.vehicles')
                        ->with(['room_for_rent_s' => function($query) use ($roomForRentStatus) {
                            $query->whereIn('status', $roomForRentStatus);
                        }, 'room_for_rent_s.renter.vehicles'])
                        // ->orderBy('id', 'DESC')
                        ->get();
        $data['list_data'] = $results;
        return view('pdf/checkcar', $data);
    }

    public function report_monthly_booking(Request $request)
    {
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

        $data['list_data'] = $results;

        return view('pdf/report-monthlyBooking', $data);
    }
    public function report_view_overview(Request $request)
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

        $results = $results->get();
        $data['list_data'] = $results;

        return view('pdf/report-viewOverview', $data);
    }
    public function report_rent_bill(Request $request)
    {
        // $results = Receipt::orderBy('rooms.id','ASC')
        //                         ->join('rent_bills', 'receipts.ref_rent_bill_id', '=', 'rent_bills.id')
        //                         ->join('renters', 'receipts.ref_renter_id', '=', 'renters.id')
        //                         ->join('rooms', 'receipts.ref_room_id', '=', 'rooms.id')
        //                         ->join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
        //                         ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
        //                         ->where('buildings.ref_branch_id', session("branch_id"))
        //                         ->where('rent_bills.ref_type_id', 1)
        //                         ->where('rent_bills.ref_status_id', 5)
        //                         ->distinct('receipts.id')
        //                         ->select('receipts.*','rent_bills.water_amount','rent_bills.electricity_amount', 'renters.prefix' , DB::raw('CONCAT(renters.name, " ", COALESCE(renters.surname, "")) as renter_name'), 'rooms.name as room_name', 'rooms.id as room_id', 'rooms.rent', 'renters.phone');
        
        // if (!empty($request->month) && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
        //     [$year, $month] = explode('-', $request->month);
        //     $results = $results->where('rent_bills.year', $year)
        //                     ->where('rent_bills.month', $month);
        // }
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
        // dd($results);
        $data['list_data'] = $results->get();

        return view('pdf/report-rentBill', $data);
    }
    public function report_move_in(Request $request)
    {
        $results = Occupancy::orderBy('id','DESC');
        
        if(@$request->month_from){
            
            $monthFrom = $request->month_from;
            $monthTo   = $request->month_to;

            $results = $results->whereBetween('check_in_date', [$monthFrom, $monthTo]);
            // $results = $results->whereBetween('receipts.created_at', [$startDate, $endDate]);
        }

        $results = $results->get();

        $data['list_data'] = $results;

        return view('pdf/report-moveIn', $data);
    }
    public function report_bad_debt(Request $request)
    {
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

                        // ตรวจสอบว่า $request->month มีค่าและอยู่ในรูปแบบที่ถูกต้อง
        if (!empty($request->month) && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
            [$year, $month] = explode('-', $request->month);
            $results = $results->where('rent_bills.year', $year)
                            ->where('rent_bills.month', $month);
        }

        $results = $results->get();

        $data['list_data'] = $results;

        return view('pdf/report-badDebt', $data);
    }
    public function report_income(Request $request)
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
    public function report_tax_invoice(Request $request)
    {
        
        $date = $request->date;
        $data['query'] = request()->query();
        $data['invoice'] = RentBill::find($request->ref_rent_bill_id);
        $data['branch'] = Branch::find(session("branch_id"));
        
        return view('report/report-tax-invoice-table', $data);
    }
    
    // public function receipt()
    // {

    //     $html = view('pdf/receipt')->render();

    //     // $pdf = new \Mpdf\Mpdf();
    //     $pdf = new \Mpdf\Mpdf([
    //         'default_font_size' => 10,
    //         'default_font' => 'sarabun',
    //         'margin_top' => 3,
    //         'margin_left' => 3
    //     ]);
    //     $pdf->autoScriptToLang = true;
    //     $pdf->autoLangToFont = true;
    //     $pdf->WriteHTML($html);
    //     $pdf->Output();
    // }
    private function convertToThaiBaht($number)
    {
        $number = number_format($number, 2, '.', '');
        [$int, $dec] = explode('.', $number);

        $result = $this->readThaiNumber($int) . 'บาท';

        if ($dec == '00') {
            $result .= 'ถ้วน';
        } else {
            $result .= $this->readThaiNumber($dec) . 'สตางค์';
        }

        return $result;
    }
    public function readThaiNumber($number)
    {
        $is_negative = $number < 0;
        $number = abs((int)$number);

        $position_call = ["", "สิบ", "ร้อย", "พัน", "หมื่น", "แสน", "ล้าน"];
        $number_call = ["", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า"];
        $number = (string)$number;

        $result = '';
        $len = strlen($number);

        for ($i = 0; $i < $len; $i++) {
            $num = $number[$i];
            $pos = $len - $i - 1;

            if ($num == 0) continue;

            if ($pos == 0 && $num == 1 && $len > 1) {
                $result .= 'เอ็ด';
            } elseif ($pos == 1 && $num == 2) {
                $result .= 'ยี่' . $position_call[$pos];
            } elseif ($pos == 1 && $num == 1) {
                $result .= $position_call[$pos];
            } else {
                $result .= $number_call[$num] . $position_call[$pos];
            }
        }

        return $is_negative ? 'ลบ' . $result : $result;
    }


}
