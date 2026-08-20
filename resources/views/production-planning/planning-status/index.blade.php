@extends('./layout/main')


@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-list-check text-primary"></i>
                                สถานะ Planning
                            </h3>

                            <p class="text-muted mb-0">
                                จัดการรายการสถานะการวางแผนการผลิต
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
                                    เพิ่มสถานะ
                                </button>
                            </div>
                        </div>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-3">
                                <input id="searchInput" type="text" class="form-control"
                                placeholder="ค้นหาชื่อสถานะ">
                            </div>
                            <div class="col-md-3">
                                <select id="searchDept" class="form-select">
                                    <option value="">ทุกแผนก</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-header">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        {{-- <th class="col-1">#</th> --}}
                                        <th class="col-1">แผนก (Dept)</th>
                                        <th class="col-4">ชื่อสถานะ</th>
                                        <th class="col-1">ลำดับ</th>
                                        <th class="col-2">สถานะ</th>
                                        <th class="col-1">จัดการ</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Modal: เพิ่ม / แก้ไข สถานะ Planning -->
    <div class="modal fade" id="planningStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content" id="result_detail"></div>
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
                url: "{{ route('production.planningstatus.datatable') }}",
                data: function(d) {
                    d.search = $('#searchInput').val();
                    d.dept = $('#searchDept').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            // เปิดให้คลิกหัวคอลัมน์เพื่อ sort ได้ (Yajra จะ ORDER BY ตาม name ของคอลัมน์ให้อัตโนมัติ)
            // ใช้ชื่อคอลัมน์แบบ qualify (tb_planning_status.* / tb_departments.*) เพราะคิวรี join 2 ตาราง
            // (name / sort / is_active มีทั้งสองตาราง จึงต้องระบุตารางกัน ambiguous column)
            columns: [
                // แผนก: sort ตามชื่อแผนกที่ join มา (tb_departments.name)
                { 'className': "text-left", data: 'dept_name', name: 'tb_departments.name', orderable: true, searchable: false },
                { 'className': "text-left", data: 'name', name: 'tb_planning_status.name', orderable: true },
                { 'className': "text-center", data: 'sort', name: 'tb_planning_status.sort', orderable: true },
                // สถานะ: sort ตามคอลัมน์จริง is_active (แม้จะแสดงเป็น badge)
                { 'className': "text-center", data: 'is_active_label', name: 'tb_planning_status.is_active', orderable: true, searchable: false },
                { 'className': "text-center", data: 'btnedit', name: 'btnedit', orderable: false, searchable: false },
            ],
            // เริ่มต้นเรียงตามคอลัมน์ "ลำดับ" (sort) เหมือนลำดับเดิมที่เคย hard-code ใน controller
            order: [
                [2, 'asc']
            ]
        });
    });

    $(document).on('keyup', '#searchInput', function(e){
        e.preventDefault();
        oTable.draw();
    });

    $(document).on('change', '#searchDept', function(e){
        e.preventDefault();
        oTable.draw();
    });

    // ---- Modal เพิ่ม / แก้ไข ----

    function openModal(id) {
        $.ajax({
            type: 'GET',
            url: '{{ route("production.planningstatus.edit") }}',
            dataType: 'json',
            cache: false,
            data: { id: id || '' },
            success: function(response) {
                if (response.status == 200) {
                    $('#result_detail').html(response.data);
                    var modal = new bootstrap.Modal(document.getElementById('planningStatusModal'));
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
        openModal('');
    });

    $(document).on('click', '.btn_edit', function(e){
        e.preventDefault();
        openModal($(this).data('id'));
    });

    // ---- บันทึก ----
    $(document).on('click', '#btn_save_planning_status', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var $form = $('#planning_status_form');
        var formData = $form.serialize() + '&_token={{ csrf_token() }}';

        $btn.prop('disabled', true).html('<i class="ti ti-loader me-1"></i>กำลังบันทึก...');

        $.ajax({
            type: 'POST',
            url: '{{ route("production.planningstatus.save") }}',
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.status == 200) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('planningStatusModal'));
                    if (modal) modal.hide();

                    oTable.draw(false); // draw(false) = คงหน้า pagination เดิม (ไม่เด้งกลับหน้า 1)

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
