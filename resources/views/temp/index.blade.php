@extends('./layout/main')


@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-list text-primary"></i>
                                จัดการ Temp
                            </h3>
                            <p class="text-muted mb-0">
                                รายการข้อมูล Temp
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
                                <select id="searchStatus" class="form-select">
                                    <option value="">ทุกสถานะ</option>
                                    <option value="Y">เปิดใช้งาน</option>
                                    <option value="N">ปิดใช้งาน</option>
                                </select>
                            </div>
                            <div class="col-md-3 ms-auto text-end">
                                <button type="button" class="btn btn-primary" id="btn_add"
                                    data-bs-target="#tempModal">
                                    <i class="ti ti-plus me-1"></i> เพิ่มข้อมูล
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
                                        <th class="col-6">ชื่อ</th>
                                        <th class="col-1">ลำดับ</th>
                                        <th class="col-1">สถานะ</th>
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

    <!-- Create/Edit Temp Modal -->
    <div class="modal fade modalHeadDecor" id="tempModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-list me-1"></i>Temp
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
                url: "{{ route('temp.datatable') }}",
                data: function(d) {
                    d.search = $('#searchInput').val();
                    d.status = $('#searchStatus').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            columns: [
                { 'className': "text-center", data: 'rownum', name: 'rownum', orderable: false },
                { 'className': "text-left", data: 'name', name: 'name', orderable: false },
                { 'className': "text-center", data: 'sort', name: 'sort', orderable: false },
                { 'className': "text-center", data: 'status_switch', name: 'status_switch', orderable: false, searchable: false },
                { 'className': "text-center", data: 'btnaction', name: 'btnaction', orderable: false, searchable: false },
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

    $(document).on('change', '#searchStatus', function(e){
        e.preventDefault();
        oTable.draw();
    });

    // สลับสถานะเปิด-ปิดใช้งานจากตารางโดยตรง
    $(document).on('change', '.switch_status', function(){
        var $sw = $(this);
        var id = $sw.data('id');
        var is_active = $sw.is(':checked') ? 'Y' : 'N';

        $sw.prop('disabled', true);
        $.ajax({
            url: "{{ route('temp.toggle-status') }}",
            method: "POST",
            dataType: 'json',
            data: { _token: "{{ csrf_token() }}", id: id, is_active: is_active },
            success: function(res){
                if (res.status == 200) {
                    Swal.fire({ icon: 'success', title: res.message, toast: true,
                        position: 'top-end', timer: 1500, showConfirmButton: false });
                } else {
                    $sw.prop('checked', !$sw.is(':checked'));
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: res.message || '' });
                }
            },
            error: function(xhr){
                $sw.prop('checked', !$sw.is(':checked'));
                Swal.fire({ icon: 'error', title: 'ผิดพลาด',
                    text: xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่' });
            },
            complete: function(){ $sw.prop('disabled', false); }
        });
    });

    $(document).on('click', '#btn_add', function(e){
        e.preventDefault();
        $.ajax({
            url: "{{ route('temp.edit') }}",
            method: "GET",
            data: { id: null },
            success: function(response) {
                $('#result_detail').html(response.data);
                $('#tempModal').modal('show');
            }
        });
    });

    $(document).on('click', '.btn_edit', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
            url: "{{ route('temp.edit') }}",
            method: "GET",
            data: { id: id },
            success: function(response) {
                $('#result_detail').html(response.data);
                $('#tempModal').modal('show');
            }
        });
    });

    // ลบรายการ
    $(document).on('click', '.btn_delete', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        Swal.fire({
            title: 'ยืนยันการลบ',
            text: 'ต้องการลบรายการนี้หรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#d33',
        }).then(function(result){
            if (!result.isConfirmed) return;
            $.ajax({
                url: "{{ route('temp.delete') }}",
                method: "POST",
                dataType: 'json',
                data: { _token: "{{ csrf_token() }}", id: id },
                success: function(res){
                    if (res.status == 200) {
                        oTable.draw();
                        Swal.fire({ icon: 'success', title: res.message, toast: true,
                            position: 'top-end', timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: res.message || '' });
                    }
                },
                error: function(xhr){
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด',
                        text: xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่' });
                }
            });
        });
    });

    // บันทึก — ฟอร์มถูกโหลดผ่าน AJAX จึงผูก delegation ที่ปุ่มบันทึก
    $(document).on('click', '#btn_temp_save', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var formData = $('#temp_master_form').serialize();

        $btn.prop('disabled', true);
        $.ajax({
            url: "{{ route('temp.store') }}",
            method: "POST",
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.status == 200) {
                    $('#tempModal').modal('hide');
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
