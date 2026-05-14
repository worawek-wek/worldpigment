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
}
.table td {
    padding-top: 14px;
    padding-bottom: 14px;
}
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
                                                    รายงานย้ายออก
                                                </h4>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="row justify-content-center py-4">
                                                    <div class="col-sm-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="badge rounded bg-label-primary me-3 p-2"><i
                                                                    class="ti ti-door-exit ti-lg"></i></div>
                                                            <div class="card-info">
                                                                <h5 class="mb-0 text-primary">{{ $all_room }} ห้อง</h5>
                                                                <small>ย้ายออกทั้งหมด</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="badge rounded bg-label-danger me-3 p-2"><i
                                                                    class="ti ti-database ti-lg"></i></div>
                                                            <div class="card-info">
                                                                <h5 class="mb-0 text-danger">{{ number_format($yod_kun) }} บาท</h5>
                                                                <small>รวมจำนวนเงินที่คืนผู้เช่า</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="badge rounded bg-label-success me-3 p-2"><i
                                                                    class="ti ti-currency-dollar ti-lg"></i></div>
                                                            <div class="card-info">
                                                                <h5 class="mb-0 text-success">{{ number_format(abs($keb)) }} บาท</h5>
                                                                <small>รวมที่เก็บจากผู้เช่าเพิ่ม</small>
                                                            </div>
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
                                                            <select name="DataTables_Table_0_length"
                                                                aria-controls="DataTables_Table_0" class="form-select">
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
                                                            {{-- <button
                                                                class="btn btn-secondary add-new btn-label-primary me-2 ms-sm-0 waves-effect waves-light"
                                                                tabindex="0" aria-controls="DataTables_Table_0"
                                                                type="button">
                                                                <span>
                                                                    <i class="ti ti-file-upload me-0 me-sm-1"></i>
                                                                    <span class="d-none d-sm-inline-block">พิมพ์
                                                                    </span>
                                                                </span>
                                                            </button> --}}
                                                            <div class="btn-group">
                                                                <button
                                                                    class="btn btn-success buttons-collection  btn-label-warning waves-effect waves-light"
                                                                    tabindex="0" aria-controls="DataTables_Table_0"
                                                                    type="button" aria-haspopup="dialog"
                                                                    aria-expanded="false"
                                                                    onclick="export_excel()">
                                                                    <span><i class="ti ti-upload me-1"></i>ดาวน์โหลด
                                                                        Excel
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="table-data">

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
    <!-- Modal -->
    <div class="modal fade modalHeadDecor" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content rounded-0">
                <div class="modal-header rounded-0">
                    <h5 class="modal-title" id="exampleModalLabel1">ห้อง A101 <small>หนี้สูญ</small></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap">
                            <thead>
                                <tr class="text-center bg-label-warning">
                                    <th>ลำดับ</th>
                                    <th>รอบบิล</th>
                                    <th>ค่าเช่าห้อง</th>
                                    <th>ค่าน้ำ</th>
                                    <th>ค่าไฟ</th>
                                    <th>ชำระแล้ว</th>
                                    <th>รวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center">1</td>
                                    <td>4/2024</td>
                                    <td class="text-end">3,500</td>
                                    <td class="text-end">6,000</td>
                                    <td class="text-end">2,786</td>
                                    <td class="text-end">0</td>
                                    <td class="text-end">6,386</td>
                                </tr>
                                <tr>
                                    <td class="text-center">2</td>
                                    <td>4/2024</td>
                                    <td class="text-end">3,500</td>
                                    <td class="text-end">6,000</td>
                                    <td class="text-end">2,618</td>
                                    <td class="text-end">0</td>
                                    <td class="text-end">6,218</td>
                                </tr>
                                <tr>
                                    <td class="text-center">3</td>
                                    <td>2/2024</td>
                                    <td class="text-end">3,500</td>
                                    <td class="text-end">6,000</td>
                                    <td class="text-end">1,974</td>
                                    <td class="text-end">2,500</td>
                                    <td class="text-end">3,118</td>
                                </tr>
                                <tr>
                                    <td class="text-center">4</td>
                                    <td>1/2024</td>
                                    <td class="text-end">3,500</td>
                                    <td class="text-end">-</td>
                                    <td class="text-end">1,974</td>
                                    <td class="text-end">0</td>
                                    <td class="text-end">6,218</td>
                                </tr>
                                <tr class="bg-label-danger">
                                    <th colspan="4"></th>
                                    <th>ยอดรวม</th>
                                    <th colspan="2" class="text-end text-danger">-15,722</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer rounded-0 justify-content-start">
                    <button type="button" class="btn btn-label-primary waves-effect"><span
                            class="ti-md ti ti-printer me-2"></span>พิมพ์ใบย้ายออก</button>
                </div>
            </div>
        </div>
    </div>
    @include('layout/inc_js')

</body>
    <iframe id="print-iframe" style="display: none;"></iframe>        
</html>
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
                    $("#table-data").html(data);
                    
                    $('.select2Position2').select2({
                        placeholder: 'เลือก',
                    });

                }
            });
            // alert(page);
        }
        function printPdfReceipt(id) {
            $.ajax({
                url: '/pdf/receipt/'+id,
                type: 'GET',
                success: function(html) {
                    const iframe = document.getElementById('print-iframe');
                    const doc = iframe.contentWindow.document;
                    doc.open();
                    doc.write(html);
                    doc.close();

                    // รอโหลดก่อนค่อยพิมพ์
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
    
        function export_excel() {
            $('.p_search').each(function () {
                var inputName = $(this).attr('name');
                var inputValue = $(this).val();
                searchData[inputName] = inputValue;
            });

            // สร้าง query string
            const queryString = new URLSearchParams(searchData).toString();

            // เปิดลิงก์พร้อม query string ในแท็บใหม่
            window.open('{{$page_url}}/export/excel?' + queryString, '_blank');
        }
</script>