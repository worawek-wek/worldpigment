@extends('layout.worker')

@section('worker_name', $worker_name)

@section('content')
    {{-- ── ค้นหา ── --}}
    <div class="card mb-3">
        <div class="card-body">
            {{-- จัด layout 20/10/2569: คำอธิบายย้ายออกมาใต้แถว — เดิมอยู่ใต้ช่องค้นหาในแถว align-items-end
                 ทำให้ช่องวันที่/ปุ่มถูกดันลงไม่ตรงกับช่องค้นหา · จอแคบ: ช่องค้นหาเต็มแถว วันที่+ปุ่มอยู่แถวถัดไป --}}
            <div class="row g-2 align-items-end">
                <div class="col-12 col-lg-6">
                    <label for="searchText" class="form-label mb-1 small text-muted">ค้นหา (เลขที่ใบเบิก / รหัสสี / รหัสเครื่อง)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input id="searchText" type="text" class="form-control" placeholder="พิมพ์คำค้นหา..." autocomplete="off">
                    </div>
                </div>
                <div class="col-7 col-sm-6 col-lg-3">
                    <label for="searchDate" class="form-label mb-1 small text-muted">วันที่ (Inplan)</label>
                    <input id="searchDate" type="text" class="form-control flatpickr-date"
                        autocomplete="off" placeholder="วว/ดด/ปปปป">
                </div>
                <div class="col-5 col-sm-6 col-lg-3 d-flex gap-2">
                    <button id="btn_search" type="button" class="btn btn-primary flex-grow-1">
                        <i class="ti ti-search me-1"></i>ค้นหา
                    </button>
                    <button id="btn_clear" type="button" class="btn btn-outline-secondary" title="ล้างการค้นหา">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </div>
            <div class="form-text mt-2 mb-0">
                <i class="ti ti-info-circle me-1"></i>ทำงานแทนคนอื่น: กรอกเลขที่ใบเบิกเพื่อหางานในแผนกของคุณ (พิมพ์บางส่วนได้)
            </div>
        </div>
    </div>

    {{-- ── ตารางงานของตัวเอง ── --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-list-check me-1"></i>รายการงาน</h5>
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
        <div class="modal-dialog modal-xl"> {{-- กว้างขึ้นให้การ์ดบรรจุอยู่แถวเดียว (22/10/2569) --}}
            <div class="modal-content" id="statusModalContent"></div>
        </div>
    </div>

    {{-- Modal: ดูรายละเอียด (อ่านอย่างเดียว) --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl"> {{-- กว้างขึ้น (22/10/2569) --}}
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
                initPackingCard();
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

    // ── การ์ดการบรรจุใน modal อัพเดทสถานะ (22/10/2569) ──
    // แสดงเมื่อสถานะที่เลือกมี is_packing='Y' (data-packing ของ option) · ค่าเดิมโหลดมากับฟอร์มจาก tb_planning_packing
    var WPK_DATETIME_OPTIONS = {
        enableTime: true, time_24hr: true,
        dateFormat: 'Y-m-d H:i', altInput: true, altFormat: 'd/m/Y H:i',
        allowInput: true, disableMobile: true, static: true // static = ปฏิทินอยู่ใน modal ไม่จมใต้ modal
    };

    function initPackingRow($row) {
        $row.find('.wpk-datetime').each(function () {
            if (!this._flatpickr) flatpickr(this, WPK_DATETIME_OPTIONS);
        });
    }

    function selectedIsPacking() {
        return $('#status_select option:selected').data('packing') === 'Y';
    }

    function syncPackingCard() {
        $('#worker_packing_card').toggleClass('d-none', !selectedIsPacking());
    }

    function initPackingCard() {
        $('#worker_packing_rows .worker-packing-row').each(function () { initPackingRow($(this)); });
        syncPackingCard();
    }

    $(document).on('change', '#status_select', syncPackingCard);

    $(document).on('click', '#btn_worker_add_packing', function () {
        var $row = $($('#worker_packing_row_tpl').html()).appendTo('#worker_packing_rows');
        initPackingRow($row);
    });

    // ลบได้เฉพาะแถวใหม่ที่ยังไม่บันทึก (แถวเดิมไม่มีปุ่มลบ — server ก็ไม่ลบแถวเดิมเช่นกัน)
    $(document).on('click', '.btn_worker_remove_packing', function () {
        $(this).closest('.worker-packing-row').remove();
    });

    // รวบรวมเฉพาะแถวใหม่เป็น array คู่ขนาน (ตัดคอมมาจากน้ำหนัก) — แถวเดิมอ่านอย่างเดียว ไม่ส่งไป server
    function collectPacking() {
        var data = { packing_datetime: [], weight_packing: [], pack_remark: [] };
        $('#worker_packing_rows .worker-packing-row.wpk-new').each(function () {
            var $r = $(this);
            data.packing_datetime.push($r.find('.wpk-datetime').val() || '');
            data.weight_packing.push(String($r.find('.wpk-weight').val() || '').replace(/,/g, ''));
            data.pack_remark.push($r.find('.wpk-remark').val() || '');
        });
        return data;
    }

    // บันทึกสถานะ (เรียกจากฟอร์มใน modal)
    function submitStatus() {
        var id     = $('#status_planning_id').val();
        var status = $('#status_select').val();
        if (!status) { Swal.fire('กรุณาเลือกสถานะ', '', 'warning'); return; }

        var payload = { _token: CSRF_TOKEN, id: id, status: status };
        // ส่งการบรรจุเฉพาะสถานะบรรจุ (server ตรวจ is_packing ซ้ำอีกชั้น)
        if (selectedIsPacking()) $.extend(payload, collectPacking());

        $.ajax({
            type: 'POST', url: URL_STATUS_U,
            data: payload,
            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
                Swal.fire({
                    icon: 'success', title: res.message || 'อัพเดทสถานะเรียบร้อย',
                    toast: true, position: 'top-end', timer: 1500, showConfirmButton: false
                });
                loadJobs();
            },
            error: function (xhr) {
                var res = xhr.responseJSON || {};
                var msg = res.message ? res.message : 'บันทึกไม่สำเร็จ';
                // งานถูกปิดแล้ว (บล็อกฝั่ง server) → ปิด modal + reload ตาราง (งานหลุดจากตารางไปแล้ว)
                if (res.job_closed) {
                    var m = bootstrap.Modal.getInstance(document.getElementById('statusModal'));
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
    $('#btn_clear').on('click', function () {
        $('#searchText').val('');
        if (fpDate) fpDate.clear(); else $('#searchDate').val('');
        loadJobs();
    });
</script>
@endsection
