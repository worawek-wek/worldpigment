<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Emp;
use App\Services\AccessControl;
use Illuminate\Support\Facades\Auth;

/**
 * อนุญาตเฉพาะพนักงานหน้างาน (Worker) — ใช้กับหน้า "อัพเดทสถานะงานของตัวเอง" (11/08/2569)
 *
 *  หน้า Worker ไม่มีเมนู → route ไม่ถูกคุมด้วย CheckAccess (pass-through)
 *  จึงต้องมี middleware นี้กันไม่ให้ admin/พนักงานอื่นเข้าถึง
 *  ต้องรันหลัง middleware 'auth' (ซึ่งตั้ง active guard = emp ให้แล้ว)
 *
 *  ออกจากระบบอัตโนมัติเมื่อไม่ได้ใช้งาน (20/10/2569):
 *   - ตัวหลักอยู่ฝั่งจอ (layout/worker) — ไม่ขยับเมาส์/คีย์บอร์ด/แตะจอ IDLE_SECONDS (3 นาที) → logout
 *   - ตรงนี้เป็นด่านสำรองฝั่ง server กรณีปิดแท็บ/เครื่องหลับ (ตัวจับเวลาฝั่งจอไม่ทำงาน)
 *     ถ้า request ล่าสุดห่างเกิน IDLE_SECONDS + SERVER_GRACE_SECONDS → logout
 *     (เผื่อเวลาไว้เพราะฝั่งจอส่ง ping ได้ไม่ถี่กว่าทุก 60 วินาที)
 */
class WorkerOnly
{
    public const IDLE_SECONDS = 180;

    private const SERVER_GRACE_SECONDS = 60;

    private const SESSION_KEY = 'worker_last_activity';

    public function handle($request, Closure $next)
    {
        $account = AccessControl::currentAccount();

        if (!($account instanceof Emp && $account->isWorker())) {
            abort(403, 'เฉพาะพนักงานหน้างาน (Worker) เท่านั้น');
        }

        $last = (int) $request->session()->get(self::SESSION_KEY, 0);
        if ($last > 0 && time() - $last > self::IDLE_SECONDS + self::SERVER_GRACE_SECONDS) {
            Auth::guard('web')->logout();
            Auth::guard('emp')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status'      => 401,
                    'message'     => 'ออกจากระบบอัตโนมัติ เนื่องจากไม่ได้ใช้งานเกิน 3 นาที',
                    'idle_logout' => true,
                ], 401);
            }

            return redirect('login');
        }

        $request->session()->put(self::SESSION_KEY, time());

        return $next($request);
    }
}
