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
.of-typegrid { display: flex; flex-direction: column; gap: .35rem; }
/* 1 แถว = 3 กลุ่ม (C / H / W) กลุ่มละ 2 ปุ่ม — เว้นช่องไฟระหว่างกลุ่มให้กว้างกว่าในกลุ่ม */
.of-typerow { display: flex; flex-wrap: wrap; gap: 8rem; }
.of-typepair { display: flex; gap: 1rem; }
/* ตรึงความกว้างแต่ละปุ่ม เพื่อให้คอลัมน์ของแถวบน-ล่างตรงกัน */
.of-typepair .form-check { min-width: 72px; margin-bottom: 0; }
.of-typerow .form-check-label { font-weight: 600; letter-spacing: .3px; }
/* ปุ่ม "เพิ่มใบสั่งซื้อใหม่" ในแถบม่วง — สีเข้มให้ตัดกับพื้นแถบ */
.of-btn-new {
    background-color: #5c4bb3;
    border-color: #5c4bb3;
    color: #fff;
    font-weight: 600;
}
.of-btn-new:hover, .of-btn-new:focus {
    background-color: #4b3f8f;
    border-color: #4b3f8f;
    color: #fff;
}

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

/* ตารางรายการในใบสั่งซื้อ — ช่องกรอก (พื้นเหลืองอ่อนตามฟอร์ม Access เดิม) */
#orderItemsTable { background: #fffdf0; }
#orderItemsTable thead th {
    background: #f5e6c8;
    white-space: nowrap;
    font-size: .8rem;
    vertical-align: middle;
    text-align: center;
}
#orderItemsTable tfoot th { background: #f5e6c8; font-size: .85rem; }
#orderItemsTable td { font-size: .85rem; padding: .3rem .35rem; }
#orderItemsTable .oi-input { min-width: 80px; }
#orderItemsTable .oi-input:focus { background: #fffbe0; }

/* ข้อความบอกที่มา/เหตุผลใต้กล่องราคา */
.of-price-note {
    font-size: .78rem;
    color: #7a6a45;
    line-height: 1.35;
    margin-top: .35rem;
    min-height: 1rem;
}
.of-price-note-warn {
    color: #8a4b0f;
    background: #fdf0e2;
    border: 1px solid #f0d5b4;
    border-radius: .375rem;
    padding: .35rem .5rem;
}

/* แถบเตือน "ต้อง Match ใหม่" — แถบแดงเหมือนฟอร์มเดิม */
.of-matchwarn {
    background: #d32f2f;
    color: #fff;
    font-weight: 700;
    text-align: center;
    padding: .45rem .75rem;
    border-radius: .375rem;
    margin-bottom: .75rem;
}

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

/* ══ ฟอร์มอนุมัติใบสั่งซื้อ (morderAPPV) — โทนเทาอ่อนตามฟอร์มเดิม ══ */
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

