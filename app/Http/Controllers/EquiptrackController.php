<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LeaveController;
use App\Models\User;
use App\Models\Equiptrack;
use App\Models\Category;
use App\Models\Province;
use App\Models\District;
use App\Models\Subdistrict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

DB::beginTransaction();

class EquiptrackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $data['page_url'] = 'equiptrack';
        // $data['equiptrack'] = Equiptrack::get();
        $data['category'] = Category::get();

        return view('equiptrack/index', $data);
    }
    public function datatable(Request $request)
    {
        // $results = Equiptrack::orderBy('id', 'desc');
        
        // $limit = $request['limit'] ?? 15;

        // // $data['prefix'] = [ 1 => 'บริษัท', 2 => 'นาย', 3 => 'นางสาว', 4 => 'นาง'];
        // $results = $results->paginate($limit);
        $data['list_data'] = '$results';

        return view('equiptrack/table', $data);
    }
    
    public function insert(Request $request)
    {
        
        try{
            $insert = new Equiptrack;
            $insert->code  =  $request->code;
            $insert->min_alert_quantity  =  $request->min_alert_quantity;
            $insert->name  =  $request->name;
            $insert->price  =  $request->price;
            $insert->detail  =  $request->detail;
            $insert->ref_branch_id  =  session("branch_id");
            if($request->file('image')){
                // return 123;
                    $request->validate([
                        'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                    ],[
                        'image.required' => 'กรุณาเลือกรูปภาพ',
                        'image.image' => 'ไฟล์ที่เลือกต้องเป็นรูปภาพเท่านั้น',
                        'image.mimes' => 'รูปภาพต้องเป็นไฟล์ประเภท: jpeg, png, jpg, gif หรือ webp',
                        'image.max' => 'ขนาดไฟล์รูปภาพต้องไม่เกิน 2MB',
                    ]);
                $file = $request->file('image');
                $nameExtension = $file->getClientOriginalName();
                $extension = pathinfo($nameExtension, PATHINFO_EXTENSION);
                $img_name = pathinfo($nameExtension, PATHINFO_FILENAME);
                $path = "upload/equiptrack/";
                $image_name = $img_name.rand().'.'.$extension;
                $insert->image = $image_name;
                $file->move($path, $image_name);

            }
            $insert->save();
            
            DB::commit();
            return 1;
        } catch (QueryException $err) {
            DB::rollBack();
        }
        //
    }

    public function edit($id)
    {
        $data['page_url'] = 'equiptrack';
        $data['equiptrack'] = Equiptrack::find($id);
        return view('equiptrack/form', $data);
    }

    public function get_form_import($id)
    {
        $data['page_url'] = 'equiptrack';
        $data['equiptrack'] = Equiptrack::find($id);
        return view('equiptrack/import-form', $data);
    }

    public function get_history($id)
    {
        $data['page_url'] = 'equiptrack';
        $data['equiptrack'] = Equiptrack::find($id);
        return view('equiptrack/history', $data);
    }

    public function update(Request $request, $id)
    {
        
        try{

            $update = Equiptrack::find($id);
            $update->code  =  $request->code;
            $update->min_alert_quantity  =  $request->min_alert_quantity;
            $update->name  =  $request->name;
            $update->price  =  $request->price;
            $update->detail  =  $request->detail;
            if($request->file('image')){
                // return 123;
                    $request->validate([
                        'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                    ],[
                        'image.required' => 'กรุณาเลือกรูปภาพ',
                        'image.image' => 'ไฟล์ที่เลือกต้องเป็นรูปภาพเท่านั้น',
                        'image.mimes' => 'รูปภาพต้องเป็นไฟล์ประเภท: jpeg, png, jpg, gif หรือ webp',
                        'image.max' => 'ขนาดไฟล์รูปภาพต้องไม่เกิน 2MB',
                    ]);
                $file = $request->file('image');
                $nameExtension = $file->getClientOriginalName();
                $extension = pathinfo($nameExtension, PATHINFO_EXTENSION);
                $img_name = pathinfo($nameExtension, PATHINFO_FILENAME);
                $path = "upload/equiptrack/";
                $image_name = $img_name.rand().'.'.$extension;
                $update->image = $image_name;
                $file->move($path, $image_name);
            }
            $update->save();
            
            DB::commit();
            return 1;
        } catch (QueryException $err) {
            DB::rollBack();
        }
    }
    public function update_stock(Request $request, $id)
    {
        
        try{

            $update = Equiptrack::find($id);
            $update->quantity  =  $request->all_qty;
            $update->save();
            
            $insert = new EquiptrackStockHistorys;
            $insert->ref_equipment_id  =  $id;
            $insert->qty  =  $request->new_qty;
            $insert->save();

            DB::commit();
            return 1;
        } catch (QueryException $err) {
            DB::rollBack();
        }
    }
    public function delete($id)
    {
        try{
            Equiptrack::destroy($id);
            
            DB::commit();
            return 1;
        } catch (QueryException $err) {
            DB::rollBack();
        }
        //
    }
    
}
