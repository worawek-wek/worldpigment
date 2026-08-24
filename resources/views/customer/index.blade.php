<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">

<head>
    @include('layout/inc_header')
    <title>C-ฐานข้อมูลลูกค้า | World Pigment</title>
</head>

<style>
    .modalHeadDecor .modal-header { padding: 0; }

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

    /* ── เรียงตาม — คลิกหัวตาราง + เน้นคอลัมน์ที่กำลังเรียง (แบบเดียวกับหน้าใบสั่งซื้อ) ── */
    #table-data th.th-sort { cursor: pointer; user-select: none; }
    #table-data th.th-sort:hover { background-color: #e9ecef; }
    .th-sort-icon { font-size: .9rem; opacity: .35; vertical-align: middle; }
    .th-sort-icon.active { opacity: 1; color: #ffd43b; font-size: 1.05rem; font-weight: 700; margin-left: .15rem; }
    #table-data thead th.col-sorted { background-color: #696cff; color: #fff; box-shadow: inset 0 -3px 0 #ffd43b; }
    #table-data thead th.col-sorted:hover { background-color: #5a5ef0; }
    #table-data thead th.col-sorted small { color: rgba(255,255,255,.8) !important; }
    #table-data tbody td.col-sorted { box-shadow: inset 2px 0 0 #696cff, inset -2px 0 0 #696cff; }

    /* ══ ฟอร์มข้อมูลลูกค้า — คงผังและโทนสีของฟอร์ม Access เดิมไว้ ══ */
    .cf-sec {
        border: 1px solid #e2e7ea;
        border-radius: .6rem;
        padding: 1rem 1.15rem 1.15rem;
        background: #fff;
    }
    .cf-sec-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #2f7a78;
        margin-bottom: .9rem;
        padding-bottom: .5rem;
        border-bottom: 2px solid #d9e6e6;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .cf-sec .form-label { margin-bottom: .25rem; font-size: .85rem; font-weight: 600; }
    .cf-sec .form-text { margin-top: .15rem; color: #8a94a6; }

    .cf-sec-sale    { background: #fdfaf4; border-color: #ecdfc4; }
    .cf-sec-sale .cf-sec-title { color: #a3781f; border-bottom-color: #ecdfc4; }
    .cf-sec-contact { background: #f8fbf7; border-color: #d6e7cf; }
    .cf-sec-contact .cf-sec-title { color: #4c7c3b; border-bottom-color: #d6e7cf; }
    .cf-sec-dv      { background: #f7fbff; border-color: #cfe2f3; }
    .cf-sec-dv .cf-sec-title { color: #2c6ea4; border-bottom-color: #cfe2f3; }
    .cf-sec-black   { background: #fdf3f3; border-color: #f0cccc; }
    .cf-sec-black .cf-sec-title { color: #a33; border-bottom-color: #f0cccc; }

    /* ช่องเน้นสีตามฟอร์ม Access */
    .cf-hl-yellow { background-color: #fffbcc !important; }
    .cf-hl-code   { background-color: #eef3ff !important; font-weight: 600; }

    .cf-checkrow { display: flex; flex-wrap: wrap; gap: 1.25rem; padding-top: .35rem; }

    /* แถบเตือน Blacklist — แถบแดงบนหัวฟอร์ม */
    .cf-blackwarn {
        background: #d32f2f;
        color: #fff;
        font-weight: 700;
        text-align: center;
        padding: .45rem .75rem;
        border-radius: .375rem;
        margin-bottom: .75rem;
    }

    #contactTable thead th { background: #eef5ea; font-size: .8rem; white-space: nowrap; }
    #contactTable td { padding: .25rem .3rem; }
</style>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('layout/inc_sidemenu')

            <div class="layout-page">
                @include('layout/inc_topmenu')

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                                        <div>
                                            <h3 class="mb-1">
                                                <i class="ti ti-address-book text-primary"></i>
                                                ฐานข้อมูลลูกค้า
                                            </h3>
                                            <p class="text-muted mb-0">
                                                ข้อมูลลูกค้า ผู้ติดต่อ และสถานที่ส่ง (customer / contact / naddress)
                                            </p>
                                        </div>

                                        <div class="d-flex gap-2 flex-wrap">
                                            <button class="btn btn-primary" onclick="customerNew()">
                                                <i class="ti ti-plus me-1"></i>
                                                เพิ่มลูกค้าใหม่
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="card">

                            <div class="card-header border-bottom">

                                {{-- แถบหัว: ปุ่มพับ/กางตัวกรอง + ปุ่มล้างตัวกรอง (ชิดขวาทั้งคู่) — ค่าเริ่มต้นคือ "ซ่อนไว้" --}}
                                <div class="d-flex justify-content-end align-items-center gap-2">
                                    {{-- รายละเอียดการค้นหา — สรุปเงื่อนไขที่กรองอยู่ โชว์เฉพาะตอนพับตัวกรอง --}}
                                    <div id="filterSummary" class="d-flex flex-wrap align-items-center gap-1 me-auto"></div>
                                    <button type="button" id="btnToggleFilters" class="btn btn-label-primary btn-sm"
                                        data-bs-toggle="collapse" data-bs-target="#customerFilterBox"
                                        aria-expanded="false" aria-controls="customerFilterBox">
                                        <i class="ti ti-filter me-1"></i>ตัวกรอง
                                        <i class="ti ti-chevron-down ms-1 toggle-caret"></i>
                                    </button>
                                    <button type="button" id="btnResetFilters" class="btn btn-label-secondary btn-sm" onclick="resetFilters()">
                                        <i class="ti ti-x me-1"></i>ล้างตัวกรอง<span class="filter-count ms-1"></span>
                                    </button>
                                </div>

                                <div class="collapse" id="customerFilterBox">
                                <div class="pt-3">

                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-medium mb-1">
                                                ค้นหา
                                                <span class="text-muted fw-normal">(รหัส / ชื่อ / ชื่อเล่น / โทร / เลขผู้เสียภาษี / ที่อยู่)</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ti ti-search"></i></span>
                                                <input type="text" name="search" class="form-control p_search" oninput="loadData(page)">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-medium mb-1">ประเภทลูกค้า</label>
                                            <select name="type" class="form-select p_search" onchange="loadData(page)">
                                                <option value="">ทั้งหมด</option>
                                                @foreach ($types as $t)
                                                    <option value="{{ $t->type }}">{{ $t->type }} — {{ $t->t_namee }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row g-3 align-items-end mt-1">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-medium mb-1">รหัสผู้ขาย</label>
                                            <select name="sale" class="form-select p_search" onchange="loadData(page)">
                                                <option value="">ทั้งหมด</option>
                                                @foreach ($sales as $s)
                                                    <option value="{{ $s['sale'] }}">{{ $s['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-medium mb-1">จังหวัด</label>
                                            <select name="city" class="form-select p_search" onchange="loadData(page)">
                                                <option value="">ทั้งหมด</option>
                                                @foreach ($cities as $c)
                                                    <option value="{{ $c }}">{{ $c }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-medium mb-1">สถานะ</label>
                                            <select name="black" class="form-select p_search" onchange="loadData(page)">
                                                <option value="">ทั้งหมด</option>
                                                <option value="N">ไม่ติด Blacklist</option>
                                                <option value="Y">ติด Blacklist</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                                </div>
                                {{-- /#customerFilterBox --}}

                                {{-- state การเรียง — เก็บนอก #table-data เพื่อคงค่าเมื่อตารางโหลดใหม่ --}}
                                <input type="hidden" name="sort_col" value="code" class="p_search">
                                <input type="hidden" name="sort_dir" value="asc" class="p_search">

                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <div class="d-flex align-items-center">
                                        <label class="form-label small fw-medium mb-0 me-2">เรียงตามรหัสลูกค้า</label>
                                        <select name="sort" class="form-select form-select-sm" style="width: 210px;"
                                            onchange="onSortDropdown(this.value)">
                                            <option value="asc">น้อย → มาก</option>
                                            <option value="desc">มาก → น้อย</option>
                                            <option value="" hidden>เรียงตามคอลัมน์ (คลิกหัวตาราง)</option>
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <label class="form-label small fw-medium mb-0 me-2">แสดง</label>
                                        <select name="limit" class="form-select form-select-sm p_search" style="width: 90px;"
                                            onchange='loadData("{{ $page_url }}/datatable")'>
                                            <option value="15">15</option>
                                            <option value="50">50</option>
                                            <option value="75">75</option>
                                            <option value="100">100</option>
                                        </select>
                                        <span class="ms-2 small fw-medium">รายการ/หน้า</span>
                                    </div>
                                </div>
                            </div>

                            <div id="table-data">
                                {{-- Table โหลดผ่าน AJAX จาก customer/datatable --}}
                                <div class="text-center py-5 text-muted">
                                    <div class="spinner-border spinner-border-sm me-2"></div>
                                    กำลังโหลดข้อมูล...
                                </div>
                            </div>

                        </div>

                    </div>
                    <!-- / Content -->

                    @include('layout/inc_footer')

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- Modal: ฟอร์มข้อมูลลูกค้า (ผังตามฟอร์ม Access เดิม)            -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="modal modalHeadDecor fade" id="customerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalTitle">
                        <i class="ti ti-address-book me-1"></i>ข้อมูลลูกค้า
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="customerFormBody">
                    {{-- โหลดฟอร์มผ่าน AJAX จาก customer/form --}}
                </div>
            </div>
        </div>
    </div>

    @include('layout/inc_js')

<script>
    var page = "{{ $page_url }}/datatable";
    var searchData = {};
    // ต้องประกาศก่อน loadData ครั้งแรก — เลี่ยง hoisting ทำให้ dtSeq เป็น NaN (spinner ค้าง)
    var dtXhr = null, dtSeq = 0;

    // ตัวนับ index ของแถวผู้ติดต่อที่เพิ่มใหม่ — เริ่มที่ 1000 เพื่อไม่ชนกับแถวเดิม (0..n)
    var cfContactSeq = 1000;

    // ป้ายชื่อของแต่ละช่องกรอง — ใช้ประกอบแถบ "รายละเอียดการค้นหา" (#filterSummary)
    // ⚠ ต้องประกาศตรงนี้ (ก่อน loadData ครั้งแรก) เพราะ renderFilterSummary() ถูกเรียกใน loadData
    var FILTER_LABELS = {
        search: 'ค้นหา',
        type:   'ประเภทลูกค้า',
        sale:   'รหัสผู้ขาย',
        city:   'จังหวัด',
        black:  'สถานะ'
    };

    loadData(page);

    $(function () {
        syncSortDropdown();
    });

    // ────────────────────────────────────────────────────────
    //  เรียงลำดับ — state เดียว (sort_col/sort_dir) ใช้ร่วม dropdown + คลิกหัวตาราง
    // ────────────────────────────────────────────────────────
    function setSort(col, dir) {
        $('input[name="sort_col"]').val(col);
        $('input[name="sort_dir"]').val(dir);
        syncSortDropdown();
        loadData(page);
    }
    function syncSortDropdown() {
        var col = $('input[name="sort_col"]').val();
        var dir = $('input[name="sort_dir"]').val();
        $('select[name="sort"]').val(col === 'code' ? dir : '');
    }
    function onSortDropdown(val) {
        if (val === '') return;
        setSort('code', val);
    }
    // delegated เพราะ thead อยู่ใน #table-data ที่โหลดใหม่ทุกครั้ง
    $(document).on('click', '#table-data th[data-sort]', function () {
        var col    = String($(this).data('sort'));
        var curCol = $('input[name="sort_col"]').val();
        var curDir = $('input[name="sort_dir"]').val();
        var dir    = (curCol === col) ? (curDir === 'asc' ? 'desc' : 'asc') : 'asc';
        setSort(col, dir);
    });

    // ────────────────────────────────────────────────────────
    //  ฟอร์มข้อมูลลูกค้า
    // ────────────────────────────────────────────────────────

    // เปิดฟอร์มลูกค้าเดิม
    function customerOpen(code) {
        loadCustomerForm(code);
    }

    // เปิดฟอร์มเปล่าสำหรับลูกค้าใหม่
    function customerNew() {
        loadCustomerForm('');
    }

    function loadCustomerForm(code) {
        $.get("{{ route('customer.form') }}", { code: code }, function (res) {
            if (res.status !== 200) {
                Swal.fire({ icon: 'error', title: 'เปิดฟอร์มไม่ได้', text: res.message || '' });
                return;
            }
            $('#customerFormBody').html(res.data);
            $('#customerModalTitle').html(code
                ? '<i class="ti ti-address-book me-1"></i>ข้อมูลลูกค้า ' + escHtml(code)
                : '<i class="ti ti-user-plus me-1"></i>เพิ่มลูกค้าใหม่');
            $('#customerModal').modal('show');
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'โหลดฟอร์มไม่สำเร็จ กรุณาลองใหม่' });
        });
    }

    // เพิ่มแถวผู้ติดต่อ (ฟอร์มโหลดผ่าน AJAX จึงผูกแบบ delegation)
    $(document).on('click', '#btn_add_contact', function () {
        var i = cfContactSeq++;
        var cell = function (field, len) {
            return '<td><input type="text" class="form-control form-control-sm" maxlength="' + len +
                   '" name="contacts[' + i + '][' + field + ']"></td>';
        };
        $('#contactTable tbody').append(
            '<tr class="cf-contact-row">'
            + cell('contactname', 20) + cell('position', 20) + cell('tel', 30)
            + cell('fax', 20) + cell('remark', 30)
            + '<td class="text-center">'
            + '<button type="button" class="btn btn-sm btn-icon btn-label-danger cf-del-contact" title="ลบแถวนี้">'
            + '<i class="ti ti-trash"></i></button></td>'
            + '</tr>'
        );
        $('#contactTable tbody tr:last input:first').trigger('focus');
    });

    $(document).on('click', '.cf-del-contact', function () {
        $(this).closest('tr').remove();
    });

    // เพิ่ม/ลบ สถานที่ส่ง
    $(document).on('click', '#btn_add_dv', function () {
        $('#dvpointList').append(
            '<div class="col-md-4 cf-dv-row">'
            + '<div class="input-group input-group-sm">'
            + '<input type="text" class="form-control" maxlength="20" name="dvpoints[]">'
            + '<button type="button" class="btn btn-label-danger cf-del-dv" title="ลบ"><i class="ti ti-trash"></i></button>'
            + '</div></div>'
        );
        $('#dvpointList .cf-dv-row:last input').trigger('focus');
    });

    $(document).on('click', '.cf-del-dv', function () {
        $(this).closest('.cf-dv-row').remove();
    });

    // บันทึก
    $(document).on('click', '#btn_customer_save', function () {
        var $btn = $(this);

        $btn.prop('disabled', true);
        $.ajax({
            url: "{{ route('customer.save') }}",
            method: 'POST',
            dataType: 'json',
            data: $('#customer_form').serialize(),
            success: function (res) {
                $('#customerModal').modal('hide');
                loadData(page);
                Swal.fire({
                    icon: 'success', title: 'สำเร็จ', text: res.message,
                    timer: 1800, showConfirmButton: false
                });
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                Swal.fire({
                    icon: xhr.status === 422 ? 'warning' : 'error',
                    title: xhr.status === 422 ? 'ข้อมูลไม่ถูกต้อง' : 'บันทึกไม่สำเร็จ',
                    text: msg
                });
            },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    // ลบลูกค้า — server จะปฏิเสธถ้ามีธุรกรรมผูกอยู่
    function customerDelete(code, name) {
        Swal.fire({
            icon: 'warning',
            title: 'ลบลูกค้ารายนี้?',
            html: '<strong>' + escHtml(code) + '</strong> ' + escHtml(name || '')
                + '<br><small class="text-muted">ผู้ติดต่อและสถานที่ส่งของลูกค้ารายนี้จะถูกลบไปด้วย</small>',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#d33'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.ajax({
                url: "{{ route('customer.delete') }}",
                method: 'POST',
                dataType: 'json',
                data: { _token: "{{ csrf_token() }}", code: code },
                success: function (res) {
                    loadData(page);
                    Swal.fire({ icon: 'success', title: res.message, toast: true,
                        position: 'top-end', timer: 1800, showConfirmButton: false });
                },
                error: function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'ลบไม่สำเร็จ กรุณาลองใหม่';
                    Swal.fire({ icon: 'error', title: 'ลบไม่ได้', text: msg });
                }
            });
        });
    }

    // ────────────────────────────────────────────────────────
    //  DataTable filter machinery
    // ────────────────────────────────────────────────────────
    function collectSearchData(){
        var data = {};
        $('.p_search').each(function(){
            var v = $(this).val();
            if (v !== '' && v !== null) data[$(this).attr('name')] = v;
        });
        return data;
    }

    function updateFilterButtonState(){
        var count = 0;
        $('.p_search:not([name="limit"]):not([name="sort_col"]):not([name="sort_dir"])').each(function(){
            var v = $(this).val();
            if (v !== '' && v !== null) count++;
        });
        var $btn = $('#btnResetFilters');
        if (count > 0) { $btn.removeClass('btn-label-secondary').addClass('btn-danger'); $btn.find('.filter-count').text('(' + count + ')'); }
        else           { $btn.removeClass('btn-danger').addClass('btn-label-secondary'); $btn.find('.filter-count').text(''); }

        renderFilterSummary();
    }

    // ── รายละเอียดการค้นหา (โชว์ตอนพับตัวกรอง) — ป้ายชื่อ FILTER_LABELS ประกาศไว้ต้นสคริปต์ ──
    // escape ค่าที่ผู้ใช้พิมพ์ก่อนยัดลง HTML (กัน XSS จากคำค้น)
    function escHtml(s){ return $('<div>').text(s == null ? '' : s).html(); }

    function renderFilterSummary(){
        var chips = [];
        $('.p_search:not([name="limit"]):not([name="sort_col"]):not([name="sort_dir"])').each(function(){
            var label = FILTER_LABELS[$(this).attr('name')];
            if (!label) return;
            // ตัดสินว่า "ไม่ได้กรอง" จาก value เสมอ — ตัวเลือก "ทั้งหมด" มี value ว่างแต่มีข้อความ
            var raw = $(this).val();
            if (raw === '' || raw === null) return;
            // select: แสดงข้อความของตัวเลือก ไม่ใช่ value
            var val = $(this).is('select') ? $(this).find('option:selected').text().trim() : raw;
            chips.push('<span class="badge bg-label-primary fw-normal">'
                + '<span class="text-dark fw-medium">' + escHtml(label) + ':</span> '
                + escHtml(val) + '</span>');
        });
        $('#filterSummary').html(chips.join(''));   // ไม่มีตัวกรอง = ปล่อยว่าง
    }

    // หมุนลูกศรตามสถานะพับ/กาง + ซ่อนแถบสรุปตอนกาง (ข้างในเห็นค่าจริงอยู่แล้ว)
    $(document).on('show.bs.collapse hide.bs.collapse', '#customerFilterBox', function(e){
        $('#btnToggleFilters .toggle-caret')
            .toggleClass('ti-chevron-down', e.type === 'hide')
            .toggleClass('ti-chevron-up',   e.type === 'show');
        $('#filterSummary').toggleClass('d-none', e.type === 'show');
    });

    // กัน AJAX race: รับเฉพาะผลของ request ล่าสุด
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
        // ไม่ล้าง limit + sort_col/sort_dir (เป็นการตั้งค่าแสดงผล ไม่ใช่ตัวกรอง)
        $('.p_search:not([name="limit"]):not([name="sort_col"]):not([name="sort_dir"])').each(function(){
            $(this).val('');
        });
        loadData("{{ $page_url }}/datatable");
    }
</script>

</body>

</html>
