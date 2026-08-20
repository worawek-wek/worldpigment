@extends('./layout/main')


@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-list-check text-primary"></i>
                                จัดการวิธีการผลิต
                            </h3>
                            <p class="text-muted mb-0">
                                รายการวิธีการผลิต (ใช้เป็นตัวเลือกในแผนการผลิต)
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
                                    @foreach($departments as $department)
                                        <option value="{{ $department->name }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 ms-auto text-end">
                                <button type="button" class="btn btn-primary" id="btn_add"
                                    data-bs-target="#prodMethodModal">
                                    <i class="ti ti-plus me-1"></i> เพิ่มวิธีการผลิต
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
                                        <th class="col-4">ชื่อวิธีการผลิต</th>
                                        <th class="col-2">แผนก</th>
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

    <!-- Create/Edit ProdMethod Modal -->
    <div class="modal fade modalHeadDecor" id="prodMethodModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-list-check me-1"></i>วิธีการผลิต
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
                url: "{{ route('prodmethod.datatable') }}",
                data: function(d) {
                    d.search = $('#searchInput').val();
                    d.dept   = $('#searchDept').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            // เปิดให้คลิกหัวคอลัมน์เพื่อ sort ได้ (Yajra จะ ORDER BY ตาม name ของคอลัมน์ให้อัตโนมัติ)
            columns: [
                // # เป็นเลขลำดับที่นับหลัง query จึง sort ไม่ได้ (จะ renumber ตามผลลัพธ์ที่เรียงแล้ว)
                { 'className': "text-center", data: 'rownum', name: 'rownum', orderable: false },
                { 'className': "text-center", data: 'name', name: 'name', orderable: true },
                // sort แผนกตามคอลัมน์จริง dept (แม้จะแสดงผ่าน dept_label)
                { 'className': "text-center", data: 'dept_label', name: 'dept', orderable: true, searchable: false },
                { 'className': "text-center", data: 'sort', name: 'sort', orderable: true },
                // sort สถานะตามคอลัมน์จริง is_active (แม้จะแสดงเป็น switch)
                { 'className': "text-center", data: 'status_switch', name: 'is_active', orderable: true, searchable: false },
                { 'className': "text-center", data: 'btnedit', name: 'btnedit', orderable: false, searchable: false },
            ],
            // เริ่มต้นเรียงตามลำดับ (คอลัมน์ ลำดับ/sort) เหมือนลำดับเดิมที่เคย hard-code ใน controller
            order: [
                [3, 'asc']
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

    // สลับสถานะเปิด-ปิดใช้งานจากตารางโดยตรง
    $(document).on('change', '.switch_status', function(){
        var $sw = $(this);
        var id = $sw.data('id');
        var is_active = $sw.is(':checked') ? 'Y' : 'N';

        $sw.prop('disabled', true);
        $.ajax({
            url: "{{ route('prodmethod.toggle-status') }}",
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
            url: "{{ route('prodmethod.edit') }}",
            method: "GET",
            data: { id: null },
            success: function(response) {
                $('#result_detail').html(response.data);
                $('#prodMethodModal').modal('show');
            }
        });
    });

    $(document).on('click', '.btn_edit', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
            url: "{{ route('prodmethod.edit') }}",
            method: "GET",
            data: { id: id },
            success: function(response) {
                $('#result_detail').html(response.data);
                $('#prodMethodModal').modal('show');
            }
        });
    });

    // บันทึก — ฟอร์มถูกโหลดผ่าน AJAX จึงผูก delegation ที่ปุ่มบันทึก
    $(document).on('click', '#btn_prod_method_save', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var formData = $('#prod_method_master_form').serialize();

        $btn.prop('disabled', true);
        $.ajax({
            url: "{{ route('prodmethod.store') }}",
            method: "POST",
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.status == 200) {
                    $('#prodMethodModal').modal('hide');
                    oTable.draw(false); // draw(false) = คงหน้า pagination เดิม (ไม่เด้งกลับหน้า 1)
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
