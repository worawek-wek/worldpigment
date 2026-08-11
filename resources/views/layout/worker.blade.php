<!doctype html>

<html lang="en" class="light-style" dir="ltr"
    data-theme="theme-default" data-assets-path="{{ url('') }}/assets/" data-template="vertical-menu-template">

<head>
    @include('layout.inc_header')
    <title>อัพเดทสถานะงาน | World Pigment</title>
    <style>
        /* Layout สลิมสำหรับพนักงานหน้างาน — ไม่มี sidebar/เมนู */
        .worker-topbar {
            background-color: #0f6e56;
            color: #fff;
        }
        .worker-topbar a { color: #fff; }
        .worker-content { max-width: 1100px; margin: 0 auto; }
    </style>
</head>

<body>
    <div class="worker-topbar py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="ti ti-clipboard-check" style="font-size:1.4rem;"></i>
            <span class="fw-semibold">อัพเดทสถานะงานผลิต</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="small"><i class="ti ti-user me-1"></i>@yield('worker_name')</span>
            <a href="{{ route('logout') }}" class="btn btn-sm btn-light">
                <i class="ti ti-logout me-1"></i>ออกจากระบบ
            </a>
        </div>
    </div>

    <div class="worker-content p-3 p-md-4">
        @yield('content')
    </div>

    @include('layout.inc_js')
    @yield('script')
</body>

</html>
