<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class LoggedIn
{
    /**
     * ถ้า login แล้ว (admin หรือพนักงาน) ให้เด้งออกจากหน้า login/register
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('web')->check() || Auth::guard('emp')->check()) {
            return redirect('/');
        }

        return $next($request);
    }
}
