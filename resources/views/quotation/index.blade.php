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
                        {{-- แถวบน: เลขที่ / วันที่ / Revise Date --}}
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">เลขที่ใบเสนอราคา <span class="text-danger">*</span></label>
                                <input type="text" name="Qno" id="q_Qno" class="form-control" maxlength="10" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">วันที่เสนอราคา <span class="text-danger">*</span></label>
                                <input type="date" name="Qdate" id="q_Qdate" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-danger">Revise Date</label>
                                <input type="date" name="Revisedate" id="q_Revisedate" class="form-control">
                            </div>
                        </div>
                        {{-- แถวล่าง: ชนิดสินค้า / พร้อมตัวอย่าง --}}
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">ชนิดสินค้า</label>
                                <select name="PDtype" id="q_PDtype" class="form-select">
                                    @foreach ($pdtypes as $pt)
                                        <option value="{{ $pt->PDType }}">{{ $pt->PDType }} — {{ $pt->PDHead1 }}</option>
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
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="alert alert-info py-2 px-3 mb-0 small">
                                <i class="ti ti-info-circle me-1"></i>กรอกเฉพาะคอลัมน์ที่ต้องการ — คอลัมน์ที่ปล่อยว่างทั้งหมดจะไม่แสดงในใบเสนอราคา
                                (หัวจดหมาย เสนอราคา/ปรับราคา ระบบตั้งให้อัตโนมัติ)
                            </div>
                            <button type="button" class="btn btn-label-warning" onclick="addQuotationItem()">
                                <i class="ti ti-plus me-1"></i>เพิ่มรายการ
                            </button>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-bordered align-middle mb-0" id="quotationItemsTable">
                                <thead class="table-light"><tr id="quotationItemsHead"></tr></thead>
                                <tbody id="quotationItems"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ── Section 4: เงื่อนไข ── --}}
                    <div class="qf-sec">
                        <div class="qf-sec-title"><i class="ti ti-adjustments-horizontal"></i>เงื่อนไข</div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">ควรกำหนดยอดซื้อขั้นต่ำ (ก.ก.)</label>
                                <input type="text" name="Qremark" id="q_Qremark" class="form-control" maxlength="50">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Payment Term</label>
                                <input type="text" name="Term" id="q_Term" class="form-control" maxlength="20">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ยืนราคาถึงวันที่</label>
                                <input type="date" name="Validto" id="q_Validto" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ส่งสินค้าได้ภายใน <span class="text-muted fw-normal">(วัน)</span></label>
                                <input type="text" name="LeadTime" id="q_LeadTime" class="form-control" maxlength="3">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
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

    <!-- / Layout wrapper -->
    @include('layout/inc_js')
<script>
    var page = "{{$page_url}}/datatable";
        var searchData = {};
        // ต้องประกาศก่อน loadData ครั้งแรก — เลี่ยง hoisting ทำให้ dtSeq เป็น NaN (spinner ค้าง)
        var dtXhr = null, dtSeq = 0;
        loadData(page);

        // ────────────────────────────────────────────────────────
        //  รายการสินค้า — คอลัมน์กำหนดเองได้ (config-driven)
        // ────────────────────────────────────────────────────────
        var COL_REG = @json($colRegistry);   // คลังคอลัมน์ทั้งหมด {key:{label,num,suffix}}
        // แสดงทุกคอลัมน์เสมอ — คอลัมน์ที่ไม่มีใครกรอกจะถูกตัดออกตอนบันทึก (ฝั่ง server)
        var ALL_COLS = Object.keys(COL_REG).map(function(k){
            return {key: k, label: COL_REG[k].label, num: !!COL_REG[k].num};
        });
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

        // ── ตารางกรอกรายการ — โชว์ทุกคอลัมน์ ──
        function renderItems(){
            var head = '';
            ALL_COLS.forEach(function(c){
                var rc = revClasses(c.key);
                head += '<th class="text-center'+(rc?' '+rc:'')+'">'+esc(c.label)+'</th>';
            });
            head += '<th width="46" class="text-center">ลบ</th>';
            $('#quotationItemsHead').html(head);

            var body = '';
            if (!currentItems.length){
                body = '<tr><td colspan="'+(ALL_COLS.length+1)+'" class="text-center py-3">ยังไม่มีรายการ — กด “เพิ่มรายการ”</td></tr>';
            } else {
                currentItems.forEach(function(row, idx){
                    body += '<tr>';
                    ALL_COLS.forEach(function(c){
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
                        if (res.term && !$('#q_Term').val()) $('#q_Term').val(res.term);
                    } else {
                        // ไม่เจอ → เคลียร์ชื่อไทย + อังกฤษ เงียบๆ (ไม่เด้ง popup)
                        $('#q_CustName').val('');
                        $('#q_Engname').val('');
                    }
                });
            }, 350);
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
            // วันที่เสนอราคา = วันนี้
            $('#q_Qdate').val(new Date().toISOString().slice(0,10));
            setItems();
            // ขอเลขที่ถัดไป (แก้ไขได้)
            $.getJSON("{{ $page_url }}/next-qno", {prefix:'WH'}, function(res){
                if (res.qno) $('#q_Qno').val(res.qno);
            });
            new bootstrap.Modal('#quotationModal').show();
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
                $('#q_Qdate').val(dateOnly(h.Qdate));
                $('#q_Revisedate').val(dateOnly(h.Revisedate));
                $('#q_Validto').val(dateOnly(h.Validto));
                $('#q_PDtype').val(h.PDtype);
                $('#q_exam').prop('checked', h.exam == 1);
                $('#q_EmpID').val(h.EmpID);
                $('#q_Custid').val(h.Custid);
                // ชื่อไทยใช้จาก customer (join) ถ้ามี ไม่งั้น fallback CustName เดิม
                $('#q_CustName').val((res.cust && res.cust.name) ? res.cust.name : (h.CustName || ''));
                $('#q_Engname').val(h.Engname || (res.cust ? res.cust.nameEN : '') || '');
                $('#q_Qremark').val(h.Qremark);
                $('#q_Term').val(h.Term);
                $('#q_LeadTime').val(h.LeadTime);
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
</script>
</body>

</html>
