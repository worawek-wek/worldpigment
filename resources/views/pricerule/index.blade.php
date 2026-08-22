<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">

<head>
    @include('layout/inc_header')
    <title>ตั้งค่าเงื่อนไขราคา | World Pigment</title>
</head>

<style>
/* ─── ปุ่มธีมเดียวกับหน้ากำหนดราคา (โทนส้ม/ทอง) ─── */
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

/* ─── ตารางเงื่อนไข ─── */
#pr_table thead th {
    background-color: #6e6e78;
    color: #fff;
    border-color: #6e6e78;
    font-weight: 600;
    vertical-align: middle;
}
#pr_table input.form-control {
    font-variant-numeric: tabular-nums;
}

/* ─── กล่องอธิบายสูตร ─── */
.pr-formula {
    background-color: #fdf3e3;
    border: 1px solid #f0dfc0;
    border-radius: 0.5rem;
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
                            <i class="ti ti-adjustments-alt text-primary"></i>
                            ตั้งค่าเงื่อนไขราคา
                        </h3>
                        <p class="text-muted mb-0">
                            ตัวคูณ / ตัวหาร / ตัวบวก ที่ใช้คิดราคาขายจากราคาทุน แยกตามกลุ่มรหัสสินค้า
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('saleinfo.index') }}" class="btn btn-label-secondary">
                            <i class="ti ti-tag me-1"></i>
                            ไปหน้ากำหนดราคา
                        </a>
                        <button type="button" class="btn btn-theme-saleinfo" id="pr_save">
                            <i class="ti ti-device-floppy me-1"></i>
                            บันทึก
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- ตารางเงื่อนไข -->
    <div class="card">
        <div class="card-body">

            {{-- ─── คำอธิบายสูตร ─── --}}
            <div class="pr-formula p-3 mb-3 small">
                <div class="fw-semibold mb-1">
                    <i class="ti ti-math-symbols me-1"></i>
                    ราคาขาย 1 = ราคาทุน × คูณ ÷ หาร + บวก
                </div>
                <div class="text-muted">
                    แก้ได้เฉพาะ 3 ช่องนี้ — ส่วนชื่อเงื่อนไขและตัวขึ้นต้น/ลงท้ายของรหัส ต้องให้ผู้ดูแลระบบแก้ให้
                </div>
                <div class="text-muted mt-1" id="pr_tier"></div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0" id="pr_table">
                    <thead>
                        <tr class="text-center">
                            <th style="min-width: 260px;">เงื่อนไข</th>
                            <th style="width: 130px;">× คูณ</th>
                            <th style="width: 130px;">÷ หาร</th>
                            <th style="width: 130px;">บวก +</th>
                        </tr>
                    </thead>
                    <tbody id="pr_tbody">
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">กำลังโหลด…</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="small mt-3 text-danger d-none" id="pr_error" style="white-space: pre-line;"></div>

            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-theme-saleinfo" id="pr_save_bottom">
                    <i class="ti ti-device-floppy me-1"></i>
                    บันทึก
                </button>
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

    @include('layout/inc_js')

