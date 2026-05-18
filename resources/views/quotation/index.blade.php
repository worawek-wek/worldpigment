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

                    <div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">

                    <div>
                        <h3 class="mb-1">
                            <i class="ti ti-file-invoice text-primary"></i>
                            ใบเสนอราคา
                        </h3>
                        <p class="text-muted mb-0">
                            จัดการข้อมูลใบเสนอราคาและ Revision
                        </p>
                    </div>

                    <div>
                        <button class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#quotationModal">
                            <i class="ti ti-plus me-1"></i>
                            สร้างใบเสนอราคา
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
                    <span class="fw-semibold text-muted">ทั้งหมด</span>
                    <h3 class="mt-2 mb-0">125</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <span class="fw-semibold text-warning">รออนุมัติ</span>
                    <h3 class="mt-2 mb-0">15</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <span class="fw-semibold text-success">อนุมัติแล้ว</span>
                    <h3 class="mt-2 mb-0">90</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <span class="fw-semibold text-danger">ยกเลิก</span>
                    <h3 class="mt-2 mb-0">20</h3>
                </div>
            </div>
        </div>

    </div>

    <!-- Table -->
    <div class="card">

    <!-- Header -->
    <div class="card-header d-flex justify-content-between align-items-center">

        <div>
            <h4 class="mb-0">
                ใบเสนอราคา
            </h4>

            <small class="text-muted">
                รายการใบเสนอราคาทั้งหมด
            </small>
        </div>

        <button class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#quotationModal">

            <i class="ti ti-plus me-1"></i>
            สร้างใบเสนอราคา

        </button>

    </div>

    <!-- Filter -->
    <div class="card-body border-bottom">

        <div class="row g-3">

            <div class="col-md-2">

                <label class="form-label">
                    เลขที่เอกสาร
                </label>

                <input type="text"
                    class="form-control"
                    placeholder="ค้นหาเลขที่">

            </div>

            <div class="col-md-3">

                <label class="form-label">
                    ลูกค้า
                </label>

                <input type="text"
                    class="form-control"
                    placeholder="ค้นหาลูกค้า">

            </div>

            <div class="col-md-2">

                <label class="form-label">
                    ชนิดสินค้า
                </label>

                <select class="form-select">

                    <option value="">
                        ทั้งหมด
                    </option>

                    <option>
                        MB
                    </option>

                    <option>
                        DB
                    </option>

                    <option>
                        CP
                    </option>

                </select>

            </div>

            <div class="col-md-2">

                <label class="form-label">
                    วันที่เริ่มต้น
                </label>

                <input type="date"
                    class="form-control">

            </div>

            <div class="col-md-2">

                <label class="form-label">
                    วันที่สิ้นสุด
                </label>

                <input type="date"
                    class="form-control">

            </div>

            <div class="col-md-1 d-flex align-items-end">

                <button class="btn btn-label-primary w-100">

                    <i class="ti ti-search"></i>

                </button>

            </div>

        </div>

    </div>

    <!-- Table -->
    <div class="table-responsive">

        <table class="table table-bordered table-hover align-middle mb-0">

            <thead class="table-light">

                <tr>

                    <th width="60" class="text-center">
                        #
                    </th>

                    <th width="150">
                        เลขที่ใบเสนอราคา
                    </th>

                    <th width="120">
                        วันที่
                    </th>

                    <th>
                        ลูกค้า
                    </th>

                    <th width="100" class="text-center">
                        ชนิดสินค้า
                    </th>

                    <th width="120" class="text-end">
                        จำนวนรายการ
                    </th>

                    <th width="140" class="text-end">
                        มูลค่ารวม
                    </th>

                    <th width="120" class="text-center">
                        สถานะ
                    </th>

                    <th width="180" class="text-center">
                        จัดการ
                    </th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td class="text-center">
                        1
                    </td>

                    <td class="fw-bold text-primary">
                        WH690270
                    </td>

                    <td>
                        22/05/2026
                    </td>

                    <td>
                        บริษัท วนวิทย์ แมนูแฟคเจอริ่ง จำกัด
                    </td>

                    <td class="text-center">
                        MB
                    </td>

                    <td class="text-end">
                        5
                    </td>

                    <td class="text-end fw-bold">
                        58,450.00
                    </td>

                    <td class="text-center">

                        <span class="badge bg-label-success">
                            ใช้งาน
                        </span>

                    </td>

                    <td class="text-center">

                        <div class="d-flex justify-content-center gap-1">

                            <button class="btn btn-sm btn-icon btn-label-primary">

                                <i class="ti ti-eye"></i>

                            </button>

                            <button class="btn btn-sm btn-icon btn-label-warning">

                                <i class="ti ti-edit"></i>

                            </button>

                            <button class="btn btn-sm btn-icon btn-label-danger">

                                <i class="ti ti-trash"></i>

                            </button>

                            <button class="btn btn-sm btn-icon btn-label-info">

                                <i class="ti ti-printer"></i>

                            </button>

                        </div>

                    </td>

                </tr>

                <tr>

                    <td class="text-center">
                        2
                    </td>

                    <td class="fw-bold text-primary">
                        WH690271
                    </td>

                    <td>
                        22/05/2026
                    </td>

                    <td>
                        บริษัท ไทยโพลีเมอร์ จำกัด
                    </td>

                    <td class="text-center">
                        DB
                    </td>

                    <td class="text-end">
                        2
                    </td>

                    <td class="text-end fw-bold">
                        12,800.00
                    </td>

                    <td class="text-center">

                        <span class="badge bg-label-secondary">
                            ยกเลิก
                        </span>

                    </td>

                    <td class="text-center">

                        <div class="d-flex justify-content-center gap-1">

                            <button class="btn btn-sm btn-icon btn-label-primary">

                                <i class="ti ti-eye"></i>

                            </button>

                            <button class="btn btn-sm btn-icon btn-label-warning">

                                <i class="ti ti-edit"></i>

                            </button>

                            <button class="btn btn-sm btn-icon btn-label-danger">

                                <i class="ti ti-trash"></i>

                            </button>

                            <button class="btn btn-sm btn-icon btn-label-info">

                                <i class="ti ti-printer"></i>

                            </button>

                        </div>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

    <!-- Footer -->
    <div class="card-footer d-flex justify-content-between align-items-center">

        <div class="text-muted">
            แสดง 1 ถึง 2 จากทั้งหมด 2 รายการ
        </div>

        <nav>

            <ul class="pagination pagination-sm mb-0">

                <li class="page-item disabled">
                    <a class="page-link">
                        Previous
                    </a>
                </li>

                <li class="page-item active">
                    <a class="page-link">
                        1
                    </a>
                </li>

                <li class="page-item disabled">
                    <a class="page-link">
                        Next
                    </a>
                </li>

            </ul>

        </nav>

    </div>

