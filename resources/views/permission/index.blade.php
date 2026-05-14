<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">

<head>
    @include('layout/inc_header')
    <title>Dashboard - CRM | Vuexy - Bootstrap Admin Template</title>

</head>
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

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('layout/inc_sidemenu')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                @include('layout/inc_topmenu')

                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

{{-- ฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝ --}}
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">

            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">

                    <div>
                        <h3 class="mb-1">
                            <i class="ti ti-shield-lock text-primary"></i>
                            สิทธิ์การใช้งาน
                        </h3>

                        <p class="text-muted mb-0">
                            จัดการ Role และ Permission ของผู้ใช้งาน
                        </p>
                    </div>

                    <div class="d-flex gap-2">

                        <button class="btn btn-label-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#roleModal">

                            <i class="ti ti-plus me-1"></i>
                            เพิ่ม Role
                        </button>

                        <button class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#userModal">

                            <i class="ti ti-user-plus me-1"></i>
                            เพิ่มผู้ใช้งาน
                        </button>

                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Summary -->
    <div class="row mb-4">

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">

                    <span class="text-primary fw-semibold">
                        ผู้ใช้งานทั้งหมด
                    </span>

                    <h3 class="mt-2 mb-0">
                        45
                    </h3>

                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">

                    <span class="text-success fw-semibold">
                        Active User
                    </span>

                    <h3 class="mt-2 mb-0">
                        39
                    </h3>

                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">

                    <span class="text-warning fw-semibold">
                        Roles
                    </span>

                    <h3 class="mt-2 mb-0">
                        8
                    </h3>

                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">

                    <span class="text-danger fw-semibold">
                        Disabled
                    </span>

                    <h3 class="mt-2 mb-0">
                        6
                    </h3>

                </div>
            </div>
        </div>

    </div>

    <!-- User Table -->
    <div class="card">

        <div class="card-header border-bottom">

            <div class="row g-3 align-items-center">

                <div class="col-md-3">
                    <input type="text"
                        class="form-control"
                        placeholder="ค้นหาผู้ใช้งาน">
                </div>

                <div class="col-md-2">
                    <select class="form-select">
                        <option>ทุก Role</option>
                        <option>Admin</option>
                        <option>Manager</option>
                        <option>Production</option>
                        <option>Sales</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select class="form-select">
                        <option>ทุกสถานะ</option>
                        <option>Active</option>
                        <option>Disabled</option>
                    </select>
                </div>

            </div>

        </div>

        <div class="table-responsive text-nowrap">

            <table class="table table-hover">

                <thead class="table-light">

                    <tr>

                        <th>Username</th>
                        <th>ชื่อ - นามสกุล</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>สถานะ</th>
                        <th>Last Login</th>
                        <th width="120">จัดการ</th>

                    </tr>

                </thead>

                <tbody>

                    <tr>

                        <td>
                            <strong>admin</strong>
                        </td>

                        <td>
                            สมชาย ใจดี
                        </td>

                        <td>
                            admin@email.com
                        </td>

                        <td>
                            <span class="badge bg-label-primary">
                                Admin
                            </span>
                        </td>

                        <td>
                            <span class="badge bg-label-success">
                                Active
                            </span>
                        </td>

                        <td>
                            07/05/2026 10:30
                        </td>

                        <td>

                            <button class="btn btn-sm btn-icon btn-label-primary">
                                <i class="ti ti-eye"></i>
                            </button>

                            <button class="btn btn-sm btn-icon btn-label-warning">
                                <i class="ti ti-edit"></i>
                            </button>

                        </td>

                    </tr>

                    <tr>

                        <td>
                            <strong>sale01</strong>
                        </td>

                        <td>
                            วิชัย รุ่งเรือง
                        </td>

                        <td>
                            sale@email.com
                        </td>

                        <td>
                            <span class="badge bg-label-success">
                                Sales
                            </span>
                        </td>

                        <td>
                            <span class="badge bg-label-danger">
                                Disabled
                            </span>
                        </td>

                        <td>
                            06/05/2026 15:10
                        </td>

                        <td>

                            <button class="btn btn-sm btn-icon btn-label-primary">
                                <i class="ti ti-eye"></i>
                            </button>

                            <button class="btn btn-sm btn-icon btn-label-warning">
                                <i class="ti ti-edit"></i>
                            </button>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>
{{-- ฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝ --}}
                    <!-- / Content -->

                    <!-- Footer -->
                    @include('layout/inc_footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!--add  Modal -->
<!-- Quotation Modal -->


{{-- ฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝ --}}
<!-- Modal -->
<!-- Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    เพิ่ม Role
                </h5>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <div class="row g-3">

                    <div class="col-md-12">
                        <label class="form-label">
                            ชื่อ Role
                        </label>

                        <input type="text"
                            class="form-control">
                    </div>

                    <div class="col-md-12">

                        <label class="form-label mb-3">
                            Permission
                        </label>

                        <div class="row">

                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox">

                                    <label class="form-check-label">
                                        Quotation
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox">

                                    <label class="form-check-label">
                                        Sale Order
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox">

                                    <label class="form-check-label">
                                        Production
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox">

                                    <label class="form-check-label">
                                        Reports
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox">

                                    <label class="form-check-label">
                                        Customer Database
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox">

                                    <label class="form-check-label">
                                        Permissions
                                    </label>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <button class="btn btn-label-secondary"
                    data-bs-dismiss="modal">

                    ยกเลิก

                </button>

                <button class="btn btn-primary">

                    บันทึกข้อมูล

                </button>

            </div>

        </div>

    </div>

</div>


{{-- ฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝ --}}

    <!--edit  Modal -->
<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    เพิ่มผู้ใช้งาน
                </h5>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">
                            Username
                        </label>

                        <input type="text"
                            class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Password
                        </label>

                        <input type="password"
                            class="form-control">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">
                            ชื่อ - นามสกุล
                        </label>

                        <input type="text"
                            class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Email
                        </label>

                        <input type="email"
                            class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Role
                        </label>

                        <select class="form-select">
                            <option>Admin</option>
                            <option>Manager</option>
                            <option>Sales</option>
                            <option>Production</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            สถานะ
                        </label>

                        <select class="form-select">
                            <option>Active</option>
                            <option>Disabled</option>
                        </select>
                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <button class="btn btn-label-secondary"
                    data-bs-dismiss="modal">

                    ยกเลิก

                </button>

                <button class="btn btn-primary">

                    บันทึกข้อมูล

                </button>

            </div>

        </div>

    </div>

</div>
    <!-- / Layout wrapper -->
    @include('layout/inc_js')
<script>
    var page = "{{$page_url}}/datatable";
        var searchData = {};
        loadData(page);
        
        function loadData(pages){
            
            $('.p_search').each(function() {
                var inputName = $(this).attr('name'); // ดึงชื่อ attribute 'name' ของ input
                var inputValue = $(this).val(); // ดึงค่า value ของ input
                
                searchData[inputName] = inputValue; // เก็บข้อมูลลงในออบเจ็กต์ searchData
            });

            // alert(page);
            page = pages;
            $.ajax({
                type: "GET",
                url: pages,
                data: searchData,
                success: function(data) {
                    $("#table-data").html(data);
                }
            });
            // alert(page);
        }
        var update_id = 999999999999;
        function view(id){
            update_id = id;
            $.ajax({
                type: "GET",
                url: "{{ $page_url }}/"+id,
                success: function(data) {
                    $("#view").html(data);
                    $('#exampleFormControlSelect'+id).select2({
                        placeholder: 'เลือกผู้เช่า',
                        allowClear: true,
                        dropdownParent: $('#editModal'), // 💥 สำคัญมาก ถ้าอยู่ใน modal
                        width: '100%'
                    });
                    // update_id = id;
                }
            });
        }

        var import_id = 999999999999;
        function getImportForm(id){
            import_id = id;
            $.ajax({
                type: "GET",
                url: "{{ $page_url }}/import/"+id,
                success: function(data) {
                    $("#importStock").html(data);
                }
            });
        }

        function getHistory(id){
            $.ajax({
                type: "GET",
                url: "{{ $page_url }}/history/"+id,
                success: function(data) {
                    $("#history-table").html(data);
                }
            });
        }

        $('#insert_category').on('submit', function(event) {
            event.preventDefault(); // ป้องกันการส่งฟอร์มปกติ
            if(!this.checkValidity()) {
                // ถ้าฟอร์มไม่ถูกต้อง
                this.reportValidity();
                return console.log('ฟอร์มไม่ถูกต้อง');
            }
            // return alert(123);
            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: 'คุณต้องการเพิ่มหมวดหมู่หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก',
                showDenyButton: false,
                didOpen: () => {
                    // โฟกัสที่ปุ่ม confirm
                    Swal.getConfirmButton().focus();
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.getElementById('insert_category');
                    let formData = new FormData(form);
                    formData.append('_token', '{{ csrf_token() }}'); // สำหรับ Laravel CSRF

                    $.ajax({
                        url: '{{$page_url}}/insert', // เปลี่ยน URL เป็นจุดหมายที่ต้องการ
                        type: 'POST',
                        data: formData,
                        contentType: false, // ต้องมีเพื่อให้ส่ง multipart/form-data ได้
                        processData: false,
                        success: function(response) {
                            if(response == true){
                                $('#insert_category')[0].reset();
                                Swal.fire('เพิ่มหมวดหมู่เรียบร้อยแล้ว', '', 'success');
                                $('#addModal').modal('hide');
                                loadData(page);
                            }
                        },
                        error: function(error) {
                            Swal.fire('เกิดข้อผิดพลาด', '', 'error');
                            console.error('เกิดข้อผิดพลาด:', error);
                        }
                    });
                } else if (result.isDismissed) {
                    // Swal.fire('ยกเลิกการดำเนินการ', '', 'info');
                }
            });
        });
        $('#update_category').on('submit', function(event) {
            event.preventDefault(); // ป้องกันการส่งฟอร์มปกติ
            if(!this.checkValidity()) {
                // ถ้าฟอร์มไม่ถูกต้อง
                this.reportValidity();
                return console.log('ฟอร์มไม่ถูกต้อง');
            }
            // return alert(123);
            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: 'คุณต้องการแก้ไขหมวดหมู่หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก',
                showDenyButton: false,
                didOpen: () => {
                    // โฟกัสที่ปุ่ม confirm
                    Swal.getConfirmButton().focus();
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    // ใช้ FormData แทน serialize เพื่อส่งไฟล์ได้
                    let form = document.getElementById('update_category');
                    let formData = new FormData(form);
                    formData.append('_token', '{{ csrf_token() }}'); // สำหรับ Laravel CSRF

                    $.ajax({
                        url: '{{$page_url}}/update/'+update_id, // เปลี่ยน URL เป็นจุดหมายที่ต้องการ
                        type: 'POST',
                        data: formData,
                        contentType: false, // ต้องมีเพื่อให้ส่ง multipart/form-data ได้
                        processData: false,
                        success: function(response) {
                            if(response == true){
                                Swal.fire('แก้ไขหมวดหมู่เรียบร้อยแล้ว', '', 'success');
                                $('#editModal').modal('hide');
                                loadData(page);
                            }
                        },
                        error: function(error) {
                            Swal.fire('เกิดข้อผิดพลาด', '', 'error');
                            console.error('เกิดข้อผิดพลาด:', error);
                        }
                    });
                } else if (result.isDismissed) {
                    // Swal.fire('ยกเลิกการดำเนินการ', '', 'info');
                }
            });
        });
        $('#update_equipment_stocks').on('submit', function(event) {
            event.preventDefault(); // ป้องกันการส่งฟอร์มปกติ
            if(!this.checkValidity()) {
                // ถ้าฟอร์มไม่ถูกต้อง
                this.reportValidity();
                return console.log('ฟอร์มไม่ถูกต้อง');
            }
            // return alert(123);
            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: 'คุณต้องการนำเข้าหมวดหมู่หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก',
                showDenyButton: false,
                didOpen: () => {
                    // โฟกัสที่ปุ่ม confirm
                    Swal.getConfirmButton().focus();
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    // ใช้ FormData แทน serialize เพื่อส่งไฟล์ได้
                    let form = document.getElementById('update_equipment_stocks');
                    let formData = new FormData(form);
                    formData.append('_token', '{{ csrf_token() }}'); // สำหรับ Laravel CSRF

                    $.ajax({
                        url: '{{$page_url}}/update_stock/'+import_id, // เปลี่ยน URL เป็นจุดหมายที่ต้องการ
                        type: 'POST',
                        data: formData,
                        contentType: false, // ต้องมีเพื่อให้ส่ง multipart/form-data ได้
                        processData: false,
                        success: function(response) {
                            if(response == true){
                                Swal.fire('นำเข้าหมวดหมู่เรียบร้อยแล้ว', '', 'success');
                                $('#addStock').modal('hide');
                                loadData(page);
                            }
                        },
                        error: function(error) {
                            Swal.fire('เกิดข้อผิดพลาด', '', 'error');
                            console.error('เกิดข้อผิดพลาด:', error);
                        }
                    });
                } else if (result.isDismissed) {
                    // Swal.fire('ยกเลิกการดำเนินการ', '', 'info');
                }
            });
        });
        
        function Delete(id){
                Swal.fire({
                    title: 'ยืนยันการดำเนินการ?',
                    text: 'คุณต้องการลบหมวดหมู่หรือไม่?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'ตกลง',
                    cancelButtonText: 'ยกเลิก',
                    showDenyButton: false,
                    didOpen: () => {
                        // โฟกัสที่ปุ่ม confirm
                        Swal.getConfirmButton().focus();
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{$page_url}}/'+id, // เปลี่ยน URL เป็นจุดหมายที่ต้องการ
                            type: 'DELETE',
                            data: {
                                _token : "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if(response == true){
                                    loadData(page);
                                    Swal.fire('ลบหมวดหมู่เรียบร้อยแล้ว', '', 'success');
                                }else{
                                    Swal.fire('ไม่สามารถลบบัญชีได้', '', 'error');
                                }
                            },
                            error: function(error) {
                                Swal.fire('เกิดข้อผิดพลาด', '', 'error');
                                console.error('เกิดข้อผิดพลาด:', error);
                            }
                        });
                    } else if (result.isDismissed) {
                        // Swal.fire('ยกเลิกการดำเนินการ', '', 'info');
                    }
                });
        }
</script>
</body>

</html>