<script>
    // ────────────────────────────────────────────────────────
    //  ตั้งค่าเงื่อนไขราคา — ย้ายมาจาก modal ในหน้ากำหนดราคา (21/08/2569)
    //  แก้ได้เฉพาะ ×คูณ ÷หาร บวก+ ของแต่ละเงื่อนไข
    //  ค่าที่แก้เก็บลง tb_price_rule (แถวที่ตรงกับค่าตั้งต้นจะถูกลบ = กลับไปใช้ค่า config)
    // ────────────────────────────────────────────────────────
    // ใช้ url() (path เต็ม) แทน path สั้น — กัน AJAX เพี้ยนเวลาเปิดหน้าด้วย URL ที่มี / ต่อท้าย
    var PAGE_URL = "{{ url($page_url) }}";

    function prNum(v) {
        // ตัดศูนย์ท้ายทศนิยมทิ้งเพื่อให้อ่านง่าย (110.0000 → 110)
        return String(parseFloat(v));
    }

    function prEsc(text) {
        return $('<div>').text(text == null ? '' : text).html();
    }

    function prRenderRows(rows) {
        const $tbody = $('#pr_tbody').empty();

        if (!rows.length) {
            $tbody.append('<tr><td colspan="4" class="text-center text-muted py-4">ไม่มีเงื่อนไข</td></tr>');
            return;
        }

        rows.forEach(function (r) {
            // บรรทัดที่สองบอกวิธีจับคู่ ให้ผู้ใช้รู้ว่าแถวนี้ใช้กับรหัสแบบไหน (อ่านอย่างเดียว)
            let cond = 'ขึ้นต้น ' + r.prefix;
            if (r.suffix) {
                cond += r.suffix_pos
                    ? ' · ตัวที่ ' + r.suffix_pos + ' เป็นต้นไป = ' + r.suffix
                    : ' · ลงท้าย ' + r.suffix;
            }

            const $tr = $(
                '<tr>' +
                    '<td>' +
                        '<div class="fw-semibold small">' + prEsc(r.label) + '</div>' +
                        '<div class="text-muted" style="font-size: 0.75rem;">' + prEsc(cond) + '</div>' +
                    '</td>' +
                    '<td><input type="number" step="any" class="form-control form-control-sm text-end pr_mul"></td>' +
                    '<td><input type="number" step="any" class="form-control form-control-sm text-end pr_div"></td>' +
                    '<td><input type="number" step="any" class="form-control form-control-sm text-end pr_add"></td>' +
                '</tr>'
            );

            $tr.attr('data-key', r.key);

            $tr.find('.pr_mul').val(prNum(r.mul));
            $tr.find('.pr_div').val(prNum(r.div));
            $tr.find('.pr_add').val(prNum(r.add));

            $tbody.append($tr);
        });
    }

    function prLoad() {
        $('#pr_error').addClass('d-none').text('');
        $('#pr_tbody').html('<tr><td colspan="4" class="text-center text-muted py-4">กำลังโหลด…</td></tr>');

        $.getJSON(PAGE_URL + "/data")
            .done(function (res) {
                prRenderRows(res.rows || []);

                // ตัวคูณระหว่างขั้นราคา — บอกไว้ให้เห็นภาพรวมสูตรทั้งชุด (หน้านี้แก้ไม่ได้)
                if (res.tier) {
                    $('#pr_tier').html(
                        '<i class="ti ti-arrow-narrow-right me-1"></i>' +
                        'ราคาขาย 2 = ราคาขาย 1 × ' + prNum(res.tier.price_2_from_price_1) +
                        ' · ราคาขาย 3 = ราคาขาย 2 × ' + prNum(res.tier.price_3_from_price_2) +
                        ' (2 ค่านี้ต้องให้ผู้ดูแลระบบแก้ให้)'
                    );
                }
            })
            .fail(function () {
                $('#pr_tbody').html('<tr><td colspan="4" class="text-center text-danger py-4">โหลดเงื่อนไขไม่สำเร็จ</td></tr>');
            });
    }

    function prSave($btn) {
        const payload = { _token: "{{ csrf_token() }}" };

        $('#pr_tbody tr[data-key]').each(function () {
            const $tr = $(this);
            const key = $tr.data('key');

            payload['rules[' + key + '][mul]'] = $tr.find('.pr_mul').val();
            payload['rules[' + key + '][div]'] = $tr.find('.pr_div').val();
            payload['rules[' + key + '][add]'] = $tr.find('.pr_add').val();
        });

        $btn.prop('disabled', true);
        $('#pr_error').addClass('d-none').text('');

        $.ajax({
            type: "POST",
            url: PAGE_URL + "/update",
            data: payload,
            success: function () {
                // โหลดใหม่จาก DB — ให้ค่าบนหน้าจอตรงกับที่บันทึกจริงเสมอ
                prLoad();
                Swal.fire({
                    title: 'บันทึกเงื่อนไขราคาแล้ว',
                    icon: 'success',
                    timer: 1400,
                    showConfirmButton: false,
                    heightAuto: false
                });
            },
            error: function (xhr) {
                // 422 = กรอกผิด (เช่น ช่องหารเป็น 0) — โชว์ในกล่องแดงใต้ตารางให้แก้ต่อได้เลย
                $('#pr_error')
                    .text(xhr.responseJSON?.error ?? 'บันทึกไม่สำเร็จ')
                    .removeClass('d-none');
            },
            complete: function () { $btn.prop('disabled', false); }
        });
    }

    $('#pr_save, #pr_save_bottom').on('click', function () { prSave($(this)); });

    prLoad();
</script>

</body>
</html>
