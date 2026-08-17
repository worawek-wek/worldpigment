<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">

<head>
    @include('layout/inc_header')
    <title>ใบสั่งซื้อ - World Pigment</title>

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

/* ── เรียงตาม — คลิกหัวตาราง + เน้นคอลัมน์ที่กำลังเรียง (เหมือนหน้าใบเสนอราคา) ── */
#table-data th.th-sort { cursor: pointer; user-select: none; }
#table-data th.th-sort:hover { background-color: #e9ecef; }
.th-sort-icon { font-size: .9rem; opacity: .35; vertical-align: middle; }
.th-sort-icon.active { opacity: 1; color: #ffd43b; font-size: 1.05rem; font-weight: 700; margin-left: .15rem; }
#table-data thead th.col-sorted { background-color: #696cff; color: #fff; box-shadow: inset 0 -3px 0 #ffd43b; }
#table-data thead th.col-sorted:hover { background-color: #5a5ef0; }
#table-data thead th.col-sorted small { color: rgba(255,255,255,.8) !important; }
#table-data tbody td.col-sorted { box-shadow: inset 2px 0 0 #696cff, inset -2px 0 0 #696cff; }

/* ══ ฟอร์มบันทึกใบสั่งซื้อ — คงผังและโทนสีของฟอร์ม Access เดิมไว้ ══ */

/* แถบประเภทใบสั่ง (ม่วง เหมือนหัวฟอร์มเดิม) */
.of-typebar {
    background: #e8e4f5;
    border: 1px solid #c9c0e6;
    border-radius: .6rem;
    padding: .85rem 1rem;
}
.of-typebar-title {
    font-weight: 700;
    color: #4b3f8f;
    margin-bottom: .5rem;
}
.of-typegrid { display: flex; flex-direction: column; gap: .25rem; }
.of-typerow { display: flex; flex-wrap: wrap; gap: 1.4rem; }
.of-typerow .form-check-label { font-weight: 600; letter-spacing: .3px; }

/* กล่อง section ในฟอร์ม */
.of-sec {
    border: 1px solid #e2e7ea;
    border-radius: .6rem;
    padding: 1rem 1.15rem 1.15rem;
    background: #fff;
}
.of-sec-title {
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
.of-sec .form-label { margin-bottom: .25rem; font-size: .85rem; font-weight: 600; }

/* กล่อง "กรณีสั่งทำสต๊อก" + "ราคา" — แยกโทนตามฟอร์มเดิม */
.of-sec-stock { background: #f7fbff; border-color: #cfe2f3; }
.of-sec-stock .of-sec-title { color: #2c6ea4; border-bottom-color: #cfe2f3; }
.of-sec-price { background: #fdfaf4; border-color: #ecdfc4; }
.of-sec-price .of-sec-title { color: #a3781f; border-bottom-color: #ecdfc4; }
.of-sec-item  { background: #f8fbf7; border-color: #d6e7cf; }
.of-sec-item .of-sec-title { color: #4c7c3b; border-bottom-color: #d6e7cf; }

/* ช่องเน้นสีตามฟอร์ม Access */
.of-hl-yellow { background-color: #fffbcc !important; }
.of-hl-green  { background-color: #dff3d8 !important; }
.of-warn      { background-color: #fbdcc4 !important; font-weight: 600; }
.of-sell      { background-color: #e8e8e8 !important; }

/* แถว checkbox ท้ายกล่องซ้าย */
.of-checkrow { display: flex; flex-wrap: wrap; gap: 1.25rem; padding-top: .35rem; }

/* ตารางรายการในใบสั่งซื้อ */
#orderItemsTable th { white-space: nowrap; font-size: .82rem; vertical-align: middle; }
#orderItemsTable td { font-size: .85rem; }
#orderItemsTable input.form-control-sm { min-width: 90px; }

/* ══ ฟอร์มขออนุมัติราคาพิเศษ — โทนฟ้าตามฟอร์ม Access เดิม ══ */
.pa-body { background: #eef6fb; }
.pa-body .form-label { margin-bottom: .25rem; font-size: .85rem; font-weight: 600; }

/* ช่องราคา 3 ช่อง + ช่องพิเศษ — กล่องเล็กเรียงแนวนอน */
.pa-pricebox { width: 130px; }
.pa-pricebox-cap {
    font-size: .72rem;
    font-weight: 600;
    color: #5a6a78;
    margin-bottom: .15rem;
    white-space: nowrap;
}
/* กลุ่มราคาที่ตรงกับจำนวนสั่งซื้อ — เน้นกรอบให้เห็นว่าใช้ช่องไหน */
.pa-pricebox.pa-active .form-control { border-color: #0d6efd; box-shadow: 0 0 0 .18rem rgba(13,110,253,.18); font-weight: 700; }
.pa-pricebox.pa-active .pa-pricebox-cap { color: #0d6efd; }

.pa-hl-yellow { background-color: #fff59d !important; font-weight: 600; }
.pa-hl-pink   { background-color: #f8bbd0 !important; font-weight: 600; }
.pa-sell      { background-color: #fff !important; color: #d32f2f; }

/* กล่องข้อมูลราคาที่ตกลงไว้ (คอลัมน์ขวา) */
.pa-sidebox {
    background: #fff;
    border: 1px solid #cfe2f3;
    border-radius: .6rem;
    padding: 1rem;
}
.pa-sidebox-title {
    font-size: .95rem;
    font-weight: 700;
    color: #2c6ea4;
    margin-bottom: .75rem;
    padding-bottom: .45rem;
    border-bottom: 2px solid #cfe2f3;
    display: flex;
    align-items: center;
    gap: .5rem;
}

#approvalGridTable th { white-space: nowrap; font-size: .8rem; }
#approvalGridTable td { font-size: .85rem; }

/* ══ ฟอร์มอนุมัติราคาใบสั่งซื้อ (morderAPPV) — โทนเทาอ่อนตามฟอร์มเดิม ══ */
.oa-body { background: #f4f4f0; }
.oa-body .form-label { margin-bottom: .25rem; font-size: .85rem; font-weight: 600; }
.oa-checkrow { display: flex; flex-wrap: wrap; gap: 1.25rem; padding: .45rem .75rem; background: #fff; border: 1px solid #d9d9d2; border-radius: .375rem; }

/* ตารางรายการ — พื้นเหลืองเหมือนฟอร์มเดิม */
.oa-grid table { background: #ffffdd; }
#oaItemsTable thead th { background: #e8e8d0; white-space: nowrap; font-size: .8rem; }
#oaItemsTable tbody tr { cursor: pointer; }
#oaItemsTable tbody tr:hover { background: #fff9a8; }
#oaItemsTable tbody tr.oa-selected { background: #ffe082; font-weight: 600; }
#oaItemsTable td { font-size: .85rem; }

.oa-hl-blue  { background-color: #cfe9fb !important; }
.oa-hl-green { background-color: #ccffcc !important; }
.oa-sell     { background-color: #ffcdd2 !important; color: #c62828; font-size: 1.15rem; }

/* แถบเดินระเบียนล่างฟอร์ม */
.oa-nav {
    display: flex;
    align-items: center;
    gap: .35rem;
    padding: .5rem .75rem;
    background: #e6e6de;
    border: 1px solid #cfcfc4;
    border-radius: .375rem;
    font-size: .85rem;
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
                            <i class="ti ti-shopping-cart text-primary"></i>
                            ใบสั่งซื้อ
                        </h3>
                        <p class="text-muted mb-0">
                            บันทึกคำสั่งซื้อจากลูกค้า (morder / suborder)
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-label-warning border" onclick="orderApprovalOpen()">
                            <i class="ti ti-gavel me-1"></i>
                            อนุมัติราคาใบสั่งซื้อ
                            <span class="badge bg-danger ms-1 d-none" id="oaQueueBadge">0</span>
                        </button>
                        <button class="btn btn-label-primary border" style="color: #1f158e;" onclick="approvalOpen()">
                            <i class="ti ti-discount-check me-1"></i>
                            ขออนุมัติราคาพิเศษ
                        </button>
                        <button class="btn btn-primary" onclick="orderNew()">
                            <i class="ti ti-plus me-1"></i>
                            เพิ่มใบสั่งซื้อใหม่
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
                    <label class="form-label small fw-medium mb-1">
                        ค้นหา
                        <span class="text-muted fw-normal">(เลขที่ใบสั่ง / P.O. / รหัสลูกค้า / ชื่อลูกค้า / รหัสสินค้า)</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" name="search" class="form-control p_search" oninput="loadData(page)">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-medium mb-1">วันที่สั่ง</label>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small fw-medium">ตั้งแต่</span>
                        <input type="text" name="date_from" class="form-control flatpickr-date p_search" autocomplete="off" placeholder="วว/ดด/ปปปป">
                        <span class="small fw-medium">ถึง</span>
                        <input type="text" name="date_to" class="form-control flatpickr-date p_search" autocomplete="off" placeholder="วว/ดด/ปปปป">
                    </div>
                </div>
            </div>

            {{-- แถวตัวกรอง 2: ประเภทใบสั่ง + ผลิตที่ --}}
            <div class="row g-3 align-items-end mt-1">
                <div class="col-md-4">
                    <label class="form-label small fw-medium mb-1">ประเภทใบสั่ง</label>
                    <select name="order_type" class="form-select p_search" onchange="loadData(page)">
                        <option value="">ทั้งหมด</option>
                        @foreach ($type_rows as $types)
                            @foreach ($types as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-medium mb-1">ผลิตที่</label>
                    <select name="company" class="form-select p_search" onchange="loadData(page)">
                        <option value="">ทั้งหมด</option>
                        @foreach ($companies as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end my-3">
                <button type="button" id="btnResetFilters" class="btn btn-label-secondary" onclick="resetFilters()">
                    <i class="ti ti-x me-1"></i>ล้างตัวกรอง<span class="filter-count ms-1"></span>
                </button>
            </div>

            {{-- state การเรียง — เก็บนอก #table-data เพื่อคงค่าเมื่อตารางโหลดใหม่ (default = วันที่สั่งล่าสุด) --}}
            <input type="hidden" name="sort_col" value="Mdate" class="p_search">
            <input type="hidden" name="sort_dir" value="desc" class="p_search">

            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <div class="d-flex align-items-center">
                    <label class="form-label small fw-medium mb-0 me-2">เรียงตามวันที่สั่ง</label>
                    <select name="sort" class="form-select form-select-sm" style="width: 210px;"
                        onchange="onSortDropdown(this.value)">
                        <option value="desc">ล่าสุด</option>
                        <option value="asc">เก่าสุด</option>
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
            {{-- Table โหลดผ่าน AJAX จาก order/datatable --}}
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

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- Modal: ฟอร์มบันทึกใบสั่งซื้อ (ผังตามฟอร์ม Access เดิม)             -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="modal modalHeadDecor fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="orderModalTitle">บันทึกคำสั่งซื้อ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            @include('order.form')

            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>

        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- Modal: อนุมัติราคาใบสั่งซื้อ (morderAPPV) — ผังตามฟอร์ม Access เดิม -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="modal modalHeadDecor fade" id="orderApprovalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    อนุมัติราคาใบสั่งซื้อ
                    <span class="fw-normal" style="font-size:.8rem;">(ไม่รวมทำ STOCK + ไม่รวมใบจอง R)</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            @include('order.order-approval')

            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>

        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- Modal: ขออนุมัติราคาพิเศษ (MD) — ผังตามฟอร์ม Access เดิม           -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="modal modalHeadDecor fade" id="approvalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">MK ขออนุมัติราคาพิเศษ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            @include('order.price-approval')

            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>

        </div>
    </div>
</div>

    <!-- / Layout wrapper -->
    @include('layout/inc_js')
<script>
    var page = "{{ $page_url }}/datatable";
    var searchData = {};
    // ต้องประกาศก่อน loadData ครั้งแรก — เลี่ยง hoisting ทำให้ dtSeq เป็น NaN (spinner ค้าง)
    var dtXhr = null, dtSeq = 0;
    loadData(page);

    $(function () {
        flatpickr('.flatpickr-date', {
            dateFormat: 'd/m/Y',
            allowInput: true,
            static: true,
            disableMobile: true,
            onChange: function (_, __, instance) {
                if (instance.input.classList.contains('p_search')) loadData(page);
            }
        });
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
        $('select[name="sort"]').val(col === 'Mdate' ? dir : '');
    }
    function onSortDropdown(val) {
        if (val === '') return;
        setSort('Mdate', val);
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
    //  ตัวช่วยจัดรูปแบบค่า
    // ────────────────────────────────────────────────────────
    function esc(v){ return String(v == null ? '' : v).replace(/"/g, '&quot;'); }

    // 'YYYY-MM-DD hh:mm:ss' → 'DD/MM/YY' (ว่าง = '')
    function fmtDate(v, withYear4){
        if (!v) return '';
        var s = String(v).substring(0, 10).split('-');
        if (s.length !== 3) return String(v);
        var yy = withYear4 ? s[0] : s[0].substring(2);
        return s[2] + '/' + s[1] + '/' + yy;
    }
    // + เวลา (ช่อง "วันที่" บนหัวฟอร์มเดิมโชว์เวลาด้วย)
    function fmtDateTime(v){
        if (!v) return '';
        var t = String(v).substring(11, 16);
        return fmtDate(v) + (t ? ' ' + t : '');
    }
    function fmtNum(v, digits){
        if (v === null || v === undefined || v === '') return '';
        var n = parseFloat(v);
        if (isNaN(n)) return '';
        return n.toLocaleString('en-US', {minimumFractionDigits: digits, maximumFractionDigits: digits});
    }
    // หมายเหตุ: ช่องกรอกตัวเลขแบบใส่คอมมาอัตโนมัติ (class="js-comma") + ตัวช่วย
    // numVal() / commaFmt() / stripCommaFields() อยู่ที่ layout/inc_js.blade.php (ใช้ร่วมทุกหน้า)

    // ตั้งค่าให้ flatpickr (รับ Y-m-d / datetime); ว่าง = เคลียร์
    function setFp(id, val){
        var el = document.getElementById(id);
        if (!el) return;
        if (el._flatpickr) {
            if (val) el._flatpickr.setDate(String(val).substring(0, 10), false, 'Y-m-d');
            else     el._flatpickr.clear();
        } else {
            $(el).val(val ? String(val).substring(0, 10) : '');
        }
    }

    // ────────────────────────────────────────────────────────
    //  ฟอร์มบันทึกใบสั่งซื้อ
    // ────────────────────────────────────────────────────────

    // เปิดใบที่มีอยู่แล้ว — โหลดหัวใบ + รายการ + ข้อมูลราคา แล้วเติมลงฟอร์ม
    function orderOpen(orderno){
        $.getJSON("{{ $page_url }}/form", {orderno: orderno}, function(res){
            if (!res.found){
                Swal.fire('ไม่พบใบสั่งซื้อ', 'เลขที่ ' + orderno + ' ไม่มีอยู่ในระบบ', 'warning');
                return;
            }
            fillOrderForm(res);
            $('#orderModal').modal('show');
        }).fail(function(){
            Swal.fire('โหลดข้อมูลไม่สำเร็จ', 'ลองใหม่อีกครั้ง', 'error');
        });
    }

    // ใบใหม่ — ล้างฟอร์ม แล้วรอผู้ใช้เลือกประเภทเพื่อดึงเลขที่ถัดไป
    function orderNew(){
        clearOrderForm();
        $('#orderModalTitle').text('บันทึกคำสั่งซื้อ — ใบใหม่');
        $('#orderModal').modal('show');
    }

    // เปลี่ยนประเภทใบสั่ง → ดึงเลขรันถัดไปของประเภทนั้นมาโชว์ (ยังไม่เดินเลขจริง)
    function onOrderTypeChange(type){
        if ($('#o_Orderno').data('existing')) return;   // ใบเดิม — ห้ามเปลี่ยนเลขที่
        $.getJSON("{{ $page_url }}/next-orderno", {type: type}, function(res){
            if (res.found && res.orderno) $('#o_Orderno').val(res.orderno);
        });
    }

    function clearOrderForm(){
        $('#orderModal input[type="text"], #orderModal input[type="number"]').val('');
        $('#orderModal input[type="checkbox"]').prop('checked', false);
        $('#orderModal input[name="order_type_form"]').prop('checked', false);
        $('#o_Company').val('');
        $('#o_DVpoint').html('<option value="">— ไม่ระบุ —</option>');
        $('#o_Orderno').removeData('existing');
        setFp('o_sendend', '');
        renderOrderItems([]);
    }

    function fillOrderForm(res){
        clearOrderForm();

        var o = res.order || {};
        $('#orderModalTitle').text('บันทึกคำสั่งซื้อ — ' + (o.Orderno || ''));

        // ประเภท (radio) + เลขที่ใบสั่ง
        if (o.type) $('#o_type_' + o.type).prop('checked', true);
        $('#o_Orderno').val(o.Orderno || '').data('existing', true);
        $('#o_Mdate').val(fmtDateTime(o.Mdate));

        $('#o_Company').val(o.Company || '');
        $('#o_PO').val(o.PO || '');
        $('#o_Custno').val(o.Custno || '');
        $('#o_Custname').val((res.customer && res.customer.name) || o.Custname || '');
        $('#o_Emp').val(o.Emp || '');
        $('#o_supno').val(o.supno || '');
        $('#o_RsvNo').val(o.RsvNo || '');
        $('#o_netqty').val(commaFmt(o.netqty, 2));

        // ประเภทอุตสาหกรรมของลูกค้า (ปุ่ม itype เดิม)
        $('#o_itype').val(res.customer && res.customer.type
            ? res.customer.type + ' — ' + (res.customer.type_name || '')
            : '');

        // สถานที่ส่ง — รายการของลูกค้ารายนี้
        fillDvpoints(res.dvpoints, o.DVpoint);

        // กรณีสั่งทำสต๊อก
        setFp('o_sendend', o.sendend);
        $('#o_SendCust').val(o.SendCust != null ? o.SendCust : '');
        $('#o_HMStore').val(commaFmt(o.HMStore, 2));
        $('#o_sendmth').val(commaFmt(o.sendmth, 2));

        // checkbox (แปลงจาก -1 ของ Access มาแล้วฝั่ง server)
        $('#o_Send').prop('checked', !!o.Send);
        $('#o_RP').prop('checked', !!o.RP);
        $('#o_Spec').prop('checked', !!o.Spec);
        $('#o_Cer').prop('checked', !!o.Cer);
        $('#o_MSDS').prop('checked', !!o.MSDS);

        // สินค้า + ราคา
        $('#o_itemno').val(res.itemno || '');
        $('#o_price').val(fmtNum(o.price, 2));
        fillPriceBox(res.price);

        renderOrderItems(res.items || []);
    }

    function fillDvpoints(list, selected){
        var html = '<option value="">— ไม่ระบุ —</option>';
        (list || []).forEach(function(p){
            html += '<option value="' + esc(p) + '">' + esc(p) + '</option>';
        });
        // ค่าที่บันทึกไว้ไม่อยู่ในรายการของลูกค้า (ข้อมูลเก่า) → เพิ่มเข้าไปเพื่อไม่ให้ค่าหาย
        if (selected && (list || []).indexOf(selected) === -1){
            html += '<option value="' + esc(selected) + '">' + esc(selected) + ' (ไม่อยู่ในรายการ)</option>';
        }
        $('#o_DVpoint').html(html).val(selected || '');
    }

    function fillPriceBox(p){
        p = p || {};
        $('#o_fixed_price').val(fmtNum(p.fixed_price, 2));
        $('#o_price2').val(fmtNum(p.price2, 2));
        // ราคาขั้นต่ำ = ราคาของกลุ่มที่ตรงกับน้ำหนักสั่ง (A ≥1,000 / B ≥500 / C ต่ำกว่า 500)
        $('#o_min_price').val(fmtNum(p.min_price, 2));
        $('#o_price_group').val(p.group ? p.group + ' — ' + (p.group_label || '') : '');
        $('#o_appv_price').val(fmtNum(p.appv_price, 2));
        $('#o_valid_to').val(fmtDate(p.valid_to));
    }

    // ── ตารางรายการ (suborder) — เฟสนี้แสดงอย่างเดียว ──
    function renderOrderItems(items){
        var body = '', total = 0;

        if (!items.length){
            body = '<tr><td colspan="9" class="text-center py-3 text-muted">ยังไม่มีรายการ</td></tr>';
        } else {
            items.forEach(function(r, i){
                total += parseFloat(r.Production || 0);
                body += '<tr>'
                     +  '<td class="text-center text-muted">' + (i + 1) + '</td>'
                     +  '<td class="text-end">' + (fmtNum(r.Stock, 2) || '-') + '</td>'
                     +  '<td class="text-end fw-semibold">' + (fmtNum(r.Production, 2) || '-') + '</td>'
                     +  '<td class="text-center">' + (fmtDate(r.custwant) || '-') + '</td>'
                     +  '<td class="text-center">' + (fmtDate(r.senddate) || '-') + '</td>'
                     +  '<td class="text-center text-primary">' + (fmtDate(r.EndP) || '-') + '</td>'
                     +  '<td class="text-center">' + (fmtDate(r.DVDate) || '-') + '</td>'
                     +  '<td>' + esc(r.outno || '-') + '</td>'
                     +  '<td>' + esc(r.Remark || '') + '</td>'
                     +  '</tr>';
            });
        }

        $('#orderItems').html(body);
        $('#o_total_prod').text(fmtNum(total, 2) || '0.00');
    }

    // ── ค้นลูกค้าจากรหัส (เติมชื่อ + สถานที่ส่ง + ค่าตั้งต้น RP/CER) ──
    var custLookupTimer = null, custLookupXhr = null;
    function lookupOrderCustomer(code){
        code = (code || '').trim();
        clearTimeout(custLookupTimer);
        if (!code){ $('#o_Custname').val(''); $('#o_itype').val(''); fillDvpoints([], ''); return; }
        custLookupTimer = setTimeout(function(){
            if (custLookupXhr) custLookupXhr.abort();
            custLookupXhr = $.getJSON("{{ $page_url }}/customer/" + encodeURIComponent(code), function(res){
                if ($('#o_Custno').val().trim() !== code) return;   // กันผลเก่ามาทับ
                if (!res.found){
                    $('#o_Custname').val('');
                    $('#o_itype').val('');
                    fillDvpoints([], '');
                    return;
                }
                var c = res.customer;
                $('#o_Custname').val(c.name || '');
                $('#o_itype').val(c.type ? c.type + ' — ' + (c.type_name || '') : '');
                fillDvpoints(res.dvpoints, '');
                // ค่าตั้งต้นจากข้อมูลลูกค้า — เฉพาะใบใหม่ (ใบเดิมใช้ค่าที่บันทึกไว้)
                if (!$('#o_Orderno').data('existing')){
                    $('#o_RP').prop('checked', !!c.RP);
                    $('#o_Cer').prop('checked', !!c.CER);
                    $('#o_MSDS').prop('checked', !!c.MSDS);
                }
            });
        }, 350);
    }

    // การบันทึกยังไม่เปิดใช้งาน — รอยืนยันกติกาการเดินเลขที่ใบสั่ง + คอลัมน์ที่แก้ได้
    function saveOrder(){
        Swal.fire({
            icon: 'info',
            title: 'ยังไม่เปิดใช้งานการบันทึก',
            html: 'หน้านี้เป็น UI + อ่านข้อมูลจริงเท่านั้น<br>' +
                  'การบันทึกจะเปิดใช้หลังยืนยันกติกาการเดินเลขที่ใบสั่ง (ตาราง <code>orderrun</code>) และคอลัมน์ที่แก้ได้'
        });
    }

    // ════════════════════════════════════════════════════════
    //  ฟอร์มอนุมัติราคาใบสั่งซื้อ (morderAPPV)
    //  — คิวเดินทีละใบเหมือนฟอร์ม Access (ระเบียน N จาก M)
    // ════════════════════════════════════════════════════════
    var OA_URL   = "{{ $page_url }}/order-approval";
    var oaQueue  = [];    // รายการใบที่รออนุมัติ
    var oaIndex  = 0;     // ระเบียนที่กำลังแสดง (0-based)
    var oaPrices = {};    // ราคาอ้างอิงของแต่ละเบอร์ในใบปัจจุบัน

    // นับคิวรออนุมัติตั้งแต่เปิดหน้า → โชว์เป็น badge บนปุ่ม
    $(function(){ oaLoadQueue(false); });

    function oaLoadQueue(openAfter){
        return $.getJSON(OA_URL + '/queue', function(res){
            oaQueue = res.rows || [];
            $('#oa_total').text(oaQueue.length);
            var $badge = $('#oaQueueBadge');
            if (oaQueue.length) $badge.text(oaQueue.length).removeClass('d-none');
            else                $badge.addClass('d-none');
            if (openAfter) oaGo(0);
        });
    }

    function orderApprovalOpen(){
        $('#orderApprovalModal').modal('show');
        oaLoadQueue(true);
    }

    function orderApprovalRefresh(){ oaLoadQueue(true); }

    // ไปยังระเบียนที่ i (i = -1 หมายถึงท้ายสุด)
    function oaGo(i){
        if (!oaQueue.length){ oaClear(); return; }
        i = parseInt(i, 10);
        if (isNaN(i) || i < 0) i = oaQueue.length - 1;
        if (i > oaQueue.length - 1) i = oaQueue.length - 1;
        oaIndex = i;
        $('#oa_pos').val(i + 1);
        oaLoadRecord(oaQueue[i].Orderno);
    }
    function oaStep(delta){
        if (!oaQueue.length) return;
        var i = oaIndex + delta;
        if (i < 0) i = 0;
        if (i > oaQueue.length - 1) i = oaQueue.length - 1;
        oaGo(i);
    }

    function oaClear(){
        $('#orderApprovalModal input[type="text"]').val('');
        $('#orderApprovalModal input[type="checkbox"]').prop('checked', false);
        $('#oa_pos').val('');
        $('#oaItems').html('<tr><td colspan="7" class="text-center py-3 text-muted">ไม่มีใบสั่งซื้อที่รออนุมัติ</td></tr>');
        oaPrices = {};
    }

    function oaLoadRecord(orderno){
        $.getJSON(OA_URL + '/record', {orderno: orderno}, function(res){
            if (!res.found){ oaClear(); return; }
            fillOrderApproval(res);
        });
    }

    function fillOrderApproval(res){
        var o = res.order || {};
        $('#oa_Mdate').val(fmtDateTime(o.Mdate));
        $('#oa_Orderno').val(o.Orderno || '');
        $('#oa_Company').val(o.Company || '');
        $('#oa_PO').val(o.PO || '');
        $('#oa_Custno').val(o.Custno || '');
        $('#oa_Custname').val(o.Custname || '');
        $('#oa_Emp').val(o.Emp || '');
        $('#oa_sale').val(o.sale || '');
        $('#oa_HMStore').val(fmtNum(o.HMStore, 2));
        $('#oa_DVpoint').val(o.DVpoint || '');
        $('#oa_SendCust').val(o.SendCust != null ? o.SendCust : '');

        $('#oa_Send').prop('checked', !!o.Send);
        $('#oa_RP').prop('checked', !!o.RP);
        $('#oa_Spec').prop('checked', !!o.Spec);
        $('#oa_Cer').prop('checked', !!o.Cer);

        $('#oa_price').val(fmtNum(o.price, 2));
        $('#oa_appv').prop('checked', !!o.appv);
        $('#oa_appvDT').val(o.appvDT ? fmtDateTime(o.appvDT) : '');
        // ท้ายฟอร์มเดิม: "(<เทอม> - <ส่วนลดเงินสด>%)"
        $('#oa_term').val(o.term ? '(' + o.term + ' - ' + (o.cashdisc || 0) + '%)' : '');

        oaPrices = res.prices || {};
        oaRenderItems(res.items || []);
    }

    function oaRenderItems(items){
        if (!items.length){
            $('#oaItems').html('<tr><td colspan="7" class="text-center py-3 text-muted">ไม่มีรายการ</td></tr>');
            oaFillPrice(null);
            return;
        }

        var body = '';
        items.forEach(function(r, i){
            body += '<tr onclick="oaSelectItem(' + i + ', \'' + esc(r.Itemno || '') + '\')">'
                 +  '<td class="text-primary">' + esc(r.Itemno || '-') + '</td>'
                 +  '<td>' + esc(r.prodname || '-') + '</td>'
                 +  '<td class="text-primary">' + esc(r.Lotno || '-') + '</td>'
                 +  '<td class="text-end">' + (fmtNum(r.Stock, 2) || '0.00') + '</td>'
                 +  '<td class="text-end">' + (fmtNum(r.Production, 2) || '0.00') + '</td>'
                 +  '<td class="text-center text-primary">' + (fmtDate(r.senddate) || '-') + '</td>'
                 +  '<td>' + esc(r.Remark || '') + '</td>'
                 +  '</tr>';
        });
        $('#oaItems').html(body);
        oaSelectItem(0, items[0].Itemno);   // เปิดมาเลือกแถวแรกให้เลย
    }

    function oaSelectItem(idx, itemno){
        $('#oaItems tr').removeClass('oa-selected').eq(idx).addClass('oa-selected');
        oaFillPrice(oaPrices[itemno] || null);
    }

    function oaFillPrice(p){
        p = p || {};
        $('#oa_rem1').val(p.rem1 || '');
        $('#oa_rem2').val(p.rem2 || '');
        $('#oa_fixed_price').val(fmtNum(p.fixed_price, 2));
        $('#oa_price1').val(fmtNum(p.price1, 2));
        $('#oa_price2').val(fmtNum(p.price2, 2));
        $('#oa_price3').val(fmtNum(p.price3, 2));
    }

    // ปุ่มอนุมัติ — ยังไม่เขียนลง morder.appv / appvDT
    function orderApprovalApprove(e){
        e.preventDefault();
        Swal.fire({
            icon: 'info',
            title: 'ยังไม่เปิดใช้งานการอนุมัติ',
            html: 'ฟอร์มนี้เป็น UI + อ่านข้อมูลจริงเท่านั้น<br>' +
                  'การกดอนุมัติจะเขียน <code>morder.appv</code> + <code>morder.appvDT</code> — ' +
                  'รอยืนยันว่าใครมีสิทธิ์อนุมัติ และต้องบันทึกอะไรเพิ่มบ้าง'
        });
    }

    // ════════════════════════════════════════════════════════
    //  ฟอร์มขออนุมัติราคาพิเศษ (MD)
    // ════════════════════════════════════════════════════════
    var APPROVAL_URL = "{{ $page_url }}/price-approval";

    // เปิดฟอร์ม — ระบุลูกค้า/เบอร์สินค้ามาด้วยก็ได้ (เช่นเรียกจากใบสั่งซื้อในอนาคต)
    function approvalOpen(custno, itemno){
        clearApprovalForm();
        $('#approvalModal').modal('show');
        if (custno){
            $('#a_custno').val(custno);
            onApprovalCustChange(custno, itemno);
        }
    }

    function clearApprovalForm(){
        $('#approvalModal input[type="text"], #approvalModal input[type="number"], #approvalModal input[type="password"]').val('');
        $('#approvalModal textarea').val('');
        $('#approvalModal input[type="checkbox"]').prop('checked', false);
        $('#a_itemno').html('<option value="">— เลือกลูกค้าก่อน —</option>');
        setFp('a_validto', '');
        highlightPriceGroup();
        renderApprovalGrid('ราคาที่ยืนไว้ของเบอร์นี้', ZCUST_COLS, []);
    }

    // เปลี่ยนรหัสลูกค้า → โหลดรายการเบอร์สินค้าของลูกค้ารายนั้น
    var apvCustTimer = null;
    function onApprovalCustChange(code, preselectItem){
        code = (code || '').trim();
        clearTimeout(apvCustTimer);
        if (!code){
            $('#a_itemno').html('<option value="">— เลือกลูกค้าก่อน —</option>');
            $('#a_custname').val('');
            $('#a_sale').val('');
            return;
        }
        apvCustTimer = setTimeout(function(){
            $.getJSON(APPROVAL_URL + '/items', {custno: code}, function(res){
                if ($('#a_custno').val().trim() !== code) return;   // กันผลเก่ามาทับ
                var html = '<option value="">— เลือกเบอร์สินค้า —</option>';
                (res.items || []).forEach(function(it){
                    html += '<option value="' + esc(it) + '">' + esc(it) + '</option>';
                });
                $('#a_itemno').html(html);
                if (preselectItem) $('#a_itemno').val(preselectItem);
                loadApprovalData();
            });
        }, 350);
    }

    // โหลดข้อมูลของคู่ (ลูกค้า, เบอร์สินค้า) มาเติมฟอร์ม
    function loadApprovalData(){
        var custno = $('#a_custno').val().trim();
        var itemno = $('#a_itemno').val() || '';
        if (!custno) return;

        $.getJSON(APPROVAL_URL + '/data', {custno: custno, itemno: itemno}, function(res){
            if (!res.found){
                $('#a_custname').val('');
                $('#a_sale').val('');
                return;
            }
            fillApprovalForm(res);
        });
    }

    function fillApprovalForm(res){
        var c = res.customer || {};
        $('#a_custname').val(c.name || '');
        $('#a_sale').val(c.sale || '');

        var r = res.request || {};
        $('#a_ReqDate').val(r.ReqDate ? fmtDateTime(r.ReqDate) : '');
        $('#a_price1').val(fmtNum(r.price1, 2));
        $('#a_price2').val(fmtNum(r.price2, 2));
        $('#a_price3').val(fmtNum(r.price3, 2));
        $('#a_price').val(commaFmt(r.price, 2));
        $('#a_weight').val(commaFmt(r.weight, 2));
        $('#a_remark').val(r.remark || '');
        $('#a_Appv').prop('checked', !!r.Appv);

        // ราคาที่ตกลงไว้ล่าสุด (uprice)
        var u = res.uprice || {};
        $('#a_uprice').val(fmtNum(u.PRICE, 2));
        $('#a_uprice_date').val(fmtDate(u.DATE, true));
        $('#a_uprice_rem1').val(u.REM1 || '');
        $('#a_uprice_rem2').val(u.REM2 || '');

        // "อนุมัติราคาถึง" = วันที่ยืนราคาของเบอร์ที่เลือก (zcustprice.enddate)
        var first = (res.rows || [])[0];
        setFp('a_validto', first ? first.enddate : '');

        highlightPriceGroup();
        renderApprovalGrid('ราคาที่ยืนไว้ของเบอร์นี้', ZCUST_COLS, res.rows || []);
    }

    // เน้นช่องราคาที่ตรงกับจำนวนสั่งซื้อ (A ≥1,000 / B ≥500 / C ต่ำกว่า 500)
    function highlightPriceGroup(){
        var w = numVal('#a_weight');                 // ถอดคอมมาก่อน — '1,000' ต้องได้ 1000 ไม่ใช่ 1
        $('.pa-pricebox').removeClass('pa-active');
        if (w === null) { $('#a_group').val(''); return; }
        var box = (w >= 1000) ? {id: '#a_box1', g: 'A'}
                : (w >= 500)  ? {id: '#a_box2', g: 'B'}
                :               {id: '#a_box3', g: 'C'};
        $(box.id).addClass('pa-active');
        $('#a_group').val(box.g);
    }

    // ── ตารางล่าง — ใช้ร่วมกันทั้งข้อมูลหลักและผลของปุ่มตรวจสอบ/ประวัติ ──
    var ZCUST_COLS = [
        {key: 'colorno', label: 'รหัสสี'},
        {key: 'exprice', label: 'ราคาขาย', num: 2},
        {key: 'enddate', label: 'ยืนราคาถึงวันที่', date: true},
        {key: 'remark',  label: 'หมายเหตุ', wide: true}
    ];
    var OTHERCUST_COLS = [
        {key: 'custno',   label: 'รหัสลูกค้า'},
        {key: 'custname', label: 'ชื่อลูกค้า'},
        {key: 'exprice',  label: 'ราคาขาย', num: 2},
        {key: 'enddate',  label: 'ยืนราคาถึงวันที่', date: true},
        {key: 'remark',   label: 'หมายเหตุ', wide: true}
    ];
    var HISTORY_COLS = [
        {key: 'ReqDate', label: 'วันที่ขอราคา', datetime: true},
        {key: 'weight',  label: 'จำนวนสั่งซื้อ', num: 2},
        {key: 'group',   label: 'กลุ่ม'},
        {key: 'price',   label: 'ราคาขายครั้งนี้', num: 2},
        {key: 'price1',  label: 'ช่อง A', num: 2},
        {key: 'price2',  label: 'ช่อง B', num: 2},
        {key: 'price3',  label: 'ช่อง C', num: 2},
        {key: 'Appv',    label: 'อนุมัติ', bool: true},
        {key: 'remark',  label: 'หมายเหตุ', wide: true}
    ];
    var RESIN_COLS = [
        {key: 'Orderno',     label: 'เลขที่ใบสั่ง'},
        {key: 'Qdate',       label: 'วันที่', date: true},
        {key: 'OrderPrice',  label: 'ราคาต่อใบสั่ง', num: 2},
        {key: 'wage',        label: 'ค่าแรง', num: 2},
        {key: 'Resin1Code',  label: 'รหัสเม็ด'},
        {key: 'Resin1Price', label: 'ราคาเม็ด', num: 2},
        {key: 'Resin1Per',   label: '%'},
        {key: 'Diff',        label: 'ส่วนต่าง', num: 2},
        {key: 'status',      label: 'สถานะ'}
    ];

    function renderApprovalGrid(title, cols, rows){
        $('#a_gridTitle').text(title);
        $('#a_gridCount').text(rows.length ? ('ระเบียน ' + rows.length + ' รายการ') : '');

        var head = '<tr>';
        cols.forEach(function(c){
            head += '<th' + (c.wide ? ' style="min-width:320px;"' : '') + '>' + esc(c.label) + '</th>';
        });
        $('#a_gridHead').html(head + '</tr>');

        if (!rows.length){
            $('#a_gridBody').html('<tr><td colspan="' + cols.length + '" class="text-center py-3 text-muted">ไม่มีข้อมูล</td></tr>');
            return;
        }

        var body = '';
        rows.forEach(function(r){
            body += '<tr>';
            cols.forEach(function(c){
                var v = r[c.key], cls = '', txt;
                if (c.num !== undefined)      { txt = fmtNum(v, c.num); cls = ' class="text-end"'; }
                else if (c.datetime)          { txt = fmtDateTime(v);   cls = ' class="text-center"'; }
                else if (c.date)              { txt = fmtDate(v);       cls = ' class="text-center"'; }
                else if (c.bool)              { txt = v ? 'อนุมัติแล้ว' : '-'; cls = ' class="text-center"'; }
                else                          { txt = v == null ? '' : String(v); }
                body += '<td' + cls + '>' + esc(txt || '-') + '</td>';
            });
            body += '</tr>';
        });
        $('#a_gridBody').html(body);
    }

    // ── ปุ่มตรวจสอบ / ประวัติ ──
    function approvalOtherItems(){
        var custno = $('#a_custno').val().trim();
        if (!custno) return;
        $.getJSON(APPROVAL_URL + '/other-items', {custno: custno}, function(res){
            renderApprovalGrid(res.title, ZCUST_COLS, res.rows || []);
        });
    }
    function approvalOtherCustomers(){
        var itemno = $('#a_itemno').val() || '';
        if (!itemno) return;
        $.getJSON(APPROVAL_URL + '/other-customers', {itemno: itemno}, function(res){
            renderApprovalGrid(res.title, OTHERCUST_COLS, res.rows || []);
        });
    }
    function approvalHistory(){
        var custno = $('#a_custno').val().trim();
        var itemno = $('#a_itemno').val() || '';
        if (!custno || !itemno) return;
        $.getJSON(APPROVAL_URL + '/history', {custno: custno, itemno: itemno}, function(res){
            renderApprovalGrid(res.title, HISTORY_COLS, res.rows || []);
        });
    }
    function approvalResinHistory(){
        var itemno = $('#a_itemno').val() || '';
        if (!itemno) return;
        $.getJSON(APPROVAL_URL + '/resin-history', {itemno: itemno}, function(res){
            renderApprovalGrid(res.title, RESIN_COLS, res.rows || []);
        });
    }
    function approvalRefresh(){ loadApprovalData(); }

    // เพิ่ม/บันทึก, ลบรายการ, พิมพ์ — ยังไม่เปิดใช้งาน (รอยืนยันสิทธิ์อนุมัติ + คอลัมน์ที่แก้ได้)
    function approvalSave(){
        Swal.fire({
            icon: 'info',
            title: 'ยังไม่เปิดใช้งาน',
            html: 'ฟอร์มนี้เป็น UI + อ่านข้อมูลจริงเท่านั้น<br>' +
                  'การบันทึก/ลบ/พิมพ์ จะเปิดใช้หลังยืนยันวิธีตรวจรหัสผ่าน MD และคอลัมน์ที่แก้ได้'
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
    }

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
            if (this._flatpickr) this._flatpickr.clear();
            else $(this).val('');
        });
        loadData("{{ $page_url }}/datatable");
    }
</script>
</body>

</html>
