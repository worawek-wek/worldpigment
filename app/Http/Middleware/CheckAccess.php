<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\AccessControl;

class CheckAccess
{
    /**
     * บล็อกการเข้าถึง route ที่บัญชีปัจจุบันไม่มีสิทธิ์ (ตาม role ระดับเมนู)
     * ต้องรันหลัง middleware 'auth' (ซึ่งตั้ง active guard ให้แล้ว)
     */
    public function handle($request, Closure $next)
    {
        $routeName = $request->route() ? $request->route()->getName() : null;

        if (!AccessControl::canAccessRoute($routeName)) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงส่วนนี้');
        }

        return $next($request);
    }
}
