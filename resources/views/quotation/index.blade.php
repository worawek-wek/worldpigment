<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">

<head>
    @include('layout/inc_header')
    <title>ใบเสนอราคา - World Pigment</title>

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

/* กล่อง section ในฟอร์มสร้าง/แก้ไข */
.qf-sec {
    border: 1px solid #e2e7ea;
    border-radius: .6rem;
    padding: 1rem 1.15rem 1.15rem;
    margin-bottom: 1rem;
}
.qf-sec:last-child { margin-bottom: 0; }
.qf-sec-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #2f7a78;
    letter-spacing: .2px;
    margin-bottom: .9rem;
    padding-bottom: .55rem;
    border-bottom: 2px solid #d9e6e6;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.qf-sec-title i { font-size: 1.25rem; }

/* ช่องกรอกในตารางรายการ (โชว์ทุกคอลัมน์ → ตารางกว้าง เลื่อนแนวนอนได้) */
#quotationItemsTable th { white-space: nowrap; font-size: .82rem; }
.qitem-input { min-width: 90px; }
.qitem-input:not(.text-end) { min-width: 130px; }   /* คอลัมน์ข้อความ (ชื่อสินค้า/หมายเหตุ) กว้างกว่า */
.qitem-input.qitem-name { min-width: 340px; }        /* ช่องชื่อสินค้า/รายละเอียด — ยาวเป็นพิเศษ */
/* เรียงตาม — คลิกหัวตาราง + เน้นคอลัมน์ที่กำลังเรียง (เหมือนหน้าเทียบสี) */
#table-data th.th-sort { cursor: pointer; user-select: none; }
#table-data th.th-sort:hover { background-color: #e9ecef; }
.th-sort-icon { font-size: .9rem; opacity: .35; vertical-align: middle; }
.th-sort-icon.active { opacity: 1; color: #ffd43b; font-size: 1.05rem; font-weight: 700; margin-left: .15rem; }
#table-data thead th.col-sorted { background-color: #696cff; color: #fff; box-shadow: inset 0 -3px 0 #ffd43b; }
#table-data thead th.col-sorted:hover { background-color: #5a5ef0; }
#table-data thead th.col-sorted small { color: rgba(255,255,255,.8) !important; }
#table-data tbody td.col-sorted { box-shadow: inset 2px 0 0 #696cff, inset -2px 0 0 #696cff; }
/* สลับภาษา section หมายเหตุ — โชว์เฉพาะ span ของภาษาที่เลือก (ข้อมูลชุดเดียว) */
#remarkSection.lang-th .i18n-en { display: none; }
#remarkSection.lang-en .i18n-th { display: none; }
/* สวิตช์ TH/EN — segmented control (กล่องเดียวมี border รอบ กัน border ขาดจาก btn-group) */
.lang-switch { display: inline-flex; border: 1px solid #CBD5E1; border-radius: .375rem; overflow: hidden; }
.lang-switch .btn { border: 0; border-radius: 0; margin: 0; color: #0D6EFD; background: #fff; font-weight: 600; min-width: 46px; }
.lang-switch .btn + .btn { border-left: 1px solid #CBD5E1; }
.lang-switch .btn:hover:not(.active) { background: #e7f0ff; color: #0a58ca; }
.lang-switch .btn.active { background: #0D6EFD; color: #fff; }
/* เน้นพื้นหลังคอลัมน์ปรับราคา (ปัจจุบัน/ใหม่) — สีเข้ม */
#quotationItemsTable th.qcol-rev { background: #3e8c8b !important; color: #fff; }
#quotationItemsTable td.qcol-rev { background: #8fcfc8 !important; }
/* กรอบล้อมบล็อกปรับราคา (บน/ล่าง/ซ้าย/ขวา) + เส้นคั่นกลาง ปัจจุบัน|ใหม่ */
#quotationItemsTable th.qcol-rev { border-top: 3px solid #2f6f6e !important; }
#quotationItems tr:last-child td.qcol-rev { border-bottom: 3px solid #2f6f6e !important; }
#quotationItemsTable th.qcol-rev-start,
#quotationItemsTable td.qcol-rev-start { border-left: 3px solid #2f6f6e !important; }
#quotationItemsTable th.qcol-rev-end,
#quotationItemsTable td.qcol-rev-end { border-right: 3px solid #2f6f6e !important; }
#quotationItemsTable th.qcol-sep,
#quotationItemsTable td.qcol-sep { border-left: 3px solid #2f6f6e !important; }

/* กล่อง "ราคา" (แยกต่างหาก — สีน้ำเงิน) */
#quotationItemsTable th.qcol-price { background: #5b7fa6 !important; color: #fff; border-top: 3px solid #3f5f85 !important; }
#quotationItemsTable td.qcol-price { background: #e9f0f7 !important; }
#quotationItems tr:last-child td.qcol-price { border-bottom: 3px solid #3f5f85 !important; }
#quotationItemsTable th.qcol-price-start,
#quotationItemsTable td.qcol-price-start { border-left: 3px solid #3f5f85 !important; }
#quotationItemsTable th.qcol-price-end,
#quotationItemsTable td.qcol-price-end { border-right: 3px solid #3f5f85 !important; }

/* ─── ช่องวันที่ในฟอร์ม: ให้ label อยู่บนหัวช่องเสมอ (เรียงตรงแถวกับช่องอื่น) ───
   flatpickr(static:true) ห่อ input ด้วย .flatpickr-wrapper ที่เป็น inline-block
   ซึ่ง .form-label ของ Bootstrap ก็ inline-block → label สั้น ๆ อย่าง "Revise Date"
   จึงไปอยู่บรรทัดเดียวกับช่องกรอก (label ยาวกว่าถึงจะดันตกบรรทัด)
   scope เฉพาะในฟอร์ม — ช่องวันที่ในตัวกรองอยู่ใน d-flex ต้องคง inline-block ไว้ */
#quotationForm .flatpickr-wrapper {
    display: block;
    width: 100%;
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
                        <button class="btn btn-label-primary border" style="color: #1f158e;" onclick="quotationCustomers()">
                            <i class="ti ti-users me-1"></i>
                            ประวัติตามลูกค้า
                        </button>
                        <button class="btn btn-primary" onclick="openCreate()">
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
                    <label class="form-label small fw-medium mb-1">ค้นหา <span class="text-muted fw-normal">(เลขที่ใบเสนอราคา / รหัสลูกค้า / ชื่อลูกค้า)</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" name="search" class="form-control p_search"
                            oninput="loadData(page)">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-medium mb-1">วันที่เสนอราคา</label>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small fw-medium">ตั้งแต่</span>
                        <input type="text" name="date_from" class="form-control flatpickr-date p_search" autocomplete="off" placeholder="วว/ดด/ปปปป">
                        <span class="small fw-medium">ถึง</span>
                        <input type="text" name="date_to" class="form-control flatpickr-date p_search" autocomplete="off" placeholder="วว/ดด/ปปปป">
                    </div>
                </div>
            </div>

            {{-- แถวตัวกรอง 2: ชนิดสินค้า --}}
            <div class="row g-3 align-items-end mt-1">
                <div class="col-md-4">
                    <label class="form-label small fw-medium mb-1">ชนิดสินค้า</label>
                    <select name="product_type" class="selectpicker w-100 p_search" data-style="btn-default" onchange="loadData(page)">
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

            {{-- state การเรียง — เก็บนอก #table-data เพื่อคงค่าเมื่อตารางโหลดใหม่ (default = id desc = ล่าสุด) --}}
            <input type="hidden" name="sort_col" value="id" class="p_search">
            <input type="hidden" name="sort_dir" value="desc" class="p_search">

            {{-- เรียงตามลำดับการเพิ่ม (id) + จำนวนต่อหน้า --}}
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <div class="d-flex align-items-center">
                    <label class="form-label small fw-medium mb-0 me-2 d-flex align-items-center"
                        title="เรียงตามลำดับการเพิ่มข้อมูลเข้าระบบ">
                        เรียงตามลำดับการเพิ่ม
                        <i class="ti ti-info-circle ms-1 text-muted"></i>
                    </label>
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
                        onchange='loadData("{{$page_url}}/datatable")'>
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

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- Modal: สร้าง / แก้ไข ใบเสนอราคา (ใช้ฟอร์มร่วมกัน)                  -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="modal modalHeadDecor fade" id="quotationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="quotationModalTitle">สร้างใบเสนอราคา</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="quotationForm">
                <input type="hidden" name="mode" id="q_mode" value="insert">
                {{-- qno เดิม (ใช้ตอน update/หา key) --}}
                <input type="hidden" name="qno" id="q_qno_key">

                <div class="modal-body px-4 py-4">

                    {{-- ── Section 1: ข้อมูลเอกสาร ── --}}
                    <div class="qf-sec">
                        <div class="qf-sec-title"><i class="ti ti-clipboard-text"></i>ข้อมูลเอกสาร</div>
                        {{-- หัวกระดาษ (บนสุด) — เลือกก่อน เพราะกำหนด prefix ของเลขที่ --}}
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">หัวกระดาษ <span class="text-muted fw-normal">(หัวเอกสารที่พิมพ์)</span></label>
                                <select name="letterhead" id="q_letterhead" class="selectpicker w-100" data-style="btn-default" onchange="onLetterheadChange()">
                                    <option value="WPI">WPI</option>
                                    <option value="WPC">WPC</option>
                                    <option value="WH">WH</option>
                                </select>
                            </div>
                        </div>
                        {{-- แถว: เลขที่ / วันที่ / Revise Date --}}
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">เลขที่ใบเสนอราคา <span class="text-danger">*</span></label>
                                <input type="text" name="Qno" id="q_Qno" class="form-control" maxlength="10" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">วันที่เสนอราคา <span class="text-danger">*</span></label>
                                <input type="text" name="Qdate" id="q_Qdate" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-danger">Revise Date</label>
                                <input type="text" name="Revisedate" id="q_Revisedate" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป">
                            </div>
                        </div>
                        {{-- แถวล่าง: ชนิดสินค้า / พร้อมตัวอย่าง --}}
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">ชนิดสินค้า</label>
                                <select name="PDtype" id="q_PDtype" class="selectpicker w-100" data-style="btn-default">
                                    @foreach ($pdtypes as $pt)
                                        @php
                                            // CP: แสดงเป็น "Compound" ในฟอร์ม (เข้าใจง่ายกว่า)
                                            // — ไม่แก้ pdtype.PDHead1 ใน DB เพราะค่านั้นถูกใช้เป็นหัวเรื่องบนใบเสนอราคาที่พิมพ์ออก
                                            $ptLabel = $pt->PDType === 'CP' ? 'Compound' : $pt->PDHead1;
                                        @endphp
                                        <option value="{{ $pt->PDType }}">{{ $pt->PDType }} — {{ $ptLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" id="q_exam" name="exam" type="checkbox" value="1">
                                    <label class="form-check-label" for="q_exam">พร้อมตัวอย่าง</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Section 2: ข้อมูลลูกค้า ── --}}
                    <div class="qf-sec">
                        <div class="qf-sec-title"><i class="ti ti-building-store"></i>ข้อมูลลูกค้า</div>
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">รหัสลูกค้า <span class="text-danger">*</span></label>
                                <input type="text" name="Custid" id="q_Custid" class="form-control" maxlength="6"
                                    oninput="lookupCustomer(this.value)" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">ชื่อลูกค้า <span class="text-danger">*</span></label>
                                <input type="text" name="CustName" id="q_CustName" class="form-control text-primary fw-bold" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">ชื่อลูกค้า (ภาษาอังกฤษ)</label>
                                <input type="text" name="Engname" id="q_Engname" class="form-control" maxlength="70">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">รหัสพนักงานขาย</label>
                                <input type="number" name="EmpID" id="q_EmpID" class="form-control">
                            </div>
                        </div>
                    </div>

                    {{-- ── Section 3: รายการสินค้า ── --}}
                    <div class="qf-sec">
                        <div class="qf-sec-title"><i class="ti ti-list-details"></i>รายการสินค้า</div>

                        {{-- รูปแบบตาราง: เลือก preset (1.1–2.3) → ตารางกรอก + PDF ใช้คอลัมน์ชุดนั้น
                             ไม่เลือก = อัตโนมัติ (โชว์ทุกคอลัมน์ในฟอร์ม, PDF ตัดคอลัมน์ที่ไม่มีใครกรอกออกเอง) --}}
                        <div class="row g-3 align-items-end mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-medium mb-1" for="q_col_format">รูปแบบตารางรายการ</label>
                                <select name="col_format" id="q_col_format" class="selectpicker w-100" data-style="btn-default" onchange="onColFormatChange()">
                                    <option value="">อัตโนมัติ — โชว์ทุกคอลัมน์</option>
                                    <optgroup label="1. ใบเสนอราคา">
                                        @foreach ($formatLabels as $code => $label)
                                            @if (str_starts_with($code, '1.'))
                                                <option value="{{ $code }}">{{ $code }} — {{ $label }}</option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="2. ใบขอปรับราคา (ปัจจุบัน / ใหม่)">
                                        @foreach ($formatLabels as $code => $label)
                                            @if (str_starts_with($code, '2.'))
                                                <option value="{{ $code }}">{{ $code }} — {{ $label }}</option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="alert alert-info py-2 px-3 mb-0 small">
                                <i class="ti ti-info-circle me-1"></i>กรอกเฉพาะคอลัมน์ที่ต้องการ — คอลัมน์ที่ปล่อยว่างทั้งหมดจะไม่แสดงในใบเสนอราคา
                                (หัวจดหมาย เสนอราคา/ปรับราคา ระบบตั้งให้อัตโนมัติ)
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                {{-- รหัสสินค้าใน PDF: กรอกรหัสไว้เพื่อค้นหาชื่อ/ราคาเสมอ แต่เลือกได้ว่าจะโชว์ให้ลูกค้าเห็นไหม --}}
                                <div class="form-check mb-0 mt-2">
                                    <input type="hidden" name="show_code" value="0">
                                    <input class="form-check-input" id="q_show_code" name="show_code" type="checkbox" value="1" checked>
                                    <label class="form-check-label" for="q_show_code">
                                        แสดงรหัสสินค้าใน PDF
                                        <i class="ti ti-info-circle text-muted"
                                            title="ไม่ติ๊ก = ยังกรอกรหัสเพื่อค้นหาสินค้าได้ แต่คอลัมน์รหัสจะไม่ขึ้นบนใบเสนอราคาที่พิมพ์"></i>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-bordered align-middle mb-0" id="quotationItemsTable">
                                <thead class="table-light"><tr id="quotationItemsHead"></tr></thead>
                                <tbody id="quotationItems"></tbody>
                            </table>
                        </div>

                        {{-- ปุ่มเพิ่มรายการ — อยู่ใต้ตาราง (แถวใหม่จะไปต่อท้ายตรงนี้พอดี) --}}
                        <div class="mt-3">
                            <button type="button" class="btn btn-label-warning" onclick="addQuotationItem()">
                                <i class="ti ti-plus me-1"></i>เพิ่มรายการ
                            </button>
                        </div>
                    </div>

                    {{-- ── Section 4: หมายเหตุ (2 ภาษา — สลับแค่ label ด้วยสวิตช์ TH/EN) ── --}}
                    <input type="hidden" name="remark_lang" id="q_remark_lang" value="th">
                    <div class="qf-sec lang-th" id="remarkSection">
                        <div class="qf-sec-title d-flex align-items-center">
                            <span><i class="ti ti-note me-2"></i><span class="i18n-th">หมายเหตุ</span><span class="i18n-en">Remarks</span></span>
                            {{-- สวิตช์ภาษา: สลับเฉพาะคำ (label) ข้อมูลชุดเดียว --}}
                            <div class="lang-switch" role="group">
                                <button type="button" class="btn btn-sm active" id="btnLangTh" onclick="setRemarkLang('th')">TH</button>
                                <button type="button" class="btn btn-sm" id="btnLangEn" onclick="setRemarkLang('en')">EN</button>
                            </div>
                        </div>
                        <div class="alert alert-info py-2 px-3 mb-3 small">
                            <i class="ti ti-info-circle me-1"></i>ช่องที่มี <span class="text-danger fw-bold">*</span> ต้องกรอก — ช่องที่ปล่อยว่างจะไม่แสดงในใบเสนอราคา
                        </div>

                        <div class="row g-3">
                            {{-- ราคาเม็ดพลาสติก --}}
                            <div class="col-md-6">
                                <label class="form-label">
                                    <span class="i18n-th">ราคาเม็ดพลาสติก</span><span class="i18n-en">Resin Price</span>
                                    <span class="text-muted fw-normal i18n-th">(เช่น 60.50 บาท เดือน มิย 2026)</span>
                                </label>
                                <input type="text" name="resin_price_note" id="q_resin_price_note" class="form-control"
                                    maxlength="100">
                            </div>

                            {{-- ราคานี้มีผลวันที่ (from → to) --}}
                            <div class="col-md-6">
                                <label class="form-label">
                                    <span class="i18n-th">ราคานี้มีผลวันที่</span><span class="i18n-en">Price validity from</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="text" name="ValidFrom" id="q_ValidFrom" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป" required>
                                    <span class="small fw-medium"><span class="i18n-th">ถึง</span><span class="i18n-en">to</span></span>
                                    <input type="text" name="Validto" id="q_Validto" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป" required>
                                </div>
                            </div>

                            {{-- จำนวนส่งมอบขั้นต่ำ --}}
                            <div class="col-md-6">
                                <label class="form-label">
                                    <span class="i18n-th">จำนวนส่งมอบขั้นต่ำ</span><span class="i18n-en">Minimum Quantity</span>
                                    <span class="text-danger">*</span>
                                    <span class="text-muted fw-normal i18n-th">(เช่น 100)</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" name="Qremark" id="q_Qremark" class="form-control" maxlength="50" required>
                                    <span class="input-group-text"><span class="i18n-th">กก.</span><span class="i18n-en">kg</span></span>
                                </div>
                            </div>

                            {{-- สถานที่ส่งสินค้า --}}
                            <div class="col-md-6">
                                <label class="form-label">
                                    <span class="i18n-th">สถานที่ส่งสินค้า</span><span class="i18n-en">Delivery Location</span>
                                    <span class="text-muted fw-normal i18n-th">(เช่น กรุงเทพฯและปริมณฑล)</span>
                                </label>
                                <input type="text" name="delivery_place" id="q_delivery_place" class="form-control"
                                    maxlength="100">
                            </div>

                            {{-- เทอมการส่งมอบสินค้า --}}
                            <div class="col-md-6">
                                <label class="form-label">
                                    <span class="i18n-th">เทอมการส่งมอบสินค้า</span><span class="i18n-en">Price Term</span>
                                    <span class="text-muted fw-normal i18n-th">(เช่น DDP)</span>
                                </label>
                                <input type="text" name="delivery_term" id="q_delivery_term" class="form-control"
                                    maxlength="50">
                            </div>

                            {{-- เทอมการชำระเงิน --}}
                            <div class="col-md-6">
                                <label class="form-label">
                                    <span class="i18n-th">เทอมการชำระเงิน</span><span class="i18n-en">Term of Payment</span>
                                    <span class="text-muted fw-normal i18n-th">(เช่น 30วัน นับจากส่งสินค้า)</span>
                                </label>
                                <input type="text" name="Term" id="q_Term" class="form-control">
                            </div>
                        </div>

                        {{-- หมายเหตุอื่น (เพิ่มได้ไม่จำกัด) --}}
                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0"><span class="i18n-th">หมายเหตุอื่น</span><span class="i18n-en">Other remarks</span></label>
                                <button type="button" class="btn btn-sm btn-label-warning" onclick="addNoteRow()">
                                    <i class="ti ti-plus me-1"></i><span class="i18n-th">เพิ่มหมายเหตุ</span><span class="i18n-en">Add remark</span>
                                </button>
                            </div>
                            <div id="otherNotesList"></div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-label-info" onclick="quotationPrintPreview()"
                        title="พิมพ์ตัวอย่างจากข้อมูลในฟอร์ม (ยังไม่บันทึก)">
                        <i class="ti ti-printer me-1"></i>พิมพ์ตัวอย่าง
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveQuotation()">
                        <i class="ti ti-device-floppy me-1"></i>บันทึกใบเสนอราคา
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- Modal: ดูรายละเอียด (โหลด HTML จาก quotation/show)               -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="quotationViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="overflow:hidden;">
            {{-- ปุ่มปิดลอย — แบนเนอร์ในเนื้อหาทำหน้าที่เป็น header แทน --}}
            <button type="button" class="btn-close btn-close-white position-absolute"
                style="top:1.15rem; right:1.25rem; z-index:1056;" data-bs-dismiss="modal" aria-label="ปิด"></button>
            <div class="modal-body p-0" id="quotationViewBody">
                <div class="text-center py-5">
                    <div class="spinner-border spinner-border-sm me-2"></div>กำลังโหลด...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- Modal: ประวัติใบเสนอราคาของลูกค้า (โหลด HTML จาก quotation/history) -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="quotationHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="overflow:hidden;">
            <button type="button" class="btn-close btn-close-white position-absolute"
                style="top:1.1rem; right:1.25rem; z-index:1056;" data-bs-dismiss="modal" aria-label="ปิด"></button>
            <div class="modal-body p-0" id="quotationHistoryBody">
                <div class="text-center py-5">
                    <div class="spinner-border spinner-border-sm me-2"></div>กำลังโหลด...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- Modal: รายชื่อลูกค้า (ขั้นแรก) — โหลด HTML จาก quotation/customers  -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="quotationCustomersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="overflow:hidden;">
            <button type="button" class="btn-close btn-close-white position-absolute"
                style="top:1.1rem; right:1.25rem; z-index:1056;" data-bs-dismiss="modal" aria-label="ปิด"></button>
            <div class="modal-body p-0" id="quotationCustomersBody">
                <div class="text-center py-5">
                    <div class="spinner-border spinner-border-sm me-2"></div>กำลังโหลด...
                </div>
            </div>
        </div>
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

        // ── Flatpickr: ทุกช่องวันที่ใช้ flatpickr-date (รูปแบบ d/m/Y เหมือนทั้งระบบ) ──
        $(function () {
            flatpickr('.flatpickr-date', {
                dateFormat: 'd/m/Y',
                allowInput: true,
                static: true,
                disableMobile: true,
                onChange: function (_, __, instance) {
                    // ช่องกรอง (p_search) → โหลดตารางใหม่เมื่อเลือกวันที่
                    if (instance.input.classList.contains('p_search')) loadData(page);
                }
            });
            // sync dropdown "เรียงตาม" ให้ตรงกับ state เริ่มต้น (id desc = ล่าสุด)
            syncSortDropdown();
        });

        // ────────────────────────────────────────────────────────
        //  เรียงลำดับ — state เดียว (sort_col/sort_dir) ใช้ร่วม 2 ทาง:
        //   1) dropdown "เรียงตามลำดับการเพิ่ม" (ล่าสุด/เก่าสุด = เรียงตาม id)
        //   2) คลิกหัวตาราง (เรียงตามคอลัมน์ใดก็ได้)
        // ────────────────────────────────────────────────────────
        function setSort(col, dir) {
            $('input[name="sort_col"]').val(col);
            $('input[name="sort_dir"]').val(dir);
            syncSortDropdown();
            loadData(page);
        }
        // dropdown สะท้อนเฉพาะการเรียงตาม id → ล่าสุด/เก่าสุด; คอลัมน์อื่น = option ซ่อน
        function syncSortDropdown() {
            var col = $('input[name="sort_col"]').val();
            var dir = $('input[name="sort_dir"]').val();
            $('select[name="sort"]').val(col === 'id' ? dir : '');
        }
        // เลือกจาก dropdown → เรียงตาม id ทิศตามที่เลือก
        function onSortDropdown(val) {
            if (val === '') return;   // option บอกสถานะ (เรียงตามคอลัมน์) — ไม่ทำอะไร
            setSort('id', val);
        }
        // คลิกหัวตาราง: คอลัมน์เดิมซ้ำ = สลับ asc/desc; คอลัมน์ใหม่ = เริ่มที่ asc
        // (delegated เพราะ thead อยู่ใน #table-data ที่โหลดใหม่ทุกครั้ง)
        $(document).on('click', '#table-data th[data-sort]', function () {
            var col    = String($(this).data('sort'));
            var curCol = $('input[name="sort_col"]').val();
            var curDir = $('input[name="sort_dir"]').val();
            var dir    = (curCol === col) ? (curDir === 'asc' ? 'desc' : 'asc') : 'asc';
            setSort(col, dir);
        });

        // bootstrap-select (.selectpicker) ไม่รู้ตัวเมื่อ JS เปลี่ยนค่าด้วย .val() หรือ form.reset()
        // → ต้องสั่งให้วาดหน้าปุ่มใหม่ ไม่งั้นโชว์ค่าเดิมค้าง
        // ⚠ ใช้ 'render' ไม่ใช่ 'refresh': refresh = สร้าง option list ใหม่ทั้งชุด ซึ่ง bootstrap-select
        // เวอร์ชันนี้ (เขียนมาสำหรับ Bootstrap 4 แต่เรารันบน 5.3) วาดซ้อนกัน ทำให้ข้อความ option ต่อกันมั่ว
        // render = อัปเดตแค่หน้าปุ่มให้ตรงกับค่าที่เลือกอยู่ ซึ่งเป็นสิ่งที่เราต้องการจริง ๆ
        function refreshPickers(scope){
            $(scope || document).find('.selectpicker').selectpicker('render');
        }

        // ตั้งค่าวันที่ให้ flatpickr (รับ Y-m-d / datetime → แสดง d/m/Y); ว่าง = เคลียร์
        // ⚠ ต้องส่ง 'Y-m-d' เป็น argument ที่ 3 เสมอ — ไม่งั้น flatpickr จะ parse string ที่เราส่งไป
        // ด้วย dateFormat ของตัวเอง (d/m/Y) แล้วได้วันที่มั่ว (ทุกวันกลายเป็น 20/06/<ปีปัจจุบัน>)
        function setFp(id, val) {
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
        //  รายการสินค้า — คอลัมน์กำหนดเองได้ (config-driven)
        // ────────────────────────────────────────────────────────
        var COL_REG = @json($colRegistry);   // คลังคอลัมน์ทั้งหมด {key:{label,num,suffix}}
        // โหมดอัตโนมัติ: แสดงทุกคอลัมน์ — คอลัมน์ที่ไม่มีใครกรอกจะถูกตัดออกตอนบันทึก (ฝั่ง server)
        var ALL_COLS = Object.keys(COL_REG).map(function(k){
            return {key: k, label: COL_REG[k].label, num: !!COL_REG[k].num};
        });
        // preset คอลัมน์ของแต่ละรูปแบบ {'1.1': [{key,label}, ...], ...} — ต้องตรงกับ presets() ฝั่ง server
        var PRESETS = @json($presets);

        // คอลัมน์ที่ตารางกรอกใช้อยู่ตอนนี้ — ตามรูปแบบที่เลือก, ไม่เลือก = ทุกคอลัมน์ (พฤติกรรมเดิม)
        // label เอาจาก preset (ชื่อหัวคอลัมน์ของรูปแบบนั้น) แต่ num/ชนิดช่องกรอกยังอิง registry
        function activeCols(){
            var fmt = $('#q_col_format').val() || '';
            var preset = PRESETS[fmt];
            if (!fmt || !preset) return ALL_COLS;
            return preset.map(function(c){
                return {key: c.key, label: c.label, num: !!(COL_REG[c.key] && COL_REG[c.key].num)};
            });
        }

        // เดารูปแบบของใบที่บันทึกไว้ — เทียบลำดับ key ของ col_config กับแต่ละ preset
        // ไม่เก็บรหัสรูปแบบลง DB (ไม่ต้องเพิ่มคอลัมน์) เพราะ col_config บอกได้อยู่แล้วว่าใช้คอลัมน์ชุดไหน
        // ตัด 'code' ออกจากทั้งสองฝั่งก่อนเทียบ — ใบที่ไม่ติ๊ก "แสดงรหัสสินค้าใน PDF" จะไม่มี code ใน col_config
        // ไม่ตรงกับ preset ไหนเลย → '' = อัตโนมัติ
        function detectColFormat(savedCols){
            var strip = function(list){
                return (list || []).map(function(c){ return c.key; })
                                   .filter(function(k){ return k !== 'code'; }).join(',');
            };
            var saved = strip(savedCols);
            if (!saved) return '';
            var found = '';
            Object.keys(PRESETS).forEach(function(fmt){
                if (fmt && !found && strip(PRESETS[fmt]) === saved) found = fmt;
            });
            return found;
        }

        // เปลี่ยนรูปแบบ → วาดตารางใหม่ (ข้อมูลที่กรอกไว้ยังอยู่ครบใน currentItems ไม่หาย)
        function onColFormatChange(){
            renderItems();
        }
        // กล่อง "ค่าผลิต/ค่าแม่สี ปรับราคา" (ปัจจุบัน|ใหม่) — เน้นพื้นหลัง (ราคาไม่รวมในกล่องนี้)
        var REVISION_COLS = {cur_process_fee:1, cur_pigment_price:1, new_process_fee:1, new_pigment_price:1};
        // คอลัมน์แรกของกลุ่ม "ใหม่" — ใส่เส้นคั่นซ้าย แยกจากกลุ่ม "ปัจจุบัน"
        var GROUP_SEP_COLS = {new_process_fee:1};
        var REV_START = 'cur_process_fee';   // คอลัมน์แรกของกล่อง (เส้นซ้าย)
        var REV_END   = 'new_pigment_price'; // คอลัมน์สุดท้ายของกล่อง (เส้นขวา)
        // กล่อง "ราคา" (แยกต่างหาก คนละสี)
        var PRICE_COLS   = {price_kg:1, new_price:1, price_vat:1};
        var PRICE_START  = 'price_kg';
        var PRICE_END    = 'price_vat';
        // รวม class เน้น/กรอบของแต่ละกล่อง
        function revClasses(key){
            var cl = [];
            if (REVISION_COLS[key])  cl.push('qcol-rev');
            if (GROUP_SEP_COLS[key])  cl.push('qcol-sep');
            if (key === REV_START)    cl.push('qcol-rev-start');
            if (key === REV_END)      cl.push('qcol-rev-end');
            if (PRICE_COLS[key])      cl.push('qcol-price');
            if (key === PRICE_START)  cl.push('qcol-price-start');
            if (key === PRICE_END)    cl.push('qcol-price-end');
            return cl.join(' ');
        }
        var currentItems = [];               // ข้อมูลรายการ (array ของ object)

        function esc(v){ return String(v == null ? '' : v).replace(/"/g,'&quot;'); }

        // ── ตารางกรอกรายการ — คอลัมน์ตามรูปแบบที่เลือก (ไม่เลือก = ทุกคอลัมน์) ──
        function renderItems(){
            var cols = activeCols();
            var head = '';
            cols.forEach(function(c){
                var rc = revClasses(c.key);
                head += '<th class="text-center'+(rc?' '+rc:'')+'">'+esc(c.label)+'</th>';
            });
            head += '<th width="46" class="text-center">ลบ</th>';
            $('#quotationItemsHead').html(head);

            var body = '';
            if (!currentItems.length){
                body = '<tr><td colspan="'+(cols.length+1)+'" class="text-center py-3">ยังไม่มีรายการ — กด “เพิ่มรายการ”</td></tr>';
            } else {
                currentItems.forEach(function(row, idx){
                    body += '<tr>';
                    cols.forEach(function(c){
                        var typ = c.num ? 'number' : 'text';
                        var cls = 'form-control form-control-sm qitem-input qitem-' + c.key + (c.num ? ' text-end' : '');
                        var step = c.num ? ' step="0.01"' : '';
                        var rc = revClasses(c.key);
                        var tdcls = rc ? ' class="'+rc+'"' : '';
                        // ช่องรหัสสินค้า → ค้นชื่อ/ราคา/รายละเอียดมาเติมช่องที่ว่าง (oninput + debounce)
                        var oncode = (c.key === 'code') ? '; lookupItem('+idx+',this.value)' : '';
                        body += '<td'+tdcls+'><input type="'+typ+'" class="'+cls+'"'+step+' value="'+esc(row[c.key])+'" oninput="updateItem('+idx+',\''+c.key+'\',this.value)'+oncode+'"></td>';
                    });
                    body += '<td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-label-danger" title="ลบ" onclick="removeItemRow('+idx+')"><i class="ti ti-trash"></i></button></td>';
                    body += '</tr>';
                });
            }
            $('#quotationItems').html(body);
        }

        function updateItem(idx, key, val){
            var row = currentItems[idx];
            if (!row) return;
            row[key] = val;
            // ผู้ใช้แก้ช่องนี้เอง → ไม่ถือเป็น auto-fill อีก (กันโดนล้างตอนรหัสไม่เจอ)
            if (row.__auto && row.__auto[key]) delete row.__auto[key];
        }

        // ── ค้นข้อมูลสินค้าจากรหัส (oninput + debounce) เติมช่องที่ว่าง / ไม่เจอ = เอาที่เติมไว้ออก ──
        var itemLookupTimer = null, itemLookupXhr = null;
        function lookupItem(idx, code){
            code = (code || '').trim();
            clearTimeout(itemLookupTimer);
            itemLookupTimer = setTimeout(function(){ applyItemLookup(idx, code); }, 350);
        }
        function clearAutoFilled(row){
            if (row.__auto){
                Object.keys(row.__auto).forEach(function(k){ row[k] = ''; });
                row.__auto = {};
            }
        }
        // อัปเดตค่าในช่องของแถวนั้นจาก data model โดยไม่ re-render (คง focus/เคอร์เซอร์ช่องรหัส)
        function refreshRowInputs(idx){
            var $inputs = $('#quotationItems tr').eq(idx).find('input');
            ALL_COLS.forEach(function(c, i){
                if (c.key === 'code') return;   // อย่าแตะช่องที่กำลังพิมพ์
                var v = currentItems[idx][c.key];
                $inputs.eq(i).val(v == null ? '' : v);
            });
        }
        function applyItemLookup(idx, code){
            var row = currentItems[idx];
            if (!row) return;
            clearAutoFilled(row);              // ล้างที่เคยเติมไว้ก่อน (เผื่อรหัสเปลี่ยน/ไม่เจอ)
            if (!code){ refreshRowInputs(idx); return; }
            if (itemLookupXhr) itemLookupXhr.abort();
            itemLookupXhr = $.getJSON("{{ $page_url }}/item-lookup", {code: code}, function(res){
                if (currentItems[idx] !== row) return;   // แถวเปลี่ยนระหว่างรอผล
                if (res.found && res.cells){
                    row.__auto = {};
                    Object.keys(res.cells).forEach(function(k){
                        var cur = row[k];
                        if (cur === undefined || cur === null || String(cur).trim() === ''){
                            row[k] = res.cells[k];
                            row.__auto[k] = true;   // จำว่าเติมอัตโนมัติ
                        }
                    });
                }
                refreshRowInputs(idx);
            }).fail(function(){ refreshRowInputs(idx); });
        }

        function addQuotationItem(){ currentItems.push({}); renderItems(); }
        function removeItemRow(idx){
            currentItems.splice(idx, 1);
            if (!currentItems.length) currentItems.push({});
            renderItems();
        }
        function setItems(items){
            currentItems = (items && items.length) ? items : [{}, {}];
            renderItems();
        }

        // ────────────────────────────────────────────────────────
        //  ค้นชื่อลูกค้าจากรหัส (เติมชื่อไทย/อังกฤษ/เทอมอัตโนมัติ)
        // ────────────────────────────────────────────────────────
        // เรียกจาก oninput → debounce กันยิง AJAX ทุกตัวอักษร, ไม่เด้ง popup ระหว่างพิมพ์
        var custLookupTimer = null, custLookupXhr = null;
        function lookupCustomer(code){
            code = (code || '').trim();
            clearTimeout(custLookupTimer);
            if (!code){ $('#q_CustName').val(''); return; }
            custLookupTimer = setTimeout(function(){
                if (custLookupXhr) custLookupXhr.abort();
                custLookupXhr = $.getJSON("{{ $page_url }}/customer/" + encodeURIComponent(code), function(res){
                    // กันผลเก่ามาทับ: เช็คว่ารหัสในช่องยังตรงกับที่ค้นอยู่
                    if ($('#q_Custid').val().trim() !== code) return;
                    if (res.found){
                        $('#q_CustName').val(res.name || '');
                        if (res.nameEN) $('#q_Engname').val(res.nameEN);
                        // เทอมการชำระเงิน — ดึงจาก customer.term มาเติมให้ (ถ้าช่องยังว่าง)
                        // ยกเว้นค่า "CASH" (เงินสด) ที่ไม่ต้องการให้ขึ้นบนใบเสนอราคา → ปล่อยช่องว่างไว้
                        if (res.term && !$('#q_Term').val() && String(res.term).trim().toUpperCase() !== 'CASH') {
                            $('#q_Term').val(res.term);
                        }
                    } else {
                        // ไม่เจอ → เคลียร์ชื่อไทย + อังกฤษ เงียบๆ (ไม่เด้ง popup)
                        $('#q_CustName').val('');
                        $('#q_Engname').val('');
                    }
                });
            }, 350);
        }

        // ────────────────────────────────────────────────────────
        //  หมายเหตุอื่น (เพิ่มได้ไม่จำกัด) — เก็บเป็น input name="other_notes[]"
        // ────────────────────────────────────────────────────────
        function noteRowHtml(val){
            return '<div class="input-group mb-2 note-row">'
                + '<input type="text" name="other_notes[]" class="form-control" maxlength="255" value="'+esc(val || '')+'">'
                + '<button type="button" class="btn btn-outline-danger" onclick="removeNoteRow(this)" title="ลบ"><i class="ti ti-trash"></i></button>'
                + '</div>';
        }
        function addNoteRow(val){
            $('#otherNotesList').append(noteRowHtml(typeof val === 'string' ? val : ''));
        }
        function removeNoteRow(btn){ $(btn).closest('.note-row').remove(); }
        function renderNotes(arr){
            $('#otherNotesList').empty();
            (arr && arr.length ? arr : []).forEach(function(n){ addNoteRow(n); });
        }

        // สลับภาษา section หมายเหตุ — เปลี่ยนแค่คำ (label) ข้อมูลกรอกชุดเดียว
        function setRemarkLang(lang){
            lang = (lang === 'en') ? 'en' : 'th';
            var sec = document.getElementById('remarkSection');
            sec.classList.toggle('lang-en', lang === 'en');
            sec.classList.toggle('lang-th', lang !== 'en');
            $('#q_remark_lang').val(lang);
            $('#btnLangEn').toggleClass('active', lang === 'en');
            $('#btnLangTh').toggleClass('active', lang !== 'en');
        }

        // ────────────────────────────────────────────────────────
        //  เปิดฟอร์มสร้างใหม่
        // ────────────────────────────────────────────────────────
        function openCreate(){
            document.getElementById('quotationForm').reset();
            $('#q_mode').val('insert');
            $('#q_qno_key').val('');
            $('#quotationModalTitle').text('สร้างใบเสนอราคา');
            $('#q_Qno').prop('readonly', false);
            // วันที่เสนอราคา = วันนี้ (ผ่าน flatpickr); เคลียร์ช่องวันที่อื่น (reset() ไม่ล้าง state flatpickr)
            setFp('q_Qdate', new Date().toISOString().slice(0,10));
            setFp('q_Revisedate', '');
            setFp('q_ValidFrom', '');
            setFp('q_Validto', '');
            $('#q_col_format').val('');   // เริ่มที่โหมดอัตโนมัติ (พฤติกรรมเดิม)
            onColFormatChange();
            setItems();
            renderNotes([]);   // เคลียร์หมายเหตุอื่น (reset() ไม่ล้าง input ที่ append เข้ามา)
            setRemarkLang('th');   // เริ่มที่ภาษาไทย
            $('#q_letterhead').val('WH');   // หัวกระดาษเริ่มต้น = WH (ตรงกับ prefix เลขที่ default)
            refreshPickers('#quotationForm');   // ให้ selectpicker วาดปุ่มตามค่าที่เพิ่ง set
            // ขอเลขที่ถัดไปตาม prefix ของหัวกระดาษ (แก้ไขได้)
            $.getJSON("{{ $page_url }}/next-qno", {prefix: $('#q_letterhead').val()}, function(res){
                if (res.qno) $('#q_Qno').val(res.qno);
            });
            new bootstrap.Modal('#quotationModal').show();
        }

        // เปลี่ยนหัวกระดาษ (เฉพาะตอนสร้างใหม่) → ออกเลขที่ใหม่ตาม prefix ของหัวกระดาษที่เลือก
        function onLetterheadChange(){
            if ($('#q_mode').val() !== 'insert') return;   // โหมดแก้ไข: ไม่เปลี่ยนเลขที่ (เป็น key)
            $.getJSON("{{ $page_url }}/next-qno", {prefix: $('#q_letterhead').val()}, function(res){
                if (res.qno) $('#q_Qno').val(res.qno);
            });
        }

        // ────────────────────────────────────────────────────────
        //  แก้ไข — ดึงข้อมูลเดิมมาเติมฟอร์ม
        // ────────────────────────────────────────────────────────
        function quotationEdit(qno){
            $.getJSON("{{ $page_url }}/edit", {qno: qno}, function(res){
                if (res.error){ Swal.fire('ไม่พบข้อมูล', qno, 'error'); return; }
                var h = res.header || {};
                document.getElementById('quotationForm').reset();
                $('#q_mode').val('update');
                $('#q_qno_key').val(h.Qno);
                $('#quotationModalTitle').text('แก้ไขใบเสนอราคา ' + (h.Qno || ''));
                $('#q_Qno').val((h.Qno || '').trim()).prop('readonly', true);   // ห้ามเปลี่ยนเลขที่
                setFp('q_Qdate', h.Qdate);
                setFp('q_Revisedate', h.Revisedate);
                setFp('q_ValidFrom', h.ValidFrom);
                setFp('q_Validto', h.Validto);
                $('#q_PDtype').val(h.PDtype);
                $('#q_letterhead').val(h.letterhead || 'WH');
                $('#q_exam').prop('checked', h.exam == 1);
                // แสดงรหัสสินค้าใน PDF: ติ๊กไว้ถ้า col_config ที่บันทึกไว้ของใบนี้มีคอลัมน์ "code"
                var savedCols = res.col_config || [];
                $('#q_show_code').prop('checked', savedCols.some(function (c) { return c.key === 'code'; }));
                // รูปแบบตาราง: เทียบ col_config ที่บันทึกไว้กับ preset — ตรงกับอันไหน = ใบนี้ใช้รูปแบบนั้น
                $('#q_col_format').val(detectColFormat(savedCols));
                refreshPickers('#quotationForm');   // ให้ selectpicker วาดปุ่มตามค่าที่โหลดมา
                onColFormatChange();
                $('#q_EmpID').val(h.EmpID);
                $('#q_Custid').val(h.Custid);
                // ชื่อไทยใช้จาก customer (join) ถ้ามี ไม่งั้น fallback CustName เดิม
                $('#q_CustName').val((res.cust && res.cust.name) ? res.cust.name : (h.CustName || ''));
                $('#q_Engname').val(h.Engname || (res.cust ? res.cust.nameEN : '') || '');
                $('#q_Qremark').val(h.Qremark);
                $('#q_Term').val(h.Term);
                // ── section หมายเหตุ (ช่องใหม่) ──
                $('#q_resin_price_note').val(h.resin_price_note || '');
                $('#q_delivery_place').val(h.delivery_place || '');
                $('#q_delivery_term').val(h.delivery_term || '');
                var otherNotes = [];
                try { otherNotes = h.other_notes ? JSON.parse(h.other_notes) : []; } catch (e) { otherNotes = []; }
                renderNotes(otherNotes);
                setRemarkLang(h.remark_lang === 'en' ? 'en' : 'th');   // ภาษาที่เคยเลือกไว้
                // รายการ: ใช้ค่าแบน (cells) จาก server ตรงๆ (ฟอร์มโชว์ทุกคอลัมน์)
                var items = (res.items || []).map(function(d){ return Object.assign({}, d.cells || {}); });
                setItems(items);
                new bootstrap.Modal('#quotationModal').show();
            }).fail(function(){ Swal.fire('เกิดข้อผิดพลาด', 'โหลดข้อมูลไม่สำเร็จ', 'error'); });
        }

        // ตัดเวลาออกจาก datetime → 'YYYY-MM-DD' สำหรับ input[type=date]
        function dateOnly(v){
            if (!v) return '';
            return String(v).substring(0,10);
        }

        // ────────────────────────────────────────────────────────
        //  บันทึก (create/update)
        // ────────────────────────────────────────────────────────
        function saveQuotation(){
            var form = document.getElementById('quotationForm');

            // ── ตรวจช่องที่ required ด้วย native validation ──
            if (!form.checkValidity()) { form.reportValidity(); return; }

            var mode = $('#q_mode').val();

            // ── ยืนยันก่อนบันทึก ──
            Swal.fire({
                title: mode === 'update' ? 'ยืนยันการแก้ไข?' : 'ยืนยันการบันทึก?',
                text: 'เลขที่ใบเสนอราคา ' + $('#q_Qno').val().trim(),
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#54BAB9',
            }).then(function(result){
                if (result.isConfirmed) submitQuotation(mode);
            });
        }

        // ส่งข้อมูลจริงหลังผู้ใช้กดยืนยัน (แยกจาก saveQuotation เพื่อคั่นด้วย confirm)
        function submitQuotation(mode){
            var form = document.getElementById('quotationForm');
            var url  = mode === 'update' ? "{{ $page_url }}/update" : "{{ $page_url }}/insert";
            var fd = new FormData(form);
            fd.append('_token', '{{ csrf_token() }}');
            // ตัด field ภายใน (__auto) ออกก่อนส่ง
            var cleanItems = currentItems.map(function(r){
                var o = {}; Object.keys(r).forEach(function(k){ if (k !== '__auto') o[k] = r[k]; }); return o;
            });
            fd.append('items_json', JSON.stringify(cleanItems));   // ข้อมูลรายการ (server จะ derive คอลัมน์ที่แสดงเอง)

            $.ajax({
                url: url, type: 'POST', data: fd, contentType: false, processData: false,
                success: function(res){
                    if (res.ok){
                        bootstrap.Modal.getInstance(document.getElementById('quotationModal')).hide();
                        Swal.fire('บันทึกเรียบร้อย', 'เลขที่ ' + res.qno, 'success');
                        loadData(page);
                    } else {
                        Swal.fire('ไม่สำเร็จ', res.error || 'ไม่ทราบสาเหตุ', 'error');
                    }
                },
                error: function(xhr){
                    var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'เกิดข้อผิดพลาด';
                    Swal.fire('ไม่สำเร็จ', msg, 'error');
                }
            });
        }

        // ────────────────────────────────────────────────────────
        //  ดูรายละเอียด / พิมพ์
        // ────────────────────────────────────────────────────────
        function quotationView(qno){
            var el = document.getElementById('quotationViewModal');
            $('#quotationViewBody').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>กำลังโหลด...</div>');
            new bootstrap.Modal(el).show();
            $.get("{{ $page_url }}/show", {qno: qno}, function(html){
                $('#quotationViewBody').html(html);
            }).fail(function(){
                $('#quotationViewBody').html('<div class="text-center py-5 text-danger">ไม่พบข้อมูล</div>');
            });
        }

        // ขั้นแรก: เปิดรายชื่อลูกค้าทั้งหมดที่มีใบเสนอราคา
        function quotationCustomers(){
            var el = document.getElementById('quotationCustomersModal');
            $('#quotationCustomersBody').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>กำลังโหลด...</div>');
            new bootstrap.Modal(el).show();
            $.get("{{ $page_url }}/customers", function(html){
                $('#quotationCustomersBody').html(html);
            }).fail(function(){
                $('#quotationCustomersBody').html('<div class="text-center py-5 text-danger">โหลดรายชื่อลูกค้าไม่สำเร็จ</div>');
            });
        }

        // ขั้นสอง: เปิดประวัติใบเสนอราคาทั้งหมดของลูกค้า (จับคู่ด้วย Custid)
        function quotationHistory(custid){
            var el = document.getElementById('quotationHistoryModal');
            $('#quotationHistoryBody').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>กำลังโหลด...</div>');
            new bootstrap.Modal(el).show();
            $.get("{{ $page_url }}/history", {custid: custid}, function(html){
                $('#quotationHistoryBody').html(html);
            }).fail(function(){
                $('#quotationHistoryBody').html('<div class="text-center py-5 text-danger">โหลดประวัติไม่สำเร็จ</div>');
            });
        }

        // รองรับ modal ซ้อน (ประวัติ → ดูรายละเอียด) — ยก z-index ตัวบนสุด + คง scroll-lock
        $(document).on('show.bs.modal', '.modal', function () {
            var zIndex = 1056 + (10 * $('.modal.show').length);
            var self = this;
            $(self).css('z-index', zIndex);
            setTimeout(function () {
                $('.modal-backdrop').not('.modal-stack').last()
                    .css('z-index', zIndex - 1).addClass('modal-stack');
            }, 0);
        });
        $(document).on('hidden.bs.modal', '.modal', function () {
            if ($('.modal.show').length) { $('body').addClass('modal-open'); }
        });

        function quotationPrint(qno){
            // พิมพ์ในหน้าเดิมผ่าน iframe ซ่อน (ไม่เปิดแท็บ/หน้าต่างใหม่)
            var frame = document.getElementById('printFrame');
            if (!frame){
                frame = document.createElement('iframe');
                frame.id = 'printFrame';
                frame.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
                document.body.appendChild(frame);
            }
            frame.onload = function(){
                try { frame.contentWindow.focus(); frame.contentWindow.print(); }
                catch (e) { console.error(e); }
            };
            frame.src = "{{ $page_url }}/print?qno=" + encodeURIComponent(qno);
        }

        // ── พิมพ์ตัวอย่างจากฟอร์มที่ยังไม่บันทึก ──
        // fetch POST ข้อมูลฟอร์ม (+ items_json) → รับ HTML → ยัดเข้า iframe ซ่อนด้วย srcdoc แล้วสั่งพิมพ์
        // ไม่เปิดหน้า/แท็บใหม่, ไม่บันทึก DB, ไม่บังคับกรอกครบ — ดูตัวอย่างได้ทันที
        function quotationPrintPreview(){
            var fd = new FormData(document.getElementById('quotationForm'));   // รวม other_notes[] ครบ
            fd.append('_token', '{{ csrf_token() }}');
            // รายการสินค้า — ส่งเป็น items_json เหมือนตอนบันทึก (ตัด __auto ออก)
            var cleanItems = currentItems.map(function(r){
                var o = {}; Object.keys(r).forEach(function(k){ if (k !== '__auto') o[k] = r[k]; }); return o;
            });
            fd.append('items_json', JSON.stringify(cleanItems));

            fetch("{{ $page_url }}/print-preview", {
                method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(res){ return res.text(); })
            .then(function(html){
                // iframe แยกสำหรับพิมพ์ตัวอย่าง (ไม่ชนกับ printFrame ของปุ่มพิมพ์ในตาราง)
                var frame = document.getElementById('printPreviewFrame');
                if (!frame){
                    frame = document.createElement('iframe');
                    frame.id = 'printPreviewFrame';
                    frame.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
                    document.body.appendChild(frame);
                }
                frame.onload = function(){
                    try { frame.contentWindow.focus(); frame.contentWindow.print(); }
                    catch (e) { console.error(e); }
                };
                frame.srcdoc = html;   // โหลดในหน้าเดิม → ไม่เปิดแท็บใหม่
            })
            .catch(function(e){
                console.error(e);
                Swal.fire('พิมพ์ตัวอย่างไม่สำเร็จ', 'โหลดตัวอย่างไม่สำเร็จ', 'error');
            });
        }

        // ────────────────────────────────────────────────────────
        //  DataTable filter machinery (คงเดิม)
        // ────────────────────────────────────────────────────────
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
            $('.p_search:not([name="limit"]):not([name="sort_col"]):not([name="sort_dir"])').each(function(){
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
            // ไม่ล้าง limit + sort_col/sort_dir (เป็นการตั้งค่าแสดงผล ไม่ใช่ตัวกรอง)
            $('.p_search:not([name="limit"]):not([name="sort_col"]):not([name="sort_dir"])').each(function(){
                // flatpickr: เคลียร์ผ่าน instance ให้ปฏิทิน + ช่องว่างจริง
                if (this._flatpickr) this._flatpickr.clear();
                else $(this).val('');
            });
            refreshPickers('.card-header');   // selectpicker ของตัวกรอง — วาดปุ่มใหม่หลังล้างค่า
            loadData("{{$page_url}}/datatable");
        }
</script>
</body>

</html>
