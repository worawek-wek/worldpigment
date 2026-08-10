@extends('./layout/main')


@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-users text-primary"></i>
                                จัดการพนักงาน
                            </h3>

                            <p class="text-muted mb-0">
                                ข้อมูลพนักงาน (เพิ่ม / แก้ไข / ลบ / ค้นหา)
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
                                placeholder="ค้นหา รหัส / ชื่อ / นามสกุล...">
                            </div>
                            <div class="col-md-3">
                                <select id="searchDept" class="form-select">
                                    <option value="">ทุกแผนก</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->name }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 ms-auto text-end">
                                <button type="button" class="btn btn-primary" id="btn_add"
                                    data-bs-target="#employeeModal">
                                    <i class="ti ti-plus me-1"></i> เพิ่มพนักงาน
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
                                        <th class="col-1">รหัสพนักงาน</th>
                                        <th class="col-2">ชื่อ</th>
                                        <th class="col-2">นามสกุล</th>
                                        <th class="col-1">แผนก</th>
                                        <th class="col-1">Role</th>
                                        <th class="col-1">ผู้ใช้</th>
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

    <!-- Employee Modal (เพิ่ม / แก้ไข) -->
    <div class="modal fade modalHeadDecor" id="employeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-user me-1"></i>ข้อมูลพนักงาน
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
                url: "{{ route('employee.datatable') }}",
                data: function(d) {
                    d.search = $('#searchInput').val();
                    d.dept = $('#searchDept').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            // เปิดให้คลิกหัวคอลัมน์เพื่อ sort ได้ (Yajra จะ ORDER BY ตาม name ของคอลัมน์ให้อัตโนมัติ)
            // ใช้ชื่อคอลัมน์แบบ qualify (emp.* / roles.*) เพราะคิวรีมี join ตาราง roles
            columns: [
                // # เป็นเลขลำดับที่นับหลัง query จึง sort ไม่ได้ (จะ renumber ตามผลลัพธ์ที่เรียงแล้ว)
                { 'className': "text-center", data: 'rownum', name: 'rownum', orderable: false },
                { 'className': "text-center", data: 'empno', name: 'emp.empno', orderable: true },
                { 'className': "text-center", data: 'empname', name: 'emp.empname', orderable: true },
                { 'className': "text-center", data: 'empsur', name: 'emp.empsur', orderable: true },
                // sort แผนก/สถานะ/role ตามคอลัมน์จริง (แม้จะแสดงผ่าน addColumn)
                { 'className': "text-center", data: 'department_name', name: 'emp.dept', orderable: true, searchable: false },
                { 'className': "text-center", data: 'role_name', name: 'roles.name', orderable: true, searchable: false },
                { 'className': "text-center", data: 'user', name: 'emp.user', orderable: true },
                { 'className': "text-center", data: 'status_badge', name: 'emp.is_active', orderable: true, searchable: false },
                { 'className': "text-center", data: 'btnedit', name: 'btnedit', orderable: false, searchable: false },
            ],
            // เริ่มต้นเรียงตามรหัสพนักงาน (empno) เหมือนลำดับเดิมที่เคย hard-code ใน controller
            order: [
                [1, 'asc']
            ]
        });
    });

    $(document).on('keyup', '#searchInput', function(e){
        e.preventDefault();
        oTable.draw();
    });

    // เปลี่ยนแผนกใน dropdown → ค้นหาใหม่
    $(document).on('change', '#searchDept', function(e){
        e.preventDefault();
        oTable.draw();
    });

    // เพิ่มพนักงาน — เปิด modal ฟอร์มเปล่า
    $(document).on('click', '#btn_add', function(e){
        e.preventDefault();
        $.ajax({
            url: "{{ route('employee.edit') }}",
            method: "GET",
            data: { empno: null },
            success: function(response) {
                $('#result_detail').html(response.data);
                $('#employeeModal').modal('show');
            }
        });
    });

    // แก้ไขพนักงาน — เปิด modal พร้อมข้อมูลเดิม
    $(document).on('click', '.btn_edit', function(e){
        e.preventDefault();
        var empno = $(this).data('empno');
        $.ajax({
            url: "{{ route('employee.edit') }}",
            method: "GET",
            data: { empno: empno },
            success: function(response) {
                $('#result_detail').html(response.data);
                $('#employeeModal').modal('show');
            }
        });
    });

    // พรีวิวรูปลายเซ็นเมื่อเลือกไฟล์
    $(document).on('change', '#employee_signature', function(e) {
        var input = e.target;
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(ev) {
                $('#employee_signature_preview').attr('src', ev.target.result);
                $('#employee_signature_box').show();
            };
            reader.readAsDataURL(input.files[0]);
            // เลือกไฟล์ใหม่ = ยกเลิกคำสั่งลบ
            $('#employee_remove_signature').val('0');
        }
    });

    // ลบรูปลายเซ็น — ล้างไฟล์ที่เลือก/ซ่อนพรีวิว และตั้ง flag ให้ลบรูปเดิมตอนบันทึก
    $(document).on('click', '#btn_remove_signature', function(e) {
        e.preventDefault();
        $('#employee_signature').val('');
        $('#employee_signature_preview').attr('src', '');
        $('#employee_signature_box').hide();
        $('#employee_remove_signature').val('1');
    });

    // บันทึกพนักงาน (เพิ่ม/แก้ไข) — ใช้ FormData เพื่อรองรับการอัพโหลดไฟล์ลายเซ็น
    $(document).on('click', '#btn_employee_save', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var formData = new FormData(document.getElementById('employee_form'));

        $btn.prop('disabled', true);
        $.ajax({
            url: "{{ route('employee.store') }}",
            method: "POST",
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status == 200) {
                    $('#employeeModal').modal('hide');
                    oTable.draw();
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });
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

    // ลบพนักงาน — ยืนยันก่อนลบ
    $(document).on('click', '.btn_delete', function(e){
        e.preventDefault();
        var empno = $(this).data('empno');

        Swal.fire({
            icon: 'warning',
            title: 'ยืนยันการลบ',
            text: 'ต้องการลบพนักงานรหัส ' + empno + ' หรือไม่?',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#d33'
        }).then(function(result){
            if (!result.isConfirmed) return;
            $.ajax({
                url: "{{ route('employee.delete') }}",
                method: "POST",
                dataType: 'json',
                data: { _token: "{{ csrf_token() }}", empno: empno },
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
