<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Morder;

class ProductionPlanController extends Controller
{
    public function planning()
    {
        $data['page_url'] = 'asd ';
        // $data['category'] = Category::get();

        return view('production-planning.index', $data);
    }
}
