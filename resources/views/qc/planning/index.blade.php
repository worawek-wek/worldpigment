@extends('layout.qc')

@section('qc_name', $qc_name)

@section('content')
    {{-- ── ค้นหา ── --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1 small text-muted">ค้นหา (เลขที่ใบเบิก / รหัสสี / รหัสเครื่อง)</label>
                    <input id="searchText" type="text" class="form-control" placeholder="พิมพ์คำค้นหา...">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1 small text-muted">ผล QC</label>
                    <select id="searchQcStatus" class="form-select">
                        <option value="">-- ทั้งหมด --</option>
                        <option value="none">ยังไม่ตรวจ</option>
                        @foreach($qc_statuses as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
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

    {{-- ── ตารางงานที่รอ QC ── --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-checkup-list me-1"></i>งานที่รอตรวจ QC</h5>
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

    {{-- Modal: บันทึกผล QC --}}
    <div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" id="resultModalContent"></div>
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
    var URL_TABLE    = '{{ route('qc.planning.datatable') }}';
    var URL_DETAIL   = '{{ route('qc.planning.detail') }}';
    var URL_RESULT_F = '{{ route('qc.planning.result-form') }}';
    var URL_RESULT_U = '{{ route('qc.planning.result-update') }}';
    var CSRF_TOKEN   = '{{ csrf_token() }}';

    var fpDate = null;

    function currentFilters() {
        return {
            search:    $('#searchText').val(),
            qc_status: $('#searchQcStatus').val(),
            date:      $('#searchDate').val()
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

    // เปิด modal บันทึกผล QC
    function openResult(id) {
        $.ajax({
            type: 'GET', url: URL_RESULT_F, data: { id: id },
            success: function (html) {
                $('#resultModalContent').html(html);
                new bootstrap.Modal(document.getElementById('resultModal')).show();
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

    // บันทึกผล QC (เรียกจากฟอร์มใน modal)
    function submitResult() {
        var id        = $('#qc_planning_id').val();
        var qc_status = $('#qc_status_select').val();
        if (!qc_status) { Swal.fire('กรุณาเลือกผล QC', '', 'warning'); return; }

        $.ajax({
            type: 'POST', url: URL_RESULT_U,
            data: { _token: CSRF_TOKEN, id: id, qc_status: qc_status },
            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById('resultModal')).hide();
                Swal.fire({
                    icon: 'success', title: res.message || 'บันทึกผล QC เรียบร้อย',
                    toast: true, position: 'top-end', timer: 1500, showConfirmButton: false
                });
                loadJobs();
            },
            error: function (xhr) {
                var res = xhr.responseJSON || {};
                var msg = res.message ? res.message : 'บันทึกไม่สำเร็จ';
                // งานถูกปิด/หลุดจากคิว (บล็อกฝั่ง server) → ปิด modal + reload ตาราง
                if (res.job_closed) {
                    var m = bootstrap.Modal.getInstance(document.getElementById('resultModal'));
                    if (m) m.hide();
                    Swal.fire(msg, '', 'warning');
                    loadJobs();
                    return;
                }
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
    $('#searchQcStatus').on('change', loadJobs);
    $('#btn_clear').on('click', function () {
        $('#searchText').val('');
        $('#searchQcStatus').val('');
        if (fpDate) fpDate.clear(); else $('#searchDate').val('');
        loadJobs();
    });
</script>
@endsection
