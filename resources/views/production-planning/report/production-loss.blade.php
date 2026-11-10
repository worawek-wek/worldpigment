@extends('./layout/main')

{{-- รายงานการผลิตและสูญเสีย (สถิติสูญเสีย) — 10/11/2569
     กรองแผนก + ช่วงวันที่ Inplan · ตาราง client-side DataTables (โหลด HTML ผ่าน AJAX) --}}
@section('content')
    <style>
        #productionLossTable th.col-loss,
        #productionLossTable td.col-loss { background-color: #fff3cd !important; }
        #productionLossTable .loss-neg { color: #198754; }   /* บรรจุได้มากกว่า TP */
        #productionLossTable .loss-high { color: #dc3545; font-weight: 600; }
    </style>

    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h3 class="mb-1"><i class="ti ti-chart-arrows-vertical text-primary"></i> รายงานการผลิตและสูญเสีย</h3>
                        <p class="text-muted mb-0">
                            สูญเสีย = น้ำหนักผลิต (TP) − นน.บรรจุได้ (Weight Produced) · สูญเสีย % = สูญเสีย ÷ น้ำหนักผลิต × 100
                            · แสดงเฉพาะงานที่จบงานแล้ว (End Job) และมีน้ำหนักที่ผลิตได้
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- เงื่อนไขการค้นหา --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center flex-wrap gap-3">
                        <h5 class="mb-0"><i class="ti ti-filter me-1"></i>เงื่อนไขการค้นหา</h5>
                        <div class="flex-grow-1">
                            @include('production-planning.partials.filter-toggle', ['box' => 'plFilterBox'])
                        </div>
                    </div>
                    <div class="collapse" id="plFilterBox">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1 small text-muted">แผนกที่ผลิต</label>
                                <select id="searchDept" class="form-select" data-fl-label="แผนก">
                                    <option value="">-- ทุกแผนก --</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->name }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1 small text-muted">Inplan ตั้งแต่</label>
                                <input id="searchDateFrom" type="text" class="form-control" autocomplete="off"
                                    data-fl-label="Inplan ตั้งแต่" data-fl-default="{{ now()->startOfMonth()->format('Y-m-d') }}"
                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1 small text-muted">ถึง</label>
                                <input id="searchDateTo" type="text" class="form-control" autocomplete="off"
                                    data-fl-label="Inplan ถึง" data-fl-default="{{ now()->endOfMonth()->format('Y-m-d') }}"
                                    value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button id="btn_search" type="button" class="btn btn-primary"><i class="ti ti-search me-1"></i>ค้นหา</button>
                                <button id="btn_clear" type="button" class="btn btn-outline-secondary"><i class="ti ti-x me-1"></i>ล้าง</button>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ผลลัพธ์ --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0"><i class="ti ti-report me-1"></i>ผลลัพธ์รายงาน</h5>
                        <div class="d-flex gap-2">
                            <a id="btn_export_excel" href="#" target="_blank" class="btn btn-success btn-sm"><i class="ti ti-file-spreadsheet me-1"></i>Export Excel</a>
                            <a id="btn_export_pdf" href="#" target="_blank" class="btn btn-danger btn-sm"><i class="ti ti-file-type-pdf me-1"></i>Export PDF</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="reportResult" class="table-responsive"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
@include('production-planning.partials.filter-toggle-js')
<script>
    var URL_PL_TABLE = '{{ route('production.report.production-loss.table') }}';
    var URL_PL_EXCEL = '{{ route('production.report.production-loss.excel') }}';
    var URL_PL_PDF   = '{{ route('production.report.production-loss.pdf') }}';
    var DEF_FROM = '{{ now()->startOfMonth()->format('Y-m-d') }}';
    var DEF_TO   = '{{ now()->endOfMonth()->format('Y-m-d') }}';

    var fpFrom = null, fpTo = null, plTable = null;

    function currentFilters() {
        return {
            dept: $('#searchDept').val(),
            date_from: $('#searchDateFrom').val(),
            date_to: $('#searchDateTo').val()
        };
    }

    function updateExportLinks() {
        var qs = $.param(currentFilters());
        $('#btn_export_excel').attr('href', URL_PL_EXCEL + '?' + qs);
        $('#btn_export_pdf').attr('href', URL_PL_PDF + '?' + qs);
    }

    function initLossTable() {
        if (!$('#productionLossTable').length || !$('#productionLossTable tbody tr').length) {
            return;
        }
        plTable = $('#productionLossTable').DataTable({
            autoWidth: false,
            order: [],
            columnDefs: [{ targets: 0, orderable: false }]
        });
        plTable.on('order.dt search.dt', function () {
            plTable.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();
    }

    function loadReport() {
        if (plTable) { plTable.destroy(); plTable = null; }
        $('#reportResult').html('<div class="text-center text-muted py-5"><div class="spinner-border text-primary"></div><p class="mt-2 mb-0">กำลังโหลดข้อมูล...</p></div>');
        updateExportLinks();
        $.ajax({
            type: 'GET', url: URL_PL_TABLE, data: currentFilters(),
            success: function (html) { $('#reportResult').html(html); initLossTable(); },
            error: function () {
                $('#reportResult').html('<div class="text-center text-danger py-5"><i class="ti ti-alert-circle" style="font-size:2.5rem;"></i><p class="mt-2 mb-0">โหลดข้อมูลไม่สำเร็จ</p></div>');
            }
        });
    }

    $(document).ready(function () {
        var fpOptions = { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', allowInput: true, disableMobile: true };
        fpFrom = flatpickr('#searchDateFrom', fpOptions);
        fpTo   = flatpickr('#searchDateTo', fpOptions);
        loadReport();

        plInitFilterToggle({
            box: '#plFilterBox',
            reset: function () { $('#btn_clear').trigger('click'); }
        });
    });

    $('#btn_search').on('click', loadReport);

    $('#btn_clear').on('click', function () {
        $('#searchDept').val('');
        fpFrom.setDate(DEF_FROM, true);
        fpTo.setDate(DEF_TO, true);
        loadReport();
    });
</script>
@endsection
