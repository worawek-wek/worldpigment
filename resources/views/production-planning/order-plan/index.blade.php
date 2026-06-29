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
                                แผนการผลิต Order
                            </h3>

                            <p class="text-muted mb-0">
                                รายการแผนการผลิตหลัก (Order) และรายละเอียดที่เกี่ยวข้อง
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
                                placeholder="ค้นหา Planning Code, Orderno, รหัสลูกค้า">
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
                                        <th class="col-1">Inplan</th>
                                        <th class="col-1">Custwant</th>
                                        <th class="col-1">Custno</th>
                                        <th class="col-2">ชื่อลูกค้า</th>
                                        <th class="col-2">สถานะ</th>
                                        <th class="col-1">รายการ</th>
                                        <th class="col-1">Manage</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Order Plan Detail Modal -->
    <div class="modal fade" id="orderPlanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" id="result_detail"></div>
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
                url: "{{ route('production.orderplan.datatable') }}",
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
                { 'className': "text-center", data: 'inplan', name: 'inplan', orderable: false, searchable: false },
                { 'className': "text-center", data: 'custwant', name: 'custwant', orderable: false },
                { 'className': "text-left", data: 'custno', name: 'custno', orderable: false },
                { 'className': "text-left", data: 'custname', name: 'custname', orderable: false, searchable: false },
                { 'className': "text-left", data: 'status_list', name: 'status_list', orderable: false, searchable: false },
                { 'className': "text-center", data: 'item_count', name: 'item_count', orderable: false, searchable: false },
                { 'className': "text-center", data: 'btnedit', name: 'btnedit', orderable: false, searchable: false },
            ],
            order: [
                [0, 'asc']
            ],
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
        let planning_header_id = $(this).data('planning_header_id');
        $.ajax({
            type: 'GET',
            url: '{{ route("production.orderplan.detail") }}',
            dataType: 'json',
            cache: false,
            data: {
                planning_header_id: planning_header_id
            },
            success: function(response) {
                if (response.status == 200) {
                    $('#result_detail').html(response.data);
                    var modal = new bootstrap.Modal(document.getElementById('orderPlanModal'));
                    modal.show();
                }
            },
            error: function(response) {
                console.log("error", response.responseJSON);
            }
        });
    });

</script>
@endsection
