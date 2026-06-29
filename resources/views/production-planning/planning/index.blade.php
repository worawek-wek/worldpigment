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
                                    สร้างแผนการผลิต
                                </button>
                            </div>
                        </div>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-3">
                                <input id="searchInput" type="text" class="form-control"
                                placeholder="ค้นหาเลขที่ใบสั่งซื้อ, รหัสลูกค้า, ขื่อลูกค้า">
                            </div>
                            <div class="col-md-2">
                                <select id="searchCompany" class="form-select">
                                    <option value="">ทุกแผนก</option>
                                    <option value="CP">CP</option>
                                    <option value="DB">DB</option>
                                    <option value="MB">MB</option>
                                    <option value="SPP">SPP</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select">
                                    <option>ทุกสถานะ</option>
                                    <option>Pending</option>
                                    <option>Production</option>
                                    <option>Completed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="card-header">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="col-1">#</th>
                                        <th class="col-2">Planning Code</th>
                                        <th class="col-1">Orderno</th>
                                        <th class="col-1">Company</th>
                                        <th class="col-1">Inplan</th>
                                        <th class="col-1">Custwant</th>
                                        <th class="col-1">Itemno</th>
                                        {{-- <th class="col-1">Quantity</th> --}}
                                        <th class="col-2">MachineNo</th>
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

    <!-- Modal สร้างแผนการผลิต (กรอกเอง) -->
    <div class="modal fade" id="createPlanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background-color:#54BAB9; padding:1rem 1.5rem;">
                    <h5 class="modal-title text-white mb-0">
                        <i class="ti ti-calendar-plus me-1"></i>สร้างแผนการผลิต
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="create_plan_form">
                        {{-- ── ข้อมูล Header ── --}}
                        <h6 class="text-primary mb-2"><i class="ti ti-info-circle me-1"></i>ข้อมูลแผนการผลิต</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Planning Code <span class="text-danger">*</span></label>
                                <input type="text" name="planning_code" class="form-control" placeholder="รหัสแผนการผลิต">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Company / แผนก <span class="text-danger">*</span></label>
                                <select name="company" class="form-select">
                                    <option value="">-- เลือก --</option>
                                    <option value="CP">CP</option>
                                    <option value="DB">DB</option>
                                    <option value="MB">MB</option>
                                    <option value="SPP">SPP</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Plan Type</label>
                                <select name="plan_type" class="form-select">
                                    <option value="">-- เลือก --</option>
                                    @foreach($plan_types as $pt)
                                        <option value="{{ $pt }}">{{ $pt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Order No.</label>
                                <input type="text" name="orderno" class="form-control" placeholder="เลขที่ใบสั่ง">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">รหัสลูกค้า (Cust No.)</label>
                                <input type="text" name="custno" class="form-control" placeholder="รหัสลูกค้า">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sale No.</label>
                                <input type="text" name="saleno" class="form-control" placeholder="เลขที่เซลล์">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">จำนวนสุทธิ (Net Qty)</label>
                                <input type="number" step="any" name="netqty" class="form-control" placeholder="0.00">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">วันที่สั่ง (Mdate)</label>
                                <input type="date" name="mdate" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">วันที่ลูกค้าต้องการ (Custwant)</label>
                                <input type="date" name="custwant" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">วันที่ส่ง (Senddate)</label>
                                <input type="date" name="senddate" class="form-control">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">หมายเหตุ (Remark)</label>
                                <input type="text" name="remark" class="form-control" placeholder="หมายเหตุ">
                            </div>
                        </div>

                        <hr class="my-2">

                        {{-- ── รายการ Planning ── --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-primary mb-0"><i class="ti ti-list-details me-1"></i>รายการ Planning</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_plan_item_row">
                                <i class="ti ti-plus me-1"></i>เพิ่มรายการ
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle" id="table_plan_items">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width:36px">#</th>
                                        <th style="min-width:160px">Item No. <span class="text-danger">*</span></th>
                                        <th style="min-width:100px">Quantity</th>
                                        <th style="min-width:100px">Weight</th>
                                        <th style="min-width:100px">Lot</th>
                                        <th style="min-width:120px">Machine No.</th>
                                        <th class="text-center" style="width:60px">ลบ</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_plan_items"></tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>ยกเลิก
                    </button>
                    <button type="button" class="btn btn-primary" id="btn_save_create_plan">
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
            responsive: true,
            ajax: {
                url: "{{ route('production.planning.datatable') }}",
                data: function(d) {
                    d.search = $('#searchInput').val();
                    d.company = $('#searchCompany').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            columns: [
                { 'className': "text-center", data: 'rownum', name: 'rownum', orderable: false },
                { 'className': "text-left", data: 'planning_code', name: 'planning_code', orderable: false },
                { 'className': "text-center", data: 'orderno', name: 'orderno', orderable: false },
                { 'className': "text-center", data: 'company', name: 'company', orderable: false },
                { 'className': "text-center", data: 'inplan', name: 'inplan', orderable: false },
                { 'className': "text-center", data: 'custwant', name: 'custwant', orderable: false },
                { 'className': "text-left", data: 'itemno', name: 'itemno', orderable: false },
                // { 'className': "text-left", data: 'quantity', name: 'quantity', orderable: false },
                { 'className': "text-left", data: 'machine_no', name: 'machine_no', orderable: false },
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

    $(document).on('change', '#searchCompany', function(e){
        e.preventDefault();
        oTable.draw();
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

    // ---- สร้างแผนการผลิต (กรอกเอง) ----
    var createPlanModal;
    var MACHINES_BY_DEPT = @json($machines_by_dept);

    // สร้าง options เครื่องจักรตามแผนก (company) ที่เลือกในฟอร์ม
    function machineOptions(selected) {
        var company = $('#create_plan_form [name="company"]').val() || '';
        var list = MACHINES_BY_DEPT[company] || [];
        var opts = '<option value="">-- เลือก --</option>';
        list.forEach(function (m) {
            opts += '<option value="' + m + '"' + (m === selected ? ' selected' : '') + '>' + m + '</option>';
        });
        return opts;
    }

    function planItemRow() {
        return '<tr>' +
            '<td class="text-center row-num"></td>' +
            '<td><input type="text" class="form-control form-control-sm" data-f="itemno" placeholder="Item No."></td>' +
            '<td><input type="number" step="any" class="form-control form-control-sm" data-f="quantity" placeholder="0"></td>' +
            '<td><input type="number" step="any" class="form-control form-control-sm" data-f="weight" placeholder="0.00"></td>' +
            '<td><input type="text" class="form-control form-control-sm" data-f="lot" placeholder="Lot"></td>' +
            '<td><select class="form-select form-select-sm" data-f="machine_no">' + machineOptions('') + '</select></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-danger btn_remove_plan_item"><i class="ti ti-trash ti-sm"></i></button></td>' +
            '</tr>';
    }

    // เมื่อเปลี่ยนแผนก → โหลดเครื่องจักรใหม่ทุกแถว (คงค่าที่เลือกไว้ถ้ายังมีในแผนกใหม่)
    $(document).on('change', '#create_plan_form [name="company"]', function () {
        $('#tbody_plan_items tr').each(function () {
            var $sel = $(this).find('[data-f="machine_no"]');
            $sel.html(machineOptions($sel.val()));
        });
    });

    function renumberPlanItems() {
        $('#tbody_plan_items tr').each(function (i) {
            $(this).find('.row-num').text(i + 1);
        });
    }

    function addPlanItemRow() {
        $('#tbody_plan_items').append(planItemRow());
        renumberPlanItems();
    }

    $(document).on('click', '#btn_add_plan_item_row', function () { addPlanItemRow(); });

    $(document).on('click', '.btn_remove_plan_item', function () {
        $(this).closest('tr').remove();
        renumberPlanItems();
    });

    $(document).on('click', '#btn_add', function(e){
        e.preventDefault();
        // reset ฟอร์ม + เริ่มด้วย 1 แถว
        $('#create_plan_form')[0].reset();
        $('#tbody_plan_items').empty();
        addPlanItemRow();
        createPlanModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('createPlanModal'));
        createPlanModal.show();
    });

    $(document).on('click', '#btn_save_create_plan', function () {
        var $btn = $(this);
        var $form = $('#create_plan_form');

        // header fields
        var header = {};
        $form.find('input, select, textarea').each(function () {
            var name = $(this).attr('name');
            if (name) header[name] = $(this).val();
        });

        // ตรวจ required ฝั่ง client
        if (!(header.planning_code || '').trim()) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบ', text: 'กรุณากรอก Planning Code' });
            return;
        }
        if (!(header.company || '').trim()) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบ', text: 'กรุณาเลือก Company / แผนก' });
            return;
        }

        // items
        var items = [];
        $('#tbody_plan_items tr').each(function () {
            var itemno = ($(this).find('[data-f="itemno"]').val() || '').trim();
            if (!itemno) return;
            items.push({
                itemno:     itemno,
                quantity:   $(this).find('[data-f="quantity"]').val()   || '',
                weight:     $(this).find('[data-f="weight"]').val()     || '',
                lot:        $(this).find('[data-f="lot"]').val()        || '',
                machine_no: $(this).find('[data-f="machine_no"]').val() || ''
            });
        });

        if (items.length === 0) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบ', text: 'กรุณาเพิ่มรายการ Planning อย่างน้อย 1 รายการ (ระบุ Item No.)' });
            return;
        }

        var payload = $.extend({}, header, { items: items, _token: '{{ csrf_token() }}' });

        $btn.prop('disabled', true).html('<i class="ti ti-loader me-1"></i>กำลังบันทึก...');
        $.ajax({
            type: 'POST',
            url: '{{ route("production.planning.store") }}',
            dataType: 'json',
            data: payload,
            success: function (response) {
                if (response.status == 200) {
                    createPlanModal.hide();
                    oTable.draw();
                    Swal.fire({ icon: 'success', title: 'สำเร็จ', text: response.message, timer: 1600, showConfirmButton: false });
                    // เปิด modal จัดการแผน เพื่อเพิ่ม Semi/Pigment หรือแก้ไขรายละเอียดต่อ
                    openPlanningModal('', response.planning_header_id);
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

</script>
@endsection
