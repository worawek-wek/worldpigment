@extends('./layout/main')


@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-calendar-event text-primary"></i>
                                ตารางวันหยุดนักขัตฤกษ์
                            </h3>
                            <p class="text-muted mb-0">
                                วันหยุดประจำปีของโรงงาน (นักขัตฤกษ์ / ชดเชย / วันหยุดบริษัท)
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" id="btn_add">
                                <i class="ti ti-plus me-1"></i> เพิ่มวันหยุด
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 mt-4">
                <div class="card">

                    {{-- แถบเลือกมุมมอง — ค่าเริ่มต้นคือ "ปฏิทินรายปี" (แท็บแรก) --}}
                    <div class="card-header pb-0">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <button type="button" class="nav-link active" id="tab_calendar"
                                    data-bs-toggle="tab" data-bs-target="#pane_calendar" role="tab">
                                    <i class="ti ti-calendar me-1"></i>ปฏิทินรายปี
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" id="tab_table"
                                    data-bs-toggle="tab" data-bs-target="#pane_table" role="tab">
                                    <i class="ti ti-list me-1"></i>ตารางวันหยุด
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content">

                        {{-- ───────── มุมมองตาราง ───────── --}}
                        <div class="tab-pane fade" id="pane_table" role="tabpanel">

                            <div class="card-header">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-3">
                                        <input id="searchInput" type="text" class="form-control"
                                            placeholder="ค้นหาชื่อวันหยุด / หมายเหตุ...">
                                    </div>
                                    <div class="col-md-2">
                                        <select id="searchYear" class="form-select no-enhance">
                                            <option value="">ทุกปี</option>
                                            @foreach($years as $year)
                                                <option value="{{ $year }}" {{ $year === $currentYear ? 'selected' : '' }}>
                                                    พ.ศ. {{ $year + 543 }} ({{ $year }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select id="searchType" class="form-select no-enhance">
                                            <option value="">ทุกประเภท</option>
                                            @foreach($types as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select id="searchStatus" class="form-select no-enhance">
                                            <option value="">ทุกสถานะ</option>
                                            <option value="Y">เปิดใช้งาน</option>
                                            <option value="N">ปิดใช้งาน</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <button type="button" class="btn btn-label-secondary" id="btn_reset">
                                            <i class="ti ti-refresh me-1"></i> ล้างตัวกรอง
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-header">
                                <div class="table-responsive">
                                    <table id="dataTable" class="table table-striped table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="col-1">#</th>
                                                <th class="col-2">วันที่</th>
                                                <th class="col-1">วัน</th>
                                                <th class="col-3">ชื่อวันหยุด</th>
                                                <th class="col-2">ประเภท</th>
                                                <th class="col-2">หมายเหตุ</th>
                                                <th class="col-1">สถานะ</th>
                                                <th class="col-1">จัดการ</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                        </div>

                        {{-- ───────── มุมมองปฏิทินรายปี ───────── --}}
                        <div class="tab-pane fade show active" id="pane_calendar" role="tabpanel">
                            <div class="card-header">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-3">
                                        <select id="calendarYear" class="form-select no-enhance">
                                            @foreach($years as $year)
                                                <option value="{{ $year }}" {{ $year === $currentYear ? 'selected' : '' }}>
                                                    พ.ศ. {{ $year + 543 }} ({{ $year }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0" id="calendarBox">
                                <div class="text-center text-muted py-5">กำลังโหลดปฏิทิน...</div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Create/Edit Holiday Modal -->
    <div class="modal fade modalHeadDecor" id="holidayModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-calendar-event me-1"></i>วันหยุด
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="result_detail">
                    {{-- โหลดฟอร์มผ่าน AJAX --}}
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>

    var oTable;
    var calendarLoaded = false;

    $(document).ready(function () {
        oTable = $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            responsive: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('holiday.datatable') }}",
                data: function(d) {
                    d.search = $('#searchInput').val();
                    d.year   = $('#searchYear').val();
                    d.type   = $('#searchType').val();
                    d.status = $('#searchStatus').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            // เปิดให้คลิกหัวคอลัมน์เพื่อ sort (Yajra ORDER BY ตาม name ของคอลัมน์ให้เอง)
            columns: [
                // # นับหลัง query จึง sort ไม่ได้ (renumber ตามผลลัพธ์ที่เรียงแล้ว)
                { 'className': "text-center", data: 'rownum', name: 'rownum', orderable: false },
                { 'className': "text-center", data: 'date_label', name: 'holiday_date', orderable: true },
                // ชื่อวันคำนวณจากวันที่ → sort ด้วยคอลัมน์วันที่จริง
                { 'className': "text-center", data: 'weekday', name: 'holiday_date', orderable: false, searchable: false },
                { 'className': "text-start", data: 'name', name: 'name', orderable: true },
                { 'className': "text-center", data: 'type_label', name: 'type', orderable: true, searchable: false },
                { 'className': "text-start", data: 'remark_label', name: 'remark', orderable: true },
                { 'className': "text-center", data: 'status_switch', name: 'is_active', orderable: true, searchable: false },
                { 'className': "text-center", data: 'btnaction', name: 'btnaction', orderable: false, searchable: false },
            ],
            // เริ่มต้นเรียงตามวันที่ เก่า → ใหม่
            order: [
                [1, 'asc']
            ],
        });

        // ปฏิทินเป็นมุมมองเริ่มต้น → โหลดทันทีตอนเปิดหน้า
        loadCalendar();

        // กลับมาที่แท็บปฏิทินแล้วข้อมูลเปลี่ยนไประหว่างนั้น (บันทึก/ลบ/สลับสถานะ) → โหลดใหม่
        $('#tab_calendar').on('shown.bs.tab', function () {
            if (!calendarLoaded) loadCalendar();
        });

        // DataTables ถูก init ตอนแท็บตารางยังซ่อนอยู่ → วัดความกว้างคอลัมน์ไม่ได้
        // ต้องสั่งวัดใหม่ตอนแท็บโผล่ ไม่งั้นหัวตารางกับเนื้อตารางเหลื่อมกัน
        $('#tab_table').on('shown.bs.tab', function () {
            oTable.columns.adjust();
            if (oTable.responsive) oTable.responsive.recalc();
        });
    });

    $(document).on('keyup', '#searchInput', function(e){
        e.preventDefault();
        oTable.draw();
    });

    $(document).on('change', '#searchYear, #searchType, #searchStatus', function(e){
        e.preventDefault();
        oTable.draw();
    });

    $(document).on('click', '#btn_reset', function(e){
        e.preventDefault();
        $('#searchInput').val('');
        $('#searchYear').val('');
        $('#searchType').val('');
        $('#searchStatus').val('');
        oTable.draw();
    });

    // ───────── ปฏิทินรายปี ─────────
    function loadCalendar() {
        var $box = $('#calendarBox');
        $box.html('<div class="text-center text-muted py-5">กำลังโหลดปฏิทิน...</div>');

        $.ajax({
            url: "{{ route('holiday.calendar') }}",
            method: "GET",
            data: { year: $('#calendarYear').val() },
            success: function(res) {
                $box.html(res.data);
                calendarLoaded = true;
            },
            error: function() {
                $box.html('<div class="alert alert-danger mb-0">โหลดปฏิทินไม่สำเร็จ กรุณาลองใหม่</div>');
            }
        });
    }

    $(document).on('change', '#calendarYear', function(){
        loadCalendar();
    });

    // สลับสถานะเปิด-ปิดใช้งานจากตารางโดยตรง
    $(document).on('change', '.switch_status', function(){
        var $sw = $(this);
        var id = $sw.data('id');
        var is_active = $sw.is(':checked') ? 'Y' : 'N';

        $sw.prop('disabled', true);
        $.ajax({
            url: "{{ route('holiday.toggle-status') }}",
            method: "POST",
            dataType: 'json',
            data: { _token: "{{ csrf_token() }}", id: id, is_active: is_active },
            success: function(res){
                if (res.status == 200) {
                    calendarLoaded = false; // ปฏิทินต้องโหลดใหม่ให้ตรงกับสถานะล่าสุด
                    Swal.fire({ icon: 'success', title: res.message, toast: true,
                        position: 'top-end', timer: 1500, showConfirmButton: false });
                } else {
                    $sw.prop('checked', !$sw.is(':checked'));
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: res.message || '' });
                }
            },
            error: function(xhr){
                $sw.prop('checked', !$sw.is(':checked'));
                Swal.fire({ icon: 'error', title: 'ผิดพลาด',
                    text: xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่' });
            },
            complete: function(){ $sw.prop('disabled', false); }
        });
    });

    $(document).on('click', '#btn_add', function(e){
        e.preventDefault();
        openHolidayForm(null);
    });

    // ปุ่มแก้ไขในตาราง + คลิกวันที่ในปฏิทิน ใช้คลาสเดียวกัน
    $(document).on('click', '.btn_edit', function(e){
        e.preventDefault();
        openHolidayForm($(this).data('id'));
    });

    function openHolidayForm(id) {
        $.ajax({
            url: "{{ route('holiday.edit') }}",
            method: "GET",
            data: { id: id },
            success: function(response) {
                $('#result_detail').html(response.data);
                initHolidayPicker();
                $('#holidayModal').modal('show');
            }
        });
    }

    // ฟอร์มถูกโหลดผ่าน AJAX → ผูก flatpickr ใหม่ทุกครั้ง
    function initHolidayPicker() {
        flatpickr('.holiday-datepicker', {
            dateFormat: 'd/m/Y',
            allowInput: true,
            static: true,
            disableMobile: true
        });
    }

    // บันทึก
    $(document).on('click', '#btn_holiday_save', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var formData = $('#holiday_master_form').serialize();

        $btn.prop('disabled', true);
        $.ajax({
            url: "{{ route('holiday.store') }}",
            method: "POST",
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.status == 200) {
                    $('#holidayModal').modal('hide');
                    oTable.draw(false); // draw(false) = คงหน้า pagination เดิม
                    refreshCalendarIfVisible();
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: response.status == 422 ? 'warning' : 'error',
                        title: response.status == 422 ? 'ข้อมูลไม่ถูกต้อง' : 'เกิดข้อผิดพลาด',
                        text: response.message || ''
                    });
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
            },
            complete: function() { $btn.prop('disabled', false); }
        });
    });

    // ลบ
    $(document).on('click', '.btn_delete', function(e){
        e.preventDefault();
        var id = $(this).data('id');

        Swal.fire({
            icon: 'warning',
            title: 'ยืนยันการลบ',
            text: 'ต้องการลบวันหยุดนี้ใช่หรือไม่?',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-label-secondary ms-2' },
            buttonsStyling: false
        }).then(function(result){
            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ route('holiday.delete') }}",
                method: "POST",
                dataType: 'json',
                data: { _token: "{{ csrf_token() }}", id: id },
                success: function(res){
                    if (res.status == 200) {
                        oTable.draw(false);
                        refreshCalendarIfVisible();
                        Swal.fire({ icon: 'success', title: res.message, toast: true,
                            position: 'top-end', timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: res.message || '' });
                    }
                },
                error: function(xhr){
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด',
                        text: xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่' });
                }
            });
        });
    });

    // ปฏิทินเปิดอยู่ = โหลดใหม่ทันที · ซ่อนอยู่ = ตั้งธงให้โหลดตอนเปิดแท็บ
    function refreshCalendarIfVisible() {
        if ($('#pane_calendar').hasClass('active')) {
            loadCalendar();
        } else {
            calendarLoaded = false;
        }
    }

