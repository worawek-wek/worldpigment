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
                                วางแผนการผลิต
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
                            <div class="col-md-12" style="text-align: right">
                                <button class="btn btn-primary" id="btn_add">
                                    <i class="ti ti-plus me-1"></i>
                                    สร้างแผนการผลิต
                                </button>
                            </div>
                        </div>
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
                                <input type="date" class="form-control">
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
                                        <th class="col-1">Company</th>
                                        <th class="col-2">Mdate</th>
                                        <th class="col-2">Itemno</th>
                                        <th class="col-1">Quantity</th>
                                        <th class="col-2">MachineNo</th>
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

    <!-- Planning Modal -->
    <div class="modal fade" id="planningModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" id="result_detail">

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
                url: "{{ route('production.planning.datatable') }}",
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
                { 'className': "text-center", data: 'orderno', name: 'orderno', orderable: false },
                { 'className': "text-center", data: 'company', name: 'company', orderable: false },
                { 'className': "text-center", data: 'mdate', name: 'mdate', orderable: false },
                { 'className': "text-left", data: 'itemno', name: 'itemno', orderable: false },
                { 'className': "text-left", data: 'quantity', name: 'quantity', orderable: false },
                { 'className': "text-left", data: 'machine_no', name: 'machine_no', orderable: false },
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

    $(document).on('click', '#btn_add', function(e){
        e.preventDefault();
        let planning_id = '';
        $.ajax({
            type: 'GET',
            url: '{{ route("production.planning.edit") }}',
            dataType: 'json',
            cache: false,
            data: {
                planning_id: planning_id
            },
            success: function(response) {
                if (response.status == 200) {
                    $('#result_detail').html(response.data);
                    const modal = new bootstrap.Modal(document.getElementById('planningModal'));
                    modal.show();
                }
            },
            error: function(response) {
                console.log("error");
                console.log(response.responseJSON);
            }
        })
    });

    $(document).on('click', '.btn_edit', function(e){
        e.preventDefault();
        let planning_id = $(this).data('planning_id');
        $.ajax({
            type: 'GET',
            url: '{{ route("production.planning.edit") }}',
            dataType: 'json',
            cache: false,
            data: {
                planning_id: planning_id
            },
            success: function(response) {
                if (response.status == 200) {
                    $('#result_detail').html(response.data);
                    const modal = new bootstrap.Modal(document.getElementById('planningModal'));
                    modal.show();
                }
            },
            error: function(response) {
                console.log("error");
                console.log(response.responseJSON);
            }
        });
    })


</script>
@endsection
