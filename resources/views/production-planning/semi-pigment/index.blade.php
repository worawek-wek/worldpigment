@extends('./layout/main')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-checklist text-primary"></i>
                                Semi &amp; Pigment (รออนุมัติ)
                            </h3>
                            <p class="text-muted mb-0">
                                รายการ Semi / Pigment ที่รออนุมัติ ก่อนนำไปสร้างแผนการผลิต
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="col-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <input id="searchInput" type="text" class="form-control"
                                    placeholder="ค้นหา Item No., รหัสลูกค้า, Order No., Company">
                            </div>
                            <div class="col-md-2">
                                <select id="searchType" class="form-select">
                                    <option value="">ทุกประเภท</option>
                                    <option value="semi">Semi</option>
                                    <option value="pigment">Pigment</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="searchStatus" class="form-select">
                                    <option value="">ทุกสถานะ</option>
                                    <option value="request" selected>รออนุมัติ</option>
                                    <option value="approved">อนุมัติแล้ว</option>
                                    <option value="reject">ไม่อนุมัติ</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-header">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>ประเภท</th>
                                        <th>Order No.</th>
                                        <th>Company</th>
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
                <div class="modal-header" style="padding: 1rem 1.5rem; color: white; background-color: #3A8EBA;">
                    <h5 class="modal-title text-white mb-0">
                        <i class="ti ti-pencil me-1"></i>แก้ไข Semi &amp; Pigment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="sp_edit_body"></div>
            </div>
        </div>
    </div>

    <!-- Detail Modal (รายการที่อนุมัติแล้ว) -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="padding: 1.25rem 1.5rem; color: white; background-color: #54BAB9;">
                    <h5 class="modal-title text-white mb-0">
                        <i class="ti ti-file-description me-1"></i>รายละเอียด Semi &amp; Pigment
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

    // แสดงตัวเลขเป็นทศนิยม 2 ตำแหน่ง (มี comma คั่นหลัก) — ว่าง/ไม่ใช่ตัวเลขแสดง '-'
    function fmt2(data) {
        if (data === null || data === '' || isNaN(data)) return '-';
        return parseFloat(data).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    $(document).ready(function () {
        oTable = $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            responsive: true,
            ajax: {
                url: "{{ route('production.semipigment.datatable') }}",
                data: function (d) {
                    d.search = $('#searchInput').val();
                    d.type   = $('#searchType').val();
                    d.status = $('#searchStatus').val();
                },
                error: function (xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            columns: [
                { className: "text-center", data: 'rownum',       name: 'rownum',       orderable: false },
                { className: "text-center", data: 'type_badge',   name: 'type_badge',   orderable: false, searchable: false },
                { className: "text-center", data: 'orderno',      name: 'orderno',      orderable: false },
                { className: "text-center", data: 'company',      name: 'company',      orderable: false },
                { className: "text-center", data: 'order_date',   name: 'order_date',   orderable: false },
                { className: "text-center", data: 'want_date',    name: 'want_date',    orderable: false },
                { className: "text-center", data: 'custno',       name: 'custno',       orderable: false },
                { className: "text-left",   data: 'itemno',         name: 'itemno',         orderable: false },
                { className: "text-center", data: 'weight_request',    name: 'weight_request',    orderable: false, render: fmt2 },
                { className: "text-center", data: 'weight_production', name: 'weight_production', orderable: false, render: fmt2 },
                { className: "text-center", data: 'status_badge',      name: 'status_badge',      orderable: false, searchable: false },
                { className: "text-center", data: 'action',       name: 'action',       orderable: false, searchable: false },
            ],
            order: [[0, 'asc']]
        });
    });

    $(document).on('keyup', '#searchInput', function () { oTable.draw(); });
    $(document).on('change', '#searchType, #searchStatus', function () { oTable.draw(); });

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
            url: '{{ route("production.semipigment.edit") }}',
            dataType: 'json',
            cache: false,
            data: { id: id },
            success: function (response) {
                if (response.status == 200) {
                    $('#sp_edit_body').html(response.data);
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
    $(document).on('click', '#btn_sp_save', function () {
        var $btn = $(this);
        var itemno = ($('#sp_edit_form [name="itemno"]').val() || '').trim();
        if (!itemno) {
            $('#sp_edit_form [name="itemno"]').addClass('is-invalid').trigger('focus');
            return;
        }
        $('#sp_edit_form [name="itemno"]').removeClass('is-invalid');

        var formData = $('#sp_edit_form').serialize() + '&_token={{ csrf_token() }}';

        $btn.prop('disabled', true);
        $.ajax({
            type: 'POST',
            url: '{{ route("production.semipigment.entry.update") }}',
            dataType: 'json',
            data: formData,
            success: function (response) {
                if (response.status == 200) {
                    closeEditModal();
                    oTable.draw();
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
        var itemno = ($('#sp_edit_form [name="itemno"]').val() || '').trim();
        if (!itemno) {
            $('#sp_edit_form [name="itemno"]').addClass('is-invalid').trigger('focus');
            return;
        }
        $('#sp_edit_form [name="itemno"]').removeClass('is-invalid');

        Swal.fire({
            title: 'ยืนยันการอนุมัติ?',
            text: 'ระบบจะบันทึกข้อมูลและนำรายการนี้ไปสร้างแผนการผลิต',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'อนุมัติ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#28a745'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var formData = $('#sp_edit_form').serialize() + '&_token={{ csrf_token() }}';

            $.ajax({
                type: 'POST',
                url: '{{ route("production.semipigment.approve") }}',
                dataType: 'json',
                data: formData,
                success: function (response) {
                    if (response.status == 200) {
                        closeEditModal();
                        oTable.draw();
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ',
                            text: response.message,
                            timer: 1800,
                            showConfirmButton: false
                        });
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
                url: '{{ route("production.semipigment.reject") }}',
                dataType: 'json',
                data: { id: id, _token: '{{ csrf_token() }}' },
                success: function (response) {
                    if (response.status == 200) {
                        closeEditModal();
                        oTable.draw();
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ',
                            text: response.message,
                            timer: 1800,
                            showConfirmButton: false
                        });
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
            url: '{{ route("production.semipigment.detail") }}',
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
                url: '{{ route("production.semipigment.convertplanning") }}',
                dataType: 'json',
                data: { id: id, _token: '{{ csrf_token() }}' },
                success: function (response) {
                    if (response.status == 200) {
                        oTable.draw();
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ',
                            text: response.message,
                            timer: 1800,
                            showConfirmButton: false
                        });
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
