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
                    <label class="form-label small fw-medium mb-1">วันที่เริ่มซื้อ</label>
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
                ['CustNo', 'st_code', 'ITEMNO', 'DATE', 'PRICE', 'REM1', 'REM2', 'PackRem', 'Label', 'Author']
                    .forEach(function (name) {
                        $('#form_saleinfo [name="' + name + '"]').val(d[name] ?? '');
                    });
                // NoAcp ปิดไว้ก่อน — ช่องในฟอร์มถูกคอมเมนต์ไว้ รอลูกค้ายืนยันความหมาย
                // $('#saleinfo_noacp').prop('checked', Number(d.NoAcp) === 1);

                $('#btn_delete_saleinfo').removeClass('d-none').data('id', id);
                lookupCustomer(d.CustNo);
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
