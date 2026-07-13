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
<link
    href="https://fonts.googleapis.com/css2?family=Lexend:wght@100;200;300;400;500;600;700;800;900&display=swap"
    rel="stylesheet" />

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

{{-- ─────────────────────────────────────────────────────────────────
     ปรับสีข้อความให้เข้มขึ้นทั้งเว็บ (ลูกค้าแจ้งว่าธีม default จางอ่านยาก)
     ธีม Sneat คุมสีข้อความผ่าน CSS variables ใต้ [data-bs-theme=light]
     → override ตัวแปรเดิมด้วยโทนม่วง-เทาเดิมแต่เข้มขึ้น + เลิกใช้ opacity
       ต่ำ ๆ (ต้นเหตุความจาง) เปลี่ยนเป็นสีทึบที่อ่านชัด
     selector html[data-bs-theme=light]/html.light-style → specificity
       ชนะค่าตั้งต้นของธีม (อยู่หลัง core.css ในลำดับด้วย)
     เฉพาะ light mode — dark mode ปล่อยตามดีไซน์เดิม (สว่างบนพื้นมืดอยู่แล้ว)
   ───────────────────────────────────────────────────────────────── --}}
<style>
  html[data-bs-theme="light"],
  html.light-style,
  :root {
    /* ใช้ Lexend เป็นฟอนต์หลัก (ละติน/ตัวเลข)
       Lexend ไม่มี glyph ไทย → ไม่ระบุฟอนต์ไทยไว้ ปล่อยให้ตัวไทยตกไปที่ sans-serif
       (ฟอนต์ไทยเริ่มต้นของระบบ) ให้ตรงกับที่เห็นในหน้า font-preview เป๊ะ
       ต้องตั้งตรงนี้ (หลัง core.css) ไม่งั้น core.css จะ override --bs-font-sans-serif กลับเป็น "Public Sans" */
    --bs-font-sans-serif: "Lexend", sans-serif;

    --bs-body-color: #000000;                 /* เดิม #6f6b7d — ข้อความหลัก ดำสนิท */
    --bs-body-color-rgb: 0, 0, 0;
    --bs-heading-color: #000000;              /* เดิม #5d596c — หัวข้อ/heading ดำสนิท */
    --bs-secondary-color: #000000;            /* เดิม rgba(111,107,125,.75) — ดำสนิท */
    --bs-secondary-color-rgb: 0, 0, 0;
    --bs-tertiary-color: #000000;            /* เดิม rgba(111,107,125,.5) — muted/placeholder ดำสนิท */
    --bs-tertiary-color-rgb: 0, 0, 0;
  }
  /* utility ที่บาง component กำหนดสีจางตรง ๆ — บังคับให้เป็นดำ */
  html[data-bs-theme="light"] .text-muted,
  html[data-bs-theme="light"] .text-body-secondary,
  html.light-style .text-muted,
  html.light-style .text-body-secondary {
    color: #000000 !important;
  }
  /* placeholder ในช่องกรอก — ดำสนิทตามที่สั่ง (ถ้าอยากให้จางกว่าตัวพิมพ์จริงบอกได้) */
  html[data-bs-theme="light"] .form-control::placeholder,
  html.light-style .form-control::placeholder {
    color: #000000;
    opacity: 1;
  }

  /* border ของช่องกรอก (input/select/textarea/select2) — เดิม #dbdade จางมาก → เข้มขึ้น
     เจาะจงเฉพาะ form field (ไม่แตะ border การ์ด/ตาราง/เส้นคั่นทั่วเว็บ)
     คง border ตอน focus เป็นสีม่วง primary ไว้ (specificity ของ :focus สูงกว่า) */
  html[data-bs-theme="light"] .form-control,
  html[data-bs-theme="light"] .form-select,
  html[data-bs-theme="light"] .input-group-text,
  html[data-bs-theme="light"] .select2-container--default .select2-selection,
  html.light-style .form-control,
  html.light-style .form-select,
  html.light-style .input-group-text,
  html.light-style .select2-container--default .select2-selection {
    border-color: #a8a5b3;
  }
  html[data-bs-theme="light"] .form-control:focus,
  html[data-bs-theme="light"] .form-select:focus,
  html.light-style .form-control:focus,
  html.light-style .form-select:focus {
    border-color: #7367f0;
  }

  /* bootstrap-select (.selectpicker) — render เป็น <button> ไม่ใช่ <select>
     กฎด้านบนจึงไม่โดน ต้องจัดให้หน้าตาเท่า form-select: ขอบเข้ม + ตัวหนังสือดำ
     (ธีมตั้งขอบจาง #dbdade + ตัวหนังสือสีจาง ทำให้ดูซีดกว่าช่องกรอกอื่นในฟอร์มเดียวกัน) */
  html[data-bs-theme="light"] .bootstrap-select > .btn.dropdown-toggle,
  html.light-style .bootstrap-select > .btn.dropdown-toggle {
    border-color: #a8a5b3;
    background-color: #fff;
    color: #000;
  }
  .bootstrap-select > .btn.dropdown-toggle .filter-option-inner-inner {
    color: #000;
  }
  /* เปิด dropdown อยู่ = สถานะ focus → ขอบม่วง primary เหมือน input อื่น */
  html[data-bs-theme="light"] .bootstrap-select.show > .btn.dropdown-toggle,
  html[data-bs-theme="light"] .bootstrap-select > .btn.dropdown-toggle:focus,
  html.light-style .bootstrap-select.show > .btn.dropdown-toggle,
  html.light-style .bootstrap-select > .btn.dropdown-toggle:focus {
    border-color: #7367f0;
  }

  /* เมนูซ้าย (sidebar) — ธีมตั้ง link สถานะปกติเป็น rgba(75,70,92,.5) จางมาก
     พึ่ง id #layout-menu + !important → ชนะทุก theme class แน่นอน
     scope เฉพาะ menu-item ที่ "ไม่ใช่ active" (active มีพื้นเข้ม+ไอคอนขาว ห้ามแตะ) */
  #layout-menu .menu-item:not(.active) > .menu-link,
  #layout-menu .menu-item:not(.active) > .menu-link > div {
    color: #000 !important;           /* ข้อความเมนู ดำสนิท */
  }
  #layout-menu .menu-item:not(.active) > .menu-link:hover,
  #layout-menu .menu-item:not(.active) > .menu-link:hover > div {
    color: #000 !important;
  }
  /* ไอคอนเมนู — สีเขียวมิ้นท์ accent ของระบบ (#54BAB9) ไม่บังคับเป็นดำ */
  #layout-menu .menu-item:not(.active) > .menu-link .menu-icon {
    color: #54BAB9 !important;
  }
  #layout-menu .menu-header {
    color: #000000 !important;        /* หัวข้อกลุ่มเมนู เดิม rgba(75,70,92,.5) */
  }
