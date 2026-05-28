<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">

<head>
    @include('layout/inc_header')
    <title>Dashboard - CRM | Vuexy - Bootstrap Admin Template</title>

</head>
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

{{-- ฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝ --}}
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">

                    <div>
                        <h3 class="mb-1">
                            <i class="ti ti-palette text-primary"></i>
                            เทียบสี
                        </h3>

                        <p class="text-muted mb-0">
                            จัดการงานเทียบสีและใบนำส่งตัวอย่าง
                        </p>
                    </div>

                    <div>
                        <button class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#colorMatchingModal">

                            <i class="ti ti-plus me-1"></i>
                            สร้างงานเทียบสี
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="row g-4 mb-4">

        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold text-body">งานทั้งหมด</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2 fw-bold text-heading">58</h4>
                                <small class="text-success fw-semibold">
                                    <i class="ti ti-arrow-up"></i> 12%
                                </small>
                            </div>
                            <small class="text-body-secondary">เทียบกับเดือนก่อน</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ti ti-files ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold text-body">รอวัตถุดิบ / รอเทียบ</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2 fw-bold text-heading">12</h4>
                                <small class="text-warning fw-semibold">
                                    <i class="ti ti-clock"></i>
                                </small>
                            </div>
                            <small class="text-body-secondary">ค้างอยู่ในคิว</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-hourglass ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold text-body">กำลังเทียบสี</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2 fw-bold text-heading">20</h4>
                                <small class="text-info fw-semibold">
                                    <i class="ti ti-loader"></i>
                                </small>
                            </div>
                            <small class="text-body-secondary">อยู่ระหว่างดำเนินการ</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ti ti-palette ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold text-body">ส่ง ต.ย. ให้ลูกค้าแล้ว</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2 fw-bold text-heading">26</h4>
                                <small class="text-success fw-semibold">
                                    <i class="ti ti-check"></i>
                                </small>
                            </div>
                            <small class="text-body-secondary">เดือนนี้</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-package-export ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Table -->
    <div class="card">

        <div class="card-header border-bottom">
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">ค้นหา</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text"
                            class="form-control"
                            placeholder="เลขที่ใบนำส่ง / เลขที่ใบส่ง ต.ย. / ลูกค้า">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">ประเภทงาน</label>
                    <select class="form-select">
                        <option>ทุกประเภท</option>
                        <option>เป่าฟิมล์</option>
                        <option>เป่าขวด</option>
                        <option>EXT</option>
                        <option>ROLL</option>
                        <option>INJ</option>
                        <option>CY</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">สถานะ</label>
                    <select class="form-select">
                        <option>ทุกสถานะ</option>
                        <option>รอวัตถุดิบ</option>
                        <option>กำลังเทียบสี</option>
                        <option>เทียบสีเสร็จ</option>
                        <option>ส่ง ต.ย. ให้ลูกค้าแล้ว</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">วันที่ส่งเทียบสี</label>
                    <input type="date" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">ปรับแก้ไข</label>
                    <select class="form-select">
                        <option>ทั้งหมด</option>
                        <option>New</option>
                        <option>Revise 1</option>
                        <option>Revise 2</option>
                    </select>
                </div>

            </div>
        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-light">
                    <tr class="align-middle">
                        <th class="align-middle">
                            เลขที่ใบนำส่ง
                            <br>
                            <small class="text-body-secondary fw-normal">วันที่ส่งเทียบสี</small>
                        </th>
                        <th class="align-middle">ลูกค้า</th>
                        <th class="align-middle">ประเภทงาน</th>
                        <th class="align-middle">สี / นำไปทำชิ้นงาน</th>
                        <th class="align-middle">Color Matcher</th>
                        <th class="align-middle">ปรับแก้ไข</th>
                        <th class="align-middle">เลขที่ใบส่ง ต.ย.</th>
                        <th class="align-middle">สถานะ</th>
                        <th class="align-middle" width="120">จัดการ</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td>
                            <strong class="text-primary">68/0255</strong>
                            <br>
                            <small class="text-muted">28/05/2026</small>
                        </td>

                        <td>
                            <span class="badge bg-label-secondary mb-1">00221</span>
                            <br>
                            <small>บริษัท เมทเทิล พลาสติก จำกัด</small>
                        </td>

                        <td>
                            <span class="badge bg-label-primary">INJ</span>
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:18px;height:18px;background:#f8b4c1;border-radius:4px;border:1px solid #ddd;"></div>
                                <div>
                                    <div class="small fw-semibold">DB PINK-Y AS50%+ABS50%</div>
                                    <small class="text-muted">ตลับแป้ง</small>
                                </div>
                            </div>
                        </td>

                        <td>เมทตา</td>

                        <td>
                            <span class="badge bg-label-info">New</span>
                        </td>

                        <td>
                            <strong>52871-DB</strong>
                        </td>

                        <td>
                            <span class="badge bg-label-info">
                                <i class="ti ti-palette me-1"></i>
                                กำลังเทียบสี
                            </span>
                        </td>

                        <td>
                            <button class="btn btn-sm btn-icon btn-label-primary" title="ดู">
                                <i class="ti ti-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-label-warning" title="แก้ไข">
                                <i class="ti ti-edit"></i>
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <strong class="text-primary">68/0254</strong>
                            <br>
                            <small class="text-muted">27/05/2026</small>
                        </td>

                        <td>
                            <span class="badge bg-label-secondary mb-1">00185</span>
                            <br>
                            <small>บริษัท ไทย พลาส อินดัสตรี จำกัด</small>
                        </td>

                        <td>
                            <span class="badge bg-label-primary">EXT</span>
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:18px;height:18px;background:#1e1e1e;border-radius:4px;border:1px solid #ddd;"></div>
                                <div>
                                    <div class="small fw-semibold">BLACK PE-HD 100%</div>
                                    <small class="text-muted">สายไฟ</small>
                                </div>
                            </div>
                        </td>

                        <td>วารุณี</td>

                        <td>
                            <span class="badge bg-label-warning">Revise 1</span>
                        </td>

                        <td>
                            <strong>52870-BK</strong>
                        </td>

                        <td>
                            <span class="badge bg-label-success">
                                <i class="ti ti-package-export me-1"></i>
                                ส่ง ต.ย. แล้ว
                            </span>
                        </td>

                        <td>
                            <button class="btn btn-sm btn-icon btn-label-primary" title="ดู">
                                <i class="ti ti-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-label-warning" title="แก้ไข">
                                <i class="ti ti-edit"></i>
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <strong class="text-primary">68/0253</strong>
                            <br>
                            <small class="text-muted">26/05/2026</small>
                        </td>

                        <td>
                            <span class="badge bg-label-secondary mb-1">00342</span>
                            <br>
                            <small>บริษัท ออโต้พาร์ท เอเชีย จำกัด</small>
                        </td>

                        <td>
                            <span class="badge bg-label-primary">เป่าขวด</span>
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:18px;height:18px;background:#c4302b;border-radius:4px;border:1px solid #ddd;"></div>
                                <div>
                                    <div class="small fw-semibold">RED RUBY PP 80%+TALC 20%</div>
                                    <small class="text-muted">อะไหล่รถยนต์</small>
                                </div>
                            </div>
                        </td>

                        <td>สมชาย</td>

                        <td>
                            <span class="badge bg-label-info">New</span>
                        </td>

                        <td>
                            <span class="text-muted">-</span>
                        </td>

                        <td>
                            <span class="badge bg-label-warning">
                                <i class="ti ti-hourglass me-1"></i>
                                รอวัตถุดิบ
                            </span>
                        </td>

                        <td>
                            <button class="btn btn-sm btn-icon btn-label-primary" title="ดู">
                                <i class="ti ti-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-label-warning" title="แก้ไข">
                                <i class="ti ti-edit"></i>
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <strong class="text-primary">68/0252</strong>
                            <br>
                            <small class="text-muted">25/05/2026</small>
                        </td>

                        <td>
                            <span class="badge bg-label-secondary mb-1">00114</span>
                            <br>
                            <small>บริษัท แฮนด์ครีเอท จำกัด</small>
                        </td>

                        <td>
                            <span class="badge bg-label-primary">ROLL</span>
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:18px;height:18px;background:#3b6f9c;border-radius:4px;border:1px solid #ddd;"></div>
                                <div>
                                    <div class="small fw-semibold">NAVY BLUE PVC SOFT</div>
                                    <small class="text-muted">หนังเทียม</small>
                                </div>
                            </div>
                        </td>

                        <td>เมทตา</td>

                        <td>
                            <span class="badge bg-label-danger">Revise 2</span>
                        </td>

                        <td>
                            <strong>52865-NB</strong>
                        </td>

                        <td>
                            <span class="badge bg-label-success">
                                <i class="ti ti-package-export me-1"></i>
                                ส่ง ต.ย. แล้ว
                            </span>
                        </td>

                        <td>
                            <button class="btn btn-sm btn-icon btn-label-primary" title="ดู">
                                <i class="ti ti-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-label-warning" title="แก้ไข">
                                <i class="ti ti-edit"></i>
                            </button>
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">แสดง 1-4 จาก 58 รายการ</small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#"><i class="ti ti-chevron-left"></i></a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#"><i class="ti ti-chevron-right"></i></a></li>
                </ul>
            </nav>
        </div>

    </div>

