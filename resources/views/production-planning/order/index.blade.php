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
                                placeholder="ค้นหาเลขที่ใบสั่งซื้อ, รหัสลูกค้า, ขื่อลูกค้า">
                            </div>
                            <div class="col-md-2">
                                <select id="searchCompany" class="form-select">
                                    <option value="">ทุกแผนก</option>
                                    <option value="CP">CP</option>
                                    <option value="DB">DB</option>
                                    <option value="MB">MB</option>
                                    <option value="SPP">SPP</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select">
                                    <option>ทุกสถานะ</option>
                                    <option>Pending</option>
                                    <option>Production</option>
                                    <option>Completed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date"
                                    class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="card-header">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="col-1">#</th>
                                        <th class="col-1">Orderno</th>
                                        <th class="col-2">Mdate</th>
                                        <th class="col-1">Company</th>
                                        <th class="col-1">Custno</th>
                                        <th class="col-3">Custname</th>
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


    <!-- Sale Order Modal -->
    <div class="modal fade" id="saleorderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" style="padding: 1.25rem 1.5rem 1.25rem; color: white; background-color: #54BAB9; position: relative;">
                        ใบสั่งซื้อ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="result_detail">

                </div>

            </div>
        </div>
    </div>
    <!-- Sale Order Modal -->

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
                url: "{{ route('production.order.datatable') }}",
                data: function(d) {
                    d.search = $('#searchInput').val();
                    d.company = $('#searchCompany').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('AJAX Error:', error, thrown);
                }
            },
            columns: [
                { 'className': "text-center", data: 'rownum', name: 'rownum', orderable: false },
                { 'className': "text-center", data: 'Orderno', name: 'Orderno', orderable: false },
                { 'className': "text-center", data: 'Mdate', name: 'Mdate', orderable: false },
                { 'className': "text-center", data: 'Company', name: 'Company', orderable: false },
                { 'className': "text-left", data: 'Custno', name: 'Custno', orderable: false },
                { 'className': "text-left", data: 'Custname', name: 'Custname', orderable: false },
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

    $(document).on('change', '#searchCompany', function(e){
        e.preventDefault();
        oTable.draw();
    });

    $(document).on('click', '.btn_view', function(e){
        e.preventDefault();
        let Orderno = $(this).data('orderno');
        $.ajax({
            type: 'GET',
            url: '{{ route("production.order.detail") }}',
            dataType: 'json',
            cache: false,
            data: {
                orderno: Orderno
            },
            success: function(response) {
                if (response.status == 200) {
                    $('#result_detail').html(response.data);
                    const modal = new bootstrap.Modal(document.getElementById('saleorderModal'));
                    modal.show();
                }
            },
            error: function(response) {
                console.log("error");
                console.log(response.responseJSON);
            }
        });
    })

    $(document).on('click', '.btn_edit', function(e){
        e.preventDefault();
        let Orderno = $(this).data('orderno');
        $.ajax({
            type: 'GET',
            url: '{{ route("production.order.convertplanning") }}',
            dataType: 'json',
            cache: false,
            data: {
                orderno: Orderno
            },
            success: function(response) {
                if (response.status == 200) {

                    Swal.fire({
                        title: "Create Complate.!",
                        text: "successfuly create complate",
                        icon: "success",
                        showConfirmButton: false,
                        timer: 1500
                    });

                }else{
                    Swal.fire({
                        title: "Can not save!",
                        text: response.message,
                        icon: "error",
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            },
            error: function(response) {
                console.log("error");
                console.log(response.responseJSON);
            }
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
