@extends('./layout/main')


@section('content')
    <style>
        /* สีพื้นหลังคอลัมน์ Inplan (น้ำเงิน) และ Custwant (แดง) — ทั้งหัวตารางและช่องข้อมูล */
        #dataTable th.col-inplan,
        #dataTable td.col-inplan {
            background-color: #cfe2ff !important;
            color: #084298 !important;
        }
        #dataTable th.col-custwant,
        #dataTable td.col-custwant {
            background-color: #f8d7da !important;
            color: #842029 !important;
        }
    </style>
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
                            <div class="col-md-12 d-flex justify-content-end gap-2">
                                <button class="btn btn-primary" id="btn_add">
                                    <i class="ti ti-plus me-1"></i>
                                    สร้างแผน
                                </button>
                                <button class="btn btn-success" id="btn_export_excel">
                                    <i class="ti ti-file-spreadsheet me-1"></i>
                                    Export Excel
                                </button>
                                <button class="btn btn-danger" id="btn_export_pdf">
                                    <i class="ti ti-file-type-pdf me-1"></i>
                                    Export PDF
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
                                <input id="searchDateStart" type="text" class="form-control flatpickr-date"
                                    autocomplete="off" placeholder="วว/ดด/ปปปป">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1 small text-muted">วันที่สิ้นสุด</label>
                                <input id="searchDateEnd" type="text" class="form-control flatpickr-date"
                                    autocomplete="off" placeholder="วว/ดด/ปปปป">
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
                                <input id="searchPackingDate" type="text" class="form-control flatpickr-date"
                                    autocomplete="off" placeholder="วว/ดด/ปปปป">
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
                                        <th class="col-1 col-inplan">Inplan</th>
                                        <th class="col-1 col-custwant">Custwant</th>
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
    <!-- backdrop=static + keyboard=false: ปิด modal ได้เฉพาะปุ่ม กากบาท (กันคลิกนอก+ESC) -->
    <div class="modal fade" id="planningModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
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
    // instance ของ flatpickr สำหรับช่องวันที่ (ใช้ clear ตอนกดล้าง)
    var fpDateStart = null;
    var fpDateEnd   = null;
    var fpPacking   = null;
    $(document).ready(function () {
        // flatpickr: แสดง d/m/Y เหมือนกันทุกเครื่อง แต่ค่าจริง (input.value) ยังเป็น Y-m-d
        // → ค่าที่ส่งให้ DataTable/server ยังเป็น Y-m-d เหมือนเดิม ไม่ต้องแก้ฝั่ง PHP
        var fpOptions = {
            dateFormat: 'Y-m-d',   // ค่าใน input จริง (ส่งให้ server)
            altInput:   true,      // สร้างช่องที่มองเห็นแยกต่างหาก
            altFormat:  'd/m/Y',   // รูปแบบที่แสดงบนจอ
            allowInput: true,
            disableMobile: true
        };
        fpDateStart = flatpickr('#searchDateStart', fpOptions);
        fpDateEnd   = flatpickr('#searchDateEnd', fpOptions);
        fpPacking   = flatpickr('#searchPackingDate', fpOptions);

        // modal "สร้างแผน" (Semi) — partial entry-fields ใช้ class flatpickr-date เช่นกัน
        // scope เฉพาะ #createSemiModal + guard _flatpickr เพื่อไม่ชน/ไม่ init ซ้ำกับช่องค้นหาด้านบน
        $('#createSemiModal .flatpickr-date').each(function () {
            if (!this._flatpickr) flatpickr(this, fpOptions);
        });

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
            // เปิดให้คลิกหัวคอลัมน์เพื่อ sort ได้ (Yajra จะ ORDER BY ตาม name ของคอลัมน์ให้อัตโนมัติ)
            // ใช้ชื่อคอลัมน์แบบ qualify (tb_planning.* / tb_planning_header.*) เพราะคิวรี join 2 ตาราง
            columns: [
                // # ใช้ DT_RowIndex (จาก addIndexColumn) เรียงตามลำดับที่แสดงจริง จึง sort เองไม่ได้
                { 'className': "text-center", data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                // { 'className': "text-left", data: 'planning_code', name: 'planning_code', orderable: false },
                { 'className': "text-center", data: 'orderno', name: 'tb_planning_header.orderno', orderable: true },
                { 'className': "text-center", data: 'red_bill_code', name: 'tb_planning.red_bill_code', orderable: true, searchable: false },
                // แผนก: sort ตามแผนกจริง COALESCE(item, header) — map ไว้ด้วย ->orderColumn('company', ...) ใน controller
                { 'className': "text-center", data: 'company', name: 'company', orderable: true },
                { 'className': "text-center col-inplan", data: 'inplan', name: 'tb_planning.inplan', orderable: true },
                { 'className': "text-center col-custwant", data: 'custwant', name: 'tb_planning.custwant', orderable: true },
                { 'className': "text-center", data: 'packing_datetie', name: 'tb_planning.packing_datetie', orderable: true, searchable: false },
                { 'className': "text-left", data: 'itemno', name: 'tb_planning.itemno', orderable: true },
                // { 'className': "text-left", data: 'quantity', name: 'quantity', orderable: false },
                { 'className': "text-left", data: 'machine_no', name: 'tb_planning.machine_no', orderable: true },
                // สถานะภายในรวมจาก planning หลายแถว (คำนวณฝั่ง PHP) → sort ที่ SQL ไม่ได้
                { 'className': "text-center", data: 'inner_status', name: 'inner_status', orderable: false, searchable: false },
                { 'className': "text-center", data: 'btnedit', name: 'btnedit', orderable: false, searchable: false },
            ],
            // order: [] = ไม่ส่ง order เริ่มต้น → controller ใช้ default (id ล่าสุด / packing เมื่อกรอง) ให้เอง
            order: [],
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

    // ---- Export Excel / PDF ----
    // เก็บค่าตัวกรองปัจจุบันชุดเดียวกับที่ส่งให้ DataTable (ajax.data) แล้วต่อเป็น querystring
    function collectExportParams() {
        return {
            search:             $('#searchInput').val() || '',
            company:            $('#searchCompany').val() || '',
            planning_status:    $('#searchStatus').val() || '',
            end_job:            $('#searchEndJob').val() || 'N',
            date_field:         $('#searchDateField').val() || '',
            date_start:         $('#searchDateStart').val() || '',
            date_end:           $('#searchDateEnd').val() || '',
            packing_date:       $('#searchPackingDate').val() || '',
            packing_time_start: $('#searchPackingTimeStart').val() || '',
            packing_time_end:   $('#searchPackingTimeEnd').val() || ''
        };
    }

    $(document).on('click', '#btn_export_excel', function(e){
        e.preventDefault();
        var qs = $.param(collectExportParams());
        window.location.href = '{{ route("production.planning.excel") }}?' + qs;
    });

    $(document).on('click', '#btn_export_pdf', function(e){
        e.preventDefault();
        var qs = $.param(collectExportParams());
        window.open('{{ route("production.planning.pdf") }}?' + qs, '_blank');
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
        // clear(false) = ไม่ trigger change → กัน redraw ซ้ำ แล้ว draw ครั้งเดียวด้านล่าง
        if (fpDateStart) fpDateStart.clear(false); else $('#searchDateStart').val('');
        if (fpDateEnd)   fpDateEnd.clear(false);   else $('#searchDateEnd').val('');
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
        if (fpPacking) fpPacking.clear(false); else $('#searchPackingDate').val('');
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
        // flatpickr ไม่รับรู้ native form.reset() → เคลียร์ช่องวันที่ผ่าน instance
        $('#createSemiModal .flatpickr-date').each(function () {
            if (this._flatpickr) this._flatpickr.clear();
        });
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

    // เมื่อ Modal 2 ปิด: ถ้า Modal 1 ยังเปิดอยู่ให้คืน backdrop ให้ Modal 1
    // แต่ถ้า Modal 1 ปิดไปแล้ว ต้องเก็บกวาด backdrop/scroll-lock ที่อาจค้าง
    // (เดิมสร้าง backdrop ใหม่เสมอ → เป็นสาเหตุพื้นดำค้างต้องรีเฟรชหน้า)
    document.getElementById('planningItemModal').addEventListener('hidden.bs.modal', function () {
        var planningModalEl = document.getElementById('planningModal');
        var planningModalOpen = planningModalEl && planningModalEl.classList.contains('show');

        if (planningModalOpen) {
            // Modal 1 ยังเปิดอยู่ → คืน backdrop ถ้ายังไม่มี
            document.body.classList.add('modal-open');
            var backdrop = document.querySelector('.modal-backdrop');
            if (!backdrop) {
                var newBackdrop = document.createElement('div');
                newBackdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(newBackdrop);
            }
        } else {
            // Modal 1 ไม่ได้เปิดแล้ว → ล้าง backdrop/scroll-lock ที่ค้างทั้งหมด
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            document.querySelectorAll('.modal-backdrop').forEach(function (b) { b.remove(); });
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

    // แปลงวันที่ Y-m-d → d/m/Y สำหรับแสดงในกล่องยืนยัน (ค่าว่าง = "(ว่าง)")
    function displayDateTH(v) {
        if (!v) return '(ว่าง)';
        var p = String(v).split('-');
        return p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : v;
    }

    // ส่งฟอร์มบันทึก Planning Item จริง (แยกออกมาเพื่อให้เรียกได้ทั้งแบบตรง ๆ และหลังกดยืนยัน)
    function submitPlanningItem($btn) {
        var $form = $('#planning_item_form');

        // เรียก serialize_fn เพื่อแปลง semi/pigment rows → JSON ก่อน serialize form
        var serializeFn = $form.data('serialize_fn');
        if (typeof serializeFn === 'function') serializeFn();

        var formData = $form.serialize() + '&_token={{ csrf_token() }}';

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

                    // Refresh DataTable หลัก — draw(false) = คงหน้า pagination เดิม (ไม่เด้งกลับหน้า 1)
                    oTable.draw(false);

                    if (response.end_order_auto_closed) {
                        // ปิดออเดอร์อัตโนมัติ (จบงานครบ) → แจ้งแบบให้กดรับทราบ เพราะ header ถูกล็อกแก้ไขแล้ว
                        Swal.fire({
                            icon: 'info',
                            title: 'ปิดออเดอร์อัตโนมัติ',
                            text: response.message,
                            confirmButtonText: 'รับทราบ'
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ',
                            text: response.message,
                            timer: 1800,
                            showConfirmButton: false
                        });
                    }
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
    }

    $(document).on('click', '#btn_save_planning_item', function(e) {
        e.preventDefault();

        var $btn  = $(this);
        var $form = $('#planning_item_form');

        // ตรวจว่ามีการแก้ไข "วันที่ต้องการรับ (custwant)" หรือไม่ (เทียบกับ data-original ที่ render มา)
        // ถ้าเปลี่ยน → ให้ยืนยันก่อนบันทึก
        var $cw     = $form.find('input[name="custwant"]');
        var original = ($cw.attr('data-original') || '').trim();
        var current  = ($cw.val() || '').trim();

        if (current !== original) {
            Swal.fire({
                icon: 'question',
                title: 'ยืนยันการแก้ไขวันที่ต้องการรับ',
                html: 'วันที่ต้องการรับ (custwant) มีการเปลี่ยนแปลง<br>'
                    + 'จาก <b>' + displayDateTH(original) + '</b> เป็น <b>' + displayDateTH(current) + '</b><br>'
                    + 'ต้องการบันทึกหรือไม่?',
                showCancelButton: true,
                confirmButtonText: 'ยืนยันบันทึก',
                cancelButtonText: 'ยกเลิก'
            }).then(function (result) {
                if (result.isConfirmed) submitPlanningItem($btn);
            });
        } else {
            submitPlanningItem($btn);
        }
    });

    // ---- จัดรูปแบบตัวเลขหลักพันสดขณะพิมพ์ (Quantity / Weight / Weight Produced) ----
    // แสดงผลเป็น 1,000.00 แต่ค่าที่บันทึกยังเป็นเลขดิบ (server ตัดจุลภาคออกใน saveItem)
    // ผูกแบบ delegation เพราะฟอร์มถูกโหลดผ่าน AJAX เข้ามาใน modal

    // format สดระหว่างพิมพ์: คงจุลภาคหลักพันในส่วนจำนวนเต็ม + ทศนิยมไม่เกิน 2 ตำแหน่ง
    function formatNumberLive(el) {
        var cleaned = el.value.replace(/[^\d.]/g, '');       // เหลือเฉพาะตัวเลขและจุด
        var firstDot = cleaned.indexOf('.');
        var intRaw, decRaw = null;
        if (firstDot === -1) {
            intRaw = cleaned;
        } else {
            intRaw = cleaned.slice(0, firstDot);
            decRaw = cleaned.slice(firstDot + 1).replace(/\./g, '').slice(0, 2); // ตัดจุดที่เกินมา, ทศนิยม 2 ตำแหน่ง
        }

        intRaw = intRaw.replace(/^0+(?=\d)/, '');             // ตัดเลข 0 นำหน้า
        var intFmt = intRaw.replace(/\B(?=(\d{3})+(?!\d))/g, ','); // ใส่จุลภาคหลักพัน

        var formatted = intFmt;
        if (firstDot !== -1) formatted += '.' + (decRaw || '');

        // คืนตำแหน่งเคอร์เซอร์โดยนับจากด้านขวา (กันเคอร์เซอร์กระโดดเวลาแทรกจุลภาค)
        var caretFromRight = el.value.length - (el.selectionStart || 0);
        el.value = formatted;
        var pos = Math.max(0, Math.min(formatted.length, formatted.length - caretFromRight));
        try { el.setSelectionRange(pos, pos); } catch (e) {}
    }

    // normalize ตอนออกจากช่อง: เติมทศนิยมให้ครบ 2 ตำแหน่ง (ว่าง = คงว่างไว้)
    function formatNumberBlur(el) {
        var cleaned = el.value.replace(/[^\d.]/g, '');
        if (cleaned === '' || cleaned === '.') { el.value = ''; return; }
        var num = parseFloat(cleaned);
        if (isNaN(num)) { el.value = ''; return; }
        el.value = num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    $(document).on('input', '.js-number-format', function () { formatNumberLive(this); });
    $(document).on('blur', '.js-number-format', function () { formatNumberBlur(this); });

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
                    oTable.draw(false); // draw(false) = คงหน้า pagination เดิม (ไม่เด้งกลับหน้า 1)
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
                    oTable.draw(false); // draw(false) = คงหน้า pagination เดิม (ไม่เด้งกลับหน้า 1)
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
