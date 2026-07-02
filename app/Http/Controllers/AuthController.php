<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Show specified view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loginView()
    {
        return view('login.main', [
            'layout' => 'login'
        ]);
    }

    /**
     * Authenticate login user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // ช่อง email รับได้ทั้ง "อีเมล admin (ตาราง users)" หรือ "username พนักงาน (ตาราง emp)"
        $login    = trim((string) $request->input('email'));
        $password = (string) $request->input('password');

        if ($login === '' || $password === '') {
            throw new \Exception('Wrong email or password.');
        }

        // 1) admin: ตาราง users (login ด้วย email)
        if (Auth::guard('web')->attempt(['email' => $login, 'password' => $password])) {
            $request->session()->regenerate();
            return;
        }

        // 2) พนักงาน: ตาราง emp (login ด้วย username, เฉพาะที่เปิดใช้งาน)
        if (Auth::guard('emp')->attempt(['user' => $login, 'password' => $password, 'is_active' => 'Y'])) {
            $request->session()->regenerate();
            return;
        }

        throw new \Exception('Wrong email or password.');
    }

    /**
     * Logout user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::guard('emp')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('login');
    }
}
