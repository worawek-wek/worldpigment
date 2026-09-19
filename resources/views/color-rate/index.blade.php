@extends('./layout/main')


@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-layers-difference text-primary"></i>
                                จัดการกลุ่มราคา
                            </h3>
                            <p class="text-muted mb-0">
                                ราคาขั้นต่ำของกลุ่ม A / B / C แยกตามรหัสสินค้า (zcolorrate)
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-primary" id="btn_add">
                                <i class="ti ti-plus me-1"></i> เพิ่มข้อมูล
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- เกณฑ์แบ่งกลุ่ม — มาจาก PriceApprovalController::PRICE_GROUPS (อ่านอย่างเดียว) --}}
            <div class="col-12 mt-4">
                <div class="alert alert-info mb-0 py-2">
                    <i class="ti ti-info-circle me-1"></i>
                    แบ่งกลุ่มตาม <strong>น้ำหนักรวมของใบสั่งซื้อ</strong> —
                    @foreach ($groups as $g)
                        <strong>{{ $g['label'] }}</strong>@if (!$loop->last) · @endif
                    @endforeach
                    <div class="small mt-1">
                        ราคาที่ตั้งไว้ถูกใช้เป็น <strong>ราคาขั้นต่ำ</strong> ตอนบันทึกใบสั่งซื้อ
                        (ช่อง "ขั้นต่ำ" ในกล่องราคา) — ขายต่ำกว่านี้ต้องขออนุมัติราคาพิเศษก่อน
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
                                    placeholder="ค้นหารหัสสินค้า...">
                            </div>
                        </div>
                    </div>

                    <div class="card-header">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="col-1">#</th>
                                        <th class="col-3">รหัสสินค้า</th>
                                        <th class="col-2">กลุ่ม A</th>
                                        <th class="col-2">กลุ่ม B</th>
                                        <th class="col-2">กลุ่ม C</th>
                                        <th class="col-2">วันที่ปรับราคา</th>
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

    <!-- Create/Edit Modal -->
    <div class="modal fade modalHeadDecor" id="colorRateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-layers-difference me-1"></i>กลุ่มราคา
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
                url: "{{ route('colorrate.datatable') }}",
                data: function(d) {
                    d.search = $('#searchInput').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            columns: [
                // # นับหลัง query จึง sort ไม่ได้
                { 'className': "text-center", data: 'rownum',  name: 'rownum',  orderable: false },
                { 'className': "text-left",   data: 'colorno', name: 'colorno', orderable: true },
                { 'className': "text-end",    data: 'rate_A',  name: 'rate_A',  orderable: true, searchable: false },
                { 'className': "text-end",    data: 'rate_B',  name: 'rate_B',  orderable: true, searchable: false },
                { 'className': "text-end",    data: 'rate_C',  name: 'rate_C',  orderable: true, searchable: false },
                { 'className': "text-center", data: 'RDate',   name: 'RDate',   orderable: true, searchable: false },
                { 'className': "text-center", data: 'btnaction', name: 'btnaction', orderable: false, searchable: false },
            ],
            // ลำดับเริ่มต้นเติมที่ controller เมื่อผู้ใช้ยังไม่คลิก sort
            order: [],
        });
    });

    $(document).on('keyup', '#searchInput', function(e){
        e.preventDefault();
        oTable.draw();
    });

    // ปฏิทินของช่อง "วันที่ปรับราคา" — ฟอร์มมาทาง AJAX จึงต้อง init ทุกครั้งหลังโหลด
    function initColorRatePicker(){
        flatpickr('.flatpickr-colorrate', {
            dateFormat: 'd/m/Y',
            allowInput: true,
            static: true,
            disableMobile: true
        });
    }

    function openColorRateForm(id){
        $.ajax({
            url: "{{ route('colorrate.edit') }}",
            method: "GET",
            data: { id: id },
            success: function(response) {
                $('#result_detail').html(response.data);
                initColorRatePicker();
                $('#colorRateModal').modal('show');
            },
            error: function(){
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'โหลดฟอร์มไม่สำเร็จ' });
            }
        });
    }

    $(document).on('click', '#btn_add', function(e){
        e.preventDefault();
        openColorRateForm(null);
    });

    $(document).on('click', '.btn_edit', function(e){
        e.preventDefault();
        openColorRateForm($(this).data('id'));
    });

    // ลบรายการ
    $(document).on('click', '.btn_delete', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        Swal.fire({
            title: 'ยืนยันการลบ',
            html: 'ต้องการลบกลุ่มราคาของรหัส <strong>' + id + '</strong> หรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#d33',
        }).then(function(result){
            if (!result.isConfirmed) return;
            $.ajax({
                url: "{{ route('colorrate.delete') }}",
                method: "POST",
                dataType: 'json',
                data: { _token: "{{ csrf_token() }}", id: id },
                success: function(res){
                    if (res.status == 200) {
                        oTable.draw(false); // draw(false) = คงหน้า pagination เดิม
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
    $(document).on('click', '#btn_colorrate_save', function(e) {
        e.preventDefault();
        var $btn = $(this);

        // ช่องราคาใช้ class js-comma → ต้องถอดคอมมาก่อน serialize ไม่งั้น '1,234.50' ลง DB เป็น 1.00
        stripCommaFields('#colorrate_master_form');
        var formData = $('#colorrate_master_form').serialize();

        $btn.prop('disabled', true);
        $.ajax({
            url: "{{ route('colorrate.store') }}",
            method: "POST",
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.status == 200) {
                    $('#colorRateModal').modal('hide');
                    oTable.draw(false);
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

    /* ปฏิทินต้องอยู่เหนือ modal (theme ตั้ง z-index ไว้แค่ 999) */
    .flatpickr-calendar { z-index: 1092; }
</style>

@endsection
