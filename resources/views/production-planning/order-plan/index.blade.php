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
                                แผนการผลิต Order
                            </h3>

                            <p class="text-muted mb-0">
                                รายการแผนการผลิตหลัก (Order) และรายละเอียดที่เกี่ยวข้อง
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="col-12 mt-4">
                <div class="card">

                    <div class="card-header">
                        {{-- แถวที่ 1: ค้นหาข้อความ + แผนก + สถานะปิดงาน --}}
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small mb-1">ค้นหา</label>
                                <input id="searchInput" type="text" class="form-control"
                                placeholder="รหัส Order, รหัสลูกค้า, ชื่อลูกค้า, ชื่อพนักงานผู้รับผิดชอบ">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">รหัส Sale</label>
                                <input id="searchSaleno" type="text" class="form-control" placeholder="saleno">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">แผนก</label>
                                <select id="searchCompany" class="form-select">
                                    <option value="">ทุกแผนก</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->name }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- สถานะปิดงาน (อ้างอิงคอลัมน์ end_order ของ tb_planning_header) — ค่าเริ่มต้น: ยังไม่ปิดงาน --}}
                            <div class="col-md-3">
                                <label class="form-label small mb-1">สถานะปิดorder</label>
                                <select id="searchEndOrder" class="form-select">
                                    {{-- ใช้ค่า 'all' (ไม่ใช่ค่าว่าง) เพื่อไม่ให้ backend เข้าใจผิดว่าไม่ได้ส่งค่ามา แล้วตกไปใช้ค่าเริ่มต้น --}}
                                    <option value="all">ทั้งหมด</option>
                                    <option value="Y">ปิดงาน</option>
                                    <option value="N" selected>ยังไม่ปิดงาน</option>
                                </select>
                            </div>
                        </div>
                        {{-- แถวที่ 2: ช่วงวันที่ Inplan / Custwant + ล้างตัวกรอง --}}
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Inplan (วันเริ่ม – สิ้นสุด)</label>
                                <div class="d-flex gap-1">
                                    <input id="inplanStart" type="text" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป" title="Inplan เริ่ม">
                                    <input id="inplanEnd" type="text" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป" title="Inplan สิ้นสุด">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Custwant (วันเริ่ม – สิ้นสุด)</label>
                                <div class="d-flex gap-1">
                                    <input id="custwantStart" type="text" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป" title="Custwant เริ่ม">
                                    <input id="custwantEnd" type="text" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป" title="Custwant สิ้นสุด">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button id="btnClearFilter" type="button" class="btn btn-label-secondary w-100" title="ล้างตัวกรอง">
                                    <i class="ti ti-eraser"></i>
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
                                        <th class="col-1">Orderno</th>
                                        <th class="col-1">Company</th>
                                        <th class="col-1 col-inplan">Inplan</th>
                                        <th class="col-1 col-custwant">Custwant</th>
                                        <th class="col-1">Custno</th>
                                        <th class="col-2">ชื่อลูกค้า</th>
                                        <th class="col-2">Item No</th>
                                        <th class="col-2">สถานะ</th>
                                        <th class="col-1">รายการ</th>
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

    <!-- Order Plan Detail Modal -->
    <div class="modal fade" id="orderPlanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" id="result_detail"></div>
        </div>
    </div>
@endsection

