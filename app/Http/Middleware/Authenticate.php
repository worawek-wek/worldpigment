<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * อนุญาตเมื่อ login แล้วไม่ว่าจะเป็น admin (web) หรือพนักงาน (emp)
     * ถ้าเป็นพนักงาน ให้ตั้ง guard 'emp' เป็นตัว active เพื่อให้ Auth::user() ทั้งแอปคืนค่า Emp
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('web')->check()) {
            return $next($request);
        }

        if (Auth::guard('emp')->check()) {
            Auth::shouldUse('emp');
            return $next($request);
        }

        return redirect('login');
    }
}
