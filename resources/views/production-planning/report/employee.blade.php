@extends('./layout/main')

@section('content')
    <style>
        /* หัวกลุ่มพนักงาน: พื้นขาว ตัวอักษรน้ำเงิน + ปิด overlay striped/hover ของ Bootstrap */
        #employeeReportTable tbody.emp-head > tr,
        #employeeReportTable tbody.emp-head > tr > td {
            background-color: #fff !important;
            color: #1d4ed8 !important;
            font-weight: 600;
            box-shadow: none !important;
        }
        /* เส้นคั่นระหว่างพนักงาน = เส้นปกติ (ขอบบนของแถวหัวชื่อพนักงาน) */
        #employeeReportTable tbody.emp-head > tr > td {
            border-top: 1px solid #000 !important;
        }
        /* เส้นแนวนอนภายในบล็อกพนักงาน = บางๆ (จางลง) — คงเส้นแนวตั้งไว้ปกติ */
        #employeeReportTable > tbody:not(.emp-head) > tr > td {
            border-top-color: #eef0f2;
            border-bottom-color: #eef0f2;
        }
        /* กริดเวลา: ตัวอักษรเล็ก จัดกึ่งกลาง อ่านง่ายแบบฟอร์มกระดาษ */
        #employeeReportTable { font-size: 12px; }
        #employeeReportTable th,
        #employeeReportTable td { vertical-align: middle; }
        #employeeReportTable td.slot { text-align: center; }
        #employeeReportTable td.rowlabel {
            white-space: nowrap;
            color: #6c757d;
            width: 110px;
            background-color: #f8f9fa;
        }
        /* แถวเว้นว่างให้เซ็นมือ */
        #employeeReportTable td.signcell {
            background: repeating-linear-gradient(45deg, transparent, transparent 6px, #f1f3f5 6px, #f1f3f5 7px);
        }
    </style>
    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- หัวข้อหน้า --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-users text-primary"></i>
                                รายงานผลิตตามพนักงาน
                            </h3>
                            <p class="text-muted mb-0">
                                แผนและการผลิตจริงรายวัน แยกตามพนักงาน (ตามช่วงเวลาทำงาน)
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── ส่วนที่ 1: การค้นหา ── --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ti ti-filter me-1"></i>เงื่อนไขการค้นหา</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            {{-- แผนก --}}
                            <div class="col-md-3">
                                <label class="form-label mb-1 small text-muted">แผนก</label>
                                <select id="searchDept" class="form-select">
                                    <option value="">-- ทุกแผนก --</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->name }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- พนักงาน (โหลดตามแผนกที่เลือก) --}}
                            <div class="col-md-3">
                                <label class="form-label mb-1 small text-muted">พนักงาน</label>
                                <select id="searchEmp" class="form-select">
                                    <option value="">-- ทุกคน --</option>
                                </select>
                            </div>

                            {{-- วันที่ (ค่าเริ่มต้น = วันนี้) — flatpickr โชว์ d/m/Y แต่ค่าจริงยังเป็น Y-m-d --}}
                            <div class="col-md-3">
                                <label class="form-label mb-1 small text-muted">วันที่</label>
                                <input id="searchDate" type="text" class="form-control flatpickr-date"
                                    autocomplete="off" placeholder="วว/ดด/ปปปป"
                                    value="{{ now()->format('Y-m-d') }}">
                            </div>

                            {{-- ปุ่ม --}}
                            <div class="col-md-3 d-flex gap-2">
                                <button id="btn_search" type="button" class="btn btn-primary w-100">
                                    <i class="ti ti-search me-1"></i>ค้นหา
                                </button>
                                <button id="btn_clear" type="button" class="btn btn-outline-secondary">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── ส่วนที่ 2: ผลลัพธ์รายงาน ── --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0"><i class="ti ti-report me-1"></i>ผลลัพธ์รายงาน</h5>
                        <div class="d-flex gap-2">
                            <a id="btn_export_excel" href="#" target="_blank" class="btn btn-success btn-sm">
                                <i class="ti ti-file-spreadsheet me-1"></i>Export Excel
                            </a>
                            <a id="btn_export_pdf" href="#" target="_blank" class="btn btn-danger btn-sm">
                                <i class="ti ti-file-type-pdf me-1"></i>Export PDF
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="reportResult" class="table-responsive">
                            <div class="text-center text-muted py-5">
                                <i class="ti ti-search-off" style="font-size: 2.5rem;"></i>
                                <p class="mt-2 mb-0">กำลังโหลดข้อมูล...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('script')
