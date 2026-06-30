@extends('./layout/main')


@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-calendar-stats text-primary"></i>
                                จัดการใบคำสั่งซื้อ
                            </h3>

                            <p class="text-muted mb-0">
                                ข้อมูลการสั่งซื้อและสร้างแผนการผลิต
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
                            <div class="col-md-3">
                                <input id="searchInput" type="text" class="form-control"
                                placeholder="ค้นหา...">
                            </div>
                            <div class="col-md-3 ms-auto text-end">
                                <button type="button" class="btn btn-primary" id="btn_add"
                                    data-bs-target="#departmentModal">
                                    <i class="ti ti-plus me-1"></i> สร้างแผนก
                                </button>
                            </div>

                        </div>
                    </div>

                    <div class="card-header">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="col-1">#</th>
                                        <th class="col-1">Name</th>
                                        <th class="col-2">Description</th>
                                        <th class="col-2">Manage</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Create Department Modal -->
    <div class="modal fade modalHeadDecor" id="departmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-plus me-1"></i>สร้างแผนก
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="result_detail">
                    {{-- @include('department.department-form') --}}
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
                url: "{{ route('department.datatable') }}",
                data: function(d) {
                    d.search = $('#searchInput').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            columns: [
                { 'className': "text-center", data: 'rownum', name: 'rownum', orderable: false },
                { 'className': "text-center", data: 'name', name: 'name', orderable: false },
                { 'className': "text-center", data: 'description', name: 'description', orderable: false },
                { 'className': "text-center", data: 'btnedit', name: 'btnedit', orderable: false, searchable: false },
            ],
            order: [
                [0, 'asc']
            ],
            rowCallback: function(row, data, index) {

            },
            initComplete: function(settings, json) {
                console.log('DataTable loaded');
            }
        });
    });

    $(document).on('keyup', '#searchInput', function(e){
        e.preventDefault();
        oTable.draw();
    });

    $(document).on('click', '#btn_add', function(e){
        e.preventDefault();
        $.ajax({
            url: "{{ route('department.edit') }}",
            method: "GET",
            data: {
                id: null
            },
            success: function(response) {
                $('#result_detail').html(response.data);
                $('#departmentModal').modal('show');
            }
        });
    });

    // แก้ไขแผนก — เปิด modal พร้อมข้อมูลเดิม
    $(document).on('click', '.btn_edit', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
            url: "{{ route('department.edit') }}",
            method: "GET",
            data: { id: id },
            success: function(response) {
                $('#result_detail').html(response.data);
                $('#departmentModal').modal('show');
            }
        });
    });

    // บันทึกแผนก — ฟอร์มถูกโหลดผ่าน AJAX และปุ่มเป็น type=button จึงผูกแบบ delegation ที่ปุ่มบันทึก
    $(document).on('click', '#btn_department_save', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var formData = $('#department_form').serialize();

        $btn.prop('disabled', true);
        $.ajax({
            url: "{{ route('department.store') }}",
            method: "POST",
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.status == 200) {
                    $('#departmentModal').modal('hide');
                    oTable.draw();
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });
                } else if (response.status == 400) {
                    var msg = Object.values(response.errors).map(function (e) { return e[0]; }).join('<br>');
                    Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ถูกต้อง', html: msg });
                } else {
                    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: response.message || '' });
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
            },
            complete: function() { $btn.prop('disabled', false); }
        });
    });

</script>

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
</style>

@endsection
