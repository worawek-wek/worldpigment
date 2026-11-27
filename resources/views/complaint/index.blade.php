@extends('./layout/main')

{{-- ระบบ Complaint & NCR — 26/11/2569 · ฟอร์ม/ตาราง/Excel ใช้เฉพาะช่องบนผัง Access "NC : ฟอร์ม" (ตัดช่องที่ไม่อยู่บนฟอร์มออกแล้ว) --}}
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1"><i class="ti ti-message-report text-primary"></i> ระบบ Complaint &amp; NCR</h3>
                            <p class="text-muted mb-0">บันทึกข้อร้องเรียนจากลูกค้า / รายงานสิ่งที่ไม่เป็นไปตามข้อกำหนด</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#ncExportModal">
                                <i class="ti ti-file-spreadsheet me-1"></i> Export Excel
                            </button>
                            <button type="button" class="btn btn-primary" id="btn_add">
                                <i class="ti ti-plus me-1"></i> เพิ่มรายการ
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <input id="searchInput" type="text" class="form-control"
                                    placeholder="ค้นหา เลขที่ใบร้องเรียน / NCRNo / ปัญหา / ลูกค้า / รหัสสินค้า / LotNo...">
                            </div>
                            <div class="col-md-2">
                                <select id="searchDegree" class="form-select">
                                    <option value="">ผล: ทั้งหมด</option>
                                    <option value="__none">รอผล</option>
                                    @foreach($degrees as $key => $label)
                                        <option value="{{ $key }}">{{ $key }} = {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <select id="searchOffspec" class="form-select" title="Off Spec">
                                    <option value="">Off Spec</option>
                                    <option value="1">ใช่</option>
                                    <option value="0">ไม่ใช่</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input id="searchDateFrom" type="text" class="form-control nc-filter-date" placeholder="วันที่ออก NCR ตั้งแต่">
                                    <input id="searchDateTo" type="text" class="form-control nc-filter-date" placeholder="ถึงวันที่">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-label-secondary w-100" id="btn_reset" title="ล้างตัวกรอง">
                                    <i class="ti ti-eraser"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-datatable table-responsive">
                        <table class="table table-bordered table-hover table-striped wp-zebra nc-table" id="dataTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">วันที่ออก NCR</th>
                                    <th class="text-center">เลขที่ใบร้องเรียน</th>
                                    <th>ลูกค้า</th>
                                    <th>รหัสสินค้า</th>
                                    <th>LotNo</th>
                                    <th>ปัญหา</th>
                                    <th class="text-center">ผล</th>
                                    <th class="text-center">NCRNo</th>
                                    <th class="text-center">สรุปสาเหตุ</th>
                                    <th>สาเหตุ</th>
                                    <th>วิธีแก้ไข</th>
                                    <th>วิธีป้องกัน</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade modalHeadDecor" id="ncModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-message-report me-1"></i>Complaint &amp; NCR</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="nc_form_box"></div>
            </div>
        </div>
    </div>

    {{-- Export Excel — 4 แบบตามกล่อง "การแสดงข้อมูล สรุปรายงานผล" ในสเปก --}}
    <div class="modal fade modalHeadDecor" id="ncExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-file-spreadsheet me-1"></i>สรุปรายงานผล (Excel)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        @foreach($exportTypes as $key => $label)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="nc_export_type" id="nc_ex_{{ $key }}"
                                    value="{{ $key }}" {{ $loop->first ? 'checked' : '' }}>
                                <label class="form-check-label" for="nc_ex_{{ $key }}">{{ $label }}</label>
                            </div>
                        @endforeach
                        <div class="form-text">คอลัมน์ = ช่องบนฟอร์ม · ลำดับรองเรียงตามเลขที่ใบร้องเรียน น้อย → มาก แล้วต่อด้วย NCR ภายใน</div>
                    </div>
                    <label class="form-label">ช่วงวันที่ออก NCR</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control nc-export-date" id="nc_ex_from" placeholder="ตั้งแต่ (DD/MM/YY)">
                        <input type="text" class="form-control nc-export-date" id="nc_ex_to" placeholder="ถึง (DD/MM/YY)">
                    </div>
                    <div class="form-text mb-3">เว้นว่าง = ทุกช่วงวันที่</div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
                        <button type="button" class="btn btn-success" id="btn_nc_export">
                            <i class="ti ti-download me-1"></i>ดาวน์โหลด
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    var oTable;
    var searchTimer;

    $(document).ready(function () {
        oTable = $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            pageLength: 25,
            scrollX: true,
            ajax: {
                url: "{{ route('complaint.datatable') }}",
                data: function (d) {
                    d.search    = $('#searchInput').val();
                    d.degree    = $('#searchDegree').val();
                    d.offspec   = $('#searchOffspec').val();
                    d.date_from = $('#searchDateFrom').val();
                    d.date_to   = $('#searchDateTo').val();
                }
            },
            columns: [
                { className: 'text-center', data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { className: 'text-center text-nowrap', data: 'NCRDate', name: 'nc.NCRDate' },
                { className: 'text-center text-nowrap', data: 'ComplainNO', name: 'nc.ComplainNO' },
                { data: 'customer', name: 'customer' },
                { className: 'text-nowrap', data: 'ProdNo', name: 'nc.ProdNo' },
                { className: 'text-nowrap', data: 'LotNo', name: 'nc.LotNo' },
                { data: 'Subject', name: 'nc.Subject' },
                { className: 'text-center', data: 'degree_label', name: 'degree_label' },
                { className: 'text-center text-nowrap', data: 'NCRNo', name: 'nc.NCRNo' },
                { className: 'text-center', data: 'CauseType', name: 'nc.CauseType' },
                { data: 'Cause', name: 'nc.Cause', orderable: false },
                { data: 'Correct', name: 'nc.Correct', orderable: false },
                { data: 'Protect', name: 'nc.Protect', orderable: false },
                { className: 'text-center', data: 'btnaction', name: 'btnaction', orderable: false, searchable: false },
            ],
            order: [[1, 'desc']],
        });

        flatpickr('.nc-filter-date', {
            dateFormat: 'd/m/y', allowInput: true, disableMobile: true,
            onChange: function () { oTable.draw(); }
        });
        flatpickr('.nc-export-date', { dateFormat: 'd/m/y', allowInput: true, disableMobile: true });
    });

    $(document).on('keyup', '#searchInput', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { oTable.draw(); }, 400);
    });
    $(document).on('change', '#searchDegree, #searchOffspec', function () { oTable.draw(); });
    $(document).on('click', '#btn_reset', function () {
        $('#searchInput').val('');
        $('#searchDegree, #searchOffspec').val('');
        document.querySelectorAll('.nc-filter-date').forEach(function (el) { if (el._flatpickr) el._flatpickr.clear(); });
        oTable.draw();
    });

    // ───────── Export Excel ─────────
    $(document).on('click', '#btn_nc_export', function () {
        var params = {
            type: $('input[name="nc_export_type"]:checked').val(),
            date_from: $('#nc_ex_from').val(),
            date_to: $('#nc_ex_to').val()
        };
        window.location = "{{ route('complaint.export_excel') }}?" + $.param(params);
    });

    // ───────── ฟอร์ม ─────────
    $(document).on('click', '#btn_add', function () { openNcForm(null); });
    $(document).on('click', '.btn_edit', function () { openNcForm($(this).data('id')); });

    function openNcForm(id) {
        $.get("{{ route('complaint.edit') }}", { id: id || '' }, function (res) {
            $('#nc_form_box').html(res.data);
            flatpickr('#nc_form .nc-datepicker', { dateFormat: 'd/m/y', allowInput: true, static: true, disableMobile: true });
            $('#ncModal').modal('show');
        });
    }

    // NCRNo ของส่วนย่อยล่าง (treat) = NCRNo ด้านบน (แสดงอย่างเดียว เหมือน subform ของ Access)
    $(document).on('input', '#nc_ncrno', function () {
        $('#nc_sub_ncrno').val($(this).val());
    });

    // ───────── รหัสลูกค้า / ชื่อลูกค้า: รายการแนะนำ (26/11/2569) ─────────
    // พิมพ์ในช่องรหัสหรือช่องชื่อ → ขึ้นรายการ "รหัส — ชื่อ" ใต้ช่อง · คลิก / ↑↓ + Enter = เติมทั้งรหัสและชื่อ
    // พิมพ์รหัสครบแล้วตรงกับลูกค้า (customer/{code}) → เติมชื่อให้เองแม้ไม่ได้เลือกจากรายการ
    var ncSuggestTimer, ncSuggestXhr, ncLookupTimer;

    function ncSuggestBox($input) {
        var $box = $input.siblings('.nc-suggest');
        if (!$box.length) {
            $box = $('<div class="nc-suggest list-group shadow-sm d-none"></div>').insertAfter($input);
        }
        return $box;
    }

    function ncSuggestHide() {
        $('#nc_form .nc-suggest').addClass('d-none').empty();
    }

    function ncPickCustomer(code, name) {
        $('#nc_custno').val(code).removeClass('is-invalid');
        $('#nc_custname').val(name);
        $('#nc_cust_msg').addClass('d-none');
        ncSuggestHide();
    }

    function ncSuggest($input) {
        var q = $.trim($input.val());
        clearTimeout(ncSuggestTimer);
        if (ncSuggestXhr) ncSuggestXhr.abort();
        var isProd = $input.is('#nc_prodno');
        // ลูกค้า: ตัวเลขพิมพ์ 1 ตัวก็ค้น ข้อความต้อง ≥ 2 · สินค้า: 1 ตัวก็ค้น
        if (!q || (!isProd && !/^\d+$/.test(q) && q.length < 2)) { ncSuggestHide(); return; }

        ncSuggestTimer = setTimeout(function () {
            var url = isProd ? "{{ route('complaint.product_search') }}" : "{{ route('complaint.customer_search') }}";
            ncSuggestXhr = $.get(url, { q: q }, function (res) {
                var $box = ncSuggestBox($input).empty();
                var items = res.items || [];
                if (!items.length) {
                    $box.append('<div class="list-group-item small text-muted">' + (isProd ? 'ไม่พบรหัสสินค้า' : 'ไม่พบลูกค้า') + '</div>');
                } else {
                    items.forEach(function (c) {
                        $('<button type="button" class="list-group-item list-group-item-action"></button>')
                            .attr('data-code', c.code).attr('data-name', c.name || '')
                            .append($('<span class="fw-semibold me-2"></span>').text(c.code))
                            .append(c.name ? $('<span></span>').text(c.name) : $('<span class="text-muted">(ไม่มีชื่อ)</span>'))
                            .appendTo($box);
                    });
                }
                $('#nc_form .nc-suggest').not($box).addClass('d-none').empty();
                $box.removeClass('d-none');
            });
        }, 250);
    }

    $(document).on('input', '#nc_custno, #nc_custname', function () {
        ncSuggest($(this));
    });

    // รหัสครบ → เติมชื่อให้เอง · ไม่พบ = ขึ้นข้อความเตือนใต้ช่อง (ยังพิมพ์ชื่อเองได้)
    $(document).on('input', '#nc_custno', function () {
        var code = $.trim($(this).val());
        clearTimeout(ncLookupTimer);
        $('#nc_custno').removeClass('is-invalid');
        $('#nc_cust_msg').addClass('d-none');
        if (!code) return;
        ncLookupTimer = setTimeout(function () {
            $.get("{{ url('complaint/customer') }}/" + encodeURIComponent(code), function (res) {
                if ($.trim($('#nc_custno').val()) !== code) return; // พิมพ์ต่อไปแล้ว
                if (res.customer) {
                    $('#nc_custname').val(res.customer.name);
                } else if (code.length >= 5) {
                    $('#nc_custno').addClass('is-invalid');
                    $('#nc_cust_msg').removeClass('d-none');
                }
            });
        }, 400);
    });

    // เลือกจากรายการ — ช่องสินค้า = เติมรหัส แล้วดึงชื่อของลูกค้ารายนี้มาแสดง · ช่องลูกค้า = เติมรหัส+ชื่อ
    function ncPick($input, code, name) {
        if ($input.is('#nc_prodno')) {
            $input.val(code);
            ncSuggestHide();
            ncProdName();
        } else {
            ncPickCustomer(code, name);
            ncProdName(); // ชื่อสินค้าขึ้นกับลูกค้า
        }
    }

    $(document).on('mousedown', '#nc_form .nc-suggest [data-code]', function (e) {
        e.preventDefault(); // กันช่องเสียโฟกัสก่อนเลือก
        ncPick($(this).closest('.nc-suggest').siblings('input').first(), String($(this).data('code')), $(this).data('name'));
    });

    // ───────── รหัสสินค้า: รายการแนะนำ + ชื่อสินค้าใต้ช่อง (แสดงอย่างเดียว ไม่บันทึก, 26/11/2569) ─────────
    var ncProdTimer, ncProdXhr;
    function ncProdName() {
        var code = $.trim($('#nc_prodno').val());
        var $out = $('#nc_prod_name').removeClass('text-danger');
        clearTimeout(ncProdTimer);
        if (ncProdXhr) ncProdXhr.abort();
        if (!code) { $out.text(''); return; }
        ncProdTimer = setTimeout(function () {
            ncProdXhr = $.get("{{ route('complaint.product_lookup') }}", { code: code, custno: $.trim($('#nc_custno').val()) }, function (res) {
                if ($.trim($('#nc_prodno').val()) !== code) return; // พิมพ์ต่อไปแล้ว
                if (res.name) $out.text(res.name);
                else if (res.found) $out.text('(ไม่มีชื่อสินค้า)');
                else $out.addClass('text-danger').text('ไม่พบรหัสสินค้านี้');
            });
        }, 400);
    }
    $(document).on('input', '#nc_prodno', function () {
        ncSuggest($(this));
        ncProdName();
    });
    // เปลี่ยนลูกค้า → ชื่อสินค้าอาจต่างไป (Label ตามลูกค้า)
    $(document).on('change', '#nc_custno', ncProdName);

    // ───────── สรุปสาเหตุ: เลือกจากรายการ A–I หรือพิมพ์เองได้ (27/11/2569) ─────────
    // รายการมาจาก data-options ของช่อง (config/complaint.php → cause_types) ไม่ยิง server
    // คลิกช่อง / ปุ่มลูกศร = รายการทั้งหมด · พิมพ์ = กรองตามตัวที่ขึ้นต้น · พิมพ์ข้อความยาวที่ไม่ตรงรหัส = ซ่อนรายการ (เก็บข้อความตามที่พิมพ์)
    function ncCauseBox() {
        var $group = $('#nc_causetype').closest('.input-group');
        var $box = $group.siblings('.nc-suggest');
        if (!$box.length) {
            $box = $('<div class="nc-suggest nc-suggest-cause list-group shadow-sm d-none"></div>').insertAfter($group);
        }
        return $box;
    }

    function ncCauseShow(showAll) {
        var $input = $('#nc_causetype');
        var opts = $input.data('options') || [];
        var q = showAll ? '' : $.trim($input.val()).toUpperCase();
        var items = opts.filter(function (o) {
            return q === '' || o.code.toUpperCase().indexOf(q) === 0;
        });
        var $box = ncCauseBox().empty();
        if (!items.length) { $box.addClass('d-none'); return; }
        items.forEach(function (o) {
            $('<button type="button" class="list-group-item list-group-item-action"></button>')
                .attr('data-cause-code', o.code)
                .append($('<span class="fw-semibold me-2"></span>').text(o.code))
                .append(o.name ? $('<span></span>').text(o.name) : '')
                .appendTo($box);
        });
        $('#nc_form .nc-suggest').not($box).addClass('d-none').empty();
        $box.removeClass('d-none');
    }

    function ncCausePick(code) {
        var $input = $('#nc_causetype').val(code).trigger('focus');
        var el = $input[0];
        el.setSelectionRange(code.length, code.length); // เคอร์เซอร์ท้ายข้อความ พิมพ์ต่อท้ายได้เลย
        ncCauseBox().addClass('d-none').empty();
    }

    $(document).on('click', '#nc_causetype', function () {
        if (ncCauseBox().hasClass('d-none')) ncCauseShow($.trim(this.value) === '');
    });
    $(document).on('input', '#nc_causetype', function () { ncCauseShow(false); });
    $(document).on('click', '#btn_nc_causetype', function () {
        var $box = ncCauseBox();
        if ($box.hasClass('d-none')) { ncCauseShow(true); $('#nc_causetype').trigger('focus'); }
        else { $box.addClass('d-none').empty(); }
    });
    $(document).on('mousedown', '#nc_form [data-cause-code]', function (e) {
        e.preventDefault();
        ncCausePick(String($(this).data('cause-code')));
    });
    $(document).on('keydown', '#nc_causetype', function (e) {
        var $box = ncCauseBox();
        if ($box.hasClass('d-none')) {
            if (e.key === 'ArrowDown') { e.preventDefault(); ncCauseShow(true); }
            return;
        }
        var $items = $box.find('[data-cause-code]');
        var $act = $items.filter('.active');
        var i = $items.index($act);
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            i = e.key === 'ArrowDown' ? Math.min(i + 1, $items.length - 1) : Math.max(i - 1, 0);
            $items.removeClass('active').eq(i).addClass('active')[0]?.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if ($act.length) ncCausePick(String($act.data('cause-code')));
            else $box.addClass('d-none').empty();
        } else if (e.key === 'Escape') {
            e.stopPropagation();
            $box.addClass('d-none').empty();
        }
    });
    $(document).on('blur', '#nc_causetype', function () {
        setTimeout(function () { ncCauseBox().addClass('d-none').empty(); }, 150);
    });

    // ↑ ↓ Enter Esc ในรายการแนะนำ
    $(document).on('keydown', '#nc_custno, #nc_custname, #nc_prodno', function (e) {
        var $box = $(this).siblings('.nc-suggest');
        if ($box.hasClass('d-none')) return;
        var $items = $box.find('[data-code]');
        var $act = $items.filter('.active');
        var i = $items.index($act);
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            i = e.key === 'ArrowDown' ? Math.min(i + 1, $items.length - 1) : Math.max(i - 1, 0);
            $items.removeClass('active').eq(i).addClass('active')[0]?.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter') {
            e.preventDefault();
            var $pick = $act.length ? $act : $items.first();
            if ($pick.length) ncPick($(this), String($pick.data('code')), $pick.data('name'));
        } else if (e.key === 'Escape') {
            e.stopPropagation();
            ncSuggestHide();
        }
    });

    $(document).on('blur', '#nc_custno, #nc_custname, #nc_prodno', function () {
        setTimeout(ncSuggestHide, 150);
    });

    $(document).on('click', '#btn_nc_save', function () {
        var $btn = $(this);
        var $form = $('#nc_form');
        stripCommaFields($form);
        var data = $form.serialize();

        $btn.prop('disabled', true);
        $.post("{{ route('complaint.store') }}", data, function (res) {
            if (res.status == 200) {
                $('#ncModal').modal('hide');
                oTable.draw(false);
                Swal.fire({ icon: 'success', title: res.message, toast: true, position: 'top-end', timer: 1800, showConfirmButton: false });
            } else {
                Swal.fire({ icon: res.status == 422 ? 'warning' : 'error', title: 'บันทึกไม่ได้', text: res.message || '',
                    target: document.getElementById('ncModal'), heightAuto: false });
            }
        }, 'json').fail(function (xhr) {
            Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่',
                target: document.getElementById('ncModal'), heightAuto: false });
        }).always(function () {
            $btn.prop('disabled', false);
            // ช่องตัวเลขถูกถอดคอมมาไปแล้ว — ถ้าฟอร์มยังเปิดอยู่ (บันทึกไม่ผ่าน) จัดรูปแบบคืน
            $form.find('.js-comma').trigger('blur');
        });
    });

    $(document).on('click', '.btn_delete', function () {
        var id = $(this).data('id');
        Swal.fire({
            icon: 'warning', title: 'ยืนยันการลบ', text: 'ต้องการลบรายการนี้ใช่หรือไม่? (ลบแล้วกู้คืนไม่ได้)',
            showCancelButton: true, confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก',
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-label-secondary ms-2' }, buttonsStyling: false
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.post("{{ route('complaint.delete') }}", { _token: "{{ csrf_token() }}", id: id }, function (res) {
                if (res.status == 200) {
                    oTable.draw(false);
                    Swal.fire({ icon: 'success', title: res.message, toast: true, position: 'top-end', timer: 1500, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: res.message || '' });
                }
            }, 'json');
        });
    });
