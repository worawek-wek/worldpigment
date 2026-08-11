@extends('layout.worker')

@section('worker_name', $worker_name)

@section('content')
    {{-- ── ค้นหา ── --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label mb-1 small text-muted">ค้นหา (เลขที่ใบเบิก / รหัสสี / รหัสเครื่อง)</label>
                    <input id="searchText" type="text" class="form-control" placeholder="พิมพ์คำค้นหา...">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1 small text-muted">วันที่ (Inplan)</label>
                    <input id="searchDate" type="text" class="form-control flatpickr-date"
                        autocomplete="off" placeholder="วว/ดด/ปปปป">
                </div>
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

    {{-- ── ตารางงานของตัวเอง ── --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-list-check me-1"></i>งานของฉัน</h5>
        </div>
        <div class="card-body">
            <div id="jobResult" class="table-responsive">
                <div class="text-center text-muted py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 mb-0">กำลังโหลดข้อมูล...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: อัพเดทสถานะ --}}
    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" id="statusModalContent"></div>
        </div>
    </div>

    {{-- Modal: ดูรายละเอียด (อ่านอย่างเดียว) --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="detailModalContent"></div>
        </div>
    </div>
@endsection

@section('script')
<script>
    var URL_TABLE    = '{{ route('worker.planning.datatable') }}';
    var URL_DETAIL   = '{{ route('worker.planning.detail') }}';
    var URL_STATUS_F = '{{ route('worker.planning.status-form') }}';
    var URL_STATUS_U = '{{ route('worker.planning.status-update') }}';
    var CSRF_TOKEN   = '{{ csrf_token() }}';

    var fpDate = null;

    function currentFilters() {
        return {
            search: $('#searchText').val(),
            date:   $('#searchDate').val()
        };
    }

    function loadJobs() {
        $('#jobResult').html(
            '<div class="text-center text-muted py-5">' +
            '<div class="spinner-border text-primary" role="status"></div>' +
            '<p class="mt-2 mb-0">กำลังโหลดข้อมูล...</p></div>'
        );
        $.ajax({
            type: 'GET',
            url: URL_TABLE,
            data: currentFilters(),
            success: function (html) { $('#jobResult').html(html); },
            error: function () {
                $('#jobResult').html(
                    '<div class="text-center text-danger py-5">' +
                    '<i class="ti ti-alert-circle" style="font-size:2.5rem;"></i>' +
                    '<p class="mt-2 mb-0">โหลดข้อมูลไม่สำเร็จ</p></div>'
                );
            }
        });
    }

    // เปิด modal อัพเดทสถานะ
    function openStatus(id) {
        $.ajax({
            type: 'GET', url: URL_STATUS_F, data: { id: id },
            success: function (html) {
                $('#statusModalContent').html(html);
                new bootstrap.Modal(document.getElementById('statusModal')).show();
            },
            error: function () { Swal.fire('เปิดฟอร์มไม่สำเร็จ', '', 'error'); }
        });
    }

    // เปิด modal ดูรายละเอียด
    function openDetail(id) {
        $.ajax({
            type: 'GET', url: URL_DETAIL, data: { id: id },
            success: function (html) {
                $('#detailModalContent').html(html);
                new bootstrap.Modal(document.getElementById('detailModal')).show();
            },
            error: function () { Swal.fire('เปิดรายละเอียดไม่สำเร็จ', '', 'error'); }
        });
    }

    // บันทึกสถานะ (เรียกจากฟอร์มใน modal)
    function submitStatus() {
        var id     = $('#status_planning_id').val();
        var status = $('#status_select').val();
        if (!status) { Swal.fire('กรุณาเลือกสถานะ', '', 'warning'); return; }

        $.ajax({
            type: 'POST', url: URL_STATUS_U,
            data: { _token: CSRF_TOKEN, id: id, status: status },
            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
                Swal.fire(res.message || 'อัพเดทสถานะเรียบร้อย', '', 'success');
                loadJobs();
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'บันทึกไม่สำเร็จ';
                Swal.fire(msg, '', 'error');
            }
        });
    }

    $(document).ready(function () {
        fpDate = flatpickr('#searchDate', {
            dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y',
            allowInput: true, disableMobile: true
        });
        loadJobs();
    });

    $('#btn_search').on('click', loadJobs);
    $('#searchText').on('keypress', function (e) { if (e.which === 13) loadJobs(); });
    $('#btn_clear').on('click', function () {
        $('#searchText').val('');
        if (fpDate) fpDate.clear(); else $('#searchDate').val('');
        loadJobs();
    });
</script>
@endsection
