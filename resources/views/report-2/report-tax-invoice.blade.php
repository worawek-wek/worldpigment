<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">

<head>
    @include('layout/inc_header')
    <title>Dashboard - CRM | Vuexy - Bootstrap Admin Template</title>
<!-- jQuery (ต้องมี เพราะคุณใช้ ajax) -->
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

<!-- Tom Select CSS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">

<!-- Tom Select JS -->
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<!-- SweetAlert (optional) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                                                    รายงานใบกำกับภาษีรายวัน/รายเดือน
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row border-bottom border-top border-light p-3">
                                        <div class="row mt-3">
                                            <div class="col-md-12 select_off" style="padding-right: unset !important;">
                                                <div class="row">
                                                    <div class="col-sm-3">
                                                        <label>เลือกห้อง</label>
                                                        <select name="ref_room_id" id="select2Room" class="">
                                                            <option selected disabled hidden value="">เลือกห้อง</option>
                                                            @foreach ($room as $r)
                                                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <label>เลือกใบแจ้งหนี้</label>
                                                        <select name="ref_rent_bill_id" id="select2District99" class="p_search">
                                                            <option selected disabled hidden value="">เลือกใบแจ้งหนี้</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-sm-6 d-flex align-items-end">
                                                        <button
                                                            class="btn btn-secondary add-new btn-label-primary me-2 ms-sm-0 waves-effect waves-light"
                                                            type="button"
                                                            onclick="printPdf()">
                                                            <span>
                                                                <i class="ti ti-file-upload me-0 me-sm-1"></i>
                                                                <span class="d-none d-sm-inline-block">พิมพ์</span>
                                                            </span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body px-0 pt-0">
                                        <div class="border border-1 pb-4" id="loadData" style="margin: 40px 20%;">
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
    <iframe id="print-iframe" style="display: none;"></iframe>    
    <!-- / Layout wrapper -->
    @include('layout/inc_js')
<script>
    var page = "{{$page_url}}/datatable";
    var searchData = {};
    // loadData(page);
    
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

                    setTimeout(() => {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    }, 300); // รอ render
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
<script>
    let tomRoom = null;
    let tomDistrict = null;

    $(document).ready(function () {

        // 🔥 init TomSelect
        tomRoom = new TomSelect("#select2Room", {
            create: false,
            maxItems: 1
        });

        tomDistrict = new TomSelect("#select2District99", {
            create: false,
            maxItems: 1
        });


        // 🔥 จังหวัด → โหลดอำเภอ
        $('#select2Room').on('change', function () {
            let roomId = $(this).val();
            if (!roomId) return;

            $('#loadingOverlay').show();

            tomDistrict.clear();
            tomDistrict.clearOptions();

            $('#zipcode').val('');

            $.get('{{$page_url}}/get-invoice-by-room/' + roomId, function (data) {

                data.forEach(item => {
                    tomDistrict.addOption({
                        value: item.id,
                        text: item.type_name+" / "+item.invoice_number
                    });
                });

                tomDistrict.refreshOptions(false);
                $('#loadingOverlay').hide();

            }).fail(function () {
                $('#loadingOverlay').hide();
                Swal.fire('เกิดข้อผิดพลาด', '', 'error');
            });
        });

        // 🔥 อำเภอ → โหลดตำบล
        $('#select2District99').on('change', function () {
            let ref_rent_bill_id = $(this).val();
            searchData[ref_rent_bill_id] = ref_rent_bill_id; // เก็บข้อมูลลงในออบเจ็กต์ searchData
            loadData(page);
            // alert(districtId)
            // ref_rent_bill_id

            // if (!districtId) return;

            // $('#loadingOverlay').show();

            // tomSubdistrict.clear();
            // tomSubdistrict.clearOptions();
            // $('#zipcode').val('');

            // $.get('/get-subdistricts/' + districtId, function (data) {

            //     data.forEach(item => {
            //         tomSubdistrict.addOption({
            //             value: item.id,
            //             text: item.name_in_thai,
            //             zipcode: item.zip_code
            //         });
            //     });

            //     tomSubdistrict.refreshOptions(false);
            //     $('#loadingOverlay').hide();

            // }).fail(function () {
            //     $('#loadingOverlay').hide();
            //     Swal.fire('เกิดข้อผิดพลาด', '', 'error');
            // });
        });

        // 🔥 ตำบล → ใส่ zipcode
        $('#select2Subdistrict').on('change', function () {
            let val = $(this).val();
            let option = tomSubdistrict.options[val];
            $('#zipcode').val(option?.zipcode || '');
        });

    });
</script>
</body>

</html>