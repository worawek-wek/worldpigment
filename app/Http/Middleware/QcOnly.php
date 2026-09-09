<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Emp;
use App\Services\AccessControl;

/**
 * อนุญาตเฉพาะพนักงาน QC — ใช้กับหน้า "ตรวจ QC งานที่ส่ง QC รอผล" (09/09/2569)
 *
 *  หน้า QC ไม่มีเมนู → route ไม่ถูกคุมด้วย CheckAccess (pass-through)
 *  จึงต้องมี middleware นี้กันไม่ให้ admin/พนักงานอื่นเข้าถึง
 *  ต้องรันหลัง middleware 'auth' (ซึ่งตั้ง active guard = emp ให้แล้ว)
 */
class QcOnly
{
    public function handle($request, Closure $next)
    {
        $account = AccessControl::currentAccount();

        if ($account instanceof Emp && $account->isQc()) {
            return $next($request);
        }

        abort(403, 'เฉพาะพนักงาน QC เท่านั้น');
    }
}
