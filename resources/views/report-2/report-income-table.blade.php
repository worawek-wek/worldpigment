<base href="{{ url('/') }}/">
<!-- Icons -->

<!-- Core CSS -->
<link rel="stylesheet" href="assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
<link rel="stylesheet" href="assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
<link rel="stylesheet" href="assets/css/demo.css" />

<!-- Vendors CSS -->
<link rel="stylesheet" href="assets/vendor/libs/node-waves/node-waves.css" />
<link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
<link rel="stylesheet" href="assets/vendor/libs/typeahead-js/typeahead.css" />
<link rel="stylesheet" href="assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
<link rel="stylesheet" href="assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
<link rel="stylesheet" href="assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<link rel="stylesheet" href="assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
<link rel="stylesheet" href="../../assets/vendor/libs/spinkit/spinkit.css" />
                                            <!-- Header -->
                                        <div class="p-4 text-black">
                                            <h4 align="center" class="fw-bold">Trial Balance Report</h4>
                                            
                                            <div class="text-end">
                                                <b>DATE</b> <span class="border-bottom d-inline-block mx-4" style="width:200px;">{{ date('d/m/Y', strtotime($query['date'])) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between my-3">
                                                <div>
                                                    <strong>Balance Brought Forward (ยอดยกมา)</strong>
                                                </div>
                                                <div class="text-end">
                                                    <span>{{ number_format($total_rent_room, 2) }}</span>
                                                </div>
                                            </div>

                                            <!-- Revenue -->
                                            <div class="mb-4">
                                                <div class="fw-bold text-decoration-underline mb-2">Revenue :</div>

                                                <div class="d-flex justify-content-between ps-4">
                                                    <span>ค่าห้องพัก</span>
                                                    <span>{{ number_format($total_rent_room, 2) }}</span>
                                                </div>

                                                <div class="d-flex justify-content-between ps-4">
                                                    <span>ค่าน้ำ</span>
                                                    <span>{{ number_format($water, 2) }}</span>
                                                </div>

                                                <div class="d-flex justify-content-between ps-4">
                                                    <span>ค่าไฟ</span>
                                                    <span>{{ number_format($electricity, 2) }}</span>
                                                </div>

                                                <div class="d-flex justify-content-between ps-4">
                                                    <span>ค่าทำความสะอาด</span>
                                                    <span>{{ number_format($cleaning, 2) }}</span>
                                                </div>

                                                <div class="d-flex justify-content-between ps-4">
                                                    <span>อื่นๆ</span>
                                                    <span>{{ number_format($other, 2) }}</span>
                                                </div>

                                                <div class="d-flex justify-content-between border-top pt-2 mt-2 fw-bold">
                                                    <span>Total</span>
                                                    <span>{{ number_format($total_all, 2) }}</span>
                                                </div>
                                            </div>

                                            <!-- Payment -->
                                            <div class="mb-4">
                                                <div class="fw-bold text-decoration-underline mb-2">Payment :</div>

                                                <div class="d-flex justify-content-between ps-4">
                                                    <span>เงินสด</span>
                                                    <span>{{ number_format($cash, 2) }}</span>
                                                </div>

                                                <div class="d-flex justify-content-between ps-4">
                                                    <span>เงินโอน</span>
                                                    <span>{{ number_format($transfer, 2) }}</span>
                                                </div>

                                                <div class="d-flex justify-content-between ps-4">
                                                    <span>บัตรเครดิต</span>
                                                    <span>{{ number_format($credit, 2) }}</span>
                                                </div>

                                                <div class="d-flex justify-content-between ps-4">
                                                    <span>City Ledger (Agoda/Expedia/Other)</span>
                                                    <span>{{ number_format($pay_other, 2) }}</span>
                                                </div>

                                                <div class="d-flex justify-content-between border-top pt-2 mt-2 fw-bold">
                                                    <span>Total</span>
                                                    <span>{{ number_format($total_all, 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between mt-4">
                                                <div>
                                                    <strong>ยอดยกไป</strong>
                                                </div>
                                                <div class="text-end">
                                                    <span>{{ number_format($total_all, 2) }}</span>
                                                    <div class="border-top mt-2" style="width:150px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                            

    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>