<script>
    var URL_EMP_OPTIONS = '{{ route('production.report.employee.options') }}';
    var URL_EMP_TABLE   = '{{ route('production.report.employee.table') }}';
    var URL_EMP_EXCEL   = '{{ route('production.report.employee.excel') }}';
    var URL_EMP_PDF     = '{{ route('production.report.employee.pdf') }}';

    // เก็บเงื่อนไขค้นหาปัจจุบันเป็น object เพื่อใช้ทั้งโหลดตารางและลิงก์ export
    function currentFilters() {
        return {
            dept:  $('#searchDept').val(),
            empno: $('#searchEmp').val(),
            date:  $('#searchDate').val()
        };
    }

    // อัปเดต href ปุ่ม export ให้ตรงกับเงื่อนไขค้นหาล่าสุด
    function updateExportLinks() {
        var qs = $.param(currentFilters());
        $('#btn_export_excel').attr('href', URL_EMP_EXCEL + '?' + qs);
        $('#btn_export_pdf').attr('href', URL_EMP_PDF + '?' + qs);
    }

    // แปลงข้อความให้ปลอดภัยก่อนใส่เป็น option
    function escHtml(v) {
        return $('<div>').text(v == null ? '' : v).html();
    }

    // โหลดตารางรายงาน (time-grid ตามพนักงาน) ตามเงื่อนไขค้นหา
    function loadReport() {
        $('#reportResult').html(
            '<div class="text-center text-muted py-5">' +
            '<div class="spinner-border text-primary" role="status"></div>' +
            '<p class="mt-2 mb-0">กำลังโหลดข้อมูล...</p></div>'
        );

        updateExportLinks();

        $.ajax({
            type: 'GET',
            url: URL_EMP_TABLE,
            data: currentFilters(),
            success: function (html) {
                $('#reportResult').html(html);
            },
            error: function (xhr, error, thrown) {
                console.error('AJAX Error:', error, thrown);
                $('#reportResult').html(
                    '<div class="text-center text-danger py-5">' +
                    '<i class="ti ti-alert-circle" style="font-size:2.5rem;"></i>' +
                    '<p class="mt-2 mb-0">โหลดข้อมูลไม่สำเร็จ</p></div>'
                );
            }
        });
    }

    // instance ของ flatpickr สำหรับช่องวันที่ (ใช้ setDate ตอนกดล้าง)
    var fpDate = null;

    $(document).ready(function () {
        var fpOptions = {
            dateFormat: 'Y-m-d',
            altInput:   true,
            altFormat:  'd/m/Y',
            allowInput: true,
            disableMobile: true
        };
        fpDate = flatpickr('#searchDate', fpOptions);

        loadReport();
    });

    // เปลี่ยนแผนก → โหลดพนักงานของแผนกนั้นมาเติม dropdown
    $('#searchDept').on('change', function () {
        var dept = $(this).val();
        var $emp = $('#searchEmp');

        $emp.html('<option value="">กำลังโหลด...</option>');

        $.ajax({
            type: 'GET',
            url: URL_EMP_OPTIONS,
            data: { dept: dept },
            success: function (res) {
                var options = '<option value="">-- ทุกคน --</option>';
                if (res && res.employees && res.employees.length) {
                    res.employees.forEach(function (e) {
                        options += '<option value="' + escHtml(e.empno) + '">' + escHtml(e.label) + '</option>';
                    });
                }
                $emp.html(options);
            },
            error: function () {
                $emp.html('<option value="">-- โหลดพนักงานไม่สำเร็จ --</option>');
            }
        });
    });

    // ค้นหา → โหลดตารางใหม่ตามเงื่อนไข
    $('#btn_search').on('click', function () {
        loadReport();
    });

    // ล้าง → คืนค่าเริ่มต้น (แผนก/พนักงานว่าง, วันที่ = วันนี้) แล้วโหลดใหม่
    $('#btn_clear').on('click', function () {
        var today = '{{ now()->format('Y-m-d') }}';
        $('#searchDept').val('');
        $('#searchEmp').html('<option value="">-- ทุกคน --</option>').val('');
        if (fpDate) fpDate.setDate(today, false, 'Y-m-d'); else $('#searchDate').val(today);
        loadReport();
    });
</script>
@endsection
