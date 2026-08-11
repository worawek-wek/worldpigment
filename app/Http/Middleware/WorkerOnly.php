<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Emp;
use App\Services\AccessControl;

/**
 * อนุญาตเฉพาะพนักงานหน้างาน (Worker) — ใช้กับหน้า "อัพเดทสถานะงานของตัวเอง" (11/08/2569)
 *
 *  หน้า Worker ไม่มีเมนู → route ไม่ถูกคุมด้วย CheckAccess (pass-through)
 *  จึงต้องมี middleware นี้กันไม่ให้ admin/พนักงานอื่นเข้าถึง
 *  ต้องรันหลัง middleware 'auth' (ซึ่งตั้ง active guard = emp ให้แล้ว)
 */
class WorkerOnly
{
    public function handle($request, Closure $next)
    {
        $account = AccessControl::currentAccount();

        if ($account instanceof Emp && $account->isWorker()) {
            return $next($request);
        }

        abort(403, 'เฉพาะพนักงานหน้างาน (Worker) เท่านั้น');
    }
}
