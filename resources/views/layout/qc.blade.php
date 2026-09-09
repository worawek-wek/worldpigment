<!doctype html>

<html lang="en" class="light-style" dir="ltr"
    data-theme="theme-default" data-assets-path="{{ url('') }}/assets/" data-template="vertical-menu-template">

<head>
    @include('layout.inc_header')
    <title>ตรวจ QC | World Pigment</title>
    <style>
        /* Layout สลิมสำหรับพนักงาน QC — ไม่มี sidebar/เมนู */
        .qc-topbar {
            background-color: #6f42c1;
            color: #fff;
        }
        .qc-topbar a { color: #fff; }
        .qc-content { max-width: 1100px; margin: 0 auto; }
    </style>
</head>

<body>
    <div class="qc-topbar py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="ti ti-checkup-list" style="font-size:1.4rem;"></i>
            <span class="fw-semibold">ตรวจ QC งานผลิต</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="small"><i class="ti ti-user me-1"></i>@yield('qc_name')</span>
            <a href="{{ route('logout') }}" class="btn btn-sm btn-light">
                <i class="ti ti-logout me-1"></i>ออกจากระบบ
            </a>
        </div>
    </div>

    <div class="qc-content p-3 p-md-4">
        @yield('content')
    </div>

    @include('layout.inc_js')
    @yield('script')
</body>

</html>