</script>

<style>
    .modalHeadDecor .modal-header {
        padding: 0;
    }

    .modalHeadDecor .modal-title {
        padding: 1.25rem 1.5rem 1.25rem;
        color: white;
        background-color: #54BAB9;
        position: relative;
    }

    .modalHeadDecor .modal-title::after {
        position: absolute;
        top: 0;
        right: -65px;
        content: '';
        width: 0;
        height: 0;
        border-top: 65px solid #54BAB9;
        border-right: 65px solid transparent;
    }

    /* flatpickr.css ของ theme ตั้ง z-index ไว้ 999 ซึ่งต่ำกว่า modal (--bs-modal-zindex: 1090) */
    .flatpickr-calendar { z-index: 1092; }

    /* ปฏิทินรายปี */
    .holiday-mini-calendar th { font-size: .75rem; font-weight: 500; padding: .15rem; }
    .holiday-mini-calendar td { padding: .1rem; }

    .hc-day {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 50%;
        font-size: .8125rem;
    }

    .hc-holiday { font-weight: 600; color: #fff; }
    .hc-holiday:hover { opacity: .85; }
    .hc-public { background-color: #ff3e1d; }      /* นักขัตฤกษ์ */
    .hc-substitute { background-color: #ffab00; }  /* ชดเชย */
    .hc-company { background-color: #03c3ec; }     /* วันหยุดบริษัท */
    .hc-off { background-color: #a8aaae; }         /* ปิดใช้งาน */
</style>

@endsection
