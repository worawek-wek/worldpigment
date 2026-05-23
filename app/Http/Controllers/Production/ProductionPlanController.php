<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductionPlanController extends Controller
{
    public function index()
    {
        $data['page_url'] = 'asd ';
        // $data['category'] = Category::get();

        return view('production-planning/index', $data);
    }
}
