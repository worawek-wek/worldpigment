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
        $('#o_netqty').val(o.netqty != null ? o.netqty : '');

        // ประเภทอุตสาหกรรมของลูกค้า (ปุ่ม itype เดิม)
        $('#o_itype').val(res.customer && res.customer.type
            ? res.customer.type + ' — ' + (res.customer.type_name || '')
            : '');

        // สถานที่ส่ง — รายการของลูกค้ารายนี้
        fillDvpoints(res.dvpoints, o.DVpoint);

        // กรณีสั่งทำสต๊อก
        setFp('o_sendend', o.sendend);
        $('#o_SendCust').val(o.SendCust != null ? o.SendCust : '');
        $('#o_HMStore').val(o.HMStore != null ? o.HMStore : '');
        $('#o_sendmth').val(o.sendmth != null ? o.sendmth : '');

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
        $('#o_min_price').val(fmtNum(p.price2, 2));   // ฟอร์มเดิมโชว์เท่ากับราคาช่อง 2
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
