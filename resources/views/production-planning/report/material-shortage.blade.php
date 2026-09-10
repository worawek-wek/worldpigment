@extends('./layout/main')

@section('content')
    <style>
        {{-- ตารางเปลี่ยนเป็น DataTables ฝั่ง client (paging/search/sort) แล้ว — 10/09/2569
             จึงไม่ต้องตรึงหัวตาราง/เลื่อนภายในกล่องเองอีก (DataTables จัดหน้าให้) --}}
        /* ไฮไลต์คอลัมน์ให้ตรงกับ PDF — !important เพื่อทับทั้งหัวตารางและ table-hover */
        /* ขาดวัตถุดิบ / ขาด semi — พื้นเหลืองตามฟอร์มต้นฉบับ */
        #materialShortageTable th.col-lack,
        #materialShortageTable td.col-lack {
            background-color: #fff8b3 !important;
            color: #665c00 !important;
        }
        /* Cust Due (กำหนดที่ลูกค้าต้องการ) — พื้นชมพู */
        #materialShortageTable th.col-custdue,
        #materialShortageTable td.col-custdue {
            background-color: #f8d7da !important;
            color: #842029 !important;
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
                                <i class="ti ti-package-off text-primary"></i>
                                รายงานการขาดวัตถุดิบ
                            </h3>
                            <p class="text-muted mb-0">
                                รายการงานผลิตที่ยังไม่ปิดงาน (ยังค้างอยู่ในแผน)
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
                            <div class="col-md-4">
                                <label class="form-label mb-1 small text-muted">แผนก</label>
                                <select id="searchDept" class="form-select">
                                    <option value="">-- ทุกแผนก --</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->name }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- ปุ่ม --}}
                            <div class="col-md-4 d-flex gap-2">
                                <button id="btn_search" type="button" class="btn btn-primary">
                                    <i class="ti ti-search me-1"></i>ค้นหา
                                </button>
                                <button id="btn_clear" type="button" class="btn btn-outline-secondary">
                                    <i class="ti ti-x me-1"></i>ล้าง
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
    var URL_MS_TABLE = '{{ route('production.report.material-shortage.table') }}';
    var URL_MS_EXCEL = '{{ route('production.report.material-shortage.excel') }}';
    var URL_MS_PDF   = '{{ route('production.report.material-shortage.pdf') }}';

    // เก็บเงื่อนไขค้นหาปัจจุบันเป็น object เพื่อใช้ทั้งโหลดตารางและลิงก์ export
    function currentFilters() {
        return {
            dept: $('#searchDept').val()
        };
    }

    // อัปเดต href ปุ่ม export ให้ตรงกับเงื่อนไขค้นหาล่าสุด
    function updateExportLinks() {
        var qs = $.param(currentFilters());
        $('#btn_export_excel').attr('href', URL_MS_EXCEL + '?' + qs);
        $('#btn_export_pdf').attr('href', URL_MS_PDF + '?' + qs);
    }

    // เก็บ instance ของ DataTable ไว้เพื่อ destroy ก่อนโหลดตารางชุดใหม่
    var msTable = null;

    // init DataTables ฝั่ง client บนตารางที่เพิ่งโหลดมา (paging/search/sort เหมือนหน้าอื่น)
    //  - ตารางนี้ประกอบข้อมูลใน PHP (attach lack_semi/lack_pigment + filter เฉพาะงานที่ขาดจริง)
    //    จึงเป็น client-side ไม่ใช่ serverSide Yajra
    //  - คอลัมน์ # ปิด sort แล้ว renumber ตามผลลัพธ์ที่เรียง/ค้นหาจริง (1..N ข้ามหน้า)
    function initMaterialShortageTable() {
        if (!$('#materialShortageTable').length) {
            return; // ไม่มีตาราง (เช่น error) → ไม่ต้อง init
        }
        msTable = $('#materialShortageTable').DataTable({
            autoWidth: false,
            order: [],                 // คงลำดับเริ่มต้นจาก server (สถานะ → เครื่อง → inplan)
            columnDefs: [
                { targets: 0, orderable: false } // คอลัมน์ # (เลขลำดับ)
            ]
        });
        // renumber คอลัมน์ # ให้ 1..N ตามลำดับ+ผลค้นหาที่แสดงจริง (ข้ามหน้า)
        msTable.on('order.dt search.dt', function () {
            msTable.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();
    }

    // โหลดตารางรายงานตามเงื่อนไขค้นหา
    function loadReport() {
        // ทำลาย DataTable เดิมก่อนแทนที่ HTML (กัน reinit ซ้ำบน DOM เดิม)
        if (msTable) {
            msTable.destroy();
            msTable = null;
        }

        $('#reportResult').html(
            '<div class="text-center text-muted py-5">' +
            '<div class="spinner-border text-primary" role="status"></div>' +
            '<p class="mt-2 mb-0">กำลังโหลดข้อมูล...</p></div>'
        );

        updateExportLinks();

        $.ajax({
            type: 'GET',
            url: URL_MS_TABLE,
            data: currentFilters(),
            success: function (html) {
                $('#reportResult').html(html);
                initMaterialShortageTable();
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

    // โหลดครั้งแรก (ทุกแผนก)
    $(document).ready(function () {
        loadReport();
    });

    // ค้นหา → โหลดตารางใหม่ตามเงื่อนไข
    $('#btn_search').on('click', function () {
        loadReport();
    });

    // ล้าง → คืนค่าเริ่มต้น (ทุกแผนก) แล้วโหลดใหม่
    $('#btn_clear').on('click', function () {
        $('#searchDept').val('');
        loadReport();
    });
</script>
@endsection
