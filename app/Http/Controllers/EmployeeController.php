<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LeaveController;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

DB::beginTransaction();

class EmployeeController extends Controller
{
///////////

    public function index(Request $request)
    {
        $data['position'] = Position::get();
        $data['page_url'] = 'employee';
        return view('employee/index', $data);
    }

///////////

    public function datatable(Request $request)
    {
        $results = Employee::orderBy('id','DESC');
        
        if(@$request->search){
            $results = $results->orWhere(function ($query) use ($request) {
                                    $query->where('name','LIKE','%'.$request->search.'%')
                                        ->orWhere('email','LIKE','%'.$request->search.'%')
                                        ->orWhere('salary','LIKE','%'.$request->search.'%')
                                        ->orWhere('phone','LIKE','%'.$request->search.'%')
                                        ->orWhere('remark','LIKE','%'.$request->search.'%');
                                });
        }

        $limit = 15;
        if(@$request['limit']){
            $limit = $request['limit'];
        }

        $results = $results->with('position')->paginate($limit);
        // return $results->items();
        // dd($results);
        $data['list_data'] = $results->appends(request()->query());
        $data['query'] = request()->query();
        $data['query']['limit'] = $limit;

        $data['list_data'] = $results;

        return view('employee/table', $data);
    }
    
///////////

    public function edit($id)
    {
        $data['position'] = Position::get();
        $data['employee'] = Employee::find($id);
        return view('employee/view', $data);
    }

///////////

    public function store(Request $request)
    {
        $work_start_date = Carbon::createFromFormat('d/m/Y', $request->work_start_date)->format('Y-m-d');
        try{
            $user = new Employee;
            $user->name  =  $request->name;
            $user->salary  =  $request->salary;
            $user->phone  =  $request->phone;
            $user->email  =  $request->email;
            $user->work_start_date  =  $work_start_date;
            $user->ref_position_id  =  $request->ref_position_id;
            $user->remark  =  $request->remark;
            $user->save();
            
            DB::commit();
            return true;
        } catch (QueryException $err) {
            DB::rollBack();
            return false;
        }
        //
    }
///////////

}
