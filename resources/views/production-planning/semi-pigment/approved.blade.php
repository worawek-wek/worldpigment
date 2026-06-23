@extends('./layout/main')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-circle-check text-success"></i>
                                Semi &amp; Pigment (อนุมัติแล้ว)
                            </h3>
                            <p class="text-muted mb-0">
                                รายการที่อนุมัติแล้ว — ดูรายละเอียดและสร้างแผนการผลิต
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
                                        <th>ผู้อนุมัติ</th>
                                        <th>แผนการผลิต</th>
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

    <!-- Detail Modal -->
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
                url: "{{ route('production.semipigment.approved.datatable') }}",
                data: function (d) {
                    d.search = $('#searchInput').val();
                    d.type   = $('#searchType').val();
                },
                error: function (xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            columns: [
                { className: "text-center", data: 'rownum',     name: 'rownum',     orderable: false },
                { className: "text-center", data: 'type_badge', name: 'type_badge', orderable: false, searchable: false },
                { className: "text-center", data: 'orderno',    name: 'orderno',    orderable: false },
                { className: "text-center", data: 'company',    name: 'company',    orderable: false },
                { className: "text-center", data: 'order_date', name: 'order_date', orderable: false },
                { className: "text-center", data: 'want_date',  name: 'want_date',  orderable: false },
                { className: "text-center", data: 'custno',     name: 'custno',     orderable: false },
                { className: "text-left",   data: 'itemno',         name: 'itemno',         orderable: false },
                { className: "text-center", data: 'weight_request',    name: 'weight_request',    orderable: false, render: fmt2 },
                { className: "text-center", data: 'weight_production', name: 'weight_production', orderable: false, render: fmt2 },
                { className: "text-center", data: 'approver_name',     name: 'approver_name',     orderable: false, searchable: false },
                { className: "text-center", data: 'plan_badge',        name: 'plan_badge',        orderable: false, searchable: false },
                { className: "text-center", data: 'action',     name: 'action',     orderable: false, searchable: false },
            ],
            order: [[0, 'asc']]
        });
    });

    $(document).on('keyup', '#searchInput', function () { oTable.draw(); });
    $(document).on('change', '#searchType', function () { oTable.draw(); });

    // ---- ดูรายละเอียด ----
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

    // ---- สร้างแผนการผลิต ----
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
