@extends('./layout/main')


@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-shield-lock text-primary"></i>
                                จัดการสิทธิ์การใช้งาน (Role)
                            </h3>

                            <p class="text-muted mb-0">
                                สร้าง Role และกำหนดเมนูที่เข้าถึงได้ แล้วนำไปกำหนดให้พนักงาน
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
                            <div class="col-md-3">
                                <input id="searchInput" type="text" class="form-control"
                                placeholder="ค้นหาชื่อ Role...">
                            </div>
                            <div class="col-md-3 ms-auto text-end">
                                <button type="button" class="btn btn-primary" id="btn_add">
                                    <i class="ti ti-plus me-1"></i> เพิ่ม Role
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
                                        <th class="col-2">ชื่อ Role</th>
                                        <th class="col-4">รายละเอียด</th>
                                        <th class="col-1">พนักงาน</th>
                                        <th class="col-1">สถานะ</th>
                                        <th class="col-2">จัดการ</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Role Modal (เพิ่ม / แก้ไข) -->
    <div class="modal fade modalHeadDecor" id="roleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-shield-lock me-1"></i>ข้อมูล Role
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="result_detail">
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
                url: "{{ route('role.datatable') }}",
                data: function(d) {
                    d.search = $('#searchInput').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            columns: [
                { 'className': "text-center", data: 'rownum', name: 'rownum', orderable: false },
                { 'className': "text-left", data: 'name', name: 'name', orderable: false },
                { 'className': "text-left", data: 'description', name: 'description', orderable: false },
                { 'className': "text-center", data: 'employees_count', name: 'employees_count', orderable: false, searchable: false },
                { 'className': "text-center", data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                { 'className': "text-center", data: 'btnedit', name: 'btnedit', orderable: false, searchable: false },
            ],
            order: [
                [1, 'asc']
            ]
        });
    });

    $(document).on('keyup', '#searchInput', function(e){
        e.preventDefault();
        oTable.draw();
    });

    function openModal(id) {
        $.ajax({
            url: "{{ route('role.edit') }}",
            method: "GET",
            data: { id: id || '' },
            success: function(response) {
                $('#result_detail').html(response.data);
                $('#roleModal').modal('show');
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

    // บันทึก Role (เพิ่ม/แก้ไข)
    $(document).on('click', '#btn_role_save', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var formData = $('#role_form').serialize();

        $btn.prop('disabled', true);
        $.ajax({
            url: "{{ route('role.store') }}",
            method: "POST",
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.status == 200) {
                    $('#roleModal').modal('hide');
                    oTable.draw();
                    Swal.fire({ icon: 'success', title: 'สำเร็จ', text: response.message,
                        timer: 1800, showConfirmButton: false });
                } else if (response.status == 400) {
                    var msg = Object.values(response.errors).map(function (er) { return er[0]; }).join('<br>');
                    Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ถูกต้อง', html: msg });
                } else {
                    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: response.message || '' });
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
            },
            complete: function() { $btn.prop('disabled', false); }
        });
    });

    // ลบ Role
    $(document).on('click', '.btn_delete', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        Swal.fire({
            icon: 'warning',
            title: 'ยืนยันการลบ',
            text: 'ต้องการลบ Role นี้หรือไม่? พนักงานที่ถูกกำหนด Role นี้จะถูกปลดสิทธิ์',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#d33'
        }).then(function(result){
            if (!result.isConfirmed) return;
            $.ajax({
                url: "{{ route('role.delete') }}",
                method: "POST",
                dataType: 'json',
                data: { _token: "{{ csrf_token() }}", id: id },
                success: function(response) {
                    if (response.status == 200) {
                        oTable.draw();
                        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: response.message,
                            timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: response.message || '' });
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
                }
            });
        });
    });

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
</style>

@endsection
