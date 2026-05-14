<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">

<head>
    @include('layout/inc_header')
    <title>Dashboard - CRM | Vuexy - Bootstrap Admin Template</title>

</head>
{{-- //////////////////////////////////// --}}
<style>
.table th {
    font-size: 15px;
    font-weight: bold;
    border: 1px solid black
}
.table td {
    padding-top: 14px;
    padding-bottom: 14px;
}
</style>
{{-- //////////////////////////////////// --}}
<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('layout/inc_sidemenu')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                @include('layout/inc_topmenu')

                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row ">
                            <div class="col-sm-12">
                                <div class="card mb-3">
                                    <div class="card-header" id="page_header">
                                        <div class="col-sm-6 mb-4">
                                            <h4 class="mb-0">
                                                <i class="tf-icons ti ti-users text-main ti-md"></i>
                                                รายงานการคืนรายเดือน
                                            </h4>
                                        </div>
                                        <div class="row text-center">
                                            <div class="col-md-3">
                                                <div class="card bg-label-primary">
                                                    <div class="card-body">
                                                        <h6>คืนทั้งหมด</h6>
                                                        <h4>120</h4>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="card bg-label-success">
                                                    <div class="card-body">
                                                        <h6>คืนตรงเวลา</h6>
                                                        <h4>90</h4>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="card bg-label-danger">
                                                    <div class="card-body">
                                                        <h6>คืนล่าช้า</h6>
                                                        <h4>20</h4>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="card bg-label-warning">
                                                    <div class="card-body">
                                                        <h6>ยังไม่คืน</h6>
                                                        <h4>10</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-datatable table-responsive">
                                        <div id="DataTables_Table_0_wrapper"
                                            class="dataTables_wrapper dt-bootstrap5 no-footer">
                                            <div
                                                class="card-header d-flex border-top rounded-0 flex-wrap py-0 flex-column flex-md-row align-items-start">
                                                <div class="me-5 ms-n4 pe-5 mb-n6 mb-md-0">

                                                    <!-- <label><input type="search" class="form-control"
                                                                placeholder="Search Product"
                                                                aria-controls="DataTables_Table_0"></label> -->
                                                    <div class="dataTables_length mx-n2 ms-2"
                                                        id="DataTables_Table_0_length">
                                                        <label>Show
                                                            <select name="limit" name="DataTables_Table_0_length" onchange='loadData("{{$page_url}}/datatable")'
                                                                aria-controls="DataTables_Table_0" class="form-select p_search">
                                                                <option value="7">7</option>
                                                                <option value="10">10</option>
                                                                <option value="20">20</option>
                                                                <option value="50">50</option>
                                                                <option value="70">70</option>
                                                                <option value="100">100</option>
                                                            </select>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div
                                                    class="d-flex justify-content-start justify-content-md-end align-items-baseline">
                                                    <div
                                                        class="dt-action-buttons d-flex flex-column align-items-start align-items-sm-center justify-content-sm-center pt-0 gap-sm-2 gap-sm-0 flex-sm-row">
                                                        <div id="DataTables_Table_0_filter"
                                                            class="dataTables_filter mx-n2 me-2">
                                                            <input type="month" name="month" class="form-control p_search" onchange='loadData("{{$page_url}}/datatable")' value="{{ date('Y-m') }}">
                                                        </div>
                                                        <div class="dt-buttons btn-group flex-wrap d-flex mb-6 mb-sm-0">

                                                            <button
                                                                class="btn btn-secondary add-new btn-label-primary me-2 ms-sm-0 waves-effect waves-light"
                                                                tabindex="0" aria-controls="DataTables_Table_0"
                                                                type="button"
                                                                onclick="printPdf()">
                                                                <span>
                                                                    <i class="ti ti-file-upload me-0 me-sm-1"></i>
                                                                    <span class="d-none d-sm-inline-block">พิมพ์
                                                                    </span>
                                                                </span>
                                                            </button>
                                                            <div class="btn-group">
                                                                <button
                                                                    class="btn btn-success buttons-collection  btn-label-warning waves-effect waves-light"
                                                                    tabindex="0" aria-controls="DataTables_Table_0"
                                                                    type="button" aria-haspopup="dialog"
                                                                    aria-expanded="false"
                                                                    onclick="exportExcel()">
                                                                    <span><i class="ti ti-upload me-1"></i>ดาวน์โหลด
                                                                        Excel
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- <div id="loadData"> --}}
                                            <div>
                                                <table class="datatables-basic table dataTable no-footer dtr-column">
                                                    <thead class="border-top">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>รหัสรายการ</th>
                                                            <th>อุปกรณ์</th>
                                                            <th>ผู้ยืม</th>
                                                            <th>วันที่ยืม</th>
                                                            <th>กำหนดคืน</th>
                                                            <th>วันที่คืน</th>
                                                            <th>วันใช้งาน</th>
                                                            <th>เกิน (วัน)</th>
                                                            <th>สถานะการคืน</th>
                                                            <th>หมายเหตุ</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>1</td>
                                                            <td>BR-0001</td>
                                                            <td>Notebook Dell</td>
                                                            <td>Somchai</td>
                                                            <td>01/05/2026</td>
                                                            <td>03/05/2026</td>
                                                            <td>03/05/2026</td>
                                                            <td>2</td>
                                                            <td>0</td>
                                                            <td><span class="badge bg-success">คืนตรงเวลา</span></td>
                                                            <td>-</td>
                                                        </tr>

                                                        <tr>
                                                            <td>2</td>
                                                            <td>BR-0002</td>
                                                            <td>Projector Epson</td>
                                                            <td>Anong</td>
                                                            <td>28/04/2026</td>
                                                            <td>30/04/2026</td>
                                                            <td>02/05/2026</td>
                                                            <td>4</td>
                                                            <td>2</td>
                                                            <td><span class="badge bg-danger">คืนล่าช้า</span></td>
                                                            <td>คืนช้า</td>
                                                        </tr>

                                                        <tr>
                                                            <td>3</td>
                                                            <td>BR-0003</td>
                                                            <td>Tablet iPad</td>
                                                            <td>Wirot</td>
                                                            <td>01/05/2026</td>
                                                            <td>02/05/2026</td>
                                                            <td>-</td>
                                                            <td>-</td>
                                                            <td>3</td>
                                                            <td><span class="badge bg-warning">ยังไม่คืน</span></td>
                                                            <td>ติดต่อไม่ได้</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div style="width: 1%;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    @include('layout/inc_footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->
    <iframe id="print-iframe" style="display: none;"></iframe>    
    @include('layout/inc_js')
    <script>
        var page = "{{$page_url}}/datatable";
        var searchData = {};
        loadData(page);
        
        function loadData(pages){
            
            $('.p_search').each(function() {
                var inputName = $(this).attr('name'); // ดึงชื่อ attribute 'name' ของ input
                var inputValue = $(this).val(); // ดึงค่า value ของ input
                
                searchData[inputName] = inputValue; // เก็บข้อมูลลงในออบเจ็กต์ searchData
            });

            // alert(page);
            page = pages;
            $.ajax({
                type: "GET",
                url: pages,
                data: searchData,
                success: function(data) {
                    $("#loadData").html(data);
                    summary();
                }
            });
            // alert(page);
        }
        summary();
        function summary(){
            
            $('.p_search').each(function() {
                var inputName = $(this).attr('name'); // ดึงชื่อ attribute 'name' ของ input
                var inputValue = $(this).val(); // ดึงค่า value ของ input
                
                searchData[inputName] = inputValue; // เก็บข้อมูลลงในออบเจ็กต์ searchData
            });

            $.ajax({
                type: "GET",
                url: "{{ $page_url }}/summary",
                data: searchData,
                success: function(data) {
                    $('#page_header').html(data);
                }
            });
        }
        function printPdf() {

            $('.p_search').each(function () {
                var inputName = $(this).attr('name');
                var inputValue = $(this).val();
                searchData[inputName] = inputValue;
            });

            $.ajax({
                url: '/pdf/{{$page_url}}',
                type: 'GET',
                data: searchData,
                success: function(html) {
                    const iframe = document.getElementById('print-iframe');
                    const doc = iframe.contentWindow.document;
                    doc.open();
                    doc.write(html);
                    doc.close();
                    iframe.onload = function () {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    };
                },
                error: function(xhr) {
                    alert('เกิดข้อผิดพลาด');
                    console.error(xhr.responseText);
                }
            });
        }
        function exportExcel() {

            $('.p_search').each(function () {
                var inputName = $(this).attr('name');
                var inputValue = $(this).val();
                searchData[inputName] = inputValue;
            });

            // แปลงเป็น query string
            const queryString = new URLSearchParams(searchData).toString();

            // สร้าง URL พร้อมพารามิเตอร์
            const targetUrl = `/{{$page_url}}/excel?${queryString}`;

            // เปิด URL ใหม่ (แท็บใหม่)
            window.open(targetUrl, '_blank');
        }
    </script>

</body>

</html>