@section('script')
<script>

    var oTable;
    // ช่องวันที่ทั้งหมด (ใช้ทั้ง init flatpickr และเคลียร์ตอนกดล้าง)
    var DATE_FILTER_SEL = '#inplanStart, #inplanEnd, #custwantStart, #custwantEnd';
    $(document).ready(function () {
        // flatpickr: แสดง d/m/Y เหมือนกันทุกเครื่อง แต่ค่าจริง (input.value) ยังเป็น Y-m-d
        // → ค่าที่ส่งให้ DataTable/server ยังเป็น Y-m-d เหมือนเดิม ไม่ต้องแก้ฝั่ง PHP
        var fpOptions = {
            dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', allowInput: true, disableMobile: true
        };
        $(DATE_FILTER_SEL).each(function () {
            if (!this._flatpickr) flatpickr(this, fpOptions);
        });

        oTable = $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ordering: true,
            lengthChange: false,
            responsive: true,
            ajax: {
                url: "{{ route('production.orderplan.datatable') }}",
                data: function(d) {
                    d.search = $('#searchInput').val();
                    d.saleno = $('#searchSaleno').val();
                    d.company = $('#searchCompany').val();
                    // ส่งค่าเสมอ ('all' | 'Y' | 'N') — ถ้าหา element ไม่เจอให้ใช้ค่าเริ่มต้น 'N'
                    d.end_order = $('#searchEndOrder').val() || 'N';
                    d.inplan_start = $('#inplanStart').val();
                    d.inplan_end = $('#inplanEnd').val();
                    d.custwant_start = $('#custwantStart').val();
                    d.custwant_end = $('#custwantEnd').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            columns: [
                { 'className': "text-center", data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { 'className': "text-center", data: 'orderno', name: 'orderno' },
                { 'className': "text-center", data: 'company', name: 'company' },
                { 'className': "text-center col-inplan", data: 'inplan', name: 'inplan', searchable: false },
                { 'className': "text-center col-custwant", data: 'custwant', name: 'custwant' },
                { 'className': "text-left", data: 'custno', name: 'custno' },
                { 'className': "text-left", data: 'custname', name: 'custname', searchable: false },
                { 'className': "text-left", data: 'itemno_list', name: 'itemno_list', orderable: false, searchable: false },
                { 'className': "text-left", data: 'status_list', name: 'status_list', orderable: false, searchable: false },
                { 'className': "text-center", data: 'item_count', name: 'item_count', searchable: false },
                { 'className': "text-center", data: 'btnedit', name: 'btnedit', orderable: false, searchable: false },
            ],
            order: [
                [3, 'desc']  // ค่าเริ่มต้น: เรียงตาม Inplan ล่าสุด → เก่าสุด
            ],
            initComplete: function(settings, json) {
                console.log('DataTable loaded');
            }
        });
    });

    $(document).on('keyup', '#searchInput', function(e){
        e.preventDefault();
        oTable.draw();
    });

    // ค้นหาด้วยรหัส Sale (saleno)
    $(document).on('keyup', '#searchSaleno', function(e){
        e.preventDefault();
        oTable.draw();
    });

    $(document).on('change', '#searchCompany', function(e){
        e.preventDefault();
        oTable.draw();
    });

    // เปลี่ยนสถานะปิดงาน (ทั้งหมด / ปิดงาน / ยังไม่ปิดงาน) → ค้นหาใหม่
    $(document).on('change', '#searchEndOrder', function(e){
        e.preventDefault();
        oTable.draw();
    });

    // ค้นหาช่วงวันที่ Inplan / Custwant (ปฏิทินเลือกวันเริ่ม–สิ้นสุด)
    $(document).on('change', '#inplanStart, #inplanEnd, #custwantStart, #custwantEnd', function(){
        oTable.draw();
    });

    // ล้างตัวกรองทั้งหมด
    $(document).on('click', '#btnClearFilter', function(){
        $('#searchInput').val('');
        $('#searchSaleno').val('');
        $('#searchCompany').val('');
        // สถานะปิดงานกลับไปที่ค่าเริ่มต้น (ยังไม่ปิดงาน)
        $('#searchEndOrder').val('N');
        // ช่องวันที่เป็น flatpickr → เคลียร์ผ่าน instance; clear(false) กัน redraw ซ้ำ แล้ว draw ครั้งเดียว
        $(DATE_FILTER_SEL).each(function () {
            if (this._flatpickr) this._flatpickr.clear(false); else this.value = '';
        });
        oTable.draw();
    });

    $(document).on('click', '.btn_view', function(e){
        e.preventDefault();
        let planning_header_id = $(this).data('planning_header_id');
        $.ajax({
            type: 'GET',
            url: '{{ route("production.orderplan.detail") }}',
            dataType: 'json',
            cache: false,
            data: {
                planning_header_id: planning_header_id
            },
            success: function(response) {
                if (response.status == 200) {
                    $('#result_detail').html(response.data);
                    // ใช้ getOrCreateInstance กันสร้าง instance/backdrop ซ้อนเมื่อกดเปิดรัว ๆ (พื้นดำค้าง)
                    var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('orderPlanModal'));
                    modal.show();
                }
            },
            error: function(response) {
                console.log("error", response.responseJSON);
            }
        });
    });

</script>
@endsection
