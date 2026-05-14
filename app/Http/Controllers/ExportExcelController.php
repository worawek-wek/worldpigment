<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserLeave;
use App\Models\User;
use App\Models\Branch;
use App\Models\Position;
use App\Models\StatusUserLeave;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Carbon\Carbon;

DB::beginTransaction();

class ExportExcelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    { 
        $data['page_url'] = 'export-excel';
        $data['page'] = 'ลา';
        $data['branch'] = Branch::get();
        $data['position'] = Position::get();
        $data['status'] = StatusUserLeave::get();
        
        return view('export-excel/index', $data);
    }

    public function ChangeDateFormat($date)
    { 
        $dateCreate = date_create_from_format('d F, Y', $date);
        $formattedDate = date_format($dateCreate, 'Y-m-d');
        return $formattedDate;
    }
    public function user_detail(Request $request)
    {
        $data[] = [
                "รหัสพนักงาน"
                ,"ชื่อ-นามสกุล"
                ,"ชื่อเล่น"
                ,"Email"
                ,"เบอร์โทรศัพท์"
                ,"วันที่เข้างาน"
                ,"อายุงาน"
                ,"วันเกิด"
                ,"ที่อยู่"
                ,"สาขา"
                ,"ตำแหน่ง"
                ,"สถานะ"
            ];		
        
        $results = User::orderBy('deleted_at','ASC');

        if($request->ref_branch_id != "null"){
            $results = $results->where('ref_branch_id', $request->ref_branch_id);
        }
        if($request->ref_position_id != "null"){
            $results = $results->where('ref_position_id', $request->ref_position_id);
        }
        $results = $results->get();
            // $status_user_leave = [ 0 => "รออนุมัติ", 1 => 'อนุมัติ', 2 => "ไม่อนุมัติ"];
            foreach($results as $row){
                
                $total_working_days = $this->calculateAge($row->work_start_date);

                $status = "พนักงาน";
                if($row->deleted_at != null){
                    $status = "ลาออก";
                }
                
                $data[] = [
                    $row->employee_id
                    ,$row->name
                    ,$row->nickname
                    ,$row->email
                    ,$row->phone
                    ,date("d/m/Y",strtotime($row->work_start_date))
                    ,$total_working_days
                    ,date("d/m/Y",strtotime($row->birthday))
                    ,$row->address
                    ,$row->branch->branch_name
                    ,$row->position->position_name
                    ,$status
                ];
            }
            // return $data;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data);

        $writer = new WriterXlsx($spreadsheet);
        $writer->save('upload/export_excel/ข้อมูลพนักงาน'.date('d-m-Y').'.xlsx');
        return redirect('upload/export_excel/ข้อมูลพนักงาน'.date('d-m-Y').'.xlsx');
    }
    public function calculateAge($date)
    {
        // แปลงวันที่เกิดเป็น Carbon instance
        $date = Carbon::parse($date);

        // วันที่ปัจจุบัน
        $now = Carbon::now();

        // คำนวณความแตกต่าง
        $years = $now->diffInYears($date);
        $months = $now->diffInMonths($date) % 12;
        $days = $now->diffInDays($date) % 30;
        
        $result = "";
        if($years > 0){
            $result .= $years.' ปี ';
        }
        if($months > 0){
            $result .= $months.' เดือน ';
        }
        if($days > 0){
            $result .= $days.' วัน ';
        }
        return $result;
    }
    public function user_leave(Request $request){
        
        $results = UserLeave::orderBy('id');
        // return $request;
        if(@$request->date_from_to){
            $date_from = $this->ChangeDateFormat(explode(' - ', $request->date_from_to)[0]);
            $date_to = $this->ChangeDateFormat(explode(' - ', $request->date_from_to)[1]);
            // $results = $results->whereDate('from_date', '>=', $date_from)->whereDate('from_date', '<=', $date_to);
        }

        if($request->ref_branch_id != "null"){
            $user_id = User::where('ref_branch_id',$request->ref_branch_id)->get("id");
            $user_id = json_decode($user_id, true);
            $user_id = array_column($user_id, 'id');

            $results = $results->whereIn('ref_user_id', $user_id);
        }
        
        if(@$request->ref_status_id != "null" && !empty($request->ref_status_id)){
            $results = $results->where('ref_status_id', $request->ref_status_id);
        }
        
        $data[] = [
            "เลขที่เอกสาร"
            ,"วันที่เอกสาร"
            ,"รหัสพนักงาน"
            ,"สาขา"
            ,"ชื่อพนักงาน"
            ,"ประเภทการลา"
            ,"วันที่ลา"
            ,"เวลาที่ลา"
            ,"จำนวนชั่วโมง(ชม:นาที)"
            ,"สถานะ"
            ,"ผู้อนุมัติ"
            ,"วันที่อนุมัติ"
            ,"สาเหตุการขอลา"
        ];	

        $results = $results->get();

            $status_user_leave = [ 0 => "รออนุมัติ", 1 => 'อนุมัติ', 2 => "ไม่อนุมัติ"];
            foreach($results as $row){
                $count_days = 0;
                // return $row->from_date;
                // if($row->type_time == "day"){
                    $count_days = $this->countDays($row->from_date, $row->to_date);
                // }
                for ($i=0; $i <= $count_days; $i++) { 
                
                    $date = Carbon::create(explode('-', $row->from_date)[0], explode('-', $row->from_date)[1], explode('-', $row->from_date)[2]); // สร้างอินสแตนซ์ของ Carbon สำหรับวันที่ 1 เมษายน 2024
                    $newDate = $date->addDays($i); // บวก กี่วัน
                    $date_leave = $newDate->toDateString(); 

                    $date_approve = "";
                    $approve = "";
                    if($row->ref_status_id == 1){
                        $date_approve = date("d/m/Y",strtotime($row->updated_at));
                        $approve = $row->approve->name;
                    }
                    if(@$request->date_from_to){
                        if($date_leave < $date_from || $date_leave > $date_to){
                            continue;
                        }
                    }

                    $date_leave = date("d/m/Y", strtotime( $date_leave));
                    $time_leave = date("H:i", strtotime($row->user->work_shift->from_time))." - ".date("H:i", strtotime($row->user->work_shift->to_time));

                    // if($row->from_date != $row->to_date){
                    //     $date_leave = date("d/m/Y", strtotime($row->from_date)).' - '.date("d/m/Y", strtotime($row->to_date)); 
                    // }
                        $time_leave = date("H:i", strtotime($row->from_time)).' - '.date("H:i", strtotime($row->to_time));

                        $ex = explode(':',$row->to_time);
                        $w_s_ex = explode(':',$row->from_time);
                        $start = Carbon::createFromTime($w_s_ex[0], $w_s_ex[1], $w_s_ex[2]);  
                        if($row->from_time < '12:00' && $row->to_time > '13:00'){
                            $end = Carbon::createFromTime($ex[0]-1, $ex[1], $ex[2]);
                        }else{
                            $end = Carbon::createFromTime($ex[0], $ex[1], $ex[2]);
                        }

                        // หาความแตกต่างระหว่างเวลา
                        $total_time = $start->diff($end);
                        // แสดงผลลัพธ์
                        $total_time = $total_time->format('%H:%I');
                    // return $total_time;

                    $data[] = [
                        $row->leave_number
                        ,date("d/m/Y",strtotime($row->created_at))
                        ,$row->user->employee_id
                        ,$row->user->branch->branch_name
                        ,$row->user->name
                        ,$row->leave->leave_name
                        ,$date_leave
                        ,$time_leave
                        ,$total_time
                        ,$status_user_leave[$row->ref_status_id]
                        ,$approve
                        ,$date_approve
                        ,$row->detail
                    ];
                }

            }
            // return $data;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data);

        $writer = new WriterXlsx($spreadsheet);
        $writer->save('upload/export_excel/ข้อมูลการลา-'.date('d-m-Y').'.xlsx');
        return redirect('upload/export_excel/ข้อมูลการลา-'.date('d-m-Y').'.xlsx');
    }
    
    public function countDays($start, $end)
    {
        $startDate = Carbon::create(explode('-', $start)[0], explode('-', $start)[1], explode('-', $start)[2]);
        $endDate = Carbon::create(explode('-', $end)[0], explode('-', $end)[1], explode('-', $end)[2]);

        $count_time = $startDate->diffInDays($endDate);

        return $count_time;
    }

    public function datatable(Request $request)
    {
        $results = UserLeave::orderBy('id','DESC');
        // if(@$request->brand_name){
        //     $results = $results->Where('brand_name','LIKE','%'.$request->brand_name.'%');
        // }
        $limit = 15;
        if(@$request['limit']){
            $limit = $request['limit'];
        }

        $results = $results->paginate($limit);

        $data['list_data'] = $results->appends(request()->query());
        $data['query'] = request()->query();
        $data['query']['limit'] = $limit;

        $data['page_url'] = 'user_leave';
        $data['list_data'] = $results;

        return view('user_leave/table', $data);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['title'] = 'Add New UserLeave';
        $data['user_leave'] = UserLeave::get();
        $data['action'] = route('user_leave.store');
        return view('user_leave/form', $data);
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            $user_leave = new UserLeave;
            $user_leave->user_leave_name = $request->user_leave_name;
            $user_leave->save();
            
            DB::commit();
            return redirect('user_leave-page')->with('message', 'Insert UserLeave "'.$request->user_leave_name.'" success');
        } catch (QueryException $err) {
            DB::rollBack();
        }
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['asset'] = asset('/');
        $data['title'] = 'Edit UserLeave';
        $data['page_before'] = 'UserLeave';
        $data['page'] = 'Edit UserLeave';
        $data['user_leave'] = UserLeave::get();
        $data['action'] = url('user_leave/'.$id);
        $data['user_leave'] = UserLeave::find($id);

        return view('user_leave/form', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        try{
            $user_leave = UserLeave::find($id);
            $user_leave->user_leave_name = $request->user_leave_name;
            $user_leave->save();

            DB::commit();
            return redirect('user_leave-page')->with('message', 'Update UserLeave "'.$request->user_leave_name.'" success');
        } catch (QueryException $err) {
            DB::rollBack();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            UserLeave::destroy($id);
            DB::commit();
            return true;
        } catch (QueryException $err) {
            DB::rollBack();
        }
        //
    }
}
