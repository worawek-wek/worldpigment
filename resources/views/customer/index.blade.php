<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">

<head>
    @include('layout/inc_header')
    <title>C-ฐานข้อมูลลูกค้า | World Pigment</title>
</head>

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

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                                        <div>
                                            <h3 class="mb-1">
                                                <i class="ti ti-address-book text-primary"></i>
                                                ฐานข้อมูลลูกค้า
                                            </h3>
                                            <p class="text-muted mb-0">
                                                ข้อมูลลูกค้าและราคาสินค้า
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="ti ti-address-book text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3 mb-1">หน้านี้อยู่ระหว่างพัฒนา</h5>
                                <p class="text-muted mb-0">ยังไม่มีข้อมูลลูกค้า — จะเพิ่ม logic การดึงข้อมูลภายหลัง</p>
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
    @include('layout/inc_js')
</body>

</html>