/* ตารางคิวรออนุมัติ (หน้าแรกของฟอร์ม) — คลิกทั้งแถวเพื่อเข้าใบนั้น */
#oaQueueTable thead th { background: #e8e8d0; white-space: nowrap; font-size: .8rem; }
#oaQueueTable tbody tr { cursor: pointer; }
#oaQueueTable tbody tr:hover { background: #fff9a8; }
#oaQueueTable td { font-size: .85rem; }

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
                            อนุมัติใบสั่งซื้อ
                            <span class="badge bg-danger ms-1 d-none" id="oaQueueBadge">0</span>
                        </button>
                        <button class="btn btn-label-primary border" style="color: #1f158e;" onclick="approvalOpen()">
                            <i class="ti ti-discount-check me-1"></i>
                            ขออนุมัติราคาพิเศษ
                        </button>
                        <button class="btn btn-primary" onclick="orderOpenNew()">
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

            {{-- แถบหัว: ปุ่มพับ/กางตัวกรอง + ปุ่มล้างตัวกรอง (ชิดขวาทั้งคู่) — ค่าเริ่มต้นคือ "ซ่อนไว้" --}}
            <div class="d-flex justify-content-end align-items-center gap-2">
                {{-- รายละเอียดการค้นหา — สรุปเงื่อนไขที่กรองอยู่ โชว์เฉพาะตอนพับตัวกรอง --}}
                <div id="filterSummary" class="d-flex flex-wrap align-items-center gap-1 me-auto"></div>
                <button type="button" id="btnToggleFilters" class="btn btn-label-primary btn-sm"
                    data-bs-toggle="collapse" data-bs-target="#orderFilterBox"
                    aria-expanded="false" aria-controls="orderFilterBox">
                    <i class="ti ti-filter me-1"></i>ตัวกรอง
                    <i class="ti ti-chevron-down ms-1 toggle-caret"></i>
                </button>
                <button type="button" id="btnResetFilters" class="btn btn-label-secondary btn-sm" onclick="resetFilters()">
                    <i class="ti ti-x me-1"></i>ล้างตัวกรอง<span class="filter-count ms-1"></span>
                </button>
            </div>

            <div class="collapse" id="orderFilterBox">
            <div class="pt-3">

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

            </div>
            </div>
            {{-- /#orderFilterBox --}}

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
<!-- Modal: อนุมัติใบสั่งซื้อ (morderAPPV) — ผังตามฟอร์ม Access เดิม -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="modal modalHeadDecor fade" id="orderApprovalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    อนุมัติใบสั่งซื้อ
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

    // ป้ายชื่อของแต่ละช่องกรอง — ใช้ประกอบแถบ "รายละเอียดการค้นหา" (#filterSummary)
    // ⚠ ต้องประกาศตรงนี้ (ก่อน loadData ครั้งแรก) เพราะ renderFilterSummary() ถูกเรียกใน loadData
    //   ถ้าไปประกาศท้ายไฟล์ ตัวแปรจะยังเป็น undefined ตอนโหลดหน้า → throw แล้ว spinner ค้าง
    var FILTER_LABELS = {
        search:     'ค้นหา',
        date_from:  'วันที่สั่ง ตั้งแต่',
        date_to:    'ถึง',
        order_type: 'ประเภทใบสั่ง',
        company:    'ผลิตที่'
    };

    loadData(page);

    $(function () {
        // ยกระดับ select ทั้งหน้า (ตัวกรอง + modal ใบสั่งซื้อ/ขออนุมัติราคา/อนุมัติใบสั่งซื้อ)
        // ตัวช่วยกลางใน layout/inc_js เลือกให้เองตามจำนวนตัวเลือก:
        //   ตั้งแต่ 10 ตัวขึ้นไป → select2 (พิมพ์ค้นหาได้) · ต่ำกว่านั้น → bootstrap-select
        // ช่องที่เติมตัวเลือกทีหลังด้วย JS (สถานที่ส่ง / รหัสสินค้าในใบขออนุมัติราคา)
        // ไม่ต้องเรียกซ้ำเอง — ตัวช่วยกลางเฝ้าดู option แล้วสลับชนิดให้เมื่อรายการยาวขึ้น
        // ⚠ ต้องมาก่อน flatpickr เพื่อไม่ให้ไปจับ <select> เดือน/ปี ที่ flatpickr สร้าง
        enhanceSelects();
        flatpickr('.flatpickr-date', {
            dateFormat: 'd/m/Y',
            allowInput: true,
            static: true,
            disableMobile: true,
            onChange: function (_, __, instance) {
                if (instance.input.classList.contains('p_search')) loadData(page);
            }
        });
        // ช่องวันที่ที่มีเวลาด้วย (วันที่เปิดใบสั่ง)
        flatpickr('.flatpickr-datetime', {
            dateFormat: 'd/m/Y H:i',
            enableTime: true,
            time_24hr: true,
            allowInput: true,
            static: true,
            disableMobile: true
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

    // ตั้งค่าช่องวันที่+เวลา — ไม่ส่งค่ามา = ใช้วันเวลาปัจจุบัน (ค่าเริ่มต้นของใบใหม่)
    function setFpDateTime(id, val){
        var el = document.getElementById(id);
        if (!el) return;
        if (el._flatpickr){
            if (val) el._flatpickr.setDate(String(val).substring(0, 19), false, 'Y-m-d H:i:S');
            else     el._flatpickr.setDate(new Date());
        } else {
            $(el).val(val ? fmtDateTime(val) : '');
        }
    }

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
                // ไม่เจอ → อยู่โหมด idle ต่อ ให้ผู้ใช้พิมพ์เลขใหม่ได้เลย
                Swal.fire('ไม่พบใบสั่งซื้อ', 'เลขที่ ' + orderno + ' ไม่มีอยู่ในระบบ', 'warning')
                    .then(function(){ $('#o_Orderno').trigger('focus').trigger('select'); });
                return;
            }
            fillOrderForm(res);              // fillOrderForm ตั้งโหมดเป็น edit ให้แล้ว
            $('#orderModal').modal('show');
        }).fail(function(){
            Swal.fire('โหลดข้อมูลไม่สำเร็จ', 'ลองใหม่อีกครั้ง', 'error');
        });
    }

    // ────────────────────────────────────────────────────────
    //  โหมดของฟอร์ม
    //   idle = เพิ่งเปิดฟอร์ม — กรอกได้แค่ 2 อย่าง: ประเภทใบสั่ง (radio) กับ เลขที่ใบสั่ง
    //          จากนั้นเลือกทางใดทางหนึ่ง: กด "เพิ่มใบสั่งซื้อใหม่" → โหมด new
    //          หรือพิมพ์เลขที่ใบเดิมแล้วกด Enter → โหมด edit
    //   new / edit = ปลดล็อกทุกช่อง (เลขที่ใบสั่งถูกกำหนดแล้ว จึงล็อกไว้)
    // ────────────────────────────────────────────────────────
    function setOrderFormMode(mode){
        var idle = (mode === 'idle');
        $('#orderModal').attr('data-mode', mode);

        // ล็อกทุกช่อง ยกเว้นตัวที่ต้องใช้ตอน idle (radio ประเภท / เลขที่ใบสั่ง / ปุ่มเพิ่มใบใหม่ / ปุ่มปิด)
        $('#orderModal').find('input, select, textarea, button')
            .not('[name="order_type_form"]')
            .not('#o_Orderno')
            .not('.of-btn-new')
            .not('[data-bs-dismiss="modal"], .btn-close')
            .prop('disabled', idle);

        // เลขที่ใบสั่ง — พิมพ์ได้เฉพาะตอน idle
        $('#o_Orderno').prop('readonly', !idle);
        $('#o_orderno_hint').toggleClass('d-none', !idle);
    }

    // เปิดฟอร์มเปล่า (ปุ่มบนหัวหน้ารายการ) — เข้าโหมด idle
    function orderOpenNew(){
        clearOrderForm();
        $('#orderModalTitle').text('บันทึกคำสั่งซื้อ');
        setOrderFormMode('idle');
        $('#orderModal').modal('show');
        setTimeout(function(){ $('#o_Orderno').trigger('focus'); }, 300);
    }

    // กด Enter ที่ช่องเลขที่ใบสั่ง (ตอน idle) → เปิดใบนั้นขึ้นมาแก้ไขทันที
    $(document).on('keydown', '#o_Orderno', function(e){
        if (e.key !== 'Enter' && e.keyCode !== 13) return;
        e.preventDefault();
        if ($(this).prop('readonly')) return;          // ไม่ได้อยู่โหมด idle
        var no = ($(this).val() || '').trim();
        if (!no) return;
        orderOpen(no);
    });

    // ปุ่ม "เพิ่มใบสั่งซื้อใหม่" ในฟอร์ม — จุดเดียวที่เจนเลขที่ใบสั่ง
    function orderNew(){
        var type = $('input[name="order_type_form"]:checked').val();
        if (!type){
            Swal.fire({
                icon: 'warning',
                title: 'ยังไม่ได้เลือกประเภทใบสั่ง',
                text: 'เลือกประเภท (CM / CI / HM / …) ก่อน แล้วกดปุ่มนี้อีกครั้ง'
            });
            return;
        }

        clearOrderForm();                              // ล้างข้อมูลใบเดิมออกก่อน
        $('#o_type_' + type).prop('checked', true);    // คงประเภทที่เลือกไว้
        $('#orderModalTitle').text('บันทึกคำสั่งซื้อ — ใบใหม่');
        setOrderFormMode('new');           // ปลดล็อกช่องที่เหลือ

        $.getJSON("{{ $page_url }}/next-orderno", {type: type}, function(res){
            if (res.found && res.orderno) $('#o_Orderno').val(res.orderno);
            else Swal.fire('เจนเลขที่ใบสั่งไม่สำเร็จ', 'ไม่พบเลขรันของประเภท ' + type, 'error');
        }).fail(function(){
            Swal.fire('เจนเลขที่ใบสั่งไม่สำเร็จ', 'ลองใหม่อีกครั้ง', 'error');
        });
    }

    // เปลี่ยนประเภทใบสั่ง — ไม่เจนเลขที่ใหม่ (เจนเฉพาะตอนกดปุ่ม "เพิ่มใบสั่งซื้อใหม่")
    // แต่ล้างเลขที่ที่ค้างอยู่ถ้าไม่ตรงกับประเภทที่เพิ่งเลือก กันเลขที่/ประเภทไม่ตรงกัน
    function onOrderTypeChange(type){
        if ($('#o_Orderno').data('existing')) return;   // ใบเดิม — ไม่แตะเลขที่
        var cur = ($('#o_Orderno').val() || '').trim();
        if (cur && cur.substring(0, 2).toUpperCase() !== type) $('#o_Orderno').val('');
    }

    function clearOrderForm(){
        $('#orderModal input[type="text"], #orderModal input[type="number"]').val('');
        $('#orderModal input[type="checkbox"]').prop('checked', false);
        $('#orderModal input[name="order_type_form"]').prop('checked', false);
        $('#o_Company').val('');
        $('#o_DVpoint').html('<option value="">— ไม่ระบุ —</option>');
        $('#o_Orderno').removeData('existing');
        setFp('o_sendend', '');
        setFpDateTime('o_Mdate', null);   // ใบใหม่ = วันเวลาปัจจุบัน (ผู้ใช้แก้ได้)
        $('#o_match_warn').addClass('d-none');
        // ผู้บันทึก = พนักงานที่ล็อกอินอยู่
        $('#o_Emp').val('{{ $current_emp }}');
        renderOrderItems([]);
    }

    function fillOrderForm(res){
        clearOrderForm();

        var o = res.order || {};
        $('#orderModalTitle').text('บันทึกคำสั่งซื้อ — ' + (o.Orderno || ''));

        // ประเภท (radio) + เลขที่ใบสั่ง
        if (o.type) $('#o_type_' + o.type).prop('checked', true);
        $('#o_Orderno').val(o.Orderno || '').data('existing', true);
        setFpDateTime('o_Mdate', o.Mdate);

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

        // สินค้า + ราคา (ราคาขายเป็นค่าที่ผู้ใช้พิมพ์ไว้เอง ไม่ได้มาจากกล่องราคา)
        $('#o_itemno').val(res.itemno || '');
        $('#o_price').val(commaFmt(o.price, 2));
        fillPriceBox(res.price);

        renderOrderItems(res.items || []);
        setOrderFormMode('edit');   // เปิดใบเดิม → ปลดล็อกทุกช่อง (ยกเว้นเลขที่ใบสั่ง)
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

    // กล่องราคา — ทุกช่องอ่านอย่างเดียว ยกเว้น "ราคาขาย" ที่ผู้ใช้พิมพ์เอง (ไม่แตะที่นี่)
    function fillPriceBox(p){
        p = p || {};
        $('#o_fixed_price').val(fmtNum(p.fixed_price, 2))
            .attr('title', p.formula ? 'ราคาขาย 1 = ราคาทุน ' + p.formula : '');
        $('#o_price2').val(fmtNum(p.price2, 2));
        $('#o_min_price').val(fmtNum(p.min_price, 2));       // = ราคาช่อง 2
        $('#o_price_group').val(p.group ? p.group + ' — ' + (p.group_label || '') : '');
        $('#o_appv_price').val(fmtNum(p.appv_price, 2));
        $('#o_valid_to').val(fmtDate(p.valid_to));

        // ── ข้อความใต้กล่องราคา: บอกที่มาของราคา หรือบอกว่าทำไมคำนวณไม่ได้ ──
        var $note = $('#o_price_note').removeClass('of-price-note-warn');

        if (p.found){
            // เหมือนหน้า "ค้นหาราคาสินค้า": ราคาทุน → เงื่อนไขที่เข้า → สูตร
            var src = [];
            if (p.cost_price != null) src.push('ราคาทุน ' + fmtNum(p.cost_price, 2));
            if (p.rule_label)         src.push(p.rule_label);
            if (p.formula)            src.push(p.formula);
            $note.text(src.join('  ·  '));
            return;
        }

        $note.addClass('of-price-note-warn').text(p.message || '');
    }

    // ════════════════════════════════════════════════════════
    //  ตารางรายการ (suborder) — ช่องกรอก เพิ่ม/ลบแถวได้
    //  อ่านค่าจาก DOM ตอนบันทึก (ไม่เก็บ model คู่ขนาน กันข้อมูลสองฝั่งไม่ตรงกัน)
    // ════════════════════════════════════════════════════════

    // 1 แถว = 1 suborder — Runno เก็บใน hidden เพื่อให้ฝั่ง server รู้ว่าแถวไหนของเดิม
    function orderItemRow(r){
        r = r || {};
        var td = function(html, cls){ return '<td' + (cls ? ' class="' + cls + '"' : '') + '>' + html + '</td>'; };
        var txt = function(field, val, extra){
            return '<input type="text" class="form-control form-control-sm oi-input" data-f="' + field + '"'
                 + ' value="' + esc(val == null ? '' : val) + '"' + (extra || '') + '>';
        };
        var num = function(field, val){
            return '<input type="text" class="form-control form-control-sm text-end js-comma oi-input" data-f="' + field + '"'
                 + ' inputmode="decimal" autocomplete="off" placeholder="0.00"'
                 + ' value="' + esc(commaFmt(val, 2)) + '" oninput="recalcOrderTotals()">';
        };
        var dat = function(field, val){
            return '<input type="text" class="form-control form-control-sm oi-date oi-input" data-f="' + field + '"'
                 + ' autocomplete="off" placeholder="วว/ดด/ปปปป" value="' + esc(fmtDate(val, true)) + '">';
        };

        return '<tr>'
            + '<input type="hidden" data-f="Runno" value="' + esc(r.Runno || '') + '">'
            + td('<span class="oi-no"></span>', 'text-center text-muted')
            + td(txt('Itemno', r.Itemno, ' maxlength="20" oninput="onItemnoInput(this)"'))
            + td(txt('nold', r.nold, ' list="noldList" maxlength="1"'))
            + td(txt('prodname', r.prodname, ' maxlength="20"'))   // suborder.prodname = varchar(20)
            + td(txt('Lotno', r.Lotno, ' maxlength="20"'))
            + td(num('Stock', r.Stock))
            + td(num('Production', r.Production))
            + td(dat('custwant', r.custwant))
            + td(dat('senddate', r.senddate))
            + td(dat('EndP', r.EndP))
            + td(dat('DVDate', r.DVDate))
            + td(txt('outno', r.outno, ' maxlength="20"'))
            + td(txt('Remark', r.Remark, ' list="ordremList"'))
            + td('<button type="button" class="btn btn-sm btn-icon btn-label-danger" title="ลบแถว"'
                + ' onclick="removeOrderItem(this)"><i class="ti ti-trash"></i></button>', 'text-center')
            + '</tr>';
    }

    function renderOrderItems(items){
        items = (items && items.length) ? items : [{}];   // ใบใหม่เริ่มที่ 1 แถวเปล่า
        $('#orderItems').html(items.map(orderItemRow).join(''));
        initRowPickers('#orderItems');
        renumberOrderItems();
        recalcOrderTotals();
    }

    function addOrderItem(){
        var $tr = $(orderItemRow({}));
        $('#orderItems').append($tr);
        initRowPickers($tr);
        renumberOrderItems();
    }

    function removeOrderItem(btn){
        $(btn).closest('tr').remove();
        if (!$('#orderItems tr').length) addOrderItem();   // เหลืออย่างน้อย 1 แถวเสมอ
        renumberOrderItems();
        recalcOrderTotals();
    }

    // flatpickr ของช่องวันที่ในแถว — ต้องผูกทุกครั้งที่สร้างแถวใหม่
    function initRowPickers(scope){
        $(scope).find('.oi-date').each(function(){
            if (this._flatpickr) return;
            flatpickr(this, {dateFormat: 'd/m/Y', allowInput: true, static: true, disableMobile: true});
        });
    }

    function renumberOrderItems(){
        $('#orderItems tr').each(function(i){ $(this).find('.oi-no').text(i + 1); });
    }

    // ยอดรวม S / P ท้ายตาราง
    function recalcOrderTotals(){
        var s = 0, p = 0;
        $('#orderItems tr').each(function(){
            s += numOf($(this).find('[data-f="Stock"]').val());
            p += numOf($(this).find('[data-f="Production"]').val());
        });
        $('#o_total_stock').text(commaFmt(s, 2));
        $('#o_total_prod').text(commaFmt(p, 2));
    }

    // ── กรอกรหัสสินค้า → เติมชื่อสินค้า + ผูกกล่องราคา + เตือน Match ใหม่ ──
    // ผู้ใช้กรอกรหัสเดียวทั้งใบ → ใช้รหัสของแถวแรกที่กรอกไว้เป็นตัวอ้างอิงของกล่องราคา
    var itemLookupTimer = null;
    function onItemnoInput(el){
        clearTimeout(itemLookupTimer);
        itemLookupTimer = setTimeout(function(){ applyItemLookup(el); }, 350);
    }

    function applyItemLookup(el){
        var $row   = $(el).closest('tr');
        var itemno = ($(el).val() || '').trim();

        if (!itemno){ syncItemnoToPrice(); return; }

        $.getJSON("{{ $page_url }}/item-lookup", {itemno: itemno}, function(res){
            if (($(el).val() || '').trim() !== itemno) return;   // ผู้ใช้พิมพ์ต่อแล้ว
            // เติมชื่อสินค้าให้เฉพาะตอนช่องยังว่าง (ไม่ทับที่ผู้ใช้พิมพ์เอง)
            var $name = $row.find('[data-f="prodname"]');
            // ชื่อที่ได้จาก uprice.Label ยาวเกิน 20 ตัวได้ แต่คอลัมน์รับได้แค่ 20 — ตัดให้พอดี
            if (res.prodname && !$name.val()) $name.val(String(res.prodname).substring(0, 20));
            showMatchWarning(res);
            syncItemnoToPrice();
        }).fail(syncItemnoToPrice);
    }

    // แถบแดง "สีที่สั่งซื้อล่าสุดเกิน 3 ปี จะต้อง Match ใหม่"
    function showMatchWarning(res){
        if (res && res.need_match){
            $('#o_match_detail').text(res.last_order_date
                ? 'เบอร์ ' + res.itemno + ' — สั่งครั้งล่าสุด ' + fmtDate(res.last_order_date, true)
                : 'เบอร์ ' + res.itemno + ' — ไม่เคยมีประวัติการสั่งซื้อ');
            $('#o_match_warn').removeClass('d-none');
        } else {
            $('#o_match_warn').addClass('d-none');
        }
    }

    // รหัสสินค้าในกล่องราคา = รหัสของแถวแรกที่กรอกไว้
    function syncItemnoToPrice(){
        var itemno = '';
        $('#orderItems [data-f="Itemno"]').each(function(){
            if (!itemno && ($(this).val() || '').trim()) itemno = $(this).val().trim();
        });
        $('#o_itemno').val(itemno);
        refreshOrderPrice();
    }

    // ดึงกล่องราคาใหม่ตามคู่ (ลูกค้า, รหัสสินค้า) + น้ำหนักรวม (ใช้หากลุ่มราคา A/B/C)
    var priceXhr = null;
    function refreshOrderPrice(){
        var custno = ($('#o_Custno').val() || '').trim();
        var itemno = ($('#o_itemno').val() || '').trim();
        if (!custno || !itemno){ fillPriceBox({}); return; }

        if (priceXhr) priceXhr.abort();
        priceXhr = $.getJSON("{{ $page_url }}/price-info", {
            custno: custno, itemno: itemno, weight: numVal('#o_netqty')
        }, function(res){ fillPriceBox(res); });
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

                // ── ค่าที่ดึงจากข้อมูลลูกค้า ──
                // ทำทุกครั้งที่ "ผู้ใช้เปลี่ยนรหัสลูกค้า" (ฟังก์ชันนี้ถูกเรียกจาก oninput เท่านั้น
                // ไม่ถูกเรียกตอนโหลดใบเดิม ค่าที่บันทึกไว้จึงไม่โดนทับ)
                $('#o_supno').val(c.sale || '');        // รหัสผู้ขายประจำลูกค้า
                $('#o_RP').prop('checked', !!c.RP);
                $('#o_Cer').prop('checked', !!c.CER);
                $('#o_MSDS').prop('checked', !!c.MSDS);
                // ส่งก่อนได้ (Send) / SPEC (Spec) ไม่มีค่าประจำลูกค้าใน customer — ผู้ใช้ติ๊กเอง

                refreshOrderPrice();   // เปลี่ยนลูกค้า → ราคาของคู่ (ลูกค้า, สินค้า) เปลี่ยนตาม
            });
        }, 350);
    }

    // ════════════════════════════════════════════════════════
    //  บันทึกใบสั่งซื้อ
    // ════════════════════════════════════════════════════════

    // เก็บค่าจากตารางรายการ — ข้ามแถวที่ยังไม่กรอกรหัสสินค้า
    function collectOrderItems(){
        var items = [];
        $('#orderItems tr').each(function(){
            var $tr = $(this), row = {};
            $tr.find('[data-f]').each(function(){
                row[$(this).data('f')] = $(this).val();
            });
            // ช่องตัวเลขมีคอมมา → ถอดออกก่อนส่ง ไม่งั้น '1,000.00' จะลง DB เป็น 1.00
            row.Stock      = numOf(row.Stock);
            row.Production = numOf(row.Production);
            if ((row.Itemno || '').trim() !== '') items.push(row);
        });
        return items;
    }

    function saveOrder(){
        var isEdit = !!$('#o_Orderno').data('existing');
        var type   = $('input[name="order_type_form"]:checked').val() || '';
        var items  = collectOrderItems();

        // ── ตรวจก่อนส่ง ให้ผู้ใช้รู้ปัญหาทันทีโดยไม่ต้องรอ server ──
        if (!isEdit && !type){
            Swal.fire('ยังไม่ได้เลือกประเภทใบสั่ง', 'เลือกประเภทแล้วกด "เพิ่มใบสั่งซื้อใหม่" เพื่อรับเลขที่', 'warning');
            return;
        }
        if (!($('#o_Custno').val() || '').trim()){
            Swal.fire('ยังไม่ได้ระบุลูกค้า', 'กรอกรหัสลูกค้าก่อนบันทึก', 'warning');
            return;
        }
        if (!items.length){
            Swal.fire('ยังไม่มีรายการสินค้า', 'กรอกรหัสสินค้าในตารางอย่างน้อย 1 แถว', 'warning');
            return;
        }

        // ถอดคอมมาออกจากช่องตัวเลขทั้งฟอร์มก่อนอ่านค่า (น้ำหนักรวม / นน.คลัง / ส่งมอบเดือนละ)
        stripCommaFields('#orderModal');

        var payload = {
            _token:     '{{ csrf_token() }}',
            mode:       isEdit ? 'update' : 'insert',
            order_type: type,
            Orderno:    $('#o_Orderno').val(),
            Mdate:      $('#o_Mdate').val(),
            Company:    $('#o_Company').val(),
            PO:         $('#o_PO').val(),
            Custno:     $('#o_Custno').val(),
            Emp:        $('#o_Emp').val(),
            supno:      $('#o_supno').val(),
            DVpoint:    $('#o_DVpoint').val(),
            RsvNo:      $('#o_RsvNo').val(),
            netqty:     $('#o_netqty').val(),
            price:      $('#o_price').val(),
            sendend:    $('#o_sendend').val(),
            SendCust:   $('#o_SendCust').val(),
            HMStore:    $('#o_HMStore').val(),
            sendmth:    $('#o_sendmth').val(),
            Send:       $('#o_Send').is(':checked')  ? 1 : 0,
            RP:         $('#o_RP').is(':checked')    ? 1 : 0,
            Spec:       $('#o_Spec').is(':checked')  ? 1 : 0,
            Cer:        $('#o_Cer').is(':checked')   ? 1 : 0,
            MSDS:       $('#o_MSDS').is(':checked')  ? 1 : 0,
            items:      items
        };

        var $btn = $('#btnSaveOrder').prop('disabled', true);

        $.post("{{ $page_url }}/save", payload)
            .done(function(res){
                if (!res.status){
                    Swal.fire('บันทึกไม่สำเร็จ', res.message || '', 'error');
                    return;
                }
                Swal.fire({icon: 'success', title: res.message, text: 'เลขที่ใบสั่ง ' + res.orderno});
                // ใบใหม่ → เปลี่ยนเป็นโหมดแก้ไขของใบที่เพิ่งบันทึก แล้วโหลดค่าจริงกลับมา
                orderOpen(res.orderno);
                loadData(page);
            })
            .fail(function(xhr){
                var body = xhr.responseJSON || {};
                // ติดด่านราคา → เสนอทางไปต่อ (ทำใบขออนุมัติราคาพิเศษ) แทนที่จะบอกแค่ว่าบันทึกไม่ได้
                if (body.price_blocked){ showPriceBlocked(body); return; }
                Swal.fire('บันทึกไม่สำเร็จ', body.message || 'เกิดข้อผิดพลาดในการบันทึก', 'error');
            })
            .always(function(){
                $btn.prop('disabled', false);
                // ใส่คอมมากลับให้ช่องตัวเลขหลังส่งข้อมูลแล้ว
                $('#orderModal .js-comma').trigger('blur');
            });
    }

    // ราคาขายต่ำกว่าราคาช่อง 2 และไม่มีราคาอนุมัติพิเศษรองรับ
    // → พาไปทำใบขออนุมัติราคาพิเศษของคู่ (ลูกค้า, เบอร์) นั้นได้เลย
    function showPriceBlocked(body){
        Swal.fire({
            icon: 'warning',
            title: 'ราคาขายต่ำกว่าเกณฑ์',
            html: esc(body.message || ''),
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-file-dollar me-1"></i>ขออนุมัติราคาพิเศษ',
            cancelButtonText: 'กลับไปแก้ราคา',
            reverseButtons: true
        }).then(function(r){
            if (!r.isConfirmed) return;
            // ปิดฟอร์มใบสั่งซื้อก่อน แล้วค่อยเปิดฟอร์มขออนุมัติ — modal ซ้อนกันจะปิดยาก
            $('#orderModal').modal('hide');
            approvalOpen(body.custno || '', body.itemno || '');
        });
    }

    // ════════════════════════════════════════════════════════
    //  ฟอร์มอนุมัติใบสั่งซื้อ (morderAPPV)
    //  — เปิดมาเจอ "รายการใบที่รออนุมัติ" ก่อน คลิกใบไหนถึงเข้าฟอร์มอนุมัติใบนั้น (25/08/2569)
    //    ในฟอร์มยังมีตัวเดินระเบียนของ Access เดิม (ระเบียน N จาก M) ไว้ไล่ใบต่อกันได้
    // ════════════════════════════════════════════════════════
    var OA_URL    = "{{ $page_url }}/order-approval";
    var oaQueue   = [];      // รายการใบที่รออนุมัติ
    var oaIndex   = 0;       // ระเบียนที่กำลังแสดง (0-based)
    var oaPrices  = {};      // ราคาอ้างอิงของแต่ละเบอร์ในใบปัจจุบัน
    var oaCurrent = null;    // ระเบียนที่โหลดมาแสดงอยู่ — ใช้ตัดสินว่าปุ่มอนุมัติจะทำอะไร

    // นับคิวรออนุมัติตั้งแต่เปิดหน้า → โชว์เป็น badge บนปุ่ม
    $(function(){ oaLoadQueue(); });

    // โหลดคิว + วาดรายการ + อัปเดต badge (ไม่เปิดใบไหนให้เอง — ผู้ใช้เป็นคนเลือก)
    function oaLoadQueue(){
        return $.getJSON(OA_URL + '/queue', function(res){
            oaQueue = res.rows || [];
            $('#oa_total').text(oaQueue.length);
            $('#oa_queue_count').text(oaQueue.length);
            var $badge = $('#oaQueueBadge');
            if (oaQueue.length) $badge.text(oaQueue.length).removeClass('d-none');
            else                $badge.addClass('d-none');
            oaRenderQueue();
        });
    }

    // ── สลับมุมมอง: รายการ ⇄ ฟอร์มอนุมัติ ──
    function oaShowList(){
        $('#oaListView').removeClass('d-none');
        $('#oaDetailView').addClass('d-none');
    }
    function oaShowDetail(){
        $('#oaListView').addClass('d-none');
        $('#oaDetailView').removeClass('d-none');
    }

    // วาดตารางคิว (กรองด้วยช่องค้นหาฝั่งจอ — คิวไม่ยาวพอที่จะต้องค้นที่ server)
    function oaRenderQueue(){
        var kw = ($('#oa_search').val() || '').trim().toLowerCase();
        var body = '';

        oaQueue.forEach(function(r, i){
            if (kw){
                var hay = [r.Orderno, r.Custno, r.custname, r.Company].join(' ').toLowerCase();
                if (hay.indexOf(kw) === -1) return;
            }
            // escHtml (ไม่ใช่ esc) — ชื่อลูกค้ามาจาก DB ต้อง escape ก่อนยัดลง HTML
            body += '<tr onclick="oaOpenOrder(' + i + ')">'
                 +  '<td class="text-center text-muted">' + (i + 1) + '</td>'
                 +  '<td class="fw-bold text-primary">' + escHtml(r.Orderno || '-') + '</td>'
                 +  '<td>' + (fmtDateTime(r.Mdate) || '-') + '</td>'
                 +  '<td class="text-center">' + escHtml(r.Company || '-') + '</td>'
                 +  '<td>' + escHtml(r.Custno || '-') + '</td>'
                 +  '<td>' + escHtml(r.custname || '-') + '</td>'
                 +  '<td class="text-end">' + (fmtNum(r.price, 2) || '—') + '</td>'
                 +  '<td class="text-center">'
                 +    '<span class="btn btn-sm btn-label-warning border">'
                 +      '<i class="ti ti-gavel me-1"></i>อนุมัติ</span>'
                 +  '</td>'
                 +  '</tr>';
        });

        if (!body){
            body = '<tr><td colspan="8" class="text-center py-4 text-muted">'
                 + (oaQueue.length ? 'ไม่พบใบสั่งซื้อที่ตรงกับคำค้น' : 'ไม่มีใบสั่งซื้อที่รออนุมัติ')
                 + '</td></tr>';
        }
        $('#oaQueueRows').html(body);
    }

    // คลิกใบในรายการ → เข้าฟอร์มอนุมัติของใบนั้น
    function oaOpenOrder(i){
        oaShowDetail();
        oaGo(i);
    }

    function orderApprovalOpen(){
        $('#orderApprovalModal').modal('show');
        $('#oa_search').val('');
        oaShowList();
        oaLoadQueue();
    }

    // Refresh — อยู่หน้ารายการก็โหลดรายการใหม่, อยู่ในฟอร์มก็โหลดใบที่ค้างอยู่ใหม่ด้วย
    function orderApprovalRefresh(){
        var inDetail = !$('#oaDetailView').hasClass('d-none');
        var at       = (oaCurrent && oaCurrent.Orderno) || null;

        oaLoadQueue().done(function(){
            if (!inDetail) return;

            // ใบเดิมยังอยู่ในคิวไหม — หลุดไปแล้ว (มีคนอนุมัติก่อน) ให้กลับหน้ารายการ
            var i = oaIndexOf(at);
            if (i === -1){ oaShowList(); oaClear(); return; }
            oaGo(i);
        });
    }

    /** ตำแหน่งของใบในคิว (-1 = ไม่อยู่ในคิวแล้ว) */
    function oaIndexOf(orderno){
        if (!orderno) return -1;
        for (var i = 0; i < oaQueue.length; i++){
            if (oaQueue[i].Orderno === orderno) return i;
        }
        return -1;
    }

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
        $('#oa_appv').prop('disabled', true);
        oaPrices  = {};
        oaCurrent = null;
    }

    function oaLoadRecord(orderno){
        $.getJSON(OA_URL + '/record', {orderno: orderno}, function(res){
            if (!res.found){ oaClear(); return; }
            fillOrderApproval(res);
        });
    }

    function fillOrderApproval(res){
        var o = res.order || {};
        oaCurrent = o;
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
        $('#oa_appv').prop('checked', !!o.appv)
            // ใบจอง R / ใบสั่งทำสต๊อก ไม่ต้องผ่านการอนุมัติ — ล็อกช่องไว้
            .prop('disabled', res.approvable === false)
            .closest('.form-check')
            .attr('title', res.approvable === false ? 'ใบสั่งนี้ไม่ต้องผ่านการอนุมัติ' : '');
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

    // ── ปุ่มอนุมัติ — เขียน morder.appv + morder.appvDT ──
    // ติ๊ก checkbox เองไม่มีผล: กันการติ๊กไว้ก่อน ถามยืนยัน แล้วค่อยโหลดสถานะจริงกลับมา
    function orderApprovalApprove(e){
        e.preventDefault();

        var o = oaCurrent;
        if (!o || !o.Orderno) return;

        // อ่านสถานะจากระเบียนที่โหลดมา ไม่พึ่งค่า checked ตอนคลิก (ขึ้นกับจังหวะ toggle ของเบราว์เซอร์)
        var approve = !o.appv;

        var html = 'เลขที่ใบสั่ง <b>' + esc(o.Orderno) + '</b><br>'
                 + 'ลูกค้า ' + esc(o.Custname || o.Custno || '-') + '<br>'
                 + 'ราคาขายครั้งนี้ <b>' + (fmtNum(o.price, 2) || '—') + '</b>';
        if (approve && (o.price === null || o.price === undefined || o.price === '')){
            html += '<br><span class="text-danger">ใบนี้ยังไม่ได้กรอกราคาขาย</span>';
        }

        Swal.fire({
            icon: approve ? 'question' : 'warning',
            title: approve ? 'อนุมัติใบสั่งซื้อนี้?' : 'ยกเลิกการอนุมัติ?',
            html: html,
            showCancelButton: true,
            confirmButtonText: approve ? 'อนุมัติ' : 'ยกเลิกการอนุมัติ',
            cancelButtonText: 'ปิด',
            confirmButtonColor: approve ? '#28a745' : '#d33'
        }).then(function(r){
            if (!r.isConfirmed) return;

            $('#oa_appv').prop('disabled', true);
            $.post(OA_URL + '/approve', {
                _token:  '{{ csrf_token() }}',
                orderno: o.Orderno,
                appv:    approve ? 1 : 0
            })
                .done(function(res){
                    if (!res.status){ Swal.fire('ทำรายการไม่สำเร็จ', res.message || '', 'error'); return; }
                    Swal.fire({icon: 'success', title: res.message, timer: 1800, showConfirmButton: false});
                    oaAfterApprove();
                })
                .fail(function(xhr){
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'เกิดข้อผิดพลาดในการบันทึก';
                    Swal.fire('ทำรายการไม่สำเร็จ', msg, 'error');
                })
                .always(function(){ $('#oa_appv').prop('disabled', false); });
        });
    }

    // ใบที่เพิ่งอนุมัติจะหลุดจากคิว → โหลดคิวใหม่แล้วกลับไปหน้ารายการ
    // (ผู้ใช้จะได้เห็นว่าเหลือกี่ใบ แล้วเลือกใบถัดไปเอง — ตรงกับ flow "เลือกจากรายการ")
    function oaAfterApprove(){
        oaLoadQueue().done(function(){
            oaClear();
            oaShowList();
        });
    }

    // ════════════════════════════════════════════════════════
    //  ฟอร์มขออนุมัติราคาพิเศษ (MD)
    // ════════════════════════════════════════════════════════
    var APPROVAL_URL = "{{ $page_url }}/price-approval";

    // ── 2 โหมดของฟอร์ม ──────────────────────────────────────────
    //   ขอราคา   = ค่าเริ่มต้น — กรอก/แก้ใบขอราคาได้ แต่ช่องอนุมัติถูกล็อก
    //   อนุมัติ  = กรอกรหัสผ่านแล้วกดปลดล็อกก่อน จึงจะติ๊กอนุมัติได้
    // ⚠ ยังไม่ยืนยันว่ารหัสนี้เป็นของ MD โดยเฉพาะ — บนจอจึงเรียกแค่ "รหัส" ไม่ผูกกับตำแหน่ง
    // ตัวแปรนี้เป็นแค่สถานะฝั่งจอ — ตัวตัดสินจริงอยู่ที่ session ฝั่ง server (save() ตรวจซ้ำเสมอ)
    var apvMdUnlocked = false;

    function setApprovalMdMode(unlocked){
        unlocked = !!unlocked;
        // ล้างรหัสที่พิมพ์ไว้เฉพาะตอนเพิ่งเข้าโหมด — ไม่งั้นจะไปลบรหัสที่ผู้ใช้กำลังพิมพ์อยู่
        if (unlocked && !apvMdUnlocked) $('#a_mdpass').val('');
        apvMdUnlocked = unlocked;

        $('#a_mdLockBox').toggleClass('d-none', unlocked);
        $('#a_mdUnlockedBox').toggleClass('d-none', !unlocked);

        // ช่องที่แตะได้เฉพาะโหมดอนุมัติ
        $('#a_Appv, #a_validto').prop('disabled', !unlocked);
        $('#a_Appv').closest('.form-check')
            .attr('title', unlocked ? '' : 'ต้องกรอกรหัสเพื่อเข้าสู่โหมดอนุมัติก่อน');
    }

    function approvalUnlock(){
        var pass = ($('#a_mdpass').val() || '').trim();
        if (!pass){
            Swal.fire('ยังไม่ได้กรอกรหัสผ่าน', 'กรอกรหัสก่อนเข้าสู่โหมดอนุมัติ', 'warning');
            return;
        }
        $.post(APPROVAL_URL + '/unlock', {_token: '{{ csrf_token() }}', md_password: pass})
            .done(function(res){
                if (!res.status){ Swal.fire('เข้าสู่โหมดอนุมัติไม่สำเร็จ', res.message || '', 'error'); return; }
                setApprovalMdMode(true);
                Swal.fire({icon: 'success', title: res.message, timer: 1600, showConfirmButton: false});
            })
            .fail(function(xhr){
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'รหัสผ่านไม่ถูกต้อง';
                Swal.fire('เข้าสู่โหมดอนุมัติไม่สำเร็จ', msg, 'error');
                $('#a_mdpass').val('').trigger('focus');
            });
    }

    function approvalLock(){
        $.post(APPROVAL_URL + '/lock', {_token: '{{ csrf_token() }}'})
            .always(function(){ setApprovalMdMode(false); });
    }

    // กด Enter ในช่องรหัสผ่าน = กดปุ่มปลดล็อก
    $(document).on('keydown', '#a_mdpass', function(e){
        if (e.key === 'Enter'){ e.preventDefault(); approvalUnlock(); }
    });

    // เปิดฟอร์ม — ระบุลูกค้า/เบอร์สินค้ามาด้วยก็ได้ (เช่นเรียกจากใบสั่งซื้อในอนาคต)
    function approvalOpen(custno, itemno){
        clearApprovalForm();
        $('#approvalModal').modal('show');
        // เริ่มที่โหมดขอราคาไว้ก่อน แล้วค่อยถาม server ว่าปลดล็อกค้างไว้จากรอบก่อนหรือเปล่า
        // (ล็อกไว้ระหว่างรอผลดีกว่าปล่อยช่องอนุมัติเปิดค้างชั่วขณะ)
        setApprovalMdMode(false);
        $.getJSON(APPROVAL_URL + '/md-state', function(res){ setApprovalMdMode(res.unlocked); });
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
        renderApprovalReqState(null);
        renderApprovalGrid('ราคาที่ยืนไว้ของเบอร์นี้', ZCUST_COLS, []);
    }

    // แถบบอกขั้นตอนของคู่ (ลูกค้า, เบอร์) — 1 คู่ = 1 ใบที่แก้ได้ (ใบล่าสุด)
    function renderApprovalReqState(r){
        var $box = $('#a_reqState').removeClass('alert-secondary alert-warning alert-success');

        if (!r || !r.ReqDate){
            $box.addClass('alert-secondary')
                .html('<i class="ti ti-file-plus me-1"></i>ยังไม่เคยขอราคาของคู่ลูกค้า/เบอร์นี้ — '
                    + 'กด "เพิ่ม / บันทึก" จะขึ้นใบขอราคาใบใหม่');
            return;
        }

        var when = esc(fmtDateTime(r.ReqDate));
        if (r.Appv){
            $box.addClass('alert-success')
                .html('<i class="ti ti-circle-check me-1"></i>ใบขอราคาเมื่อ <b>' + when + '</b> — '
                    + '<b>อนุมัติแล้ว</b> · กดบันทึกจะแก้ทับใบเดิม');
        } else {
            $box.addClass('alert-warning')
                .html('<i class="ti ti-clock-hour-4 me-1"></i>ใบขอราคาเมื่อ <b>' + when + '</b> — '
                    + '<b>รออนุมัติ</b> · กดบันทึกจะแก้ทับใบเดิม');
        }
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
        // เก็บ ReqDate ของใบเดิมไว้ให้ปุ่มลบใช้ — ไม่มี = ยังไม่เคยขอราคาคู่นี้ (ใบใหม่)
        // เก็บสถานะอนุมัติเดิมไว้ด้วย เพื่อแยก "อนุมัติใหม่" (ต้องอยู่ในโหมด MD) ออกจาก "ใบที่อนุมัติไปแล้ว"
        $('#a_ReqDate').val(r.ReqDate ? fmtDateTime(r.ReqDate) : '')
            .data('reqdate', r.ReqDate || '')
            .data('appv', r.Appv ? 1 : 0);
        $('#a_price').val(commaFmt(r.price, 2));
        $('#a_weight').val(commaFmt(r.weight, 2));
        $('#a_remark').val(r.remark || '');
        $('#a_costup').prop('checked', !!r.costup);
        $('#a_Appv').prop('checked', !!r.Appv);

        // ราคา 3 ช่อง = ราคาขาย 1/2/3 จากเมนู "กำหนดราคา" — คำนวณสด ๆ จากรหัสสินค้าเสมอ
        // (ค่าที่เคยบันทึกใน appvreq ใช้เป็นค่าสำรองเฉพาะตอนคำนวณไม่ได้ จะได้ไม่โชว์ช่องว่างเปล่า)
        fillApprovalPrices(res.calc, r);

        // ลบได้เฉพาะใบที่มีอยู่จริง
        $('#btnApprovalDelete').prop('disabled', !r.ReqDate);

        // ขั้นตอนของคู่นี้ + สถานะโหมดอนุมัติล่าสุดจาก server (session อาจหมดอายุระหว่างเปิดฟอร์มค้างไว้)
        renderApprovalReqState(r);
        setApprovalMdMode(res.md_unlocked);

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

    // ราคา 3 ช่อง + DB 3-4 / 1-2 Kg. — มาจากระบบกำหนดราคา (ProductPriceService.lookup)
    //   calc = payload จาก /price-approval/data → key `calc`
    //   req  = ใบขออนุมัติเดิม (ใช้เป็นค่าสำรองเมื่อคำนวณไม่ได้เท่านั้น)
    function fillApprovalPrices(calc, req){
        calc = calc || {};
        req  = req  || {};
        var p = calc.prices || null;
        var $note = $('#a_price_note').removeClass('of-price-note-warn');

        if (p){
            $('#a_price1').val(fmtNum(p.price_1, 2));
            $('#a_price2').val(fmtNum(p.price_2, 2));
            $('#a_price3').val(fmtNum(p.price_3, 2));
            $('#a_price_34').val(fmtNum(p.db_3_4, 2));
            $('#a_price_12').val(fmtNum(p.db_1_2, 2));

            // ที่มาของราคา: ราคาทุน → เงื่อนไขที่จับคู่ได้ → สูตรคูณ/หาร/บวก
            var src = [];
            if (calc.base_price != null) src.push('ราคาทุน ' + fmtNum(calc.base_price, 2));
            if (calc.rule && calc.rule.label) src.push(calc.rule.label);
            if (calc.rule) src.push('× ' + calc.rule.mul + ' ÷ ' + calc.rule.div + ' + ' + calc.rule.add);
            $note.text(src.join('  ·  '));
            return;
        }

        // คำนวณไม่ได้ (ไม่มีรหัสในตารางราคาทุน / ไม่มีเงื่อนไขรองรับ) — บอกเหตุผลไว้ ไม่ปล่อยว่างเงียบ ๆ
        $('#a_price1').val(fmtNum(req.price1, 2));
        $('#a_price2').val(fmtNum(req.price2, 2));
        $('#a_price3').val(fmtNum(req.price3, 2));
        $('#a_price_34').val('');
        $('#a_price_12').val('');

        var msg = calc.reason || '';
        if (msg && (req.price1 != null || req.price2 != null || req.price3 != null)){
            msg += ' — แสดงราคาที่บันทึกไว้ในใบเดิมแทน';
        }
        // ยังไม่ได้เลือกรหัสสินค้า = ไม่มีเหตุผลให้บอก ปล่อยว่างไว้ อย่าขึ้นกรอบเตือนเปล่า ๆ
        $note.text(msg);
        if (msg) $note.addClass('of-price-note-warn');
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

    // ── เพิ่ม / บันทึกใบขออนุมัติราคา ──
    function approvalSave(){
        var custno = ($('#a_custno').val() || '').trim();
        var itemno = $('#a_itemno').val() || '';

        if (!custno){ Swal.fire('ยังไม่ได้ระบุลูกค้า', 'กรอกรหัสลูกค้าก่อนบันทึก', 'warning'); return; }
        if (!itemno){ Swal.fire('ยังไม่ได้เลือกรหัสสินค้า', 'เลือกเบอร์สินค้าก่อนบันทึก', 'warning'); return; }

        stripCommaFields('#approvalModal');
        var approve = $('#a_Appv').is(':checked');
        var wasApproved = !!($('#a_ReqDate').data('appv'));   // ใบนี้อนุมัติไปแล้วก่อนหน้านี้

        // ติ๊กอนุมัติใหม่ = ต้องอยู่ในโหมดอนุมัติก่อน (ใบที่อนุมัติไปแล้ว MK แก้ต่อได้)
        if (approve && !wasApproved && !apvMdUnlocked){
            Swal.fire('ยังไม่ได้เข้าสู่โหมดอนุมัติ',
                'กรอกรหัสด้านบนแล้วกด "เข้าสู่โหมดอนุมัติ" ก่อนอนุมัติราคา', 'warning');
            $('#approvalModal .js-comma').trigger('blur');
            return;
        }

        var payload = {
            _token:      '{{ csrf_token() }}',
            custno:      custno,
            itemno:      itemno,
            price:       $('#a_price').val(),
            weight:      $('#a_weight').val(),
            price1:      $('#a_price1').val(),
            price2:      $('#a_price2').val(),
            price3:      $('#a_price3').val(),
            remark:      $('#a_remark').val(),
            costup:      $('#a_costup').is(':checked') ? 1 : 0,
            Appv:        approve ? 1 : 0,
            valid_to:    $('#a_validto').val()
        };
        // หมายเหตุ: ไม่ต้องส่ง ReqDate — server หาใบล่าสุดของคู่นี้เอง (มี = แก้ทับ, ไม่มี = ใบใหม่)

        var $btn = $('#btnApprovalSave').prop('disabled', true);

        $.post(APPROVAL_URL + '/save', payload)
            .done(function(res){
                if (!res.status){ Swal.fire('บันทึกไม่สำเร็จ', res.message || '', 'error'); return; }
                Swal.fire({icon: 'success', title: res.message});
                loadApprovalData();     // โหลดค่าที่บันทึกจริงกลับมา
            })
            .fail(function(xhr){
                var body = xhr.responseJSON || {};
                // โหมดอนุมัติหมดอายุระหว่างกรอก — ล็อกจอกลับให้ตรงกับ server แล้วให้กรอกรหัสใหม่
                if (body.md_locked) setApprovalMdMode(false);
                Swal.fire('บันทึกไม่สำเร็จ', body.message || 'เกิดข้อผิดพลาดในการบันทึก', 'error');
            })
            .always(function(){
                $btn.prop('disabled', false);
                $('#approvalModal .js-comma').trigger('blur');   // ใส่คอมมากลับ
            });
    }

    // ── ลบใบขออนุมัติราคาใบที่กำลังดูอยู่ ──
    function approvalDelete(){
        var reqdate = $('#a_ReqDate').data('reqdate') || '';
        if (!reqdate){ Swal.fire('ไม่มีใบให้ลบ', 'คู่ลูกค้า/เบอร์นี้ยังไม่เคยมีใบขออนุมัติราคา', 'info'); return; }

        Swal.fire({
            icon: 'warning',
            title: 'ลบใบขออนุมัติราคา?',
            html: 'วันที่ขอราคา ' + fmtDateTime(reqdate) + '<br>ราคาที่ยืนไว้ (ตารางล่าง) จะไม่ถูกลบ',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#d33'
        }).then(function(r){
            if (!r.isConfirmed) return;
            $.post(APPROVAL_URL + '/delete', {
                _token:  '{{ csrf_token() }}',
                custno:  ($('#a_custno').val() || '').trim(),
                itemno:  $('#a_itemno').val() || '',
                ReqDate: reqdate
            }).done(function(res){
                if (!res.status){ Swal.fire('ลบไม่สำเร็จ', res.message || '', 'error'); return; }
                Swal.fire({icon: 'success', title: res.message});
                loadApprovalData();
            }).fail(function(xhr){
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'เกิดข้อผิดพลาดในการลบ';
                Swal.fire('ลบไม่สำเร็จ', msg, 'error');
            });
        });
    }

    // พิมพ์ — ยังไม่มีแบบฟอร์มกระดาษให้อ้างอิง จึงใช้พิมพ์หน้าจอไปก่อน
    function approvalPrint(){
        window.print();
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
    $(document).on('show.bs.collapse hide.bs.collapse', '#orderFilterBox', function(e){
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
            if (this._flatpickr) this._flatpickr.clear();
            else $(this).val('');
        });
        loadData("{{ $page_url }}/datatable");
    }
</script>
</body>

</html>
