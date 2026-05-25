<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductionPlanController extends Controller
{
    public function order()
    {
        $data['page_url'] = 'asd ';
        // $data['category'] = Category::get();

        return view('production-planning/index', $data);
    }

    public function planning()
    {
        $data['page_url'] = 'asd ';
        // $data['category'] = Category::get();

        return view('production-planning/index', $data);
    }
}
