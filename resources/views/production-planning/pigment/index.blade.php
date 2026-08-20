@extends('./layout/main')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-color-swatch text-success"></i>
                                Pigment (รออนุมัติ)
                            </h3>
                            <p class="text-muted mb-0">
                                รายการ Pigment ที่รออนุมัติ ก่อนนำไปสร้างแผนการผลิต
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="col-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        {{-- แถวบนสุด: ปุ่ม Export ชิดขวา — export ตามเงื่อนไขค้นหาปัจจุบัน (ทุกหน้า ไม่ใช่เฉพาะหน้าที่แสดงอยู่) --}}
                        <div class="d-flex justify-content-end mb-3">
                            <button id="btn_export_excel" type="button" class="btn btn-success"
                                title="Export Excel ตามเงื่อนไขค้นหาปัจจุบัน (ทุกหน้า)">
                                <i class="ti ti-file-spreadsheet me-1"></i>Export Excel
                            </button>
                        </div>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label mb-1 small text-muted">ค้นหา</label>
                                <input id="searchInput" type="text" class="form-control"
                                    placeholder="ค้นหา Item No., รหัสลูกค้า, Order No.">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1 small text-muted">สถานะ</label>
                                <select id="searchStatus" class="form-select">
                                    <option value="">ทุกสถานะ</option>
                                    <option value="request" selected>รออนุมัติ</option>
                                    <option value="approved">อนุมัติแล้ว</option>
                                    <option value="reject">ไม่อนุมัติ</option>
                                </select>
                            </div>
                        </div>
                        {{-- แถวที่ 2: ค้นหาช่วงวันที่ — เลือกฟิลด์ (วันที่ขอ/วันที่สั่ง/วันที่ต้องการรับ) แล้วระบุวันที่เริ่ม–ถึง --}}
                        <div class="row g-3 align-items-end mt-1">
                            <div class="col-md-4">
                                <label class="form-label mb-1 small text-muted">ค้นหาตามวันที่</label>
                                <select id="searchDateField" class="form-select">
                                    <option value="created_at">วันที่ขอ</option>
                                    <option value="order_date">วันที่สั่ง</option>
                                    <option value="want_date">วันที่ต้องการรับ</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1 small text-muted">วันที่เริ่ม</label>
                                <input id="searchDateStart" type="text" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1 small text-muted">ถึง</label>
                                <input id="searchDateEnd" type="text" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป">
                            </div>
                            <div class="col-md-2">
                                <button id="btn_clear_date" type="button" class="btn btn-outline-secondary w-100">
                                    <i class="ti ti-x me-1"></i>ล้างวันที่
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-header">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-striped table-hover nowrap" style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Order No.</th>
                                        <th>วันที่ขอ</th>
                                        <th>วันที่สั่ง</th>
                                        <th>วันที่ต้องการรับ</th>
                                        <th>รหัสลูกค้า</th>
                                        <th>Item No.</th>
                                        <th>น้ำหนักที่จะใช้</th>
                                        <th>น้ำหนักที่ผลิต</th>
                                        <th>สถานะ</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Edit / Detail Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="padding: 1rem 1.5rem; color: white; background-color: #28a745;">
                    <h5 class="modal-title text-white mb-0">
                        <i class="ti ti-pencil me-1"></i>แก้ไข Pigment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="pg_edit_body"></div>
            </div>
        </div>
    </div>

    <!-- Detail Modal (รายการที่อนุมัติแล้ว) -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="padding: 1.25rem 1.5rem; color: white; background-color: #54BAB9;">
                    <h5 class="modal-title text-white mb-0">
                        <i class="ti ti-file-description me-1"></i>รายละเอียด Pigment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="result_detail"></div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    var oTable;
    // instance flatpickr ของช่องวันที่ (ใช้ clear ตอนกดล้าง)
    var fpDateStart = null, fpDateEnd = null;

    // แสดงตัวเลขเป็นทศนิยม 2 ตำแหน่ง (มี comma คั่นหลัก) — ว่าง/ไม่ใช่ตัวเลขแสดง '-'
    function fmt2(data) {
        if (data === null || data === '' || isNaN(data)) return '-';
        return parseFloat(data).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    $(document).ready(function () {
        // flatpickr: แสดง d/m/Y เหมือนกันทุกเครื่อง แต่ค่าจริง (input.value) ยังเป็น Y-m-d → ไม่ต้องแก้ฝั่ง PHP
        var fpOptions = {
            dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', allowInput: true, disableMobile: true
        };
        fpDateStart = flatpickr('#searchDateStart', fpOptions);
        fpDateEnd   = flatpickr('#searchDateEnd', fpOptions);

        oTable = $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            // ปิด responsive (ไม่ยุบคอลัมน์) — ให้ตารางเลื่อนซ้าย-ขวาผ่าน wrapper .table-responsive แทน
            // เพื่อให้เห็นครบทุกคอลัมน์ รวมถึงคอลัมน์ "จัดการ" ที่มีปุ่มสร้างแผน
            responsive: false,
            ajax: {
                url: "{{ route('production.pigment.datatable') }}",
                data: function (d) {
                    d.search = $('#searchInput').val();
                    d.status = $('#searchStatus').val();
                    d.date_field = $('#searchDateField').val();
                    d.date_start = $('#searchDateStart').val();
                    d.date_end   = $('#searchDateEnd').val();
                },
                error: function (xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            // เปิดให้คลิกหัวคอลัมน์เพื่อ sort ได้ (Yajra จะ ORDER BY ตาม name ของคอลัมน์ให้อัตโนมัติ)
            columns: [
                // # ใช้ DT_RowIndex (จาก addIndexColumn) เรียงตามลำดับที่แสดงจริง จึง sort เองไม่ได้
                { className: "text-center", data: 'DT_RowIndex',       name: 'DT_RowIndex',       orderable: false, searchable: false },
                { className: "text-center", data: 'orderno',           name: 'orderno',           orderable: true },
                { className: "text-center", data: 'created_at',        name: 'created_at',        orderable: true },
                { className: "text-center", data: 'order_date',        name: 'order_date',        orderable: true },
                { className: "text-center", data: 'want_date',         name: 'want_date',         orderable: true },
                { className: "text-center", data: 'custno',            name: 'custno',            orderable: true },
                { className: "text-left",   data: 'itemno',            name: 'itemno',            orderable: true },
                { className: "text-center", data: 'weight_request',    name: 'weight_request',    orderable: true, render: fmt2 },
                { className: "text-center", data: 'weight_production', name: 'weight_production', orderable: true, render: fmt2 },
                // sort สถานะตามคอลัมน์จริง status (แม้จะแสดงเป็น badge)
                { className: "text-center", data: 'status_badge',      name: 'status',            orderable: true, searchable: false },
                { className: "text-center", data: 'action',            name: 'action',            orderable: false, searchable: false },
            ],
            // เริ่มต้นเรียงตามวันที่ขอ (created_at) ใหม่→เก่า ให้ใกล้เคียงลำดับเดิม (id desc)
            order: [[2, 'desc']]
        });
    });

    $(document).on('keyup', '#searchInput', function () { oTable.draw(); });
    $(document).on('change', '#searchStatus', function () { oTable.draw(); });

    // ค้นหาช่วงวันที่ (วันที่ขอ / วันที่สั่ง / วันที่ต้องการรับ) — redraw เมื่อเปลี่ยนฟิลด์หรือวันที่
    $(document).on('change', '#searchDateField, #searchDateStart, #searchDateEnd', function () {
        oTable.draw();
    });

    // ล้างช่วงวันที่แล้วค้นหาใหม่
    $(document).on('click', '#btn_clear_date', function (e) {
        e.preventDefault();
        // clear(false) = ไม่ trigger change → กัน redraw ซ้ำ แล้ว draw ครั้งเดียว
        if (fpDateStart) fpDateStart.clear(false); else $('#searchDateStart').val('');
        if (fpDateEnd)   fpDateEnd.clear(false);   else $('#searchDateEnd').val('');
        oTable.draw();
    });

    // ---- Export Excel: ส่งเงื่อนไขค้นหาปัจจุบันไปที่ endpoint แล้วให้เบราว์เซอร์ดาวน์โหลด ----
    // ใช้เงื่อนไขชุดเดียวกับตาราง จึงได้ข้อมูลทุกหน้าตามที่ค้นหาไว้ (ไม่จำกัดเฉพาะหน้าที่แสดงอยู่)
    $(document).on('click', '#btn_export_excel', function (e) {
        e.preventDefault();

        var params = $.param({
            search:     $('#searchInput').val(),
            status:     $('#searchStatus').val(),
            date_field: $('#searchDateField').val(),
            date_start: $('#searchDateStart').val(),
            date_end:   $('#searchDateEnd').val()
        });

        window.location.href = '{{ route("production.pigment.export-excel") }}?' + params;
    });

    function getEditModal() {
        return bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal'));
    }

    function closeEditModal() {
        getEditModal().hide();
    }

    // ---- เปิด modal แก้ไข / รายละเอียด ----
    $(document).on('click', '.btn_edit', function () {
        var id = $(this).data('id');
        $.ajax({
            type: 'GET',
            url: '{{ route("production.pigment.edit") }}',
            dataType: 'json',
            cache: false,
            data: { id: id },
            success: function (response) {
                if (response.status == 200) {
                    $('#pg_edit_body').html(response.data);
                    getEditModal().show();
                } else {
                    Swal.fire({ icon: 'warning', title: 'ผิดพลาด', text: response.message });
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
            }
        });
    });

    // ---- บันทึกข้อมูล (แก้ไข) ----
    $(document).on('click', '#btn_pg_save', function () {
        var $btn = $(this);
        var itemno = ($('#pg_edit_form [name="itemno"]').val() || '').trim();
        if (!itemno) {
            $('#pg_edit_form [name="itemno"]').addClass('is-invalid').trigger('focus');
            return;
        }
        $('#pg_edit_form [name="itemno"]').removeClass('is-invalid');

        var formData = $('#pg_edit_form').serialize() + '&_token={{ csrf_token() }}';

        $btn.prop('disabled', true);
        $.ajax({
            type: 'POST',
            url: '{{ route("production.pigment.entry.update") }}',
            dataType: 'json',
            data: formData,
            success: function (response) {
                if (response.status == 200) {
                    closeEditModal();
                    oTable.draw(false); // draw(false) = คงหน้า pagination เดิม (ไม่เด้งกลับหน้า 1)
                    Swal.fire({ icon: 'success', title: 'สำเร็จ', text: response.message, timer: 1800, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'warning', title: response.status == 422 ? 'ข้อมูลไม่ถูกต้อง' : 'ผิดพลาด', text: response.message });
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
            },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    // ---- อนุมัติ (บันทึกข้อมูลฟอร์ม + เปลี่ยนสถานะเป็นอนุมัติ) ----
    $(document).on('click', '.btn_approve', function () {
        var itemno = ($('#pg_edit_form [name="itemno"]').val() || '').trim();
        if (!itemno) {
            $('#pg_edit_form [name="itemno"]').addClass('is-invalid').trigger('focus');
            return;
        }
        $('#pg_edit_form [name="itemno"]').removeClass('is-invalid');

        Swal.fire({
            title: 'ยืนยันการอนุมัติ?',
            text: 'ระบบจะบันทึกข้อมูลรายการนี้ (จากนั้นสร้างแผนการผลิตได้ที่ปุ่มในตาราง)',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'อนุมัติ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#28a745'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var formData = $('#pg_edit_form').serialize() + '&_token={{ csrf_token() }}';

            $.ajax({
                type: 'POST',
                url: '{{ route("production.pigment.approve") }}',
                dataType: 'json',
                data: formData,
                success: function (response) {
                    if (response.status == 200) {
                        closeEditModal();
                        oTable.draw(false); // draw(false) = คงหน้า pagination เดิม (ไม่เด้งกลับหน้า 1)
                        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: response.message, timer: 1800, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'warning', title: 'ผิดพลาด', text: response.message });
                    }
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
                }
            });
        });
    });

    // ---- ไม่อนุมัติ ----
    $(document).on('click', '.btn_reject', function () {
        var id = $(this).data('id');

        Swal.fire({
            title: 'ยืนยันไม่อนุมัติ?',
            text: 'รายการนี้จะถูกตีกลับ ไม่นำไปสร้างแผนการผลิต',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ไม่อนุมัติ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#dc3545'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                type: 'POST',
                url: '{{ route("production.pigment.reject") }}',
                dataType: 'json',
                data: { id: id, _token: '{{ csrf_token() }}' },
                success: function (response) {
                    if (response.status == 200) {
                        closeEditModal();
                        oTable.draw(false); // draw(false) = คงหน้า pagination เดิม (ไม่เด้งกลับหน้า 1)
                        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: response.message, timer: 1800, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'warning', title: 'ผิดพลาด', text: response.message });
                    }
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
                }
            });
        });
    });

    // ---- ดูรายละเอียด (รายการที่อนุมัติแล้ว) ----
    $(document).on('click', '.btn_view', function () {
        var id = $(this).data('id');
        $.ajax({
            type: 'GET',
            url: '{{ route("production.pigment.detail") }}',
            dataType: 'json',
            cache: false,
            data: { id: id },
            success: function (response) {
                if (response.status == 200) {
                    $('#result_detail').html(response.data);
                    new bootstrap.Modal(document.getElementById('detailModal')).show();
                } else {
                    Swal.fire({ icon: 'warning', title: 'ผิดพลาด', text: response.message });
                }
            },
            error: function (response) {
                console.log(response.responseJSON);
            }
        });
    });

    // ---- สร้างแผนการผลิต (รายการที่อนุมัติแล้ว) ----
    $(document).on('click', '.btn_create_plan', function () {
        var id = $(this).data('id');

        Swal.fire({
            title: 'สร้างแผนการผลิต?',
            text: 'นำข้อมูลรายการนี้ไปสร้างแผนการผลิต',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'สร้าง',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#ff9f43'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                type: 'POST',
                url: '{{ route("production.pigment.convertplanning") }}',
                dataType: 'json',
                data: { id: id, _token: '{{ csrf_token() }}' },
                success: function (response) {
                    if (response.status == 200) {
                        oTable.draw(false); // draw(false) = คงหน้า pagination เดิม (ไม่เด้งกลับหน้า 1)
                        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: response.message, timer: 1800, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'warning', title: 'ผิดพลาด', text: response.message });
                    }
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
                }
            });
        });
    });
</script>
@endsection
