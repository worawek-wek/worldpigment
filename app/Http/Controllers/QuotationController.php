<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $data['page_url'] = 'category';
        $data['category'] = Category::get();

        return view('quotation/index', $data);
    }
}