</script>

<style>
    .modalHeadDecor .modal-header { padding: 0; }
    .modalHeadDecor .modal-title { color: white; background-color: #54BAB9; position: relative; }
    .flatpickr-calendar { z-index: 1092; }
    .nc-table td { vertical-align: top; }
    .nc-clip { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    /* ฟอร์มตามผัง Access "NC : ฟอร์ม" (26/11/2569) */
    #nc_form .form-label { margin-bottom: .15rem; font-weight: 600; font-size: .8125rem; }
    #nc_form .nc-mgr { background-color: #fffbe6; color: #c00; }
    #nc_form .nc-sub { border: 1px solid #d9dee3; border-radius: .375rem; padding: .75rem; background: #f8f9fb; }
    #nc_form .nc-sub-head { font-weight: 600; color: #5d596c; margin-bottom: .5rem; }
    #nc_form .nc-claim { color: #1a3fbf; font-weight: 600; }
    /* รายการแนะนำ (ลูกค้า / รหัสสินค้า / สรุปสาเหตุ) — ทุกรายการสูงเท่ารายการของช่อง "ผล" และตัวอักษรเท่าช่องกรอก (27/11/2569 ตามที่ผู้ใช้สั่ง) */
    #nc_form .nc-suggest { position: absolute; z-index: 1080; left: calc(var(--bs-gutter-x) * .5); min-width: 360px;
        max-height: 400px; overflow-y: auto; background: #fff; font-size: .9375rem; }
    #nc_form .nc-suggest .list-group-item { padding: .5rem .875rem; line-height: 1.5; }
    #nc_form .nc-suggest .list-group-item.active { color: #fff; }
    #nc_form .nc-suggest-cause { min-width: 200px; }
    #nc_form .nc-prod-name { min-height: 1.2em; color: #1a3fbf; }
</style>
@endsection
