<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportPageController extends Controller
{
    public function index(Request $request)
    {
        // หน้าอยู่ระหว่างการพัฒนา (11/08/2569) — ยังไม่มีข้อมูลรายงาน
        return view('report/index');
    }
}
