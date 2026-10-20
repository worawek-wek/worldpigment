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

    {{-- ออกจากระบบอัตโนมัติเมื่อไม่ได้ใช้งาน (20/10/2569)
         ไม่ขยับเมาส์/คีย์บอร์ด/แตะจอ/เลื่อนจอ 3 นาที → logout · เตือนก่อน 30 วินาที (ขยับ/แตะ = ใช้งานต่อ)
         ฝั่ง server มีด่านสำรองใน middleware WorkerOnly (กรณีปิดแท็บ/เครื่องหลับ) — ping เพื่อบอก server ว่ายังใช้งานอยู่ --}}
    <script>
        (function () {
            var IDLE_MS     = {{ \App\Http\Middleware\WorkerOnly::IDLE_SECONDS }} * 1000;
            var WARN_MS     = 30 * 1000;   // เตือนก่อนหมดเวลา
            var PING_MS     = 60 * 1000;   // ping server ไม่ถี่กว่านี้
            var URL_LOGOUT  = '{{ route('logout') }}';
            var URL_PING    = '{{ route('worker.ping') }}';

            var lastActive  = Date.now();
            var lastPing    = Date.now();
            var warning     = false;
            var loggingOut  = false;

            function logout() {
                if (loggingOut) return;
                loggingOut = true;
                window.location.href = URL_LOGOUT;
            }

            function onActivity() {
                if (loggingOut) return;
                lastActive = Date.now();
                if (warning) {
                    warning = false;
                    Swal.close();
                }
                if (lastActive - lastPing >= PING_MS) {
                    lastPing = lastActive;
                    $.get(URL_PING);
                }
            }

            function showWarning() {
                warning = true;
                Swal.fire({
                    icon: 'warning',
                    title: 'ไม่ได้ใช้งานนานเกินไป',
                    html: 'ระบบจะออกจากระบบอัตโนมัติใน <b id="idleCountdown">30</b> วินาที',
                    confirmButtonText: 'ใช้งานต่อ',
                    allowOutsideClick: true
                }).then(function () { onActivity(); });
            }

            ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'wheel'].forEach(function (ev) {
                document.addEventListener(ev, onActivity, { passive: true, capture: true });
            });

            setInterval(function () {
                var idle = Date.now() - lastActive;
                if (idle >= IDLE_MS) {
                    logout();
                } else if (idle >= IDLE_MS - WARN_MS) {
                    if (!warning) showWarning();
                    $('#idleCountdown').text(Math.ceil((IDLE_MS - idle) / 1000));
                }
            }, 1000);

            // server ตัด session ไปแล้ว (ด่านสำรอง) → กลับหน้า login
            $(document).ajaxError(function (e, xhr) {
                if (xhr.status === 401 && xhr.responseJSON && xhr.responseJSON.idle_logout) {
                    loggingOut = true;
                    window.location.href = '{{ url('login') }}';
                }
            });
        })();
    </script>

    @yield('script')
</body>

</html>
