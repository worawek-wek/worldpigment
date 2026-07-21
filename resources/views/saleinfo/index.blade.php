<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">

<head>
    @include('layout/inc_header')
    <title>กำหนดราคา | World Pigment</title>
</head>

<style>
/* ─── หัว modal ทรงเฉียง (ใช้ธีมเดียวกับหน้าอื่น แต่เป็นโทนส้ม/ทอง = กำหนดราคา) ─── */
.modalHeadDecor .modal-header {
    padding: 0;
}
.modalHeadDecor .modal-title {
    padding: 1.25rem 1.5rem 1.25rem;
    color: white;
    background-color: #E08A1E;
    position: relative;
}
.modalHeadDecor .modal-title::after {
    position: absolute;
    top: 0;
    right: -65px;
    content: '';
    width: 0;
    height: 0;
    border-top: 65px solid #E08A1E;
    border-right: 65px solid transparent;
}

/* ─── ปุ่มธีมกำหนดราคา ─── */
.btn-theme-saleinfo {
    background-color: #E08A1E;
    border-color: #E08A1E;
    color: #fff;
}
.btn-theme-saleinfo:hover,
.btn-theme-saleinfo:focus,
.btn-theme-saleinfo:active {
    background-color: #c4760f;
    border-color: #c4760f;
    color: #fff;
}

/* ─── หัวตารางสีเข้ม (ให้เหมือนหน้าเทียบสี) ─── */
#table-data thead.pr-thead-dark th {
    background-color: #6e6e78;
    color: #fff;
    border-color: #6e6e78;
    font-weight: 600;
    letter-spacing: .4px;
    vertical-align: middle;
}
#table-data thead.pr-thead-dark th small {
    color: rgba(255, 255, 255, .65) !important;
    letter-spacing: 0;
}

/* ─── ตารางประวัติการปรับราคา (ใน modal) — โทนส้ม/ทองให้เข้าธีมกำหนดราคา ─── */
.saleinfo-history {
    border: 1px solid #f0dfc0;
}
/* คอลัมน์กว้างเท่ากันทั้ง 6 ช่อง */
.saleinfo-history table {
    table-layout: fixed;
}
.saleinfo-history th,
.saleinfo-history td {
    width: 16.66%;
    word-break: break-word;
}
.saleinfo-history .card-header {
    background-color: #fdf3e3;
    border-bottom: 2px solid #E08A1E;
    border-radius: 0.375rem 0.375rem 0 0;
}
.saleinfo-history thead th {
    background-color: #f6ead3;
    color: #7a4d05;
    font-size: 0.8rem;
    font-weight: 600;
    line-height: 1.2;
    border-bottom: 1px solid #ecd9b4;
    white-space: nowrap;
    vertical-align: middle;
}
.saleinfo-history tbody td {
    font-size: 0.85rem;
    border-color: #f1e6d2;
}
.saleinfo-history tbody tr:nth-child(even) {
    background-color: #fffaf1;
}
/* แถวล่าสุด (บนสุด) = ราคาปัจจุบัน — เน้นให้เห็นชัด */
.saleinfo-history tbody tr:first-child {
    background-color: #fdf3e3;
}
.saleinfo-history tbody tr:first-child td {
    font-weight: 600;
    color: #7a4d05;
}
.saleinfo-history .badge-current {
    background-color: #E08A1E;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 600;
    padding: 0.15rem 0.45rem;
    border-radius: 0.7rem;
    margin-left: 0.35rem;
}

/* ─── ราคาตามขนาดบรรจุ (DB) + ค่าสี — โทนส้ม/ทองให้เข้าธีม ─── */
.saleinfo-dbprice {
    border: 1px solid #f0dfc0;
}
.saleinfo-dbprice .card-header {
    background-color: #fdf3e3;
    border-bottom: 2px solid #E08A1E;
    border-radius: 0.375rem 0.375rem 0 0;
}
/* layout ทั้ง section: 4 คอลัมน์ = ราคา 3 ช่อง/แถว + กล่องค่าสี span 2 แถวขวาสุด
   ใช้ minmax(0,1fr) ให้คอลัมน์หดได้ (กัน input/ข้อความดันคอลัมน์จนล้นออกนอกกรอบ) */
