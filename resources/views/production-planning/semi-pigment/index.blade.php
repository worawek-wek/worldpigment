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
                                    <option value="request">รออนุมัติ</option>
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
                                        <th>Quantity</th>
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
@endsection

@section('script')
<script>
    var oTable;
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
                { className: "text-left",   data: 'itemno',       name: 'itemno',       orderable: false },
                { className: "text-center", data: 'quantity',     name: 'quantity',     orderable: false },
                { className: "text-center", data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                { className: "text-center", data: 'action',       name: 'action',       orderable: false, searchable: false },
            ],
            order: [[0, 'asc']]
        });
    });

    $(document).on('keyup', '#searchInput', function () { oTable.draw(); });
    $(document).on('change', '#searchType, #searchStatus', function () { oTable.draw(); });

    // ---- อนุมัติ ----
    $(document).on('click', '.btn_approve', function () {
        var id = $(this).data('id');

        Swal.fire({
            title: 'ยืนยันการอนุมัติ?',
            text: 'รายการนี้จะถูกนำไปสร้างแผนการผลิต',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'อนุมัติ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#28a745'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                type: 'POST',
                url: '{{ route("production.semipigment.approve") }}',
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
