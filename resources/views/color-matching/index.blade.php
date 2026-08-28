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

/* ─── Theme override: ฟอร์มใบส่ง ต.ย. (SD) ใช้ theme ม่วง ─── */
#sampleDeliveryModal .modal-title {
    background-color: #6c5ce7;
}
#sampleDeliveryModal .modal-title::after {
    border-top-color: #6c5ce7;
}

/* ─── ปุ่มเปิด modal: สีตาม theme แต่ละฟอร์ม ─── */
.btn-theme-cm {
    background-color: #54BAB9;
    border-color: #54BAB9;
    color: #fff;
}
.btn-theme-cm:hover,
.btn-theme-cm:focus,
.btn-theme-cm:active {
    background-color: #429a99;
    border-color: #429a99;
    color: #fff;
}
.btn-theme-sd {
    background-color: #6c5ce7;
    border-color: #6c5ce7;
    color: #fff;
}
.btn-theme-sd:hover,
.btn-theme-sd:focus,
.btn-theme-sd:active {
    background-color: #5a4bd1;
    border-color: #5a4bd1;
    color: #fff;
}
/* ปุ่มผลการทดสอบตัวอย่างสี — โทนชมพู (10/08/2569) */
.btn-theme-result {
    background-color: #d6336c;
    border-color: #d6336c;
    color: #fff;
}
.btn-theme-result:hover,
.btn-theme-result:focus,
.btn-theme-result:active {
    background-color: #b82a5b;
    border-color: #b82a5b;
    color: #fff;
}

/* ─── ตาราง: badge/ปุ่ม ใช้ theme เดียวกับฟอร์ม (CM=teal #54BAB9, SD=ม่วง #6c5ce7) ─── */
/* badge ชนิดเอกสาร — โทนอ่อน (soft) เหมือน bg-label-* แต่เป็นสี theme */
.badge-doc-cm {
    background-color: #fff;
    color: #14706e;
    border: 2px solid #54BAB9;
    font-weight: 700;
}
.badge-doc-sd {
    background-color: #fff;
    color: #3a2db0;
    border: 2px solid #6c5ce7;
    font-weight: 700;
}
/* สถานะ "กำลังเทียบสี" (ขั้นตอนของ CM) ให้เป็นโทน teal */
.badge-status-cm {
    background-color: #e8f6f6;
    color: #1f7a78;
}

/* หัวตารางสีเข้ม (Dark Table head) — ตัวหนังสือขาว ตัวพิมพ์ใหญ่ */
#table-data thead.cm-thead-dark th {
    background-color: #6e6e78;
    color: #fff;
    border-color: #6e6e78;
    font-weight: 600;
    letter-spacing: .4px;
    text-transform: uppercase;
    vertical-align: middle;
}
#table-data thead.cm-thead-dark th small {
    color: rgba(255, 255, 255, .65) !important;
    text-transform: none;
    letter-spacing: 0;
}

/* หัวตารางคลิกเรียงลำดับได้ — hover เน้นพื้นหลัง, ไอคอนบอกทิศ */
#table-data th.th-sort {
    cursor: pointer;
    user-select: none;
}
#table-data th.th-sort:hover {
    background-color: #5c5c66;
}
.th-sort-icon {
    font-size: .9rem;
    opacity: .35;
    vertical-align: middle;
}

/* คอลัมน์ที่กำลังเรียง — เน้นทั้งคอลัมน์ (หัวตาราง + cell) ให้เห็นชัดทันที */
/* หัวตาราง: สี theme teal เข้ม ตัวอักษรขาว */
#table-data thead.cm-thead-dark th.col-sorted {
    background-color: #54BAB9;
    color: #fff;
    box-shadow: inset 0 -3px 0 #ffd43b;   /* ขีดเส้นใต้สีเหลืองเน้นซ้ำ */
}
#table-data thead.cm-thead-dark th.col-sorted:hover {
    background-color: #4aa9a8;
}
#table-data thead.cm-thead-dark th.col-sorted small {
    color: rgba(255, 255, 255, .8) !important;
}
/* cell ในคอลัมน์: ไม่ใส่พื้นหลัง ใส่แค่เส้นข้างซ้าย-ขวาให้เห็นเป็นแนวคอลัมน์ */
#table-data tbody td.col-sorted {
    box-shadow: inset 2px 0 0 #54BAB9, inset -2px 0 0 #54BAB9;
}
.th-sort-icon.active {
    opacity: 1;
    color: #ffd43b;        /* ลูกศรทิศทางสีเหลือง เด่นบนพื้น teal */
    font-size: 1.05rem;
    font-weight: 700;
    margin-left: .15rem;
}

/* พื้นหลังแถวตามชนิดเอกสาร (CM=teal, SD=ม่วง) + เส้นบอกชนิดด้านซ้าย */
/* ตั้งค่าผ่านตัวแปร --bs-table-bg ต่อแถว → hover ของ Bootstrap ยังทำงานทับได้ปกติ */
#table-data tbody tr.tr-cm { --bs-table-bg: #d6eeed; }
#table-data tbody tr.tr-sd { --bs-table-bg: #e6e1fb; }
/* ช่องสถานะ + จัดการ — พื้นหลังขาว (ไม่อิงสีพื้นแถว) */
#table-data tbody td.td-status,
#table-data tbody td.td-action { background-color: #fff; }

