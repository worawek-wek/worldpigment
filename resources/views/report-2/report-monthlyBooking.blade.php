<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">

<head>
    @include('layout/inc_header')
    <title>Dashboard - CRM | Vuexy - Bootstrap Admin Template</title>

</head>

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
                                    <div class="card-header">
                                        <div class="row g-3 justify-content-between mb-4">
                                            <div class="col-sm-12">
                                                <h4 class="mb-0">
                                                    <i class="tf-icons ti ti-chart-pie-3 text-main ti-md"></i>
                                                    รายงานจองรายเดือน
                                                </h4>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="card card-border-shadow-success h-100">
                                                    <div
                                                        class="card-body d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center">
                                                            <div class="card-icon me-3">
                                                                <span class="badge bg-label-success rounded p-2">
                                                                    <i class="ti ti-check ti-26px"></i>
                                                                </span>
                                                            </div>
                                                            <h4 class="mb-0 me-2 text-success">มีการจองทั้งหมด
                                                                <span id="all-booking">

                                                                </span> 
                                                                 การจอง
                                                            </h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="card card-border-shadow-danger h-100">
                                                    <div
                                                        class="card-body d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center">
                                                            <div class="card-icon me-3">
                                                                <span class="badge bg-label-danger rounded p-2">
                                                                    <i class="ti ti-x ti-26px"></i>
                                                                </span>
                                                            </div>
                                                            <h4 class="mb-0 me-2 text-danger">ถูกยกเลิกทั้งหมด 
                                                                <span id="all-booking-cancel">

                                                                </span>
                                                                การจอง
                                                            </h4>
                                                        </div>
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
                                                            <select name="limit" onchange='loadData("{{$page_url}}/datatable")'
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
                                            <div id="loadData">

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
                }
            });
            // alert(page);
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