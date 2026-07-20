@extends('./layout/main')


@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-tools text-primary"></i>
                                จัดการเครื่องจักร
                            </h3>

                            <p class="text-muted mb-0">
                                ข้อมูลเครื่องจักรในแต่ละแผนก
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
                            <div class="col-md-3">
                                <select id="searchDept" class="form-select">
                                    <option value="">ทุกแผนก</option>
                                    @foreach($dept_codes as $code)
                                        <option value="{{ $code }}">{{ $code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 ms-auto text-end">
                                <button type="button" class="btn btn-primary" id="btn_add"
                                    data-bs-target="#machineModal">
                                    <i class="ti ti-plus me-1"></i> เพิ่มเครื่องจักร
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
                                        <th class="col-2">แผนก</th>
                                        <th class="col-3">เครื่องจักร (MBX)</th>
                                        <th class="col-3">Speed RPM</th>
                                        <th class="col-2">จัดการ</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Create/Edit Machine Modal -->
    <div class="modal fade modalHeadDecor" id="machineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-tools me-1"></i>เครื่องจักร
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="result_detail">
                    {{-- โหลดฟอร์มผ่าน AJAX --}}
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
                url: "{{ route('machine.datatable') }}",
                data: function(d) {
                    d.search = $('#searchInput').val();
                    d.dept   = $('#searchDept').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            columns: [
                { 'className': "text-center", data: 'rownum', name: 'rownum', orderable: false },
                { 'className': "text-center", data: 'dept', name: 'dept', orderable: false },
                { 'className': "text-center", data: 'MBX', name: 'MBX', orderable: false },
                { 'className': "text-center", data: 'speed_rpm', name: 'speed_rpm', orderable: false, searchable: false },
                { 'className': "text-center", data: 'btnedit', name: 'btnedit', orderable: false, searchable: false },
            ],
            order: [
                [0, 'asc']
            ],
        });
    });

    $(document).on('keyup', '#searchInput', function(e){
        e.preventDefault();
        oTable.draw();
    });

    $(document).on('change', '#searchDept', function(e){
        e.preventDefault();
        oTable.draw();
    });

    // เพิ่มเครื่องจักร — เปิด modal ฟอร์มเปล่า
    $(document).on('click', '#btn_add', function(e){
        e.preventDefault();
        $.ajax({
            url: "{{ route('machine.edit') }}",
            method: "GET",
            data: { id: null },
            success: function(response) {
                $('#result_detail').html(response.data);
                $('#machineModal').modal('show');
            }
        });
    });

    // แก้ไขเครื่องจักร — เปิด modal พร้อมข้อมูลเดิม
    $(document).on('click', '.btn_edit', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
            url: "{{ route('machine.edit') }}",
            method: "GET",
            data: { id: id },
            success: function(response) {
                $('#result_detail').html(response.data);
                $('#machineModal').modal('show');
            }
        });
    });

    // บันทึกเครื่องจักร — ฟอร์มถูกโหลดผ่าน AJAX จึงผูก delegation ที่ปุ่มบันทึก
    $(document).on('click', '#btn_machine_save', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var formData = $('#machine_form').serialize();

        $btn.prop('disabled', true);
        $.ajax({
            url: "{{ route('machine.store') }}",
            method: "POST",
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.status == 200) {
                    $('#machineModal').modal('hide');
                    oTable.draw();
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: response.status == 422 ? 'warning' : 'error',
                        title: response.status == 422 ? 'ข้อมูลไม่ถูกต้อง' : 'เกิดข้อผิดพลาด',
                        text: response.message || ''
                    });
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
            },
            complete: function() { $btn.prop('disabled', false); }
        });
    });

    // ลบเครื่องจักร — ยืนยันก่อนลบ (hard delete)
    $(document).on('click', '.btn_delete', function(e){
        e.preventDefault();
        var id = $(this).data('id');

        Swal.fire({
            icon: 'warning',
            title: 'ยืนยันการลบ',
            text: 'ต้องการลบเครื่องจักรนี้ใช่หรือไม่?',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#d33',
        }).then(function(result){
            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ route('machine.delete') }}",
                method: "POST",
                dataType: 'json',
                data: { _token: "{{ csrf_token() }}", id: id },
                success: function(response) {
                    if (response.status == 200) {
                        oTable.draw();
                        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: response.message,
                            timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: response.message || '' });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด',
                        text: xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่' });
                }
            });
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