/* filter ประเภทเอกสาร — option ที่ไม่ติ๊กให้จางลง เห็นชัดว่าอันไหนกำลังแสดง */
.doc-filter {
    font-size: 1rem;
}
.doc-filter .form-check:has(input:not(:checked)) .form-check-label {
    opacity: .4;
}
.doc-filter .form-check-label {
    cursor: pointer;
    transition: opacity .15s;
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
                        <button class="btn btn-theme-cm"
                            data-bs-toggle="modal"
                            data-bs-target="#colorMatchingModal">
                            <i class="ti ti-file-text me-1"></i>
                            สร้างใบนำส่งเทียบสี
                        </button>

                        <button class="btn btn-theme-sd"
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

    {{-- ⏸ Summary ปิดไว้ก่อน (ค่อยเปิดใช้ภายหลัง) — โหลดผ่าน AJAX จาก color-matching/summary
    <div id="summary-data" class="row g-4 mb-4">
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
    --}}

    <!-- Table -->
    <div class="card">

        <div class="card-header border-bottom">

            {{-- แถบหัว: ปุ่มพับ/กางตัวกรอง + ปุ่มล้างตัวกรอง (ชิดขวาทั้งคู่) — ค่าเริ่มต้นคือ "ซ่อนไว้" --}}
            <div class="d-flex justify-content-end align-items-center gap-2">
                {{-- รายละเอียดการค้นหา — สรุปเงื่อนไขที่กรองอยู่ โชว์เฉพาะตอนพับตัวกรอง --}}
                <div id="filterSummary" class="d-flex flex-wrap align-items-center gap-1 me-auto"></div>
                <button type="button" id="btnToggleFilters" class="btn btn-label-primary btn-sm"
                    data-bs-toggle="collapse" data-bs-target="#cmFilterBox"
                    aria-expanded="false" aria-controls="cmFilterBox">
                    <i class="ti ti-filter me-1"></i>ตัวกรอง
                    <i class="ti ti-chevron-down ms-1 toggle-caret"></i>
                </button>
                <button type="button" id="btnResetFilters" class="btn btn-label-secondary btn-sm" onclick="resetFilters()">
                    <i class="ti ti-x me-1"></i>ล้างตัวกรอง<span class="filter-count ms-1"></span>
                </button>
            </div>

            <div class="collapse" id="cmFilterBox">
            <div class="pt-3">

            {{-- แถวตัวกรอง 1: ค้นหา + ช่วงวันที่ --}}
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small fw-medium mb-1">ค้นหา <span class="text-muted fw-normal">(เลขที่ใบนำส่ง / เลขที่ใบส่ง ต.ย. / รหัสลูกค้า / ชื่อลูกค้า / สี)</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" name="search"
                            class="form-control p_search"
                            oninput="loadData(page)">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-medium mb-1">วันที่ส่งเทียบสี</label>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small fw-medium">ตั้งแต่</span>
                        <input type="text" name="test_date_from" class="form-control flatpickr-date p_search"
                            placeholder="วว/ดด/ปปปป">
                        <span class="small fw-medium">ถึง</span>
                        <input type="text" name="test_date_to" class="form-control flatpickr-date p_search"
                            placeholder="วว/ดด/ปปปป">
                    </div>
                </div>
            </div>

            {{-- แถวตัวกรอง 2: ประเภทงาน + ปรับแก้ไข + Standard + สี + Lot --}}
            <div class="row g-3 align-items-end mt-1">
                <div class="col-md-3">
                    <label class="form-label small fw-medium mb-1">ประเภทงาน</label>
                    <select name="job_type" class="form-select p_search" onchange="loadData(page)">
                        <option value="">ทุกประเภท</option>
                        @foreach ($options['Type_Work'] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-medium mb-1">ปรับแก้ไข</label>
                    <select name="revision" class="form-select p_search" onchange="loadData(page)">
                        <option value="">ทั้งหมด</option>
                        @foreach ($options['Adj'] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-medium mb-1">Standard <span class="text-muted fw-normal">(เช่น PT 494 C)</span></label>
                    <input type="text" name="std" class="form-control p_search"
                        oninput="loadData(page)">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-medium mb-1">สี</label>
                    <input type="text" name="color" class="form-control p_search"
                        oninput="loadData(page)">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-medium mb-1">Resin (Match) <span class="text-muted fw-normal">(เช่น PVC, ABS)</span></label>
                    <input type="text" name="resin" class="form-control p_search"
                        oninput="loadData(page)">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-medium mb-1">Lot No. <span class="text-muted fw-normal">(เช่น 690112-1-2/01)</span></label>
                    <input type="text" name="lot" class="form-control p_search"
                        oninput="loadData(page)">
                </div>
            </div>
            <div class="d-flex justify-content-center align-items-center my-3">
                {{-- ประเภทเอกสาร (อยู่ตรงกลาง) — กลุ่ม checkbox เลือกได้หลายอัน (ติ๊ก=แสดง) --}}
                <div class="doc-filter d-flex align-items-center flex-wrap justify-content-center gap-3 py-2">
                    <div class="form-check mb-0">
                        <input class="form-check-input p_search" type="checkbox"
                            name="show_cm" value="1" id="filter_show_cm" checked onchange="loadData(page)">
                        <label class="form-check-label fw-medium d-flex align-items-center" for="filter_show_cm">
                            <i class="ti ti-file-text text-primary me-1"></i>ใบนำส่งเทียบสี
                        </label>
                    </div>
                    <div class="form-check mb-0">
                        <input class="form-check-input p_search" type="checkbox"
                            name="show_sd" value="1" id="filter_show_sd" checked onchange="loadData(page)">
                        <label class="form-check-label fw-medium d-flex align-items-center" for="filter_show_sd">
                            <i class="ti ti-package text-info me-1"></i>ใบส่ง ต.ย.
                        </label>
                    </div>
                </div>
            </div>

            </div>
            </div>
            {{-- /#cmFilterBox --}}

            {{-- เรียงข้อมูล (คลิกหัวตาราง) + จำนวนต่อหน้า (ขวา) --}}
            {{-- state การเรียง — เก็บไว้นอก #table-data เพื่อให้คงค่าเมื่อตารางโหลดใหม่ (default = id desc = ล่าสุด) --}}
            <input type="hidden" name="sort_col" value="id" class="p_search">
            <input type="hidden" name="sort_dir" value="desc" class="p_search">
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                {{-- เรียงตามลำดับบันทึก (id) — ทำงานร่วมกับการคลิกหัวตาราง ผ่าน state เดียวกัน (sort_col/sort_dir) --}}
                <div class="d-flex align-items-center">
                    <label class="form-label small fw-medium mb-0 me-2 d-flex align-items-center"
                        title="เรียงตามลำดับการเพิ่มข้อมูลเข้าระบบ">
                        เรียงตามลำดับการเพิ่ม
                        <i class="ti ti-info-circle ms-1 text-muted"></i>
                    </label>
                    <select name="sort" class="form-select form-select-sm"
                        style="width: 210px;" onchange="onSortDropdown(this.value)">
                        <option value="desc">ล่าสุด</option>
                        <option value="asc">เก่าสุด</option>
                        <option value="" hidden>เรียงตามคอลัมน์ (คลิกหัวตาราง)</option>
                    </select>
                </div>
                <div class="d-flex align-items-center">
                    <label class="form-label small fw-medium mb-0 me-2">แสดง</label>
                    <select name="limit" class="form-select form-select-sm p_search"
                        style="width: 90px;" onchange='loadData("{{$page_url}}/datatable")'>
                        <option value="15">15</option>
                        <option value="50">50</option>
                        <option value="75">75</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                    </select>
                    <span class="ms-2 small fw-medium">รายการ/หน้า</span>
                </div>
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
    @include('color-matching.modal-result')
    @include('color-matching.modal-detail')


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
        // กัน AJAX race (ต้องประกาศก่อนเรียก loadData ครั้งแรก — เลี่ยง hoisting ทำให้ dtSeq เป็น NaN)
        var dtXhr = null;
        var dtSeq = 0;

        // ป้ายชื่อของแต่ละช่องกรอง — ใช้ประกอบแถบ "รายละเอียดการค้นหา" (#filterSummary)
        // ⚠ ต้องประกาศตรงนี้ (ก่อน loadData ครั้งแรก) เพราะ renderFilterSummary() ถูกเรียกใน loadData
        //   ถ้าไปประกาศท้ายไฟล์ ตัวแปรจะยังเป็น undefined ตอนโหลดหน้า → throw แล้ว spinner ค้าง
        var FILTER_LABELS = {
            search:         'ค้นหา',
            test_date_from: 'วันที่ส่งเทียบสี ตั้งแต่',
            test_date_to:   'ถึง',
            job_type:       'ประเภทงาน',
            revision:       'ปรับแก้ไข',
            std:            'Standard',
            color:          'สี',
            resin:          'Resin',
            lot:            'Lot No.'
        };

        loadData(page);

        // ────────────────────────────────────────────────────────
        //  ยกระดับ select + Flatpickr init
        //  ⚠ ลำดับสำคัญ: ยกระดับ select ก่อน flatpickr — เพื่อให้ flatpickr สร้าง <select> เดือน
        //  ทีหลัง (กันปัญหา UI flatpickr พังเหมือนรอบก่อน)
        // ────────────────────────────────────────────────────────
        $(function() {
            // ── select ทั้งหน้า (ตัวกรอง + modal) ──
            // ตัวช่วยกลางใน layout/inc_js เลือกให้เองตามจำนวนตัวเลือก:
            //   ตั้งแต่ 10 ตัวขึ้นไป → select2 (พิมพ์ค้นหาได้)  ต่ำกว่านั้น → bootstrap-select
            // ช่องในฟอร์ม CM/SD ที่ติด class select2-tags = พิมพ์ค่านอกรายการเองได้
            //   (= ช่อง "อื่นๆ ระบุ" ในฟอร์มกระดาษ + กันค่าเดิมใน DB หาย) จึงบังคับเป็น select2 เสมอ
            //   ยกเว้นประเภท (TestType) ที่เป็นค่าตายตัว 1-4 → ห้ามพิมพ์ค่าใหม่
            enhanceSelects();

            // ── Flatpickr ── หลัง select2 ──
            flatpickr('.flatpickr-date', {
                dateFormat: 'd/m/Y',
                allowInput: true,
                static: true,
                disableMobile: true,
                onChange: function(_, _str, instance) {
                    if (instance.input.classList.contains('p_search')) {
                        loadData(page);
                    }
                }
            });

            // ปิด autocomplete ของช่องวันที่ทั้งหมด (flatpickr + native date)
            // กัน dropdown ประวัติของเบราว์เซอร์มาบังปฏิทิน
            $('.flatpickr-date, input[type="date"]').attr('autocomplete', 'off');

            // sync dropdown "เรียงตาม" ให้ตรงกับ state การเรียงเริ่มต้น (id desc = ล่าสุด)
            syncSortDropdown();
        });

        // เก็บ filter ที่กรอกใน UI เป็น object — ใช้ทั้ง loadData() และ loadSummary()
        function collectSearchData(){
            var data = {};
            $('.p_search').each(function() {
                var inputName = $(this).attr('name');
                var inputValue = $(this).val();
                // checkbox/radio: ส่งเฉพาะตัวที่เลือก + มีค่า (ข้าม "ทั้งหมด" value="")
                if ($(this).is(':checkbox, :radio')) {
                    if ($(this).is(':checked') && inputValue !== '' && inputValue !== null) {
                        data[inputName] = inputValue;
                    }
                    return;
                }
                if (inputValue !== '' && inputValue !== null) {
                    data[inputName] = inputValue;
                }
            });
            return data;
        }

        // อัปเดตสีปุ่ม "ล้างตัวกรอง" ตามว่ามี filter ใช้งานอยู่กี่ตัว (ไม่นับ limit)
        function updateFilterButtonState(){
            var count = 0;
            $('.p_search:not([name="limit"]):not([name="sort_col"]):not([name="sort_dir"])').each(function(){
                if ($(this).is(':checkbox, :radio')) return;   // นับแยกด้านล่าง
                var v = $(this).val();
                if (v !== '' && v !== null) count++;
            });
            // ประเภทเอกสาร: นับเป็น filter เมื่อ "ไม่ใช่ติ๊กครบทั้งคู่" (default = ติ๊กครบ = แสดงทั้งหมด)
            var $cb = $('.p_search[type="checkbox"]');
            if ($cb.length && $cb.filter(':checked').length !== $cb.length) count++;
            var $btn = $('#btnResetFilters');
            if (count > 0) {
                $btn.removeClass('btn-label-secondary').addClass('btn-danger');
                $btn.find('.filter-count').text('(' + count + ')');
            } else {
                $btn.removeClass('btn-danger').addClass('btn-label-secondary');
                $btn.find('.filter-count').text('');
            }

            renderFilterSummary();
        }

        // ── รายละเอียดการค้นหา (โชว์ตอนพับตัวกรอง) — ป้ายชื่อ FILTER_LABELS ประกาศไว้ต้นสคริปต์ ──
        // escape ค่าที่ผู้ใช้พิมพ์ก่อนยัดลง HTML (กัน XSS จากคำค้น)
        function escHtml(s){ return $('<div>').text(s == null ? '' : s).html(); }

        function renderFilterSummary(){
            var chips = [];
            $('.p_search:not([name="limit"]):not([name="sort_col"]):not([name="sort_dir"])').each(function(){
                if ($(this).is(':checkbox')) return;   // ประเภทเอกสาร: สรุปแยกด้านล่าง
                var label = FILTER_LABELS[$(this).attr('name')];
                if (!label) return;
                // ตัดสินว่า "ไม่ได้กรอง" จาก value เสมอ — ตัวเลือก "ทั้งหมด/ทุกประเภท" มี value ว่างแต่มีข้อความ
                var raw = $(this).val();
                if (raw === '' || raw === null) return;
                // select: แสดงข้อความของตัวเลือก ไม่ใช่ value
                var val = $(this).is('select') ? $(this).find('option:selected').text().trim() : raw;
                chips.push('<span class="badge bg-label-primary fw-normal">'
                    + '<span class="text-dark fw-medium">' + escHtml(label) + ':</span> '
                    + escHtml(val) + '</span>');
            });
            // ประเภทเอกสาร: บอกเฉพาะเมื่อ "ไม่ได้ติ๊กครบ" (ติ๊กครบ = แสดงทั้งหมด = ไม่ได้กรอง)
            var $cb = $('.p_search[type="checkbox"]');
            if ($cb.length && $cb.filter(':checked').length !== $cb.length) {
                var docs = $cb.filter(':checked').map(function(){
                    return $('label[for="' + this.id + '"]').text().trim();
                }).get();
                chips.push('<span class="badge bg-label-primary fw-normal">'
                    + '<span class="text-dark fw-medium">ประเภทเอกสาร:</span> '
                    + escHtml(docs.length ? docs.join(', ') : 'ไม่เลือกเลย') + '</span>');
            }
            $('#filterSummary').html(chips.join(''));   // ไม่มีตัวกรอง = ปล่อยว่าง
        }

        // หมุนลูกศรตามสถานะพับ/กาง + ซ่อนแถบสรุปตอนกาง (ข้างในเห็นค่าจริงอยู่แล้ว)
        $(document).on('show.bs.collapse hide.bs.collapse', '#cmFilterBox', function(e){
            $('#btnToggleFilters .toggle-caret')
                .toggleClass('ti-chevron-down', e.type === 'hide')
                .toggleClass('ti-chevron-up',   e.type === 'show');
            $('#filterSummary').toggleClass('d-none', e.type === 'show');
        });

        // กัน AJAX race: ยกเลิก request เก่า + รับเฉพาะผลของ request ล่าสุด
        // (ช่องค้นหาเป็น oninput ยิงถี่ → ผลอาจกลับมาไม่เรียงลำดับ ทับกันเอง)
        // หมายเหตุ: dtXhr/dtSeq ประกาศไว้ด้านบนแล้ว (ก่อน loadData แรก)
        function loadData(pages){
            updateFilterButtonState();
            searchData = collectSearchData();
            page = pages;

            var seq = ++dtSeq;
            if (dtXhr) dtXhr.abort();        // ตัด request ก่อนหน้าที่ยังค้าง

            dtXhr = $.ajax({
                type: "GET",
                url: pages,
                data: searchData,
                success: function(data) {
                    if (seq !== dtSeq) return;   // มี request ใหม่กว่าแล้ว → ทิ้งผลเก่า
                    $("#table-data").html(data);
                    // loadSummary(); // ⏸ ปิด summary ไว้ก่อน — ค่อยเปิดเมื่อต้องใช้
                },
                complete: function() {
                    if (seq === dtSeq) dtXhr = null;
                }
            });
        }

        // ────────────────────────────────────────────────────────
        //  เรียงลำดับ — state เดียว (sort_col/sort_dir) ใช้ร่วมกัน 2 ทาง:
        //   1) dropdown "เรียงตาม" (ล่าสุด/เก่าสุด = เรียงตาม id)
        //   2) คลิกหัวตาราง (เรียงตามคอลัมน์ใดก็ได้)
        // ────────────────────────────────────────────────────────
        function setSort(col, dir) {
            $('input[name="sort_col"]').val(col);
            $('input[name="sort_dir"]').val(dir);
            syncSortDropdown();
            loadData(page);
        }

        // dropdown สะท้อนเฉพาะการเรียงตามลำดับบันทึก (id) → เลือก ล่าสุด/เก่าสุด
        // ถ้าเรียงตามคอลัมน์อื่น → แสดง option "เรียงตามคอลัมน์…"
        function syncSortDropdown() {
            var col = $('input[name="sort_col"]').val();
            var dir = $('input[name="sort_dir"]').val();
            $('select[name="sort"]').val(col === 'id' ? dir : '');
        }

        // เลือกจาก dropdown → เรียงตาม id ทิศตามที่เลือก
        function onSortDropdown(val) {
            if (val === '') return;   // option ตัวบอกสถานะ (เรียงตามคอลัมน์) — ไม่ทำอะไร
            setSort('id', val);
        }

        // คลิกหัวตาราง: คอลัมน์เดิมซ้ำ = สลับ asc/desc; คอลัมน์ใหม่ = เริ่มที่ asc
        // (handler แบบ delegated เพราะ thead อยู่ใน #table-data ที่โหลดใหม่ทุกครั้งผ่าน AJAX)
        $(document).on('click', '#table-data th[data-sort]', function () {
            var col    = String($(this).data('sort'));
            var curCol = $('input[name="sort_col"]').val();
            var curDir = $('input[name="sort_dir"]').val();
            var dir    = (curCol === col) ? (curDir === 'asc' ? 'desc' : 'asc') : 'asc';
            setSort(col, dir);
        });

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
            // ไม่ล้าง name="limit" (รายการ/หน้า) และ sort_col/sort_dir (การเรียง) — เป็นการตั้งค่าแสดงผล ไม่ใช่ตัวกรอง
            $('.p_search:not([name="limit"]):not([name="sort_col"]):not([name="sort_dir"])').each(function () {
                const $input = $(this);
                // flatpickr: เคลียร์ผ่าน instance เพื่อให้ช่องวันที่ว่างจริง
                if ($input.hasClass('flatpickr-date')) {
                    const fp = this._flatpickr;
                    if (fp) fp.clear();
                    else    $input.val('');
                    return;
                }
                // select2: ต้อง trigger change.select2 เพื่อ refresh UI ไม่ให้ค้างค่าเดิม
                if ($input.is('select')) {
                    $input.val('').trigger('change.select2');
                    return;
                }
                // ประเภทเอกสาร: ล้างตัวกรอง = ติ๊กกลับทั้งหมด (default แสดงทั้งหมด)
                if ($input.is(':checkbox')) {
                    $input.prop('checked', true);
                    return;
                }
                $input.val('');
            });
            loadData("{{$page_url}}/datatable");
        }
        // ────────────────────────────────────────────────────────
        //  CRUD: testmain (Color Matching + Sample Delivery)
        // ────────────────────────────────────────────────────────

        const CSRF = "{{ csrf_token() }}";

        // flag กันไม่ให้ show.bs.modal reset ข้อมูลที่ view() เพิ่งเติม (โหมดแก้ไข)
        let cmEditing = false;
        let sdEditing = false;

        // เปิด modal ใบนำส่งเทียบสีในโหมด create — เคลียร์ form
        $('#colorMatchingModal').on('show.bs.modal', function () {
            if (cmEditing) return;   // โหมดแก้ไข → view() เติมข้อมูลแล้ว ห้าม reset
            resetColorMatchingForm();
        });
        // ปิด modal → กลับเป็นโหมด create เสมอ
        $('#colorMatchingModal').on('hidden.bs.modal', function () { cmEditing = false; });

        function resetColorMatchingForm() {
            $('#form_color_matching')[0].reset();
            // select2 ต้อง trigger change เพื่อ refresh UI ไม่ให้ค้างค่าเดิม
            // multiple ต้องล้างด้วย [] (ไม่ใช่ '') และทิ้ง option ที่เป็น tag พิมพ์เอง กันสะสมข้ามใบ
            $('#form_color_matching select').each(function () {
                const $sel = $(this);
                $sel.find('option[data-select2-tag]').remove();
                $sel.val($sel.prop('multiple') ? [] : '').trigger('change.select2');
            });
            $('#form_color_matching [name="custname"], #form_color_matching [name="custnameEN"]').prop('readonly', false);
            $('#form_color_matching [name="_mode"]').val('create');
            $('#form_color_matching [name="_pk"]').val('');
            // โหมดสร้างใหม่ → ยังไม่มี record ให้ลบ
            $('#btn_delete_cm').addClass('d-none').removeData('id').removeData('label');
        }

        // ── Auto-fill ชื่อลูกค้าจากรหัส (custno) ในฟอร์มเทียบสี ──
        // พิมพ์รหัส → ดึง name(ไทย)/nameEN(อังกฤษ) จากตาราง customer มาเติม + ล็อกแก้ไขไม่ได้
        // ถ้าไม่พบรหัส → ปลดล็อกให้กรอกเองเหมือนเดิม
        let custLookupTimer = null;
        $('#form_color_matching').on('input', '[name="custno"]', function () {
            const code = $(this).val().trim();
            const $th  = $('#form_color_matching [name="custname"]');
            const $en  = $('#form_color_matching [name="custnameEN"]');

            clearTimeout(custLookupTimer);

            // รหัสว่าง → ปลดล็อกให้กรอกเอง
            if (code === '') {
                $th.add($en).prop('readonly', false);
                return;
            }

            custLookupTimer = setTimeout(function () {
                $.ajax({
                    type: 'GET',
                    url: '{{$page_url}}/customer/' + encodeURIComponent(code),
                    success: function (resp) {
                        if (resp && resp.found) {
                            $th.val(resp.name   ?? '').prop('readonly', true);
                            $en.val(resp.nameEN ?? '').prop('readonly', true);
                        } else {
                            // ไม่พบรหัส → ล้างชื่อให้ว่าง แล้วปลดล็อกให้กรอกเอง
                            $th.add($en).val('').prop('readonly', false);
                        }
                    },
                    error: function () {
                        $th.add($en).prop('readonly', false);
                    }
                });
            }, 300);
        });

        // เปิด modal ใบส่ง ต.ย. ในโหมด create — เคลียร์ form
        $('#sampleDeliveryModal').on('show.bs.modal', function () {
            if (sdEditing) return;   // โหมดแก้ไข → ห้าม reset
            resetSampleDeliveryForm();
        });
        $('#sampleDeliveryModal').on('hidden.bs.modal', function () { sdEditing = false; });

        function resetSampleDeliveryForm() {
            $('#form_sample_delivery')[0].reset();
            $('#form_sample_delivery select').val('').trigger('change.select2');
            $('#form_sample_delivery [name="_mode"]').val('create');
            $('#form_sample_delivery [name="_pk"]').val('');
            $('#form_sample_delivery [name="SendNo"]').val('');
            // โหมดสร้างใหม่ → ยังไม่มี record ให้ลบ
            $('#btn_delete_sd').addClass('d-none').removeData('id').removeData('label');
        }

        // ── พิมพ์ "เลขที่ใบนำส่งเทียบสี (อ้างอิง)" ในฟอร์ม SD ──
        // → ดึง record CM ตาม SendNo มาเติมช่องที่ตรงกัน (ลูกค้า/สี)
        //   และนำ SendNo ไปตั้งต้นในช่อง "เลขที่ใบส่ง ต.ย." (ผู้ใช้พิมพ์เพิ่มได้)
        // เติมส่วน "ข้อมูลสี" ในฟอร์ม SD จาก record ใบนำส่งเทียบสี (เป็น field แก้ไขได้ เหมือน CM)
        // ใช้ fillForm เพื่อให้ select2 รีเฟรช + เพิ่ม option ที่ไม่มีในรายการให้อัตโนมัติ
        function fillSdColorInfo($f, resp) {
            resp = resp || {};
            fillForm('#form_sample_delivery', {
                STD:        resp.STD         ?? '', // Standard: ดึงจากใบนำส่งเทียบสี (readonly)
                TestDesc:   resp.color       ?? '', // รายละเอียด: ดึงจาก "สี" (color) ของใบนำส่งเทียบสี
                ResinMatch: resp.CustPigment ?? '', // Resin (Match): ดึงจาก "เม็ดที่ลูกค้าใช้" (CustPigment) เช่น AS50%+ABS50%
            });
        }

        let sdRefTimer = null;
        $('#form_sample_delivery').on('input', '[name="SendNo"]', function () {
            const sendNo = $(this).val().trim();
            clearTimeout(sdRefTimer);
            if (sendNo === '') return;

            sdRefTimer = setTimeout(function () {
                $.ajax({
                    type: 'GET',
                    url: '{{$page_url}}/ref?sendno=' + encodeURIComponent(sendNo),
                    success: function (resp) {
                        const $f = $('#form_sample_delivery');
                        // ไม่พบ → ล้างช่องที่ auto-fill มาจากใบอ้างอิง (ลูกค้า/ข้อมูลสี)
                        if (!resp || !resp.found) {
                            $f.find('[name="custno"], [name="custname"], [name="custnameEN"]').val('');
                            fillSdColorInfo($f, {});
                            return;
                        }
                        $f.find('[name="custno"]').val(resp.custno ?? '');
                        $f.find('[name="custname"]').val(resp.custname ?? '');
                        $f.find('[name="custnameEN"]').val(resp.custnameEN ?? '');
                        // เติม "ข้อมูลสี" จากใบนำส่งเทียบสี (ผู้ใช้แก้ไขต่อได้)
                        fillSdColorInfo($f, resp);
                        // นำ SendNo ตั้งต้นในช่องเลขที่ใบส่ง ต.ย. เฉพาะเมื่อยังว่าง (กันทับค่าตอนแก้ไข)
                        if (!$f.find('[name="Testno"]').val()) {
                            $f.find('[name="Testno"]').val(sendNo);
                        }
                        // นำ SendNo แสดงในช่องเลขที่ใบรายงานผลด้วย เฉพาะเมื่อยังว่าง (กันทับค่าที่แก้ไข)
                        if (!$f.find('[name="rptno"]').val()) {
                            $f.find('[name="rptno"]').val(sendNo);
                        }
                    }
                });
            }, 300);
        });

        // ── แก้ไข ใบนำส่งเทียบสี (CM) — โหลด record (by id) มาเติม form แล้วเปิด modal ──
        function view(id) {
            cmEditing = true;
            $.ajax({
                type: "GET",
                url: "{{$page_url}}/" + encodeURIComponent(id),
                success: function(row) {
                    fillForm('#form_color_matching', row);
                    $('#form_color_matching [name="_mode"]').val('edit');
                    $('#form_color_matching [name="_pk"]').val(row.id);
                    // โหมดแก้ไข → เปิดปุ่มลบใน footer ของ form (ป้ายกำกับ = เลขที่ใบนำส่งเทียบสี)
                    $('#btn_delete_cm')
                        .removeClass('d-none')
                        .data('id', row.id)
                        .data('label', row.SendNo ?? '');
                    $('#colorMatchingModal').modal('show');
                },
                error: function() {
                    cmEditing = false;
                    Swal.fire('ไม่พบข้อมูล', '', 'error');
                }
            });
        }

        // ── แก้ไข ใบส่ง ต.ย. (SD) — เปิด modal SD พร้อมข้อมูล (by id) ──
        function viewSampleDelivery(id) {
            sdEditing = true;
            $.ajax({
                type: "GET",
                url: "{{$page_url}}/" + encodeURIComponent(id),
                success: function(row) {
                    fillForm('#form_sample_delivery', row);
                    $('#form_sample_delivery [name="_mode"]').val('edit');
                    $('#form_sample_delivery [name="_pk"]').val(row.id);
                    $('#form_sample_delivery [name="SendNo"]').val(row.SendNo ?? '');
                    // โหมดแก้ไข → เปิดปุ่มลบใน footer ของ form (ป้ายกำกับ = เลขที่ใบส่ง ต.ย.)
                    $('#btn_delete_sd')
                        .removeClass('d-none')
                        .data('id', row.id)
                        .data('label', row.Testno ?? '');
                    // ข้อมูลสี (color/ColorChar/TestType/pop/Model) ถูก fillForm เติมจากแถว SD แล้ว
                    // — ไม่ trigger ดึงจาก CM ซ้ำ เพื่อไม่ให้ทับค่าที่บันทึกไว้ของใบส่ง ต.ย.
                    $('#sampleDeliveryModal').modal('show');
                },
                error: function() {
                    sdEditing = false;
                    Swal.fire('ไม่พบข้อมูล', '', 'error');
                }
            });
        }

        // ────────────────────────────────────────────────────────
        //  ผลการทดสอบตัวอย่างสี (ของใบส่ง ต.ย. แต่ละใบ) — 10/08/2569
        //  บันทึกลง testmain: TyResp (ตัวเลือก) / Resp (ระบุ) / Respdate (วันที่ทราบผล)
        // ────────────────────────────────────────────────────────

        // ตัวเลือกจาก config/color_matching.php — ใช้แสดงข้อความบอกใบ้ใต้ช่อง "ระบุ"
        const TR_OPTIONS = @json(config('color_matching.test_result_options', []));

        // แสดงวันที่แบบ d/m/Y จากค่า 'Y-m-d H:i:s' ที่ได้จาก JSON
        function trShowDate(val) {
            if (!val) return '—';
            const p = String(val).substring(0, 10).split('-');
            return p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : String(val);
        }

        function trShowText(val) {
            const s = (val === null || val === undefined) ? '' : String(val).trim();
            return s === '' ? '—' : s;
        }

        // เปิดฟอร์มผลการทดสอบของใบส่ง ต.ย. (by id) — โหลด record มาเติมแล้วเปิด modal
        function viewTestResult(id) {
            $.ajax({
                type: "GET",
                url: "{{$page_url}}/" + encodeURIComponent(id),
                success: function(row) {
                    const $f = $('#form_test_result');
                    $f[0].reset();
                    $f.find('[name="_pk"]').val(row.id);

                    // ── ส่วนอ่านอย่างเดียว (ข้อมูลใบส่ง ต.ย.) ──
                    $('#tr_Testno').text(trShowText(row.Testno));
                    $('#tr_SendNo').text(trShowText(row.SendNo));
                    $('#tr_TestDate').text(trShowDate(row.TestDate));
                    $('#tr_custno').text(trShowText(row.custno));
                    $('#tr_custname').text(trShowText(row.custname));
                    $('#tr_sale').text(trShowText(row.sale));
                    $('#tr_TestDesc').text(trShowText(row.TestDesc));
                    $('#tr_Wage').text(trShowText(row.Wage));

                    // ── ส่วนกรอก (ผลการทดสอบ) ──
                    // TyResp ว่าง/ไม่รู้จัก → default เป็น '0' (ยังไม่ตอบ) เหมือนฟอร์ม Access เดิม
                    let ty = (row.TyResp === null || row.TyResp === undefined) ? '' : String(row.TyResp).trim();
                    if (!TR_OPTIONS.hasOwnProperty(ty)) ty = '0';
                    $f.find('[name="TyResp"][value="' + ty + '"]').prop('checked', true);

                    $f.find('[name="Resp"]').val(row.Resp ?? '');

                    // วันที่ทราบผล — flatpickr ต้องระบุ format ของ string ที่ส่งไป
                    const $dt = $f.find('[name="Respdate"]');
                    const fp  = $dt[0]._flatpickr;
                    if (row.Respdate) {
                        if (fp) fp.setDate(String(row.Respdate).substring(0, 10), false, 'Y-m-d');
                        else    $dt.val(String(row.Respdate).substring(0, 10));
                    } else {
                        if (fp) fp.clear();
                        else    $dt.val('');
                    }

                    trUpdateHint();
                    $('#testResultModal').modal('show');
                },
                error: function() {
                    Swal.fire('ไม่พบข้อมูล', '', 'error');
                }
            });
        }

        // ข้อความบอกใบ้ใต้ช่อง "ระบุ" — เปลี่ยนตามตัวเลือกที่เลือกอยู่
        function trUpdateHint() {
            const code = $('#form_test_result [name="TyResp"]:checked').val();
            const opt  = code ? TR_OPTIONS[code] : null;

            // บอกใบ้ใต้ช่องเท่านั้น — ไม่ใส่ placeholder ในช่องกรอก
            if (opt && opt.specify) {
                $('#tr_resp_hint').text(opt.hint || 'ระบุรายละเอียดเพิ่มเติม').addClass('text-danger');
            } else {
                $('#tr_resp_hint').text('ตัวเลือกนี้ไม่ต้องระบุเพิ่ม (กรอกได้ถ้าต้องการบันทึกหมายเหตุ)').removeClass('text-danger');
            }
        }

        // เลือกผลการทดสอบ → อัปเดตข้อความบอกใบ้ + ถ้ายังไม่เคยลงวันที่ทราบผล ให้ใส่วันนี้ให้
        // (ยกเว้น "ยังไม่ตอบ" ซึ่งยังไม่ถือว่าทราบผล)
        $('#form_test_result').on('change', '[name="TyResp"]', function () {
            trUpdateHint();

            const $dt = $('#form_test_result [name="Respdate"]');
            if ($(this).val() !== '0' && !$dt.val()) {
                const fp = $dt[0]._flatpickr;
                if (fp) fp.setDate(new Date(), false);
                else    $dt.val('');
            }
        });

        // บันทึกผลการทดสอบ → POST /result/{id} (แตะเฉพาะ TyResp/Resp/Respdate)
        $('#form_test_result').on('submit', function(event) {
            event.preventDefault();

            const pk = $(this).find('[name="_pk"]').val();
            if (!pk) { Swal.fire('ไม่พบใบส่ง ต.ย.', '', 'error'); return; }

            const fd = new FormData(this);
            fd.append('_token', CSRF);

            $.ajax({
                url: '{{$page_url}}/result/' + encodeURIComponent(pk),
                type: 'POST', data: fd,
                contentType: false, processData: false,
                success: function(resp) {
                    if (resp && resp.ok) {
                        Swal.fire({ title: 'บันทึกผลการทดสอบเรียบร้อย', icon: 'success', heightAuto: false });
                        $('#testResultModal').modal('hide');
                        loadData(page);
                    } else {
                        Swal.fire({ title: 'เกิดข้อผิดพลาด', text: resp.error ?? '', icon: 'error', heightAuto: false });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.error ?? '', icon: 'error', heightAuto: false });
                }
            });
        });

        // กด Enter ในช่อง "ระบุ" ไม่ให้ submit ทันที (ให้กดปุ่มตกลงเอง)
        $('#form_test_result').on('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });

        // ── ลบ record testmain (CM/SD) — ยืนยันก่อน แล้ว POST ไป /delete/{id} ──
        // เรียกจากปุ่มลบใน footer ของ form (โหมดแก้ไข) — modalSel = modal ที่ต้องปิดเมื่อลบสำเร็จ
        function deleteRow(id, label, modalSel) {
            const name = label ? String(label) : '#' + id;
            Swal.fire({
                title: 'ยืนยันการลบ?',
                html: 'ต้องการลบรายการ <strong>' + $('<div>').text(name).html() + '</strong> ใช่หรือไม่?'
                    + '<br><small class="text-muted">การลบไม่สามารถกู้คืนได้</small>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ลบ',
                confirmButtonColor: '#d33',
                cancelButtonText: 'ยกเลิก',
                // Swal เปิดซ้อนบน modal → กัน bootstrap ปรับความสูง body จนหน้ากระตุก
                heightAuto: false
            }).then((result) => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: "{{$page_url}}/delete/" + encodeURIComponent(id),
                    type: 'POST',
                    data: { _token: CSRF },
                    success: function(resp) {
                        if (resp && resp.ok) {
                            if (modalSel) $(modalSel).modal('hide');
                            Swal.fire({ title: 'ลบเรียบร้อย', icon: 'success', heightAuto: false });
                            loadData(page);
                        } else {
                            Swal.fire({ title: 'เกิดข้อผิดพลาด', text: resp.error ?? '', icon: 'error', heightAuto: false });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({ title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.error ?? '', icon: 'error', heightAuto: false });
                    }
                });
            });
        }

        // ปุ่มลบใน footer ของหน้ารายละเอียด (detail.blade.php) — เนื้อหาอาจอยู่ใน modal ชั้น 1 หรือ ชั้น 2
        // ลบสำเร็จ → ปิดทั้งสองชั้น (ชั้น 1 อาจเป็นใบ CM ที่ลิสต์ใบ ต.ย. ซึ่งเพิ่งถูกลบไป — ข้อมูลจะค้าง)
        function deleteFromDetail(id, label) {
            deleteRow(id, label, '#detailModal2, #detailModal');
        }

        // ปุ่มลบใน footer ของ form (id/label ถูกผูกไว้ตอนเปิด modal โหมดแก้ไข)
        $('#btn_delete_cm').on('click', function () {
            deleteRow($(this).data('id'), $(this).data('label'), '#colorMatchingModal');
        });
        $('#btn_delete_sd').on('click', function () {
            deleteRow($(this).data('id'), $(this).data('label'), '#sampleDeliveryModal');
        });

        // ────────────────────────────────────────────────────────
        //  ดูรายละเอียด (อ่านอย่างเดียว) — โหลด HTML จาก server (CM/SD ต่างกัน)
        //  เนื้อหา render ที่ color-matching/detail.blade.php
        // ────────────────────────────────────────────────────────
        // โหลดรายละเอียดเข้า dialog/modal ที่ระบุ (ใช้ซ้ำทั้งชั้น 1 และชั้น 2)
        function loadDetailInto(id, viewSel, modalSel) {
            $(viewSel).html('<div class="modal-content rounded-0"><div class="modal-body text-center py-5 text-muted">'
                + '<div class="spinner-border spinner-border-sm me-2"></div>กำลังโหลดข้อมูล...</div></div>');
            $(modalSel).modal('show');
            $.ajax({
                type: "GET",
                url: "{{$page_url}}/detail/" + encodeURIComponent(id),
                success: function(html) { $(viewSel).html(html); },
                error: function() {
                    Swal.fire('ไม่พบข้อมูล', '', 'error');
                    $(modalSel).modal('hide');
                }
            });
        }

        // ชั้น 1 — เปิดจากปุ่มในตาราง
        function viewDetail(id)  { loadDetailInto(id, '#detailView',  '#detailModal');  }
        // ชั้น 2 — เปิดจากปุ่ม "ดู" ของใบส่ง ต.ย. ในรายละเอียด CM (ซ้อนทับ ชั้น 1 ยังอยู่)
        function viewDetail2(id) { loadDetailInto(id, '#detailView2', '#detailModal2'); }

        // ── จัดการ modal ซ้อนชั้น: เพิ่ม z-index ให้ตัวที่เปิดทีหลังอยู่บนสุด ──
        // (กัน backdrop ของชั้น 2 ไปบังจน UI งง และให้ชั้น 1 ยังเห็นอยู่ข้างหลัง)
        $(document).on('show.bs.modal', '.modal', function () {
            var zIndex = 1040 + (10 * $('.modal:visible').length);
            $(this).css('z-index', zIndex);
            setTimeout(function () {
                $('.modal-backdrop:not(.modal-stack)')
                    .css('z-index', zIndex - 1).addClass('modal-stack');
            }, 0);
        });
        // ปิดชั้นบนแล้ว ถ้ายังมี modal อื่นเปิดอยู่ → คง scroll-lock ของ body ไว้
        $(document).on('hidden.bs.modal', '.modal', function () {
            if ($('.modal:visible').length) $('body').addClass('modal-open');
        });

        // เติม field ของ form ด้วย JSON record
        function fillForm(formSelector, row) {
            Object.keys(row).forEach(function(col) {
                // select multiple ใช้ชื่อแบบ array ("ChemSafety[]") — หาแบบตรงตัวก่อน ไม่เจอค่อยหาแบบ array
                let $input = $(formSelector + ' [name="' + col + '"]');
                if ($input.length === 0) $input = $(formSelector + ' select[name="' + col + '[]"]');
                if ($input.length === 0) return;

                const val = row[col];

                if ($input.is(':checkbox')) {
                    $input.prop('checked', val == 1);
                    return;
                }

                // flatpickr: setDate() ต้องระบุ format ของ string ที่ส่งไป ('Y-m-d') เป็น argument ที่ 3
                // ไม่งั้น flatpickr จะ parse ด้วย dateFormat ของตัวเอง (d/m/Y) แล้วได้วันที่มั่ว
                if ($input.hasClass('flatpickr-date')) {
                    const fp = $input[0]._flatpickr;
                    if (val) {
                        if (fp) fp.setDate(String(val).substring(0, 10), false, 'Y-m-d');
                        else    $input.val(String(val).substring(0, 10));
                    } else {
                        if (fp) fp.clear();
                        else    $input.val('');
                    }
                    return;
                }

                // select (รวม select2 ที่ต้อง trigger change เพื่อ refresh UI)
                if ($input.is('select')) {
                    // เลือกได้หลายค่า (เช่น ChemSafety) — DB เก็บรวมในคอลัมน์เดียวคั่นด้วย ", " (29/08/2569)
                    if ($input.prop('multiple')) {
                        const vals = String(val ?? '').split(',')
                            .map(function (s) { return s.trim(); })
                            .filter(function (s) { return s !== ''; });
                        // ค่าที่ผู้ใช้เคยพิมพ์เอง (tag) ยังไม่มีใน option → เพิ่มให้ กันค่าหาย
                        vals.forEach(function (v) {
                            const exists = $input.find('option').filter(function () { return this.value === v; }).length > 0;
                            if (!exists) $input.append($('<option>', { value: v, text: v, 'data-select2-tag': 'true' }));
                        });
                        $input.val(vals).trigger('change.select2');
                        return;
                    }

                    const sv = val ?? '';
                    // ถ้าค่าใน DB ไม่มีใน option (เช่น variant ของ pop) → เพิ่ม option ชั่วคราว กันค่าหาย
                    if (sv !== '' && $input.find('option').filter(function () { return this.value === String(sv); }).length === 0) {
                        $input.append(new Option(sv, sv, true, true));
                    }
                    $input.val(sv).trigger('change.select2');
                    return;
                }

                // ช่องตัวเลขแบบใส่คอมมา (js-comma) → จัดรูปแบบก่อนเติม
                if ($input.hasClass('js-comma')) {
                    $input.val(commaFmt(val, commaDecimals($input)));
                    return;
                }

                $input.val(val ?? '');
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

                stripCommaFields(this);          // ถอดคอมมาออกจากช่อง js-comma ก่อนส่ง
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

        // Submit ใบส่ง ต.ย. (SD) — update/insert row testmain (อ้างอิงด้วย id)
        $('#form_sample_delivery').on('submit', function(event) {
            event.preventDefault();
            if (!this.checkValidity()) { this.reportValidity(); return; }

            const pk = $(this).find('[name="_pk"]').val();

            // มี id (_pk) → แก้ไข; ไม่มี → สร้างใหม่
            const isUpdate = !!pk;

            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: 'บันทึกใบส่ง ต.ย. ให้ลูกค้า?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (!result.isConfirmed) return;

                // ⚠ TODO(ชั่วคราว): SD create ที่ยังไม่กรอก SendNo อ้างอิง — gen เลขชั่วคราวให้ insert ผ่านก่อน
                // SendNo เป็น varchar(12) → ('SD' + 10 หลักท้ายของ timestamp = 12 ตัว)
                if (!isUpdate && !$('#form_sample_delivery [name="SendNo"]').val()) {
                    $('#form_sample_delivery [name="SendNo"]').val('SD' + String(Date.now()).slice(-10));
                }

                stripCommaFields(this);          // ถอดคอมมาออกจากช่อง js-comma ก่อนส่ง (เช่น น้ำหนัก Wage)
                const fd = new FormData(this);
                fd.append('_token', CSRF);

                const url = isUpdate
                    ? '{{$page_url}}/update/' + encodeURIComponent(pk)
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

        // กด Enter ในช่องกรอก ไม่ต้อง submit ฟอร์ม (ยกเว้น textarea)
        $('#form_color_matching, #form_sample_delivery').on('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });
</script>
</body>

</html>
