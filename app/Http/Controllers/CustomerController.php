<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        // หน้าอยู่ระหว่างการพัฒนา (11/08/2569) — ยังไม่มีข้อมูลลูกค้า
        return view('customer/index');
    }
}
