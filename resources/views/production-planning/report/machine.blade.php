@extends('./layout/main')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- หัวข้อหน้า --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-tools text-primary"></i>
                                รายงานผลิตตามเครื่องจักร
                            </h3>
                            <p class="text-muted mb-0">
                                สรุปผลการผลิตแยกตามเครื่องจักร
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

                            {{-- เครื่องจักร (โหลดตามแผนกที่เลือก) --}}
                            <div class="col-md-3">
                                <label class="form-label mb-1 small text-muted">เครื่องจักร</label>
                                <select id="searchMachine" class="form-select" disabled>
                                    <option value="">-- เลือกแผนกก่อน --</option>
                                </select>
                            </div>

                            {{-- วันที่เริ่ม (ค่าเริ่มต้น = วันนี้) --}}
                            <div class="col-md-2">
                                <label class="form-label mb-1 small text-muted">วันที่เริ่ม</label>
                                <input id="searchDateStart" type="date" class="form-control"
                                    value="{{ now()->format('Y-m-d') }}">
                            </div>

                            {{-- วันที่ถึง (ค่าเริ่มต้น = วันนี้) --}}
                            <div class="col-md-2">
                                <label class="form-label mb-1 small text-muted">วันที่ถึง</label>
                                <input id="searchDateEnd" type="date" class="form-control"
                                    value="{{ now()->format('Y-m-d') }}">
                            </div>

                            {{-- ปุ่ม --}}
                            <div class="col-md-2 d-flex gap-2">
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
<script src="{{ asset('assets/vendor/libs/sortablejs/sortable.js') }}"></script>
<script>
    var URL_MACHINE_OPTIONS = '{{ route('production.report.machine.options') }}';
    var URL_MACHINE_TABLE   = '{{ route('production.report.machine.table') }}';
    var URL_MACHINE_QUEUE   = '{{ route('production.report.machine.queue') }}';
    var URL_MACHINE_EXCEL   = '{{ route('production.report.machine.excel') }}';
    var URL_MACHINE_PDF     = '{{ route('production.report.machine.pdf') }}';
    var CSRF_TOKEN          = '{{ csrf_token() }}';

    // เก็บ instance ของ SortableJS ไว้ทำลายก่อนสร้างใหม่ทุกครั้งที่โหลดตาราง
    var machineSortable = null;

    // เก็บเงื่อนไขค้นหาปัจจุบันเป็น object เพื่อใช้ทั้งโหลดตารางและลิงก์ export
    function currentFilters() {
        return {
            dept:       $('#searchDept').val(),
            machine_no: $('#searchMachine').val(),
            date_start: $('#searchDateStart').val(),
            date_end:   $('#searchDateEnd').val()
        };
    }

    // อัปเดต href ปุ่ม export ให้ตรงกับเงื่อนไขค้นหาล่าสุด
    function updateExportLinks() {
        var qs = $.param(currentFilters());
        $('#btn_export_excel').attr('href', URL_MACHINE_EXCEL + '?' + qs);
        $('#btn_export_pdf').attr('href', URL_MACHINE_PDF + '?' + qs);
    }

    // แปลงข้อความให้ปลอดภัยก่อนใส่เป็น option
    function escHtml(v) {
        return $('<div>').text(v == null ? '' : v).html();
    }

    // โหลดตารางรายงาน (จัดกลุ่มตามเครื่องจักร) ตามเงื่อนไขค้นหา
    function loadReport() {
        $('#reportResult').html(
            '<div class="text-center text-muted py-5">' +
            '<div class="spinner-border text-primary" role="status"></div>' +
            '<p class="mt-2 mb-0">กำลังโหลดข้อมูล...</p></div>'
        );

        updateExportLinks();

        $.ajax({
            type: 'GET',
            url: URL_MACHINE_TABLE,
            data: currentFilters(),
            success: function (html) {
                $('#reportResult').html(html);
                initQueueSortable();
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

    // ── จัด/แทรกคิว (drag & drop) ─────────────────────────────────────────────
    // แต่ละงานเป็น <tbody class="qjob"> — ลากได้เฉพาะภายในเครื่องจักร+วันเดียวกัน
    function initQueueSortable() {
        var table = document.getElementById('machineReportTable');
        if (!table || typeof Sortable === 'undefined') {
            return;
        }

        if (machineSortable) {
            machineSortable.destroy();
            machineSortable = null;
        }

        machineSortable = Sortable.create(table, {
            draggable: 'tbody.qjob',
            handle: '.qhandle',
            animation: 150,
            // กันลากข้ามเครื่องจักร/ข้ามวัน และห้ามวางคร่อมหัวกลุ่มเครื่อง
            onMove: function (evt) {
                var related = evt.related;
                if (!related || !related.classList.contains('qjob')) {
                    return false;
                }
                return evt.dragged.dataset.machine === related.dataset.machine
                    && evt.dragged.dataset.day === related.dataset.day;
            },
            onEnd: function (evt) {
                // ไม่ได้ขยับจริง — เช่น ลากแล้วปล่อยที่เดิม หรือถูกกันข้ามเครื่อง/ข้ามวันแล้วเด้งกลับ
                // → ไม่บันทึก ไม่รีเฟรช (กัน user เข้าใจผิดว่ามีการบันทึก)
                if (evt.oldIndex === evt.newIndex) {
                    return;
                }

                var moved   = evt.item;
                var machine = moved.dataset.machine;
                var day     = moved.dataset.day;

                // รวบรวม id ทุกงานในบล็อกเดียวกัน (เครื่อง+วัน) ตามลำดับปัจจุบันบนจอ
                var ids = [];
                table.querySelectorAll('tbody.qjob').forEach(function (tb) {
                    if (tb.dataset.machine === machine && tb.dataset.day === day) {
                        ids.push(tb.dataset.planningId);
                    }
                });

                // อัปเดตเลข # ทันทีตามลำดับใหม่ (ไม่รีโหลดตาราง) แล้วค่อยบันทึกเบื้องหลัง
                renumberRows();
                saveQueueOrder(ids);
            }
        });
    }

    // ไล่เลข # ใหม่ตามลำดับแถวบนจอปัจจุบัน (เรียงต่อเนื่องข้ามทุกกลุ่มเครื่องจักร)
    function renumberRows() {
        var n = 0;
        document.querySelectorAll('#machineReportTable tbody.qjob .qnum').forEach(function (cell) {
            cell.textContent = ++n;
        });
    }

    // บันทึกลำดับคิวใหม่แบบเงียบ ๆ เบื้องหลัง — ไม่ toast/ไม่รีเฟรชเมื่อสำเร็จ
    // กรณีล้มเหลวเท่านั้นจึงโหลดตารางใหม่เพื่อคืนลำดับที่ถูกต้องจาก server + แจ้งเตือน
    function saveQueueOrder(ids) {
        if (!ids || ids.length === 0) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: URL_MACHINE_QUEUE,
            data: { _token: CSRF_TOKEN, ids: ids },
            error: function () {
                if (typeof toastr !== 'undefined') {
                    toastr.error('บันทึกลำดับคิวไม่สำเร็จ');
                }
                loadReport(); // คืนสภาพเดิมจากข้อมูลจริง
            }
        });
    }

    // โหลดครั้งแรกด้วยค่าเริ่มต้น (วันนี้)
    $(document).ready(function () {
        loadReport();
    });

    // เปลี่ยนแผนก → โหลดเครื่องจักรของแผนกนั้นมาเติม dropdown เครื่องจักร
    $('#searchDept').on('change', function () {
        var dept = $(this).val();
        var $machine = $('#searchMachine');

        $machine.prop('disabled', true).html('<option value="">-- เลือกแผนกก่อน --</option>');

        if (!dept) {
            return;
        }

        $machine.html('<option value="">กำลังโหลด...</option>');

        $.ajax({
            type: 'GET',
            url: URL_MACHINE_OPTIONS,
            data: { dept: dept },
            success: function (res) {
                var options = '<option value="">-- ทุกเครื่องจักร --</option>';
                if (res && res.machines && res.machines.length) {
                    res.machines.forEach(function (m) {
                        // value = code (machine.MBX) ให้ตรงกับ tb_planning.machine_no
                        options += '<option value="' + escHtml(m.code) + '">' + escHtml(m.label) + '</option>';
                    });
                    $machine.prop('disabled', false);
                } else {
                    options = '<option value="">-- ไม่พบเครื่องจักรในแผนกนี้ --</option>';
                    $machine.prop('disabled', true);
                }
                $machine.html(options);
            },
            error: function () {
                $machine.prop('disabled', true)
                    .html('<option value="">-- โหลดเครื่องจักรไม่สำเร็จ --</option>');
            }
        });
    });

    // ค้นหา → โหลดตารางใหม่ตามเงื่อนไข
    $('#btn_search').on('click', function () {
        loadReport();
    });

    // ล้าง → คืนค่าเริ่มต้น (แผนก/เครื่องจักรว่าง, วันที่ = วันนี้) แล้วโหลดใหม่
    $('#btn_clear').on('click', function () {
        var today = '{{ now()->format('Y-m-d') }}';
        $('#searchDept').val('');
        $('#searchMachine').prop('disabled', true).html('<option value="">-- เลือกแผนกก่อน --</option>');
        $('#searchDateStart').val(today);
        $('#searchDateEnd').val(today);
        loadReport();
    });
</script>
@endsection
