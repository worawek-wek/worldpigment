<meta charset="utf-8" />
<meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />


<meta name="description" content="" />
<base href="{{ url('/') }}/">
<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="assets/img/illustrations/main.png" />

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
    rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@100;200;300;400;500;600;700&display=swap"
    rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="assets/vendor/fonts/fontawesome.css" />
<link rel="stylesheet" href="assets/vendor/fonts/tabler-icons.css" />
<link rel="stylesheet" href="assets/vendor/fonts/flag-icons.css" />

<!-- Core CSS -->
<link rel="stylesheet" href="assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
<link rel="stylesheet" href="assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
<link rel="stylesheet" href="assets/css/demo.css" />

<!-- Vendors CSS -->
<link rel="stylesheet" href="assets/vendor/libs/node-waves/node-waves.css" />
<link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
<link rel="stylesheet" href="assets/vendor/libs/typeahead-js/typeahead.css" />
<link rel="stylesheet" href="assets/vendor/libs/flatpickr/flatpickr.css" />
<link rel="stylesheet" href="assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
{{-- <link rel="stylesheet" href="assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" /> --}}
<link rel="stylesheet" href="assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
{{-- <link rel="stylesheet" href="assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" /> --}}
<link rel="stylesheet" href="assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css" />
{{-- <link rel="stylesheet" href="assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="assets/vendor/libs/bootstrap-select/bootstrap-select.css" /> --}}
{{-- <link rel="stylesheet" href="assets/vendor/libs/sweetalert2/sweetalert2.css" /> --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<link rel="stylesheet" href="assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
<link rel="stylesheet" href="../../assets/vendor/libs/spinkit/spinkit.css" />
    <style>
        .swal2-container {
            z-index: 9999 !important; /* ปรับ z-index ของ SweetAlert */
        }
    </style>
    <style>
        .custom-title {
            margin-bottom: 50px; /* ปรับค่าตามต้องการ */
        }
    </style>

    {{-- ─────────────────────────────────────────────────────────────────
         WIP marker (สำหรับ dev): แปะ class "wip" ที่ element/ส่วนที่ยังเขียน
         ไม่เสร็จ → พื้นหลังแดง ลูกค้าจะเห็นว่าส่วนนั้นยัง test ไม่ได้
         เสร็จแล้ว → ลบคำว่า "wip" ออก "คำเดียว" สไตล์เดิมกลับมาเอง (ไม่ต้องจำสีเดิม)
         ใช้ได้ทุกระดับ:  <div class="card wip">, <li class="menu-item wip">,
                          <button class="btn wip">, <tr class="wip"> ฯลฯ

         ⚙ สวิตช์รวม: ตั้ง $WIP_MODE = false → สีแดงหายหมดทุกหน้าทันที
            (ไม่ต้องไล่ลบ class — เผื่อลืม จะได้ไม่หลุดสีแดงขึ้น production)
       ───────────────────────────────────────────────────────────────── --}}
    @php $WIP_MODE = true; @endphp
    @if ($WIP_MODE)
    <style>
        /* สีพื้นฐาน (เผื่อบริเวณที่ไม่มี element ลูกทับ) */
        .wip,
        .wip > td,
        .wip > th {
            background-color: #ffe3e3 !important;
        }
        .wip {
            position: relative !important;
            outline: 2px dashed #ff8c8c !important;
            outline-offset: -2px;
        }
        /* ม่านแดงโปร่งแสงคลุมทับทั้ง section → แดงทั้งหมดแม้ลูกมีพื้นหลังทึบ/inline style
           pointer-events:none → ยังคลิก/พิมพ์ทะลุได้ปกติ */
        .wip::after {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(224, 49, 49, 0.16);
            pointer-events: none;
            border-radius: inherit;
            z-index: 5;
        }
    </style>
    @endif

<!-- Page CSS -->

<!-- Helpers -->
<script src="assets/vendor/js/helpers.js"></script>
<!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
<!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
<script src="assets/vendor/js/template-customizer.js"></script>
<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
<script src="assets/js/config.js"></script>

<style>
  /* พื้นหลังทึบ */
  #loadingOverlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(234, 244, 255, 0.8);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  /* สปินเนอร์หมุน */
  .spinner {
    border: 8px solid #f3f3f3;
    border-top: 8px solid #28c76f;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
</style>

<div id="loadingOverlay" style="display: none;">
    <div class="col">
        <!-- Chase -->
        <div class="sk-chase sk-primary m-auto">
            <div class="sk-chase-dot"></div>
            <div class="sk-chase-dot"></div>
            <div class="sk-chase-dot"></div>
            <div class="sk-chase-dot"></div>
            <div class="sk-chase-dot"></div>
            <div class="sk-chase-dot"></div>
        </div>
    </div>
</div>