</div>

</div>
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
<div class="modal modalHeadDecor fade" id="quotationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    สร้างใบเสนอราคา
                </h5>

                <button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-5">

{{-- ////////////////////////////////////////////////////////////////////////////////////////// --}}
            <!-- Top Form -->
            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label">
                        เลขที่ใบเสนอราคา
                    </label>

                    <input type="text"
                        class="form-control"
                        placeholder="กรอกเลขที่ใบเสนอราคา"
                        value="WH690270">
                </div>
                <div class="col-md-8">

                </div>
                <div class="col-md-2">
                    <label class="form-label">
                        วันที่เสนอราคา
                    </label>

                    <input type="date"
                        class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label text-danger">
                        Revise Date
                    </label>

                    <input type="date"
                        class="form-control">
                </div>


                <div class="col-md-8"></div>
                <div class="col-md-2">
                    <label class="form-label">
                        ชนิดสินค้า
                    </label>

                    <select class="form-select">
                        <option>MB</option>
                        <option>DB</option>
                        <option>CP</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check">

                        <input class="form-check-input" id="example"
                            type="checkbox">

                        <label class="form-check-label" for="example">
                            พร้อมตัวอย่าง
                        </label>

                    </div>
                </div>
                <div class="col-md-8"></div>

                <div class="col-md-2">
                    <label class="form-label">
                        รหัสพนักงานขาย
                    </label>
                    <input type="text"
                            class="form-control"
                            value="9961">
                </div>
                <div class="col-md-1">
                    <label class="form-label">
                        &nbsp;
                    </label>
                        <input type="text"
                            class="form-control text-center"
                            value="8">
                </div>
                <div class="col-md-4">
                    <label class="form-label">
                        &nbsp;
                    </label>
                        <input type="text"
                            class="form-control"
                            value="มานะ พงษ์ชูธนโชคภากร">

                </div>
                <div class="col-md-5"></div>
                <div class="col-md-2">
                    <label class="form-label">
                        รหัสลูกค้า
                    </label>

                    <select class="form-select">
                        <option>36017</option>
                    </select>
                </div>

                <div class="col-md-9">
                    <label class="form-label">
                        ชื่อลูกค้า
                    </label>

                    <input type="text"
                        class="form-control text-primary fw-bold"
                        value="บริษัท วนวิทย์ แมนูแฟคเจอริ่ง จำกัด">
                </div>

            </div>

            <!-- Price Buttons -->
            <div class="row mt-4">

                <div class="col-md-12">

                    <div class="d-flex flex-wrap gap-2">

                        <button class="btn btn-label-primary">
                            ใบเสนอราคา &lt; 12 รายการ
                        </button>

                        <button class="btn btn-label-primary">
                            ใบเสนอราคา 13 - 15 รายการ
                        </button>

                        <button class="btn btn-danger">
                            ขึ้นราคา &lt; 5 รายการ
                        </button>

                        <button class="btn btn-danger">
                            ขึ้นราคา 6-10 รายการ
                        </button>

                    </div>

                </div>

            </div>

            <!-- Product Table -->
            <div class="table-responsive mt-4">

                <table class="table table-bordered align-middle">

                    <thead class="table-light">

                        <tr>

                            <th width="150">
                                รหัสสินค้า
                            </th>

                            <th>
                                ชื่อสินค้า
                            </th>

                            <th width="150">
                                ราคาเก่า
                            </th>

                            <th width="150">
                                ราคาใหม่
                            </th>

                            <th width="150">
                                ราคารวมภาษี
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr>

                            <td>
                                <input type="text"
                                    class="form-control"
                                    value="1908053">
                            </td>

                            <td>
                                <input type="text"
                                    class="form-control"
                                    value="MB BLUE-J [MB POM RAL 2308520]">
                            </td>

                            <td>
                                <input type="number"
                                    class="form-control text-end"
                                    value="275.00">
                            </td>

                            <td>
                                <input type="number"
                                    class="form-control text-end"
                                    value="285.00">
                            </td>

                            <td>
                                <input type="number"
                                    class="form-control text-end"
                                    value="304.95">
                            </td>

                        </tr>

                        <tr>

                            <td>
                                <input type="text"
                                    class="form-control">
                            </td>

                            <td>
                                <input type="text"
                                    class="form-control">
                            </td>

                            <td>
                                <input type="number"
                                    class="form-control text-end"
                                    value="0.00">
                            </td>

                            <td>
                                <input type="number"
                                    class="form-control text-end"
                                    value="0.00">
                            </td>

                            <td>
                                <input type="number"
                                    class="form-control text-end"
                                    value="0.00">
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

            <!-- Bottom Form -->
            <div class="row g-3 mt-3">

                <div class="col-md-3">
                    <label class="form-label">
                        MB ควรกำหนดยอดซื้อขั้นต่ำ (ก.ก.)
                    </label>

                    <input type="text"
                        class="form-control text-center"
                        value="-">
                </div>

                <div class="col-md-2">
                    <label class="form-label">
                        Payment Term
                    </label>

                    <input type="text"
                        class="form-control text-center"
                        value="90 วัน">
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        ยืนราคาถึงวันที่
                    </label>

                    <input type="date"
                        class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        ผู้เสนอราคา
                    </label>

                    <input type="text"
                        class="form-control"
                        value="Wanawit Manufacturing">
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        ส่งสินค้าได้ภายใน
                    </label>

                    <input type="text"
                        class="form-control"
                        placeholder="วัน">
                </div>

            </div>

{{-- ////////////////////////////////////////////////////////////////////////////////////////// --}}

            </div>

            <div class="modal-footer">

                <button class="btn btn-label-secondary"
                    data-bs-dismiss="modal">
                    ยกเลิก
                </button>

                <button class="btn btn-primary">
                    บันทึกใบเสนอราคา
                </button>

            </div>

        </div>
    </div>
</div>

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