</div>
{{-- ฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝ --}}
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
    <!--add  Modal -->
<!-- Quotation Modal -->


{{-- ฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝ --}}
<!-- Modal -->

<div class="modal modalHeadDecor fade" id="colorMatchingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">

                <h5 class="modal-title">
                    เทียบสี (Color Matching)
                </h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body px-5 py-4" style="background-color: #f8f9fb;">

                {{-- ═══════════════════════════════════════════════════════════ --}}
                {{-- ส่วนที่ 1: ใบนำส่งเทียบสี                                       --}}
                {{-- ═══════════════════════════════════════════════════════════ --}}
                <div class="card shadow-sm mb-4"
                    style="border: 1px solid #cfe4e3; border-bottom-width: 0;">

                    <div class="card-header py-2 px-3 d-flex align-items-center"
                        style="background-color: #e8f6f6; border-bottom: 2px solid #54BAB9; border-radius: 0.375rem 0.375rem 0 0;">
                        <span class="badge me-2 fw-bold"
                            style="background-color: #54BAB9; color: #fff;">1</span>
                        <h6 class="mb-0 fw-semibold" style="color: #1f5d5c;">
                            <i class="ti ti-file-text me-1"></i>
                            ใบนำส่งเทียบสี
                        </h6>
                    </div>

                    <div class="card-body p-4">

                        {{-- ─── กลุ่ม: ข้อมูลลูกค้า ─── --}}
                        <div class="mb-4 pb-3 border-bottom">
                            <div class="d-flex align-items-center mb-3 ps-2"
                                style="border-left: 3px solid #54BAB9;">
                                <i class="ti ti-user-circle me-2" style="color: #2a8a89;"></i>
                                <span class="fw-semibold" style="font-size: 0.95rem; color: #2a8a89;">
                                    ข้อมูลลูกค้า
                                </span>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-2">
                                    <label class="form-label small mb-1">รหัสลูกค้า</label>
                                    <input type="text"
                                        class="form-control"
                                        value="00221">
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label small mb-1">
                                        <span class="badge bg-label-secondary me-1">TH</span>
                                        ชื่อบริษัท (ไทย)
                                    </label>
                                    <input type="text"
                                        class="form-control"
                                        value="บริษัท เมทเทิล พลาสติก จำกัด">
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label small mb-1">
                                        <span class="badge bg-label-secondary me-1">EN</span>
                                        ชื่อบริษัท (อังกฤษ)
                                    </label>
                                    <input type="text"
                                        class="form-control"
                                        value="Metal Plastic Co., Ltd.">
                                </div>

                            </div>
                        </div>

                        {{-- ─── กลุ่ม: ข้อมูลใบนำส่ง ─── --}}
                        <div class="mb-4 pb-3 border-bottom">
                            <div class="d-flex align-items-center mb-3 ps-2"
                                style="border-left: 3px solid #54BAB9;">
                                <i class="ti ti-file-invoice me-2" style="color: #2a8a89;"></i>
                                <span class="fw-semibold" style="font-size: 0.95rem; color: #2a8a89;">
                                    ข้อมูลใบนำส่ง
                                </span>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">เลขที่ใบนำส่งเทียบสี</label>
                                    <input type="text"
                                        class="form-control"
                                        value="68/0255">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">วันที่ส่งเทียบสี</label>
                                    <input type="date"
                                        class="form-control"
                                        value="{{ date('Y-m-d') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">ประเภทงาน</label>
                                    <select class="form-select">
                                        <option>เป่าฟิมล์</option>
                                        <option>เป่าขวด</option>
                                        <option>EXT</option>
                                        <option>ROLL</option>
                                        <option selected>INJ</option>
                                        <option>CY</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small mb-1 text-danger">
                                        <i class="ti ti-asterisk-simple"></i>
                                        ปรับแก้ไขครั้งที่
                                    </label>
                                    <select class="form-select">
                                        <option>New</option>
                                        <option>Revise 1</option>
                                        <option>Revise 2</option>
                                    </select>
                                </div>

                            </div>
                        </div>

                        {{-- ─── กลุ่ม: ข้อมูลสี ─── --}}
                        <div class="mb-4 pb-3 border-bottom">
                            <div class="d-flex align-items-center mb-3 ps-2"
                                style="border-left: 3px solid #54BAB9;">
                                <i class="ti ti-palette me-2" style="color: #2a8a89;"></i>
                                <span class="fw-semibold" style="font-size: 0.95rem; color: #2a8a89;">
                                    ข้อมูลสี
                                </span>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-5">
                                    <label class="form-label small mb-1">สี</label>
                                    <select class="form-select">
                                        <option>DB PINK-Y AS50%+ABS50%</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">คุณสมบัติ</label>
                                    <select class="form-select">
                                        <option></option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small mb-1">นำไปทำชิ้นงาน</label>
                                    <select class="form-select">
                                        <option>ตลับแป้ง</option>
                                        <option>สายไฟ</option>
                                        <option>สายรัด</option>
                                        <option>หลอดโฟม</option>
                                        <option>หนังเทียม</option>
                                        <option>อะไหล่รถยนต์</option>
                                        <option>แฮนด์รถจักรยาน</option>
                                    </select>
                                </div>

                            </div>
                        </div>

                        {{-- ─── กลุ่ม: การติดตามงาน ─── --}}
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-3 ps-2"
                                style="border-left: 3px solid #54BAB9;">
                                <i class="ti ti-clipboard-check me-2" style="color: #2a8a89;"></i>
                                <span class="fw-semibold" style="font-size: 0.95rem; color: #2a8a89;">
                                    การติดตามงาน
                                </span>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">ผู้รับเอกสาร</label>
                                    <select class="form-select">
                                        <option>วารุณี</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">เลขที่ใบรายงานผล</label>
                                    <input type="text"
                                        class="form-control bg-label-secondary"
                                        value="68/0255">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">รอวัตถุดิบ</label>
                                    <input type="text" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">กำหนดเทียบสีเสร็จ</label>
                                    <input type="date" class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label small mb-1">
                                        <i class="ti ti-note me-1"></i>
                                        หมายเหตุ
                                    </label>
                                    <input type="text" class="form-control">
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                {{-- ═══════════════════════════════════════════════════════════ --}}
                {{-- ส่วนที่ 2: ใบส่ง ต.ย. ให้ลูกค้า                                 --}}
                {{-- ═══════════════════════════════════════════════════════════ --}}
                <div class="card shadow-sm mb-3"
                    style="border: 1px solid #dcd8f5; border-bottom-width: 0;">

                    <div class="card-header py-2 px-3 d-flex align-items-center"
                        style="background-color: #efedfd; border-bottom: 2px solid #6c5ce7; border-radius: 0.375rem 0.375rem 0 0;">
                        <span class="badge me-2 fw-bold"
                            style="background-color: #6c5ce7; color: #fff;">2</span>
                        <h6 class="mb-0 fw-semibold" style="color: #3a309d;">
                            <i class="ti ti-package me-1"></i>
                            ใบส่ง ต.ย. ให้ลูกค้า
                        </h6>
                    </div>

                    <div class="card-body p-4">

                        {{-- ─── กลุ่ม: วันที่ดำเนินการ ─── --}}
                        <div class="mb-4 pb-3 border-bottom">
                            <div class="d-flex align-items-center mb-3 ps-2"
                                style="border-left: 3px solid #6c5ce7;">
                                <i class="ti ti-calendar-event me-2" style="color: #4b3fb8;"></i>
                                <span class="fw-semibold" style="font-size: 0.95rem; color: #4b3fb8;">
                                    วันที่ดำเนินการ และผู้เทียบสี
                                </span>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Start Date</label>
                                    <input type="date" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Sample Date</label>
                                    <input type="date" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Ready Date</label>
                                    <input type="date" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Color Matcher</label>
                                    <input type="text"
                                        class="form-control"
                                        value="เมทตา">
                                </div>

                            </div>
                        </div>

                        {{-- ─── กลุ่ม: รายละเอียดผลิตภัณฑ์ ─── --}}
                        <div class="mb-4 pb-3 border-bottom">
                            <div class="d-flex align-items-center mb-3 ps-2"
                                style="border-left: 3px solid #6c5ce7;">
                                <i class="ti ti-flask me-2" style="color: #4b3fb8;"></i>
                                <span class="fw-semibold" style="font-size: 0.95rem; color: #4b3fb8;">
                                    รายละเอียดผลิตภัณฑ์
                                </span>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-7">
                                    <label class="form-label small mb-1">รายละเอียด</label>
                                    <input type="text"
                                        class="form-control bg-label-secondary"
                                        value="DB PINK-Y AS50%+ABS50% สี/">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label small mb-1">ประเภท</label>
                                    <select class="form-select">
                                        <option>2</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">รหัสสินค้า</label>
                                    <input type="text"
                                        class="form-control bg-label-secondary">
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label small mb-1">สีผง</label>
                                    <input type="text" class="form-control">
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label small mb-1">Resin (Match)</label>
                                    <input type="text"
                                        class="form-control bg-label-secondary"
                                        value="AS50%+ABS50%=">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label small mb-1">PHR</label>
                                    <input type="number"
                                        class="form-control text-end"
                                        value="1.0000">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Lot No.</label>
                                    <input type="text"
                                        class="form-control bg-dark text-white"
                                        value="680731/55">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small mb-1">น้ำหนัก (กรัม)</label>
                                    <input type="number"
                                        class="form-control"
                                        value="100">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small mb-1">ตัวอย่างลูกค้า</label>
                                    <select class="form-select">
                                        <option>ตลับแป้ง</option>
                                        <option>สายไฟ</option>
                                        <option>สายรัด</option>
                                        <option>หลอดโฟม</option>
                                        <option>หนังเทียม</option>
                                        <option>อะไหล่รถยนต์</option>
                                        <option>แฮนด์รถจักรยาน</option>
                                    </select>
                                </div>

                            </div>
                        </div>

                        {{-- ─── กลุ่ม: การยกเลิก / Sales ─── --}}
                        <div class="mb-4 pb-3 border-bottom">
                            <div class="d-flex align-items-center mb-3 ps-2"
                                style="border-left: 3px solid #6c5ce7;">
                                <i class="ti ti-alert-circle me-2" style="color: #4b3fb8;"></i>
                                <span class="fw-semibold" style="font-size: 0.95rem; color: #4b3fb8;">
                                    ข้อมูลการขาย / การยกเลิก
                                </span>
                            </div>

                            <div class="row g-3 align-items-end">

                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Saleman Code</label>
                                    <input type="text"
                                        class="form-control"
                                        value="1">
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check p-2 rounded border border-danger-subtle bg-danger-subtle">
                                        <input class="form-check-input" type="checkbox">
                                        <label class="form-check-label text-danger fw-semibold ms-1">
                                            cancel / วัตถุดิบแก้ไข Lot
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small mb-1">สาเหตุที่ยกเลิก</label>
                                    <input type="text"
                                        class="form-control bg-label-secondary">
                                </div>

                                <div class="col-12">
                                    <label class="form-label small mb-1">
                                        <i class="ti ti-note me-1"></i>
                                        หมายเหตุ
                                    </label>
                                    <textarea class="form-control" rows="2"></textarea>
                                </div>

                            </div>
                        </div>

                        {{-- ─── กลุ่ม: เอกสารปิดงาน (ล่างสุด) ─── --}}
                        <div class="p-3 rounded"
                            style="background-color: #fffaf0; border: 1px dashed #ffc107;">
                            <div class="d-flex align-items-center mb-3 ps-2"
                                style="border-left: 3px solid #f0a500;">
                                <i class="ti ti-receipt me-2" style="color: #8a6100;"></i>
                                <span class="fw-semibold" style="font-size: 0.95rem; color: #8a6100;">
                                    เอกสารปิดงาน
                                </span>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label small mb-1 text-danger">
                                        <i class="ti ti-asterisk-simple"></i>
                                        เลขที่ใบส่ง ต.ย. ให้ลูกค้า
                                    </label>
                                    <input type="text"
                                        class="form-control"
                                        value="52871-DB">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small mb-1">วันที่เบิก</label>
                                    <input type="date" class="form-control">
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer justify-content-between flex-wrap gap-2">

                <div class="d-flex gap-2 flex-wrap">

                    <button class="btn btn-label-primary">
                        ค้นหาเลขที่ส่ง
                    </button>

                    <button class="btn btn-label-primary">
                        ค้นหารหัสสี
                    </button>

                </div>

                <div class="d-flex gap-2">

                    <button class="btn btn-label-secondary"
                        data-bs-dismiss="modal">

                        ปิด

                    </button>

                    <button class="btn btn-success px-5">
                        ลงข้อมูล
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>
{{-- ฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝฝ --}}

    <!--edit  Modal -->
    <div class="modal fade modalHeadDecor" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="insert_category" enctype="multipart/form-data">
                @csrf
                <div class="modal-content rounded-0">
                    <div class="modal-header rounded-0">
                        <h5 class="modal-title" id="exampleModalLabel1">เพิ่มข้อมูลหมวดหมู่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    @include('category/form')
                </div>
            </form>
        </div>
    </div>
    <!-- / Layout wrapper -->
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
                    $("#table-data").html(data);
                }
            });
            // alert(page);
        }
        var update_id = 999999999999;
        function view(id){
            update_id = id;
            $.ajax({
                type: "GET",
                url: "{{ $page_url }}/"+id,
                success: function(data) {
                    $("#view").html(data);
                    $('#exampleFormControlSelect'+id).select2({
                        placeholder: 'เลือกผู้เช่า',
                        allowClear: true,
                        dropdownParent: $('#editModal'), // 💥 สำคัญมาก ถ้าอยู่ใน modal
                        width: '100%'
                    });
                    // update_id = id;
                }
            });
        }

        var import_id = 999999999999;
        function getImportForm(id){
            import_id = id;
            $.ajax({
                type: "GET",
                url: "{{ $page_url }}/import/"+id,
                success: function(data) {
                    $("#importStock").html(data);
                }
            });
        }

        function getHistory(id){
            $.ajax({
                type: "GET",
                url: "{{ $page_url }}/history/"+id,
                success: function(data) {
                    $("#history-table").html(data);
                }
            });
        }

        $('#insert_category').on('submit', function(event) {
            event.preventDefault(); // ป้องกันการส่งฟอร์มปกติ
            if(!this.checkValidity()) {
                // ถ้าฟอร์มไม่ถูกต้อง
                this.reportValidity();
                return console.log('ฟอร์มไม่ถูกต้อง');
            }
            // return alert(123);
            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: 'คุณต้องการเพิ่มหมวดหมู่หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก',
                showDenyButton: false,
                didOpen: () => {
                    // โฟกัสที่ปุ่ม confirm
                    Swal.getConfirmButton().focus();
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.getElementById('insert_category');
                    let formData = new FormData(form);
                    formData.append('_token', '{{ csrf_token() }}'); // สำหรับ Laravel CSRF

                    $.ajax({
                        url: '{{$page_url}}/insert', // เปลี่ยน URL เป็นจุดหมายที่ต้องการ
                        type: 'POST',
                        data: formData,
                        contentType: false, // ต้องมีเพื่อให้ส่ง multipart/form-data ได้
                        processData: false,
                        success: function(response) {
                            if(response == true){
                                $('#insert_category')[0].reset();
                                Swal.fire('เพิ่มหมวดหมู่เรียบร้อยแล้ว', '', 'success');
                                $('#addModal').modal('hide');
                                loadData(page);
                            }
                        },
                        error: function(error) {
                            Swal.fire('เกิดข้อผิดพลาด', '', 'error');
                            console.error('เกิดข้อผิดพลาด:', error);
                        }
                    });
                } else if (result.isDismissed) {
                    // Swal.fire('ยกเลิกการดำเนินการ', '', 'info');
                }
            });
        });
        $('#update_category').on('submit', function(event) {
            event.preventDefault(); // ป้องกันการส่งฟอร์มปกติ
            if(!this.checkValidity()) {
                // ถ้าฟอร์มไม่ถูกต้อง
                this.reportValidity();
                return console.log('ฟอร์มไม่ถูกต้อง');
            }
            // return alert(123);
            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: 'คุณต้องการแก้ไขหมวดหมู่หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก',
                showDenyButton: false,
                didOpen: () => {
                    // โฟกัสที่ปุ่ม confirm
                    Swal.getConfirmButton().focus();
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    // ใช้ FormData แทน serialize เพื่อส่งไฟล์ได้
                    let form = document.getElementById('update_category');
                    let formData = new FormData(form);
                    formData.append('_token', '{{ csrf_token() }}'); // สำหรับ Laravel CSRF

                    $.ajax({
                        url: '{{$page_url}}/update/'+update_id, // เปลี่ยน URL เป็นจุดหมายที่ต้องการ
                        type: 'POST',
                        data: formData,
                        contentType: false, // ต้องมีเพื่อให้ส่ง multipart/form-data ได้
                        processData: false,
                        success: function(response) {
                            if(response == true){
                                Swal.fire('แก้ไขหมวดหมู่เรียบร้อยแล้ว', '', 'success');
                                $('#editModal').modal('hide');
                                loadData(page);
                            }
                        },
                        error: function(error) {
                            Swal.fire('เกิดข้อผิดพลาด', '', 'error');
                            console.error('เกิดข้อผิดพลาด:', error);
                        }
                    });
                } else if (result.isDismissed) {
                    // Swal.fire('ยกเลิกการดำเนินการ', '', 'info');
                }
            });
        });
        $('#update_equipment_stocks').on('submit', function(event) {
            event.preventDefault(); // ป้องกันการส่งฟอร์มปกติ
            if(!this.checkValidity()) {
                // ถ้าฟอร์มไม่ถูกต้อง
                this.reportValidity();
                return console.log('ฟอร์มไม่ถูกต้อง');
            }
            // return alert(123);
            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: 'คุณต้องการนำเข้าหมวดหมู่หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก',
                showDenyButton: false,
                didOpen: () => {
                    // โฟกัสที่ปุ่ม confirm
                    Swal.getConfirmButton().focus();
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    // ใช้ FormData แทน serialize เพื่อส่งไฟล์ได้
                    let form = document.getElementById('update_equipment_stocks');
                    let formData = new FormData(form);
                    formData.append('_token', '{{ csrf_token() }}'); // สำหรับ Laravel CSRF

                    $.ajax({
                        url: '{{$page_url}}/update_stock/'+import_id, // เปลี่ยน URL เป็นจุดหมายที่ต้องการ
                        type: 'POST',
                        data: formData,
                        contentType: false, // ต้องมีเพื่อให้ส่ง multipart/form-data ได้
                        processData: false,
                        success: function(response) {
                            if(response == true){
                                Swal.fire('นำเข้าหมวดหมู่เรียบร้อยแล้ว', '', 'success');
                                $('#addStock').modal('hide');
                                loadData(page);
                            }
                        },
                        error: function(error) {
                            Swal.fire('เกิดข้อผิดพลาด', '', 'error');
                            console.error('เกิดข้อผิดพลาด:', error);
                        }
                    });
                } else if (result.isDismissed) {
                    // Swal.fire('ยกเลิกการดำเนินการ', '', 'info');
                }
            });
        });
        
        function Delete(id){
                Swal.fire({
                    title: 'ยืนยันการดำเนินการ?',
                    text: 'คุณต้องการลบหมวดหมู่หรือไม่?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'ตกลง',
                    cancelButtonText: 'ยกเลิก',
                    showDenyButton: false,
                    didOpen: () => {
                        // โฟกัสที่ปุ่ม confirm
                        Swal.getConfirmButton().focus();
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{$page_url}}/'+id, // เปลี่ยน URL เป็นจุดหมายที่ต้องการ
                            type: 'DELETE',
                            data: {
                                _token : "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if(response == true){
                                    loadData(page);
                                    Swal.fire('ลบหมวดหมู่เรียบร้อยแล้ว', '', 'success');
                                }else{
                                    Swal.fire('ไม่สามารถลบบัญชีได้', '', 'error');
                                }
                            },
                            error: function(error) {
                                Swal.fire('เกิดข้อผิดพลาด', '', 'error');
                                console.error('เกิดข้อผิดพลาด:', error);
                            }
                        });
                    } else if (result.isDismissed) {
                        // Swal.fire('ยกเลิกการดำเนินการ', '', 'info');
                    }
                });
        }
</script>
</body>

</html>