</style>

{{-- ─────────────────────────────────────────────────────────────────
     เพิ่มขนาดฟอนต์ทั้งระบบให้ใหญ่ขึ้น (ลูกค้าแจ้งตัวหนังสือเล็กไป)
     หลักการ: ดันเฉพาะ "ตัวปกติ/ตัวเล็ก" ให้ใหญ่ขึ้น ~2px
              ไม่แตะหัวข้อใหญ่ (h1–h6 / .display-* / .fs-1..3) ที่ใหญ่อยู่แล้ว
     ปรับง่าย: อยากใหญ่/เล็กกว่านี้ แก้แค่ตัวเลข --wp-font-base / --wp-font-sm 2 ค่าล่าง
     ต้องอยู่หลัง core.css เพื่อ override ค่าเดิมของธีม
   ───────────────────────────────────────────────────────────────── --}}
<style>
  :root {
    --wp-font-base: 1.0625rem;   /* ตัวปกติ เดิม 0.9375rem (15px) → 17px */
    --wp-font-sm:   0.9375rem;   /* ตัวเล็ก  เดิม 0.8125rem (13px) → 15px */
    /* ดันข้อความ body และทุกอย่างที่ inherit ขนาดจาก body (ตาราง/ย่อหน้า/list ฯลฯ) */
    --bs-body-font-size: var(--wp-font-base);
  }
  /* คอมโพเนนต์ที่กำหนดขนาดตัวเองไว้ (ไม่ได้ inherit body) — ดันขึ้นให้เท่ากัน */
  .form-control, .form-select, .input-group-text,
  .btn,
  .dropdown-item,
  .nav-link, .menu-link,
  .table,
  .table th, .table td,
  .select2-container .select2-selection__rendered,
  .select2-results__option {
    font-size: var(--wp-font-base);
  }
  /* ตัวหนังสือเล็กพิเศษ (คำอธิบาย/label ย่อย) — ดันขึ้นเล็กน้อยพออ่านออก
     ไม่รวม .badge — badge เป็น pill ที่ควรมีขนาดสัมพัทธ์ (0.81em) เล็กตามดีไซน์
     ถ้าดันให้ใหญ่จะดูหนา/เด่นผิดที่ โดยเฉพาะ badge สถานะในตาราง */
  small, .small,
  .form-label, .col-form-label,
  .form-text {
    font-size: var(--wp-font-sm);
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