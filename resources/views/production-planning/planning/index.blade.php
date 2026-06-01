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
                                        <th class="col-1">Orderno</th>
                                        <th class="col-1">Company</th>
                                        <th class="col-2">Mdate</th>
                                        <th class="col-2">Custwant</th>
                                        <th class="col-2">Itemno</th>
                                        {{-- <th class="col-1">Quantity</th> --}}
                                        <th class="col-2">MachineNo</th>
                                        <th class="col-2">Manage</th>
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
                { 'className': "text-center", data: 'orderno', name: 'orderno', orderable: false },
                { 'className': "text-center", data: 'company', name: 'company', orderable: false },
                { 'className': "text-center", data: 'mdate', name: 'mdate', orderable: false },
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

    $(document).on('click', '#btn_add', function(e){
        e.preventDefault();
        openPlanningModal('', '');
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
