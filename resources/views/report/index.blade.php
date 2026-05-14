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
                            <i class="ti ti-report-analytics text-primary"></i>
                            รายงาน
                        </h3>

                        <p class="text-muted mb-0">
                            สรุปข้อมูลการขาย การผลิต และใบเสนอราคา
                        </p>
                    </div>

                    <div>
                        <button class="btn btn-success">
                            <i class="ti ti-file-export me-1"></i>
                            Export Excel
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
                        ยอดขายรวม
                    </span>

                    <h3 class="mt-2 mb-0">
                        12.5M
                    </h3>

                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">

                    <span class="text-success fw-semibold">
                        ผลิตสำเร็จ
                    </span>

                    <h3 class="mt-2 mb-0">
                        520
                    </h3>

                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">

                    <span class="text-warning fw-semibold">
                        ค้างผลิต
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

                    <span class="text-info fw-semibold">
                        ใบเสนอราคา
                    </span>

                    <h3 class="mt-2 mb-0">
                        1,250
                    </h3>

                </div>
            </div>
        </div>

    </div>

    <!-- Report Cards -->
    <div class="row g-4">

        <div class="col-md-4">

            <div class="card h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between mb-3">

                        <div>
                            <h5 class="mb-1">
                                รายงานใบเสนอราคา
                            </h5>

                            <p class="text-muted mb-0">
                                สรุปข้อมูลใบเสนอราคาทั้งหมด
                            </p>
                        </div>

                        <i class="ti ti-file-invoice fs-1 text-primary"></i>

                    </div>

                    <button class="btn btn-primary w-100">
                        เปิดรายงาน
                    </button>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between mb-3">

                        <div>
                            <h5 class="mb-1">
                                รายงาน Sale Order
                            </h5>

                            <p class="text-muted mb-0">
                                สรุปยอดขายและคำสั่งขาย
                            </p>
                        </div>

                        <i class="ti ti-shopping-cart fs-1 text-success"></i>

                    </div>

                    <button class="btn btn-success w-100">
                        เปิดรายงาน
                    </button>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between mb-3">

                        <div>
                            <h5 class="mb-1">
                                รายงานการผลิต
                            </h5>

                            <p class="text-muted mb-0">
                                สรุปสถานะและแผนการผลิต
                            </p>
                        </div>

                        <i class="ti ti-settings-automation fs-1 text-warning"></i>

                    </div>

                    <button class="btn btn-warning w-100">
                        เปิดรายงาน
                    </button>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between mb-3">

                        <div>
                            <h5 class="mb-1">
                                รายงานค้างผลิต
                            </h5>

                            <p class="text-muted mb-0">
                                ตรวจสอบรายการที่ยังผลิตไม่เสร็จ
                            </p>
                        </div>

                        <i class="ti ti-alert-circle fs-1 text-danger"></i>

                    </div>

                    <button class="btn btn-danger w-100">
                        เปิดรายงาน
                    </button>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between mb-3">

                        <div>
                            <h5 class="mb-1">
                                รายงานยอดขาย
                            </h5>

                            <p class="text-muted mb-0">
                                วิเคราะห์ยอดขายย้อนหลัง
                            </p>
                        </div>

                        <i class="ti ti-chart-bar fs-1 text-info"></i>

                    </div>

                    <button class="btn btn-info w-100">
                        เปิดรายงาน
                    </button>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between mb-3">

                        <div>
                            <h5 class="mb-1">
                                รายงานลูกค้า
                            </h5>

                            <p class="text-muted mb-0">
                                สรุปข้อมูลลูกค้าและราคา
                            </p>
                        </div>

                        <i class="ti ti-users fs-1 text-dark"></i>

                    </div>

                    <button class="btn btn-dark w-100">
                        เปิดรายงาน
                    </button>

                </div>

            </div>

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
<!-- Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-xl">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    เพิ่มข้อมูลลูกค้า
                </h5>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <div class="row g-3">

                    <div class="col-md-3">
                        <label class="form-label">
                            Customer Code
                        </label>

                        <input type="text"
                            class="form-control"
                            value="CUS-0003">
                    </div>

                    <div class="col-md-9">
                        <label class="form-label">
                            ชื่อลูกค้า
                        </label>

                        <input type="text"
                            class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            เบอร์โทร
                        </label>

                        <input type="text"
                            class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Email
                        </label>

                        <input type="email"
                            class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Tax ID
                        </label>

                        <input type="text"
                            class="form-control">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">
                            ที่อยู่
                        </label>

                        <textarea class="form-control"
                            rows="3"></textarea>
                    </div>

                    <hr class="my-3">

                    <div class="col-md-4">
                        <label class="form-label">
                            รหัสสินค้า
                        </label>

                        <input type="text"
                            class="form-control">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">
                            ชื่อสินค้า
                        </label>

                        <input type="text"
                            class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Price 1
                        </label>

                        <input type="number"
                            class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Price 2
                        </label>

                        <input type="number"
                            class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Price 3
                        </label>

                        <input type="number"
                            class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            สถานะ
                        </label>

                        <select class="form-select">
                            <option>Active</option>
                            <option>Inactive</option>
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
{{-- ฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝ --}}

    <!--edit  Modal -->
    <div class="modal fade modalHeadDecor" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="insert_category" enctype="multipart/form-data">
                @csrf
                <div class="modal-content rounded-0">
                    <div class="modal-header rounded-0">
                        <h5 class="modal-title" id="exampleModalLabel1">เพิ่มข้อมูลหมวดหมู่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    @include('category/form')
                </div>
            </form>
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