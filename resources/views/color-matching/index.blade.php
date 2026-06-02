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
                            <i class="ti ti-palette text-primary"></i>
                            เทียบสี
                        </h3>

                        <p class="text-muted mb-0">
                            จัดการงานเทียบสีและใบนำส่งตัวอย่าง
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#colorMatchingModal">
                            <i class="ti ti-file-text me-1"></i>
                            สร้างใบนำส่งเทียบสี
                        </button>

                        <button class="btn btn-label-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#sampleDeliveryModal">
                            <i class="ti ti-package me-1"></i>
                            สร้างใบส่ง ต.ย. ให้ลูกค้า
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Summary (โหลดผ่าน AJAX จาก color-matching/summary หลัง loadData()) -->
    <div id="summary-data" class="row g-4 mb-4">
        {{-- placeholder ขณะรอ AJAX โหลด --}}
        @for ($i = 0; $i < 4; $i++)
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="placeholder-glow">
                            <span class="placeholder col-7 mb-2"></span>
                            <h4 class="mb-1"><span class="placeholder col-4"></span></h4>
                            <span class="placeholder col-8"></span>
                        </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>

    <!-- Table -->
    <div class="card">

        <div class="card-header border-bottom">
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">ค้นหา</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" name="search"
                            class="form-control p_search"
                            placeholder="เลขที่ใบนำส่ง / เลขที่ใบส่ง ต.ย. / ลูกค้า"
                            onkeyup="if(event.key==='Enter') loadData(page)">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">ประเภทงาน</label>
                    <select name="job_type" class="form-select p_search" onchange="loadData(page)">
                        <option value="">ทุกประเภท</option>
                        @foreach (['เป่าฟิมล์','เป่าขวด','EXT','ROLL','INJ','CY'] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">วันที่ส่งเทียบสี</label>
                    <input type="date" name="test_date" class="form-control p_search" onchange="loadData(page)">
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">ปรับแก้ไข</label>
                    <select name="revision" class="form-select p_search" onchange="loadData(page)">
                        <option value="">ทั้งหมด</option>
                        @foreach (['New','Revise 1','Revise 2'] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-label-secondary" onclick="resetFilters()">
                        <i class="ti ti-x me-1"></i>ล้างตัวกรอง
                    </button>
                </div>

            </div>

            {{-- Row 2: Show limit selector (จัดชิดขวา) --}}
            <div class="d-flex justify-content-end align-items-center mt-3">
                <label class="form-label small text-muted mb-0 me-2">แสดง</label>
                <select name="limit" class="form-select form-select-sm p_search"
                    style="width: 90px;" onchange='loadData("{{$page_url}}/datatable")'>
                    <option value="15">15</option>
                    <option value="50">50</option>
                    <option value="75">75</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                </select>
                <span class="ms-2 small text-muted">รายการ/หน้า</span>
            </div>
        </div>

        <div id="table-data">
            {{-- Table โหลดผ่าน AJAX จาก color-matching/datatable --}}
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm me-2"></div>
                กำลังโหลดข้อมูล...
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


    @include('color-matching.modal-cm')
    @include('color-matching.modal-sd')


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

        // เก็บ filter ที่กรอกใน UI เป็น object — ใช้ทั้ง loadData() และ loadSummary()
        function collectSearchData(){
            var data = {};
            $('.p_search').each(function() {
                var inputName = $(this).attr('name');
                var inputValue = $(this).val();
                if (inputValue !== '' && inputValue !== null) {
                    data[inputName] = inputValue;
                }
            });
            return data;
        }

        function loadData(pages){
            searchData = collectSearchData();
            page = pages;
            $.ajax({
                type: "GET",
                url: pages,
                data: searchData,
                success: function(data) {
                    $("#table-data").html(data);
                    loadSummary(); // โหลด summary ต่อ ให้สอดคล้องกับ filter ปัจจุบัน
                }
            });
        }

        function loadSummary(){
            $.ajax({
                type: "GET",
                url: "{{$page_url}}/summary",
                data: collectSearchData(),
                success: function(data) {
                    $("#summary-data").html(data);
                }
            });
        }

        function resetFilters() {
            $('.p_search').val('');
            loadData("{{$page_url}}/datatable");
        }
        // ────────────────────────────────────────────────────────
        //  CRUD: testmain (Color Matching + Sample Delivery)
        // ────────────────────────────────────────────────────────

        const CSRF = "{{ csrf_token() }}";

        // เปิด modal ใบนำส่งเทียบสีในโหมด create — เคลียร์ form
        $('#colorMatchingModal').on('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            // ถ้าเปิดจากปุ่ม + (ไม่ใช่ปุ่ม edit ที่มี data-sendno) → reset
            if (!trigger || !trigger.dataset.sendno) {
                $('#form_color_matching')[0].reset();
                $('#form_color_matching [name="_mode"]').val('create');
                $('#form_color_matching [name="_pk"]').val('');
            }
        });

        // เปิด modal ใบส่ง ต.ย. ในโหมด create — เคลียร์ form
        $('#sampleDeliveryModal').on('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger || !trigger.dataset.sendno) {
                $('#form_sample_delivery')[0].reset();
                $('#form_sample_delivery [name="_mode"]').val('create');
                $('#form_sample_delivery [name="_pk"]').val('');
                $('#form_sample_delivery [name="SendNo"]').val('');
            }
        });

        // ดู / แก้ไข — โหลด record มาเติม form แล้วเปิด modal
        function view(sendno) {
            $.ajax({
                type: "GET",
                url: "{{$page_url}}/" + encodeURIComponent(sendno),
                success: function(row) {
                    fillForm('#form_color_matching', row);
                    $('#form_color_matching [name="_mode"]').val('edit');
                    $('#form_color_matching [name="_pk"]').val(sendno);
                    $('#colorMatchingModal').modal('show');
                },
                error: function() {
                    Swal.fire('ไม่พบข้อมูล', '', 'error');
                }
            });
        }

        // ดู / แก้ไข — เปิด modal SD พร้อมข้อมูล
        function viewSampleDelivery(sendno) {
            $.ajax({
                type: "GET",
                url: "{{$page_url}}/" + encodeURIComponent(sendno),
                success: function(row) {
                    fillForm('#form_sample_delivery', row);
                    $('#form_sample_delivery [name="_mode"]').val('edit');
                    $('#form_sample_delivery [name="_pk"]').val(sendno);
                    $('#form_sample_delivery [name="SendNo"]').val(sendno);
                    $('#sampleDeliveryModal').modal('show');
                },
                error: function() {
                    Swal.fire('ไม่พบข้อมูล', '', 'error');
                }
            });
        }

        // เติม field ของ form ด้วย JSON record
        function fillForm(formSelector, row) {
            Object.keys(row).forEach(function(col) {
                const $input = $(formSelector + ' [name="' + col + '"]');
                if ($input.length === 0) return;

                if ($input.is(':checkbox')) {
                    $input.prop('checked', row[col] == 1);
                } else if ($input.attr('type') === 'date' && row[col]) {
                    // datetime / date string → 'YYYY-MM-DD'
                    $input.val(String(row[col]).substring(0, 10));
                } else {
                    $input.val(row[col] ?? '');
                }
            });
        }

        // Submit ใบนำส่งเทียบสี (CM)
        $('#form_color_matching').on('submit', function(event) {
            event.preventDefault();
            if (!this.checkValidity()) { this.reportValidity(); return; }

            const mode = $(this).find('[name="_mode"]').val();
            const pk   = $(this).find('[name="_pk"]').val();
            const isEdit = mode === 'edit' && pk;

            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: isEdit ? 'บันทึกการแก้ไขใบนำส่งเทียบสี?' : 'สร้างใบนำส่งเทียบสีใหม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const fd = new FormData(this);
                fd.append('_token', CSRF);

                const url = isEdit
                    ? '{{$page_url}}/update/' + encodeURIComponent(pk)
                    : '{{$page_url}}/insert';

                $.ajax({
                    url: url, type: 'POST', data: fd,
                    contentType: false, processData: false,
                    success: function(resp) {
                        if (resp && resp.ok) {
                            Swal.fire(isEdit ? 'แก้ไขเรียบร้อย' : 'เพิ่มเรียบร้อย', '', 'success');
                            $('#colorMatchingModal').modal('hide');
                            loadData(page);
                        } else {
                            Swal.fire('เกิดข้อผิดพลาด', resp.error ?? '', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('เกิดข้อผิดพลาด', xhr.responseJSON?.error ?? '', 'error');
                    }
                });
            });
        });

        // Submit ใบส่ง ต.ย. (SD) — โดย default เป็นการ update row testmain (อ้างอิงด้วย SendNo)
        $('#form_sample_delivery').on('submit', function(event) {
            event.preventDefault();
            if (!this.checkValidity()) { this.reportValidity(); return; }

            const mode = $(this).find('[name="_mode"]').val();
            const pk   = $(this).find('[name="_pk"]').val();
            const sendNo = $(this).find('[name="SendNo"]').val();

            // ถ้ามี SendNo (อ้างอิงใบ CM) → update; ถ้าไม่มี → insert
            const isUpdate = !!(pk || sendNo);

            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: 'บันทึกใบส่ง ต.ย. ให้ลูกค้า?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const fd = new FormData(this);
                fd.append('_token', CSRF);

                const url = isUpdate
                    ? '{{$page_url}}/update/' + encodeURIComponent(pk || sendNo)
                    : '{{$page_url}}/insert';

                $.ajax({
                    url: url, type: 'POST', data: fd,
                    contentType: false, processData: false,
                    success: function(resp) {
                        if (resp && resp.ok) {
                            Swal.fire('บันทึกเรียบร้อย', '', 'success');
                            $('#sampleDeliveryModal').modal('hide');
                            loadData(page);
                        } else {
                            Swal.fire('เกิดข้อผิดพลาด', resp.error ?? '', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('เกิดข้อผิดพลาด', xhr.responseJSON?.error ?? '', 'error');
                    }
                });
            });
        });

        // ลบ record
        function Delete(sendno) {
            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: 'จะลบใบเทียบสี ' + sendno + ' ออกจากระบบ ไม่สามารถกู้คืนได้',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ลบ',
                confirmButtonColor: '#d33',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: '{{$page_url}}/' + encodeURIComponent(sendno),
                    type: 'DELETE',
                    data: { _token: CSRF },
                    success: function(resp) {
                        if (resp && resp.ok) {
                            Swal.fire('ลบเรียบร้อย', '', 'success');
                            loadData(page);
                        } else {
                            Swal.fire('ไม่สามารถลบได้', resp.error ?? '', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('เกิดข้อผิดพลาด', xhr.responseJSON?.error ?? '', 'error');
                    }
                });
            });
        }
</script>
</body>

</html>
