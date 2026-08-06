@extends('./layout/main')


@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-calendar-stats text-primary"></i>
                                วางแผนการผลิต
                            </h3>

                            <p class="text-muted mb-0">
                                ข้อมูลการสั่งซื้อและสร้างแผนการผลิต
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="col-12 mt-4">
                <div class="card">

                    <div class="card-header">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-12" style="text-align: right">
                                <button class="btn btn-primary" id="btn_add">
                                    <i class="ti ti-plus me-1"></i>
                                    สร้างแผน
                                </button>
                            </div>
                        </div>
                        {{-- แถวที่ 1: ค้นหาข้อความ + แผนก + สถานะ --}}
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label mb-1 small text-muted">ค้นหา</label>
                                <input id="searchInput" type="text" class="form-control"
                                placeholder="ค้นหาเลขที่ใบสั่งซื้อ, รหัสลูกค้า, Item No., เลขที่ใบเบิก (Red Bill), ชื่อพนักงานผู้รับผิดชอบ">
                            </div>
                            {{-- สถานะปิดงาน (อ้างอิงคอลัมน์ end_job ของ tb_planning) — ค่าเริ่มต้น: ยังไม่ปิดงาน --}}
                            <div class="col-md-2">
                                <label class="form-label mb-1 small text-muted">สถานะปิดงาน</label>
                                <select id="searchEndJob" class="form-select">
                                    {{-- ใช้ค่า 'all' (ไม่ใช่ค่าว่าง) เพื่อไม่ให้ backend เข้าใจผิดว่าไม่ได้ส่งค่ามา แล้วตกไปใช้ค่าเริ่มต้น --}}
                                    <option value="all">ทั้งหมด</option>
                                    <option value="Y">ปิดงาน</option>
                                    <option value="N" selected>ยังไม่ปิดงาน</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1 small text-muted">แผนก</label>
                                <select id="searchCompany" class="form-select">
                                    <option value="">ทุกแผนก</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->name }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1 small text-muted">สถานะ</label>
                                {{-- สถานะจะโหลดตามแผนกที่เลือก (อ้างอิงความสัมพันธ์แผนก↔สถานะในฐานข้อมูล) --}}
                                <select id="searchStatus" class="form-select" disabled>
                                    <option value="">ทุกสถานะ</option>
                                </select>
                            </div>
                        </div>
                        {{-- แถวที่ 2: ค้นหาช่วงวันที่ — เลือกฟิลด์ (Inplan/Custwant) แล้วระบุวันที่เริ่ม–สิ้นสุด --}}
                        <div class="row g-3 align-items-end mt-1">
                            <div class="col-md-4">
                                <label class="form-label mb-1 small text-muted">ค้นหาตามวันที่</label>
                                <select id="searchDateField" class="form-select">
                                    <option value="inplan">วันที่วางแผนผลิต (Inplan)</option>
                                    <option value="custwant">วันที่ต้องการรับ (Custwant)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1 small text-muted">วันที่เริ่ม</label>
                                <input id="searchDateStart" type="date" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1 small text-muted">วันที่สิ้นสุด</label>
                                <input id="searchDateEnd" type="date" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <button id="btn_clear_date" type="button" class="btn btn-outline-secondary w-100">
                                    <i class="ti ti-x me-1"></i>ล้างวันที่
                                </button>
                            </div>
                        </div>
                        {{-- แถวที่ 3: ค้นหาตามวันเวลาบรรจุเสร็จ — ระบุวันที่บรรจุ และช่วงเวลาเริ่ม–สิ้นสุด --}}
                        <div class="row g-3 align-items-end mt-1">
                            <div class="col-md-4">
                                <label class="form-label mb-1 small text-muted">วันที่บรรจุ (Packing)</label>
                                <input id="searchPackingDate" type="date" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1 small text-muted">เวลาเริ่ม</label>
                                <input id="searchPackingTimeStart" type="time" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1 small text-muted">เวลาสิ้นสุด</label>
                                <input id="searchPackingTimeEnd" type="time" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <button id="btn_clear_packing" type="button" class="btn btn-outline-secondary w-100">
                                    <i class="ti ti-x me-1"></i>ล้างวันบรรจุ
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-header">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-striped table-hover nowrap" style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        <th class="col-1">#</th>
                                        {{-- <th class="col-2">Planning Code</th> --}}
                                        <th class="col-1">Orderno</th>
                                        <th class="col-1">เลขที่ใบเบิก</th>
                                        <th class="col-1">Company</th>
                                        <th class="col-1">Inplan</th>
                                        <th class="col-1">Custwant</th>
                                        <th class="col-1">วันเวลาบรรจุเสร็จ</th>
                                        <th class="col-1">Itemno</th>
                                        {{-- <th class="col-1">Quantity</th> --}}
                                        <th class="col-2">MachineNo</th>
                                        <th class="col-1">สถานะภายใน</th>
                                        <th class="col-1">Manage</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Modal 1: Planning Header + Planning List -->
    <div class="modal fade" id="planningModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" id="result_detail"></div>
        </div>
    </div>

    <!-- Modal 2: Planning Item Form (nested) -->
    <div class="modal fade" id="planningItemModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" id="result_planning_item"></div>
        </div>
    </div>

    <!-- Modal สร้างแผน: สร้าง Semi กรอกเอง (ไม่ผูกแผนการผลิต) → เข้ารายการรออนุมัติ -->
    <div class="modal fade" id="createSemiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background-color:#54BAB9; padding:1rem 1.5rem;">
                    <h5 class="modal-title text-white mb-0">
                        <i class="ti ti-calendar-plus me-1"></i>สร้างแผน (Semi)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        <i class="ti ti-info-circle me-1"></i>สร้าง Semi โดยไม่ผูกกับแผนการผลิต — เมื่อบันทึกแล้วจะเข้าสู่รายการรออนุมัติ
                    </div>
                    <form id="create_semi_form">
                        @include('production-planning.semi-pigment.partials.entry-fields', [
                            'prefix'         => 'cs',
                            'companies'      => ['CP', 'MB', 'DB', 'SPP'],
                            'custnoReadonly' => false,
                        ])
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>ยกเลิก
                    </button>
                    <button type="button" class="btn btn-primary" id="btn_save_create_semi">
                        <i class="ti ti-device-floppy me-1"></i>บันทึก
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>

    var oTable;
    $(document).ready(function () {
        oTable = $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            // ปิด responsive (ไม่ยุบคอลัมน์) — ให้ตารางเลื่อนซ้าย-ขวาผ่าน wrapper .table-responsive แทน
            responsive: false,
            ajax: {
                url: "{{ route('production.planning.datatable') }}",
                data: function(d) {
                    d.search = $('#searchInput').val();
                    d.company = $('#searchCompany').val();
                    d.planning_status = $('#searchStatus').val();
                    // ส่งค่าเสมอ ('all' | 'Y' | 'N') — ถ้าหา element ไม่เจอให้ใช้ค่าเริ่มต้น 'N'
                    d.end_job = $('#searchEndJob').val() || 'N';
                    d.date_field = $('#searchDateField').val();
                    d.date_start = $('#searchDateStart').val();
                    d.date_end   = $('#searchDateEnd').val();
                    // ค้นหาตามวันเวลาบรรจุเสร็จ: วันที่บรรจุ + ช่วงเวลาเริ่ม–สิ้นสุด
                    d.packing_date       = $('#searchPackingDate').val();
                    d.packing_time_start = $('#searchPackingTimeStart').val();
                    d.packing_time_end   = $('#searchPackingTimeEnd').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            columns: [
                { 'className': "text-center", data: 'rownum', name: 'rownum', orderable: false },
                // { 'className': "text-left", data: 'planning_code', name: 'planning_code', orderable: false },
                { 'className': "text-center", data: 'orderno', name: 'orderno', orderable: false },
                { 'className': "text-center", data: 'red_bill_code', name: 'red_bill_code', orderable: false, searchable: false },
                { 'className': "text-center", data: 'company', name: 'company', orderable: false },
                { 'className': "text-center", data: 'inplan', name: 'inplan', orderable: false },
                { 'className': "text-center", data: 'custwant', name: 'custwant', orderable: false },
                { 'className': "text-center", data: 'packing_datetie', name: 'packing_datetie', orderable: false, searchable: false },
                { 'className': "text-left", data: 'itemno', name: 'itemno', orderable: false },
                // { 'className': "text-left", data: 'quantity', name: 'quantity', orderable: false },
                { 'className': "text-left", data: 'machine_no', name: 'machine_no', orderable: false },
                { 'className': "text-center", data: 'inner_status', name: 'inner_status', orderable: false, searchable: false },
                { 'className': "text-center", data: 'btnedit', name: 'btnedit', orderable: false, searchable: false },
            ],
            order: [
                [0, 'asc']
            ],
            rowCallback: function(row, data, index) {

            },
            initComplete: function(settings, json) {
                console.log('DataTable loaded');
            }
        });
    });

    $(document).on('keyup', '#searchInput', function(e){
        e.preventDefault();
        oTable.draw();
    });

    // เปลี่ยนแผนก → โหลดสถานะของแผนกนั้น (ตามความสัมพันธ์แผนก↔สถานะในฐานข้อมูล) แล้วค้นหาใหม่
    var URL_DEPT_OPTIONS = '{{ route('production.planning.dept-options') }}';

    function escOption(v) {
        return $('<div>').text(v == null ? '' : v).html();
    }

    $(document).on('change', '#searchCompany', function(e){
        e.preventDefault();
        var company = $(this).val() || '';
        var $status = $('#searchStatus');

        // ล้างสถานะเดิม (ของแผนกเก่า) ทุกครั้งที่เปลี่ยนแผนก
        $status.prop('disabled', true).html('<option value="">ทุกสถานะ</option>');

        // ไม่เลือกแผนก → ค้นหาแบบไม่กรองสถานะ
        if (company === '') {
            oTable.draw();
            return;
        }

        $.ajax({
            type: 'GET', url: URL_DEPT_OPTIONS, dataType: 'json',
            data: { company: company },
            success: function (res) {
                var opts = '<option value="">ทุกสถานะ</option>';
                (res.statuses || []).forEach(function (s) {
                    opts += '<option value="' + escOption(s) + '">' + escOption(s) + '</option>';
                });
                $status.html(opts).prop('disabled', false);
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'โหลดสถานะของแผนกไม่สำเร็จ กรุณาลองใหม่' });
            },
            complete: function () {
                oTable.draw();
            }
        });
    });

    // เปลี่ยนสถานะปิดงาน (ทั้งหมด / ปิดงาน / ยังไม่ปิดงาน) → ค้นหาใหม่
    $(document).on('change', '#searchEndJob', function(e){
        e.preventDefault();
        oTable.draw();
    });

    $(document).on('change', '#searchStatus', function(e){
        e.preventDefault();
        oTable.draw();
    });

    // ค้นหาช่วงวันที่ (Inplan/Custwant) — redraw เมื่อเปลี่ยนฟิลด์หรือวันที่
    $(document).on('change', '#searchDateField, #searchDateStart, #searchDateEnd', function(e){
        e.preventDefault();
        oTable.draw();
    });

    // ล้างช่วงวันที่แล้วค้นหาใหม่
    $(document).on('click', '#btn_clear_date', function(e){
        e.preventDefault();
        $('#searchDateStart').val('');
        $('#searchDateEnd').val('');
        oTable.draw();
    });

    // ค้นหาตามวันเวลาบรรจุเสร็จ — redraw เมื่อเปลี่ยนวันที่บรรจุหรือช่วงเวลา
    $(document).on('change', '#searchPackingDate, #searchPackingTimeStart, #searchPackingTimeEnd', function(e){
        e.preventDefault();
        oTable.draw();
    });

    // ล้างเงื่อนไขวันเวลาบรรจุแล้วค้นหาใหม่
    $(document).on('click', '#btn_clear_packing', function(e){
        e.preventDefault();
        $('#searchPackingDate').val('');
        $('#searchPackingTimeStart').val('');
        $('#searchPackingTimeEnd').val('');
        oTable.draw();
    });

    // ---- สร้างแผน: สร้าง Semi กรอกเอง (ไม่ผูกแผนการผลิต) → เข้ารายการรออนุมัติ ----
    var createSemiModal;

    // น้ำหนักที่จะผลิต = น้ำหนักที่จะใช้ + ผลิตเพิ่ม (คำนวณอัตโนมัติ แต่แก้เองได้)
    var csProdManual = false;
    function csNum(v) { v = parseFloat(v); return isNaN(v) ? 0 : v; }
    function csRound(v) { return Math.round(v * 100) / 100; }
    function csRecalc() {
        if (csProdManual) return;
        var req = ($('#cs_weight_request').val() || '').trim();
        var inc = ($('#cs_increase_production').val() || '').trim();
        if (req === '' && inc === '') { $('#cs_weight_production').val(''); return; }
        $('#cs_weight_production').val(csRound(csNum(req) + csNum(inc)));
    }
    $(document).on('input', '#cs_weight_request, #cs_increase_production', function () {
        csProdManual = false;
        csRecalc();
    });
    $(document).on('input', '#cs_weight_production', function () { csProdManual = true; });

    $(document).on('click', '#btn_add', function (e) {
        e.preventDefault();
        $('#create_semi_form')[0].reset();
        $('#cs_itemno').removeClass('is-invalid');
        csProdManual = false;
        createSemiModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('createSemiModal'));
        createSemiModal.show();
        setTimeout(function () { $('#cs_itemno').trigger('focus'); }, 300);
    });

    $(document).on('click', '#btn_save_create_semi', function () {
        var $btn = $(this);
        var itemno = ($('#cs_itemno').val() || '').trim();
        if (!itemno) {
            $('#cs_itemno').addClass('is-invalid').trigger('focus');
            return;
        }
        $('#cs_itemno').removeClass('is-invalid');

        var payload = {
            _token:              '{{ csrf_token() }}',
            company:             $('#cs_company').val()             || '',
            custno:              $('#cs_custno').val()              || '',
            mdate:               $('#cs_mdate').val()               || '',
            custwant:            $('#cs_custwant').val()            || '',
            itemno:              itemno,
            semi_code:           $('#cs_semi_code').val()           || '',
            primary_color:       $('#cs_primary_color').val()       || '',
            lot_no:              $('#cs_lot_no').val()              || '',
            red_bill_code:       $('#cs_red_bill_code').val()       || '',
            balance:             $('#cs_balance').val()             || '',
            retrospective:       $('#cs_retrospective').val()       || '',
            weight_request:      $('#cs_weight_request').val()      || '',
            increase_production: $('#cs_increase_production').val()  || '',
            weight_production:   $('#cs_weight_production').val()    || ''
        };

        $btn.prop('disabled', true).html('<i class="ti ti-loader me-1"></i>กำลังบันทึก...');
        $.ajax({
            type: 'POST',
            url: '{{ route("production.semipigment.standalone.store") }}',
            dataType: 'json',
            data: payload,
            success: function (response) {
                if (response.status == 200) {
                    createSemiModal.hide();
                    Swal.fire({ icon: 'success', title: 'สำเร็จ', text: response.message, timer: 1800, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'warning', title: response.status == 422 ? 'ข้อมูลไม่ถูกต้อง' : 'ผิดพลาด', text: response.message });
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i>บันทึก');
            }
        });
    });

    // ---- Modal 1: Planning Header ----

    function openPlanningModal(planning_id, planning_header_id) {
        $.ajax({
            type: 'GET',
            url: '{{ route("production.planning.edit") }}',
            dataType: 'json',
            cache: false,
            data: {
                planning_id: planning_id || '',
                planning_header_id: planning_header_id || ''
            },
            success: function(response) {
                if (response.status == 200) {
                    $('#result_detail').html(response.data);
                    var modal = new bootstrap.Modal(document.getElementById('planningModal'));
                    modal.show();
                }
            },
            error: function(response) {
                console.log("error", response.responseJSON);
            }
        });
    }

    $(document).on('click', '.btn_edit', function(e){
        e.preventDefault();
        let planning_id = $(this).data('planning_id');
        openPlanningModal(planning_id, '');
    });

    // ---- Modal 2: Planning Item Form ----

    function openPlanningItemModal(planning_id, planning_header_id) {
        $.ajax({
            type: 'GET',
            url: '{{ route("production.planning.edit-item") }}',
            dataType: 'json',
            cache: false,
            data: {
                planning_id: planning_id || '',
                planning_header_id: planning_header_id || ''
            },
            success: function(response) {
                if (response.status == 200) {
                    $('#result_planning_item').html(response.data);
                    var itemModal = new bootstrap.Modal(document.getElementById('planningItemModal'));
                    itemModal.show();
                }
            },
            error: function(response) {
                console.log("error", response.responseJSON);
            }
        });
    }

    // ปุ่มเพิ่ม Planning Item (อยู่ใน Modal 1)
    $(document).on('click', '.btn_add_planning_item', function(e){
        e.preventDefault();
        let planning_header_id = $(this).data('planning_header_id');
        openPlanningItemModal('', planning_header_id);
    });

    // ปุ่มแก้ไข Planning Item (อยู่ใน Modal 1)
    $(document).on('click', '.btn_edit_planning_item', function(e){
        e.preventDefault();
        let planning_id = $(this).data('planning_id');
        let planning_header_id = $(this).data('planning_header_id');
        openPlanningItemModal(planning_id, planning_header_id);
    });

    // เมื่อ Modal 2 ปิด ให้ Modal 1 ยังคงแสดงอยู่
    document.getElementById('planningItemModal').addEventListener('hidden.bs.modal', function () {
        document.body.classList.add('modal-open');
        var backdrop = document.querySelector('.modal-backdrop');
        if (!backdrop) {
            var newBackdrop = document.createElement('div');
            newBackdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(newBackdrop);
        }
    });

    // ---- บันทึก Planning Item ----

    // โหลดเนื้อหา Modal 1 ใหม่โดยไม่เปิด modal ซ้ำ
    function reloadPlanningHeaderContent(planning_header_id) {
        $.ajax({
            type: 'GET',
            url: '{{ route("production.planning.edit") }}',
            dataType: 'json',
            cache: false,
            data: { planning_header_id: planning_header_id },
            success: function(response) {
                if (response.status == 200) {
                    $('#result_detail').html(response.data);
                }
            }
        });
    }

    $(document).on('click', '#btn_save_planning_item', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var $form = $('#planning_item_form');

        // เรียก serialize_fn เพื่อแปลง semi/pigment rows → JSON ก่อน serialize form
        var serializeFn = $form.data('serialize_fn');
        if (typeof serializeFn === 'function') serializeFn();

        var formData = $form.serialize() + '&_token={{ csrf_token() }}';
        var planning_header_id = $form.find('input[name="planning_header_id"]').val();

        $btn.prop('disabled', true).html('<i class="ti ti-loader me-1"></i>กำลังบันทึก...');

        $.ajax({
            type: 'POST',
            url: '{{ route("production.planning.save-item") }}',
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.status == 200) {
                    // ปิด Modal 2
                    var itemModal = bootstrap.Modal.getInstance(document.getElementById('planningItemModal'));
                    if (itemModal) itemModal.hide();

                    // โหลดตาราง Planning ใน Modal 1 ใหม่
                    reloadPlanningHeaderContent(response.planning_header_id);

                    // Refresh DataTable หลัก
                    oTable.draw();

                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: response.status == 422 ? 'ข้อมูลไม่ถูกต้อง' : 'ผิดพลาด',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
                console.log(xhr.responseJSON);
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i>บันทึก');
            }
        });
    });

    // ---- ปิดออเดอร์ (end_order) จากโมดัลแผนการผลิต ----
    $(document).on('change', '#planning_end_order', function () {
        var $cb = $(this);
        var planning_header_id = $cb.data('planning_header_id');
        var end_order = $cb.is(':checked') ? 'Y' : 'N';

        $cb.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: '{{ route("production.planning.save-end-order") }}',
            dataType: 'json',
            data: {
                planning_header_id: planning_header_id,
                end_order: end_order,
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {
                if (res.status == 200) {
                    // รีเฟรชการ์ด Modal 1 (สถานะ checkbox/disabled จะถูกคำนวณใหม่ฝั่ง server)
                    reloadPlanningHeaderContent(planning_header_id);
                    oTable.draw();
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: res.message,
                        timer: 1600,
                        showConfirmButton: false
                    });
                } else {
                    // ย้อนสถานะ checkbox กลับ
                    $cb.prop('checked', !$cb.is(':checked'));
                    Swal.fire({
                        icon: 'warning',
                        title: 'ผิดพลาด',
                        text: res.message
                    });
                }
            },
            error: function (xhr) {
                $cb.prop('checked', !$cb.is(':checked'));
                var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
            },
            complete: function () {
                $cb.prop('disabled', false);
            }
        });
    });

    // ---- ปิดจบงาน (end_close) จากโมดัลแผนการผลิต ----
    // ติ๊ก end_close → mirror ไปที่ checkbox ปิดออเดอร์ (end_order) + แสดงเครื่องหมายบังคับกรอกหมายเหตุ
    // ใช้ .prop() จึงไม่ trigger change ของ #planning_end_order (ไม่ auto-save ซ้ำ)
    $(document).on('change', '#planning_end_close', function () {
        var checked = $(this).is(':checked');
        $('#planning_end_order').prop('checked', checked);
        // แสดง/ซ่อนเครื่องหมาย * (บังคับกรอก) — textarea พิมพ์ได้ตลอดอยู่แล้ว
        $('#end_close_remark_required').toggle(checked);
        if (checked) {
            $('#planning_end_close_remark').trigger('focus');
        }
    });

    // ปุ่มบันทึกส่วนปิดจบงาน (end_close + end_close_remark) — end_order จะถูกตั้งตาม end_close ฝั่ง server
    $(document).on('click', '#btn_save_end_close', function () {
        var $btn = $(this);
        var planning_header_id = $btn.data('planning_header_id');
        var end_close = $('#planning_end_close').is(':checked') ? 'Y' : 'N';
        var remark = ($('#planning_end_close_remark').val() || '').trim();

        // ปิดจบงานต้องมีหมายเหตุ
        if (end_close === 'Y' && remark === '') {
            Swal.fire({ icon: 'warning', title: 'กรุณาระบุหมายเหตุ', text: 'เมื่อปิดจบงาน ต้องกรอกหมายเหตุการปิดจบงาน' });
            $('#planning_end_close_remark').trigger('focus');
            return;
        }

        $btn.prop('disabled', true).html('<i class="ti ti-loader me-1"></i>กำลังบันทึก...');

        $.ajax({
            type: 'POST',
            url: '{{ route("production.planning.save-end-close") }}',
            dataType: 'json',
            data: {
                planning_header_id: planning_header_id,
                end_close: end_close,
                end_close_remark: remark,
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {
                if (res.status == 200) {
                    reloadPlanningHeaderContent(planning_header_id);
                    oTable.draw();
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: res.message,
                        timer: 1600,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ผิดพลาด',
                        text: res.message
                    });
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i>บันทึกการปิดจบงาน');
            }
        });
    });

</script>
@endsection
