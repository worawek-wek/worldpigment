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
                            จัดการใบเสนอราคาและ Revision
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quotationModal">
                            <i class="ti ti-plus me-1"></i>
                            สร้างใบเสนอราคา
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">

        <div class="card-header border-bottom">

            {{-- แถวตัวกรอง 1: ค้นหา + ช่วงวันที่ --}}
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small fw-medium mb-1">ค้นหา</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" name="search" class="form-control p_search"
                            placeholder="เลขที่ใบเสนอราคา / รหัสลูกค้า / ชื่อลูกค้า"
                            oninput="loadData(page)">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-medium mb-1">วันที่เสนอราคา</label>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small fw-medium">ตั้งแต่</span>
                        <input type="date" name="date_from" class="form-control p_search" autocomplete="off" onchange="loadData(page)">
                        <span class="small fw-medium">ถึง</span>
                        <input type="date" name="date_to" class="form-control p_search" autocomplete="off" onchange="loadData(page)">
                    </div>
                </div>
            </div>

            {{-- แถวตัวกรอง 2: ชนิดสินค้า --}}
            <div class="row g-3 align-items-end mt-1">
                <div class="col-md-4">
                    <label class="form-label small fw-medium mb-1">ชนิดสินค้า</label>
                    <select name="product_type" class="form-select p_search" onchange="loadData(page)">
                        <option value="">ทั้งหมด</option>
                        @foreach ($pdtypes as $pt)
                            <option value="{{ $pt->PDType }}">{{ $pt->PDType }} — {{ $pt->PDHead1 }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end my-3">
                <button type="button" id="btnResetFilters" class="btn btn-label-secondary" onclick="resetFilters()">
                    <i class="ti ti-x me-1"></i>ล้างตัวกรอง<span class="filter-count ms-1"></span>
                </button>
            </div>

            {{-- จำนวนต่อหน้า (มุมขวาล่าง) --}}
            <div class="d-flex justify-content-end align-items-center mt-3 pt-3 border-top">
                <label class="form-label small fw-medium mb-0 me-2">แสดง</label>
                <select name="limit" class="form-select form-select-sm p_search" style="width: 90px;"
                    onchange='loadData("{{$page_url}}/datatable")'>
                    <option value="15">15</option>
                    <option value="50">50</option>
                    <option value="75">75</option>
                    <option value="100">100</option>
                </select>
                <span class="ms-2 small fw-medium">รายการ/หน้า</span>
            </div>
        </div>

        <div id="table-data">
            {{-- Table โหลดผ่าน AJAX จาก quotation/datatable --}}
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm me-2"></div>
                กำลังโหลดข้อมูล...
            </div>
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

                <div class="col-md-5">
                    <label class="form-label">
                        ชื่อลูกค้า
                    </label>

                    <input type="text"
                        class="form-control text-primary fw-bold"
                        value="บริษัท วนวิทย์ แมนูแฟคเจอริ่ง จำกัด">
                </div>
                <div class="col-md-5">
                    <label class="form-label">
                        ชื่อลูกค้า(ภาษาอังกฤษ)
                    </label>

                    <input type="text"
                        class="form-control"
                        value="Wanawit Manufacturing">
                </div>

            </div>

            <!-- Price Buttons -->
            <div class="row mt-4">

                <div class="col-md-12">

                    <div class="d-flex flex-wrap gap-2">

                        <button type="button" class="btn btn-label-warning" onclick="addQuotationItem()">
                            <i class="ti ti-plus me-1"></i>
                            เพิ่มรายการ
                        </button>

                    </div>

                </div>

            </div>

            <!-- Product Table -->
            <div class="table-responsive mt-4">

                <table class="table table-bordered align-middle" id="quotationItemsTable">

                    <thead class="table-light">

                        <tr>
                            <th width="150">รหัสสินค้า</th>
                            <th>ชื่อสินค้า</th>
                            <th width="150">ราคาเก่า</th>
                            <th width="150">ราคาใหม่</th>
                            <th width="150">ราคารวมภาษี</th>
                            <th width="60" class="text-center">ลบ</th>
                        </tr>

                    </thead>

                    <tbody id="quotationItems">

                        <tr>
                            <td><input type="text" name="item_code[]" class="form-control" value="1908053"></td>
                            <td><input type="text" name="item_name[]" class="form-control" value="MB BLUE-J [MB POM RAL 2308520]"></td>
                            <td><input type="number" name="item_old_price[]" class="form-control text-end" value="275.00"></td>
                            <td><input type="number" name="item_new_price[]" class="form-control text-end" value="285.00"></td>
                            <td><input type="number" name="item_total_price[]" class="form-control text-end" value="304.95"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-icon btn-label-danger" title="ลบรายการ" onclick="removeQuotationItem(this)">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <tr>
                            <td><input type="text" name="item_code[]" class="form-control"></td>
                            <td><input type="text" name="item_name[]" class="form-control"></td>
                            <td><input type="number" name="item_old_price[]" class="form-control text-end" value="0.00"></td>
                            <td><input type="number" name="item_new_price[]" class="form-control text-end" value="0.00"></td>
                            <td><input type="number" name="item_total_price[]" class="form-control text-end" value="0.00"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-icon btn-label-danger" title="ลบรายการ" onclick="removeQuotationItem(this)">
                                    <i class="ti ti-trash"></i>
                                </button>
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
        // ต้องประกาศก่อน loadData ครั้งแรก — เลี่ยง hoisting ทำให้ dtSeq เป็น NaN (spinner ค้าง)
        var dtXhr = null, dtSeq = 0;
        loadData(page);

        // ────────────────────────────────────────────────────────
        //  รายการสินค้าในใบเสนอราคา — เพิ่ม/ลบแถวได้
        // ────────────────────────────────────────────────────────
        function addQuotationItem() {
            var row = '<tr>'
                + '<td><input type="text" name="item_code[]" class="form-control"></td>'
                + '<td><input type="text" name="item_name[]" class="form-control"></td>'
                + '<td><input type="number" name="item_old_price[]" class="form-control text-end" value="0.00"></td>'
                + '<td><input type="number" name="item_new_price[]" class="form-control text-end" value="0.00"></td>'
                + '<td><input type="number" name="item_total_price[]" class="form-control text-end" value="0.00"></td>'
                + '<td class="text-center">'
                +   '<button type="button" class="btn btn-sm btn-icon btn-label-danger" title="ลบรายการ" onclick="removeQuotationItem(this)">'
                +     '<i class="ti ti-trash"></i>'
                +   '</button>'
                + '</td>'
                + '</tr>';
            $('#quotationItems').append(row);
        }

        function removeQuotationItem(btn) {
            // เหลืออย่างน้อย 1 แถว — ถ้าลบแถวสุดท้ายให้เคลียร์ค่าแทน
            if ($('#quotationItems tr').length <= 1) {
                $(btn).closest('tr').find('input').val('');
                return;
            }
            $(btn).closest('tr').remove();
        }

        // เก็บ filter สด (เฉพาะที่มีค่า) ทุกครั้ง — ไม่สะสมค่าเก่า
        function collectSearchData(){
            var data = {};
            $('.p_search').each(function(){
                var v = $(this).val();
                if (v !== '' && v !== null) data[$(this).attr('name')] = v;
            });
            return data;
        }

        // อัปเดตปุ่มล้างตัวกรองตามจำนวน filter ที่ใช้อยู่ (ไม่นับ limit)
        function updateFilterButtonState(){
            var count = 0;
            $('.p_search:not([name="limit"])').each(function(){
                var v = $(this).val();
                if (v !== '' && v !== null) count++;
            });
            var $btn = $('#btnResetFilters');
            if (count > 0) { $btn.removeClass('btn-label-secondary').addClass('btn-danger'); $btn.find('.filter-count').text('('+count+')'); }
            else           { $btn.removeClass('btn-danger').addClass('btn-label-secondary'); $btn.find('.filter-count').text(''); }
        }

        // กัน AJAX race: รับเฉพาะผลของ request ล่าสุด (dtXhr/dtSeq ประกาศด้านบนแล้ว)
        function loadData(pages){
            updateFilterButtonState();
            searchData = collectSearchData();
            page = pages;
            var seq = ++dtSeq;
            if (dtXhr) dtXhr.abort();
            dtXhr = $.ajax({
                type: "GET", url: pages, data: searchData,
                success: function(data){ if (seq === dtSeq) $("#table-data").html(data); },
                complete: function(){ if (seq === dtSeq) dtXhr = null; }
            });
        }

        function resetFilters(){
            $('.p_search:not([name="limit"])').val('');
            loadData("{{$page_url}}/datatable");
        }

        // ── ปุ่มจัดการในตาราง (ยังไม่ได้ต่อ CRUD — จะทำสเตปถัดไป) ──
        function quotationView(qno)   { Swal.fire('ดูรายละเอียด', 'อยู่ระหว่างพัฒนา (' + qno + ')', 'info'); }
        function quotationEdit(qno)   { Swal.fire('แก้ไข', 'อยู่ระหว่างพัฒนา (' + qno + ')', 'info'); }
        function quotationPrint(qno)  { Swal.fire('พิมพ์', 'อยู่ระหว่างพัฒนา (' + qno + ')', 'info'); }
        function quotationDelete(qno) { Swal.fire('ลบ', 'อยู่ระหว่างพัฒนา (' + qno + ')', 'info'); }
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