.saleinfo-dbprice .dbprice-layout {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr)) minmax(170px, 1fr);
    gap: 0.75rem;
    align-items: stretch;
}
.saleinfo-dbprice .dbprice-layout > * {
    min-width: 0;
}
.saleinfo-dbprice .dbprice-layout .dbprice-colorbox {
    grid-column: 4;
    grid-row: 1 / span 2;
}
/* จอแคบ (< lg): เหลือ 2 คอลัมน์ กล่องค่าสีลงเต็มแถวล่าง */
@media (max-width: 991.98px) {
    .saleinfo-dbprice .dbprice-layout {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .saleinfo-dbprice .dbprice-layout .dbprice-colorbox {
        grid-column: 1 / -1;
        grid-row: auto;
    }
}
/* ชั้นราคาแต่ละขนาดบรรจุ */
.saleinfo-dbprice .dbprice-tier {
    border: 1px solid #f0dfc0;
    border-radius: 0.5rem;
    padding: 0.75rem 0.85rem;
    background-color: #fffdf9;
    height: 100%;
}
.saleinfo-dbprice .dbprice-tier-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #7a4d05;
    margin-bottom: 0.5rem;
    white-space: nowrap;
}
.saleinfo-dbprice .dbprice-tier-size {
    display: inline-block;
    font-size: 0.68rem;
    font-weight: 500;
    color: #b26a09;
    background-color: #fbeacd;
    border-radius: 0.6rem;
    padding: 0.05rem 0.5rem;
    margin-left: 0.15rem;
}
.saleinfo-dbprice .dbprice-value {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px solid #e6d7bb;
    border-radius: 0.4rem;
    background-color: #fff;
    padding: 0 0.6rem;
}
.saleinfo-dbprice .dbprice-unit {
    color: #b26a09;
    font-weight: 600;
}
.saleinfo-dbprice .dbprice-input {
    flex: 1;
    min-width: 0;
    width: 100%;
    border: 0;
    outline: 0;
    background: transparent;
    text-align: right;
    font-weight: 600;
    font-size: 1.05rem;
    color: #55350a;
    padding: 0.4rem 0;
    font-variant-numeric: tabular-nums;
}
.saleinfo-dbprice .dbprice-input::placeholder {
    color: #cbb48c;
    font-weight: 400;
}
/* กล่องค่าสี — 2 ช่อง (ค่าสีทั้งสิ้น / % สี) */
.saleinfo-dbprice .dbprice-colorbox {
    border: 1px solid #f0dfc0;
    border-radius: 0.5rem;
    background-color: #fbf1e0;
    overflow: hidden;
    display: grid;
    grid-template-columns: 1fr 1fr;
    text-align: center;
}
.saleinfo-dbprice .dbprice-colorcell {
    padding: 0.6rem 0.5rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.saleinfo-dbprice .dbprice-colorcell + .dbprice-colorcell {
    border-left: 1px dashed #e6d3b0;
}
.saleinfo-dbprice .dbprice-colorlabel {
    font-size: 0.75rem;
    color: #b26a09;
    margin-bottom: 0.2rem;
}
.saleinfo-dbprice .dbprice-colorval {
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1.1;
    color: #55350a;
    font-variant-numeric: tabular-nums;
}
</style>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            @include('layout/inc_sidemenu')

            <div class="layout-page">

                @include('layout/inc_topmenu')

                <!-- Content wrapper -->
                <div class="content-wrapper">

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">

                    <div>
                        <h3 class="mb-1">
                            <i class="ti ti-tag text-primary"></i>
                            กำหนดราคา
                        </h3>
                        <p class="text-muted mb-0">
                            กำหนดราคาสินค้าแยกตามลูกค้า (ราคา วันที่เริ่มซื้อ หมายเหตุ ฉลาก)
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-label-secondary"
                            data-bs-toggle="modal"
                            data-bs-target="#testPriceModal">
                            <i class="ti ti-flask me-1"></i>
                            Test Price
                        </button>
                        <button class="btn btn-label-secondary"
                            data-bs-toggle="modal"
                            data-bs-target="#newPriceModal">
                            <i class="ti ti-search me-1"></i>
                            ค้นหาราคาสินค้า
                        </button>
                        <button class="btn btn-theme-saleinfo"
                            data-bs-toggle="modal"
                            data-bs-target="#saleinfoModal">
                            <i class="ti ti-plus me-1"></i>
                            กำหนดราคาใหม่
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">

        <div class="card-header border-bottom">

            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small fw-medium mb-1">
                        ค้นหา <span class="text-muted fw-normal">(รหัสลูกค้า / ชื่อลูกค้า / ชื่อสินค้า / รหัสสินค้า)</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" name="search" class="form-control p_search" oninput="loadData(page)">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-medium mb-1">วันที่เริ่มราคาใหม่</label>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small fw-medium">ตั้งแต่</span>
                        <input type="text" name="date_from" class="form-control flatpickr-date p_search"
                            placeholder="วว/ดด/ปปปป">
                        <span class="small fw-medium">ถึง</span>
                        <input type="text" name="date_to" class="form-control flatpickr-date p_search"
                            placeholder="วว/ดด/ปปปป">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <button type="button" id="btnResetFilters" class="btn btn-label-secondary" onclick="resetFilters()">
                    <i class="ti ti-x me-1"></i>ล้างตัวกรอง
                </button>
                <div class="d-flex align-items-center">
                    <label class="form-label small fw-medium mb-0 me-2">แสดง</label>
                    <select name="limit" class="form-select form-select-sm p_search"
                        style="width: 90px;" onchange='loadData("{{ $page_url }}/datatable")'>
                        <option value="15">15</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="ms-2 small fw-medium">รายการ/หน้า</span>
                </div>
            </div>
        </div>

        <div id="table-data">
            {{-- ตารางโหลดผ่าน AJAX จาก saleinfo/datatable --}}
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm me-2"></div>
                กำลังโหลดข้อมูล...
            </div>
        </div>

    </div>

</div>

                    @include('layout/inc_footer')

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- / Content wrapper -->
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    @include('saleinfo.modal-price')
    @include('saleinfo.modal-newprice')
    @include('saleinfo.modal-testprice')

    @include('layout/inc_js')

<script>
    var page = "{{ $page_url }}/datatable";
    var searchData = {};
    var dtXhr = null;
    var dtSeq = 0;

    loadData(page);

    $(function () {
        flatpickr('.flatpickr-date', {
            dateFormat: 'd/m/Y',
            allowInput: true,
            static: true,
            disableMobile: true,
            onChange: function (_, _str, instance) {
                if (instance.input.classList.contains('p_search')) {
                    loadData(page);
                }
            }
        });
        $('.flatpickr-date').attr('autocomplete', 'off');
    });

    // เก็บค่า filter ทั้งหมดใน UI เป็น object
    function collectSearchData() {
        var data = {};
        $('.p_search').each(function () {
            var name  = $(this).attr('name');
            var value = $(this).val();
            if (value !== '' && value !== null) {
                data[name] = value;
            }
        });
        return data;
    }

    // กัน AJAX race: ยกเลิก request เก่า + รับเฉพาะผลของ request ล่าสุด
    function loadData(pages) {
        searchData = collectSearchData();
        page = pages;

        var seq = ++dtSeq;
        if (dtXhr) dtXhr.abort();

        dtXhr = $.ajax({
            type: "GET",
            url: pages,
            data: searchData,
            success: function (data) {
                if (seq !== dtSeq) return;
                $("#table-data").html(data);
            },
            complete: function () {
                if (seq === dtSeq) dtXhr = null;
            }
        });
    }

    function resetFilters() {
        $('.p_search:not([name="limit"])').each(function () {
            if ($(this).hasClass('flatpickr-date')) {
                const fp = this._flatpickr;
                if (fp) fp.clear();
                else    $(this).val('');
                return;
            }
            $(this).val('');
        });
        loadData("{{ $page_url }}/datatable");
    }

    // ────────────────────────────────────────────────────────
    //  ฟอร์มกำหนดราคา
    // ────────────────────────────────────────────────────────
    let saleinfoEditing = false;

    $('#saleinfoModal').on('show.bs.modal', function () {
        if (saleinfoEditing) return;   // โหมดแก้ไข → ห้าม reset ทับข้อมูลที่เติมไว้
        resetSaleinfoForm();
    });
    $('#saleinfoModal').on('hidden.bs.modal', function () { saleinfoEditing = false; });

    function resetSaleinfoForm() {
        $('#form_saleinfo')[0].reset();
        $('#form_saleinfo [name="_mode"]').val('create');
        $('#form_saleinfo [name="_pk"]').val('');
        $('#btn_delete_saleinfo').addClass('d-none').removeData('id');
        setCustomerPanel(null);
        clearSaleinfoHistory();
    }

    // ────────────────────────────────────────────────────────
    //  ประวัติการปรับราคา — โชว์เมื่อรหัสลูกค้า + รหัสสินค้า ตรงกับที่เคยบันทึกไว้
    // ────────────────────────────────────────────────────────
    let saleinfoHistXhr = null;

    function clearSaleinfoHistory() {
        if (saleinfoHistXhr) { saleinfoHistXhr.abort(); saleinfoHistXhr = null; }
        $('#saleinfo_history_card').addClass('d-none');
        $('#saleinfo_history_body').empty();
        $('#saleinfo_history_count').text('0 รายการ');
    }

    function fmtNum(v) {
        if (v === null || v === '' || v === undefined) return '—';
        const n = Number(v);
        return isNaN(n) ? v : n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function esc(s) {
        return $('<div>').text(s ?? '').html();
    }

    // อ่านรหัสลูกค้า + รหัสสินค้าจากฟอร์ม → ดึงประวัติมาแสดง
    // (excludeId = id ของแถวที่กำลังแก้ไข จะไม่เอามาโชว์ซ้ำ)
    function loadSaleinfoHistory(excludeId) {
        const custno = ($('#form_saleinfo [name="CustNo"]').val() || '').trim();
        const itemno = ($('#form_saleinfo [name="ITEMNO"]').val() || '').trim();

        if (!custno || !itemno) {
            clearSaleinfoHistory();
            return;
        }

        if (saleinfoHistXhr) saleinfoHistXhr.abort();
        saleinfoHistXhr = $.ajax({
            type: "GET",
            url: "{{ $page_url }}/history",
            data: { custno: custno, itemno: itemno, exclude_id: excludeId || '' },
            success: function (res) {
                const rows = res.rows || [];
                if (!rows.length) {
                    clearSaleinfoHistory();
                    return;
                }

                const html = rows.map(function (r, i) {
                    // แถวบนสุด = ปรับล่าสุด = ราคาที่ใช้อยู่ปัจจุบัน
                    const badge = (i === 0) ? '<span class="badge-current">ปัจจุบัน</span>' : '';
                    return '<tr>'
                        + '<td class="text-center">' + esc(r.NotifyDate || '—') + '</td>'
                        + '<td class="text-center">' + esc(r.DATE || '—') + '</td>'
                        + '<td>' + esc(r.ITEMNO || '—') + '</td>'
                        + '<td class="text-end">' + fmtNum(r.MOQ) + '</td>'
                        + '<td class="text-end">' + fmtNum(r.PRICE) + badge + '</td>'
                        + '<td class="text-muted">' + esc(r.REM1 || '—') + '</td>'
                        + '</tr>';
                }).join('');

                $('#saleinfo_history_body').html(html);
                $('#saleinfo_history_count').text(rows.length + ' รายการ');
                $('#saleinfo_history_card').removeClass('d-none');
            },
            complete: function () { saleinfoHistXhr = null; }
        });
    }

    // แถบข้อมูลลูกค้า (อ่านอย่างเดียว) ในฟอร์ม
    function setCustomerPanel(cust) {
        $('#saleinfo_cust_code').text(cust?.code ?? '—');
        $('#saleinfo_cust_name').text(cust?.name ?? '—');
        $('#saleinfo_cust_road').text(cust?.road ?? '—');
        $('#saleinfo_cust_term').text(cust?.term ?? '—');
    }

    // กรอกรหัสลูกค้าเสร็จ → ดึงชื่อ/ที่อยู่/เงื่อนไข มาเติมแถบขวา
    let custLookupXhr = null;
    function lookupCustomer(code) {
        code = (code || '').trim();
        if (!code) {
            setCustomerPanel(null);
            return;
        }

        if (custLookupXhr) custLookupXhr.abort();
        custLookupXhr = $.ajax({
            type: "GET",
            url: "{{ $page_url }}/customer/" + encodeURIComponent(code),
            success: function (res) {
                setCustomerPanel(res.found ? res : null);
            },
            complete: function () { custLookupXhr = null; }
        });
    }

    $('#form_saleinfo [name="CustNo"]').on('change', function () {
        lookupCustomer($(this).val());
        loadSaleinfoHistory($('#form_saleinfo [name="_pk"]').val());
    });

    // รหัสสินค้าเปลี่ยน → โหลดประวัติของคู่ลูกค้า/สินค้านี้
    $('#form_saleinfo [name="ITEMNO"]').on('change', function () {
        loadSaleinfoHistory($('#form_saleinfo [name="_pk"]').val());
    });

    // แก้ไขราคา — ดึงข้อมูลจริงมาเติมฟอร์มแล้วเปิด modal โหมดแก้ไข
    function viewSaleinfo(id) {
        $.ajax({
            type: "GET",
            url: "{{ $page_url }}/edit/" + id,
            success: function (res) {
                if (!res.found) return;

                resetSaleinfoForm();
                saleinfoEditing = true;

                const d = res.data;
                $('#form_saleinfo [name="_mode"]').val('edit');
                $('#form_saleinfo [name="_pk"]').val(id);

                // เติมทุกช่องที่ชื่อตรงกับคอลัมน์ (ยกเว้น checkbox จัดการแยก)
                ['CustNo', 'st_code', 'ITEMNO', 'DATE', 'NotifyDate', 'MOQ', 'PRICE', 'REM1', 'PackRem', 'Label', 'Author']
                    .forEach(function (name) {
                        $('#form_saleinfo [name="' + name + '"]').val(d[name] ?? '');
                    });
                // NoAcp ปิดไว้ก่อน — ช่องในฟอร์มถูกคอมเมนต์ไว้ รอลูกค้ายืนยันความหมาย
                // $('#saleinfo_noacp').prop('checked', Number(d.NoAcp) === 1);

                $('#btn_delete_saleinfo').removeClass('d-none').data('id', id);
                lookupCustomer(d.CustNo);
                // แก้ไขอยู่ → โชว์ประวัติของคู่นี้ แต่ไม่รวมแถวที่กำลังแก้เอง
                loadSaleinfoHistory(id);
                $('#saleinfoModal').modal('show');
            },
            error: function () {
                Swal.fire({ title: 'ไม่พบข้อมูล', text: 'รายการนี้อาจถูกลบไปแล้ว', icon: 'error', heightAuto: false });
            }
        });
    }

    $('#btn_delete_saleinfo').on('click', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'ยืนยันการลบ',
            text: 'ต้องการลบราคานี้ใช่หรือไม่? ลบแล้วกู้คืนไม่ได้',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false,
            heightAuto: false
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                type: "POST",
                url: "{{ $page_url }}/delete",
                data: { _token: "{{ csrf_token() }}", id: id },
                success: function () {
                    $('#saleinfoModal').modal('hide');
                    loadData(page);
                    Swal.fire({ title: 'ลบแล้ว', icon: 'success', timer: 1200, showConfirmButton: false, heightAuto: false });
                },
                error: function (xhr) {
                    Swal.fire({
                        title: 'ลบไม่สำเร็จ',
                        text: xhr.responseJSON?.error ?? 'เกิดข้อผิดพลาด',
                        icon: 'error',
                        heightAuto: false
                    });
                }
            });
        });
    });

    $('#form_saleinfo').on('submit', function (e) {
        e.preventDefault();

        const isEdit = $('#form_saleinfo [name="_mode"]').val() === 'edit';
        const url    = "{{ $page_url }}/" + (isEdit ? 'update' : 'insert');

        const formData = $(this).serializeArray();
        if (isEdit) {
            formData.push({ name: 'id', value: $('#form_saleinfo [name="_pk"]').val() });
        }

        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true);

        $.ajax({
            type: "POST",
            url: url,
            data: $.param(formData),
            success: function () {
                $('#saleinfoModal').modal('hide');
                loadData(page);
                Swal.fire({
                    title: isEdit ? 'แก้ไขราคาแล้ว' : 'บันทึกราคาแล้ว',
                    icon: 'success',
                    timer: 1200,
                    showConfirmButton: false,
                    heightAuto: false
                });
            },
            error: function (xhr) {
                Swal.fire({
                    title: 'บันทึกไม่สำเร็จ',
                    text: xhr.responseJSON?.error ?? 'เกิดข้อผิดพลาด',
                    icon: 'error',
                    heightAuto: false
                });
            },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    // ────────────────────────────────────────────────────────
    //  ค้นหาราคาสินค้า (modal "New Price") — อ่านอย่างเดียว ไม่มีบันทึก
    //  วางรหัสสินค้า → ราคาขึ้นเอง (ไม่ต้องกดปุ่ม)
    //  ⚠ ตัวเลขยังไม่ต่อ DB — ยังไม่รู้ที่มา/สูตรของราคาขาย 1/2/3 (รอลูกค้า)
    // ────────────────────────────────────────────────────────
    const NP_FIELDS = ['#np_price_1', '#np_price_2', '#np_price_3', '#np_db_3_4', '#np_db_1_2'];
    let npTimer = null;

    function clearNewPrice() {
        NP_FIELDS.forEach(function (sel) { $(sel).val(''); });
    }

    // TODO: ต่อ endpoint จริง (saleinfo/price-lookup?code=...) แล้วเติมราคาลง NP_FIELDS
    //       ตอนนี้แค่โชว์ว่ายังไม่มีข้อมูล เพื่อไม่ให้เข้าใจผิดว่าราคาเป็น 0
    function lookupNewPrice(code) {
        code = (code || '').trim();
        if (!code) {
            clearNewPrice();
            return;
        }
        NP_FIELDS.forEach(function (sel) { $(sel).val('รอต่อข้อมูล'); });
    }

    // หน่วง 400ms กันยิงรัวตอนพิมพ์ (วาง/พิมพ์ทีละตัวก็ทำงานเหมือนกัน)
    $('#np_code').on('input', function () {
        const code = $(this).val();
        clearTimeout(npTimer);
        npTimer = setTimeout(function () { lookupNewPrice(code); }, 400);
    });

    // ฟอร์มนี้ไม่มี submit — กัน Enter รีโหลดหน้า
    $('#np_code').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(npTimer);
            lookupNewPrice($(this).val());
        }
    });

    $('#np_clear').on('click', function () {
        $('#np_code').val('').focus();
        clearNewPrice();
    });

    // เปิด modal ทีไร = เริ่มใหม่ + โฟกัสช่องรหัสให้วางได้เลย
    $('#newPriceModal').on('show.bs.modal', function () {
        $('#np_code').val('');
        clearNewPrice();
    });
    $('#newPriceModal').on('shown.bs.modal', function () {
        $('#np_code').focus();
    });

    // ────────────────────────────────────────────────────────
    //  Test Price — อ่านอย่างเดียว ไม่มีบันทึก
    //  กรอกได้แค่ Customer / Test No. / Lot Test → ที่เหลือขึ้นเอง
    //  ⚠ ยังไม่ต่อ DB — ยังไม่รู้ว่าจอนี้ดึงจากตารางไหน (รอลูกค้า)
    // ────────────────────────────────────────────────────────
    const TP_KEYS   = ['#tp_customer', '#tp_testno', '#tp_lottest'];   // ช่องที่กรอกได้
    const TP_RESULT = ['#tp_sample', '#tp_resin_cust', '#tp_resin_match', '#tp_quotation',
                       '#tp_price_1', '#tp_price_2', '#tp_price_3', '#tp_db_3_4', '#tp_db_1_2'];
    let tpTimer = null;

    function clearTestPrice() {
        TP_RESULT.forEach(function (sel) { $(sel).val(''); });
        $('#tp_cust_name').text('—');
        $('#tp_setcode').text('—');
    }

    // TODO: ต่อ endpoint จริง (saleinfo/test-price?customer=&testno=&lottest=)
    //       แล้วเติมผลลง TP_RESULT + ชื่อลูกค้า + "ตั้งเบอร์เป็น"
    //       ตอนนี้แค่โชว์ว่ายังไม่มีข้อมูล เพื่อไม่ให้เข้าใจผิดว่าราคาเป็น 0
    function lookupTestPrice() {
        const hasKey = TP_KEYS.some(function (sel) { return $(sel).val().trim() !== ''; });
        if (!hasKey) {
            clearTestPrice();
            return;
        }
        TP_RESULT.forEach(function (sel) { $(sel).val('รอต่อข้อมูล'); });
        $('#tp_cust_name').text('รอต่อข้อมูล');
        $('#tp_setcode').text('รอต่อข้อมูล');
    }

    // หน่วง 400ms กันยิงรัวตอนพิมพ์
    $(TP_KEYS.join(', ')).on('input', function () {
        clearTimeout(tpTimer);
        tpTimer = setTimeout(lookupTestPrice, 400);
    });

    // ฟอร์มนี้ไม่มี submit — กด Enter = ค้นเลย ไม่ใช่รีโหลดหน้า
    $(TP_KEYS.join(', ')).on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(tpTimer);
            lookupTestPrice();
        }
    });

    $('#tp_search').on('click', function () {
        clearTimeout(tpTimer);
        lookupTestPrice();
    });

    // Refresh — ล้างทุกช่องแล้วเริ่มใหม่
    $('#tp_refresh').on('click', function () {
        TP_KEYS.forEach(function (sel) { $(sel).val(''); });
        clearTestPrice();
        $('#tp_customer').focus();
    });

    $('#testPriceModal').on('show.bs.modal', function () {
        TP_KEYS.forEach(function (sel) { $(sel).val(''); });
        clearTestPrice();
    });
    $('#testPriceModal').on('shown.bs.modal', function () {
        $('#tp_customer').focus();
    });
</script>

</body>
</html>
