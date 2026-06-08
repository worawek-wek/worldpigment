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

            {{-- ปุ่มล้างตัวกรอง (มุมขวาบน) --}}

            {{-- แถวตัวกรอง 1: ค้นหา + ช่วงวันที่ --}}
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small fw-medium mb-1">ค้นหา</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" name="search"
                            class="form-control p_search"
                            placeholder="เลขที่ใบนำส่ง / เลขที่ใบส่ง ต.ย. / ลูกค้า"
                            oninput="loadData(page)">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-medium mb-1">วันที่ส่งเทียบสี</label>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small fw-medium">ตั้งแต่</span>
                        <input type="date" name="test_date_from" class="form-control p_search" onchange="loadData(page)">
                        <span class="small fw-medium">ถึง</span>
                        <input type="date" name="test_date_to" class="form-control p_search"
                            onchange="loadData(page)">
                    </div>
                </div>
            </div>

            {{-- แถวตัวกรอง 2: ประเภทงาน + Standard + ปรับแก้ไข --}}
            <div class="row g-3 align-items-end mt-1">
                <div class="col-md-3">
                    <label class="form-label small fw-medium mb-1">ประเภทงาน</label>
                    <select name="job_type" class="form-select p_search" onchange="loadData(page)">
                        <option value="">ทุกประเภท</option>
                        @foreach (['เป่าฟิมล์','เป่าขวด','EXT','ROLL','INJ','CY'] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-medium mb-1">Standard</label>
                    <input type="text" name="std" class="form-control p_search"
                        placeholder="เช่น PT 494 C" onkeyup="if(event.key==='Enter') loadData(page)">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-medium mb-1">ปรับแก้ไข</label>
                    <select name="revision" class="form-select p_search" onchange="loadData(page)">
                        <option value="">ทั้งหมด</option>
                        @foreach (['New','Revise 1','Revise 2'] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-center align-items-center my-3 position-relative">
                {{-- ประเภทเอกสาร (อยู่ตรงกลาง) — กลุ่ม checkbox เลือกได้หลายอัน (ติ๊ก=แสดง) --}}
                <div class="doc-filter d-flex align-items-center flex-wrap justify-content-center gap-3 py-2">
                    <span class="fw-bold text-nowrap text-heading mb-0">
                        <i class="ti ti-filter me-1 text-primary"></i>แสดงเอกสาร
                    </span>
                    <div class="vr d-none d-sm-block"></div>
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
                <button type="button" id="btnResetFilters" class="btn btn-label-secondary position-absolute end-0" onclick="resetFilters()">
                    <i class="ti ti-x me-1"></i>ล้างตัวกรอง<span class="filter-count ms-1"></span>
                </button>
            </div>

            {{-- จำนวนต่อหน้า (มุมขวาล่าง) --}}
            <div class="d-flex justify-content-end align-items-center mt-3 pt-3 border-top">
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
        loadData(page);

        // ────────────────────────────────────────────────────────
        //  Select2 + Flatpickr init
        //  ⚠ ลำดับสำคัญ: select2 ก่อน flatpickr — เพื่อให้ flatpickr สร้าง <select> เดือน
        //  ทีหลัง select2 จะไม่จับ (กันปัญหา UI flatpickr พังเหมือนรอบก่อน)
        // ────────────────────────────────────────────────────────
        $(function() {
            // ── Select2 ── ทุก select ใน modal + filter (ยกเว้น name="limit" = ปุ่มเลือกจำนวนต่อหน้า)
            // dropdownParent = .modal-content (position:relative) ไม่ใช่ .modal (position:fixed + scroll)
            // เพื่อกันบั๊กตำแหน่ง dropdown เพี้ยนตอนเปิดขึ้นบน/ตอน modal scroll
            $('#colorMatchingModal select:not(.select2-tags)').select2({
                width: '100%',
                dropdownParent: $('#colorMatchingModal .modal-content')
            });
            // คุณสมบัติ (pop) = select2 แบบ tags → เลือกจาก list หรือพิมพ์ค่าใหม่เองได้ (กัน variant ใน DB หาย)
            $('#colorMatchingModal .select2-tags').select2({
                width: '100%',
                tags: true,
                dropdownParent: $('#colorMatchingModal .modal-content')
            });
            $('#sampleDeliveryModal select').select2({
                width: '100%',
                dropdownParent: $('#sampleDeliveryModal .modal-content')
            });
            // filter ของหน้าหลัก (ไม่อยู่ใน modal) → ไม่ต้องมี dropdownParent
            $('.card-header select[name]:not([name="limit"])').select2({
                width: '100%'
            });

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
            $('.p_search:not([name="limit"])').each(function(){
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
        }

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
            // ไม่ล้าง name="limit" (รายการ/หน้า) — เป็นการตั้งค่าแสดงผล ไม่ใช่ตัวกรอง
            $('.p_search:not([name="limit"])').each(function () {
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
            $('#form_color_matching select').val('').trigger('change.select2');
            $('#form_color_matching [name="custname"], #form_color_matching [name="custnameEN"]').prop('readonly', false);
            $('#form_color_matching [name="_mode"]').val('create');
            $('#form_color_matching [name="_pk"]').val('');
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
        }

        // ── พิมพ์ "เลขที่ใบนำส่งเทียบสี (อ้างอิง)" ในฟอร์ม SD ──
        // → ดึง record CM ตาม SendNo มาเติมช่องที่ตรงกัน (ลูกค้า/สี)
        //   และนำ SendNo ไปตั้งต้นในช่อง "เลขที่ใบส่ง ต.ย." (ผู้ใช้พิมพ์เพิ่มได้)
        let sdRefTimer = null;
        $('#form_sample_delivery').on('input', '[name="SendNo"]', function () {
            const sendNo = $(this).val().trim();
            clearTimeout(sdRefTimer);
            if (sendNo === '') return;

            sdRefTimer = setTimeout(function () {
                $.ajax({
                    type: 'GET',
                    url: '{{$page_url}}/ref/' + encodeURIComponent(sendNo),
                    success: function (resp) {
                        if (!resp || !resp.found) return;   // ไม่พบ → ไม่แตะอะไร
                        const $f = $('#form_sample_delivery');
                        $f.find('[name="custno"]').val(resp.custno ?? '');
                        $f.find('[name="custname"]').val(resp.custname ?? '');
                        $f.find('[name="custnameEN"]').val(resp.custnameEN ?? '');
                        $f.find('[name="powder_color"]').val(resp.color ?? '');
                        // นำ SendNo ตั้งต้นในช่องเลขที่ใบส่ง ต.ย. (ผู้ใช้พิมพ์ต่อ)
                        $f.find('[name="Testno"]').val(sendNo);
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
                    // สีผง (powder_color) ใน DB เก็บเป็น column color → เติมกลับเอง
                    $('#form_sample_delivery [name="powder_color"]').val(row.color ?? '');
                    $('#form_sample_delivery [name="_mode"]').val('edit');
                    $('#form_sample_delivery [name="_pk"]').val(row.id);
                    $('#form_sample_delivery [name="SendNo"]').val(row.SendNo ?? '');
                    $('#sampleDeliveryModal').modal('show');
                },
                error: function() {
                    sdEditing = false;
                    Swal.fire('ไม่พบข้อมูล', '', 'error');
                }
            });
        }

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
                const $input = $(formSelector + ' [name="' + col + '"]');
                if ($input.length === 0) return;

                const val = row[col];

                if ($input.is(':checkbox')) {
                    $input.prop('checked', val == 1);
                    return;
                }

                // flatpickr: ใช้ instance.setDate() ที่ parse Y-m-d ได้เลย แสดงผลเป็น d/m/Y
                if ($input.hasClass('flatpickr-date')) {
                    const fp = $input[0]._flatpickr;
                    if (val) {
                        if (fp) fp.setDate(String(val).substring(0, 10), false);
                        else    $input.val(String(val).substring(0, 10));
                    } else {
                        if (fp) fp.clear();
                        else    $input.val('');
                    }
                    return;
                }

                // select (รวม select2 ที่ต้อง trigger change เพื่อ refresh UI)
                if ($input.is('select')) {
                    const sv = val ?? '';
                    // ถ้าค่าใน DB ไม่มีใน option (เช่น variant ของ pop) → เพิ่ม option ชั่วคราว กันค่าหาย
                    if (sv !== '' && $input.find('option').filter(function () { return this.value === String(sv); }).length === 0) {
                        $input.append(new Option(sv, sv, true, true));
                    }
                    $input.val(sv).trigger('change.select2');
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
</script>
</body>

</html>
