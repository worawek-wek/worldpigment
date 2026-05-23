<style>
    .active .menu-link i {
        color: #ffffff !important; /* เปลี่ยนสีของไอคอนใน <li> ที่มีคลาส active */
    }
</style>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme pt-2">
    <div class="app-brand">
        <div class="app-brand-link d-block text-center w-100">
            <img src="assets/img/illustrations/main.png" alt="" class="mw-100" height="100%">
        </div>

        <a href="javascript:void(0);" class="layout-menu-toggle text-large ms-auto" style="color: white;">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
          </a>
    </div>

    {{-- <div class="menu-inner-shadow"></div> --}}

    <ul class="menu-inner py-3">
        {{-- <li class="menu-item">
            <a href="/user" class="menu-link">
                <i class="menu-icon tf-icons ti ti-copy"></i>
                <div data-i18n="บุคลากร">บุคลากร</div>
            </a>
        </li> --}}
        <li class="menu-item">
            <a href="/category/color-matching" class="menu-link">
                <i class="menu-icon tf-icons ti ti-receipt-tax"></i>
                <div data-i18n="เทียบสี">เทียบสี</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="/category" class="menu-link">
                <i class="menu-icon tf-icons ti ti-receipt-tax"></i>
                <div data-i18n="ใบเสนอราคา">ใบเสนอราคา</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="/category/order" class="menu-link">
                <i class="menu-icon tf-icons ti ti-receipt-tax"></i>
                <div data-i18n="Order">Order</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="/category/production-planning" class="menu-link">
                <i class="menu-icon tf-icons ti ti-receipt-tax"></i>
                <div data-i18n="วางแผนการผลิต">วางแผนการผลิต</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="/category/customer" class="menu-link">
                <i class="menu-icon tf-icons ti ti-receipt-tax"></i>
                <div data-i18n="ฐานข้อมูลลูกค้า">ฐานข้อมูลลูกค้า</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="/category/report" class="menu-link">
                <i class="menu-icon tf-icons ti ti-receipt-tax"></i>
                <div data-i18n="รายงาน">รายงาน</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="/category/permission" class="menu-link">
                <i class="menu-icon tf-icons ti ti-receipt-tax"></i>
                <div data-i18n="สิทธิ์การใช้งาน">สิทธิ์การใช้งาน</div>
            </a>
        </li>
        {{-- <li class="menu-item">
            <a href="/equipments" class="menu-link">
                <i class="menu-icon tf-icons ti ti-receipt-tax"></i>
                <div data-i18n="อุปกรณ์">อุปกรณ์</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="/equiptrack" class="menu-link">
                <i class="menu-icon tf-icons ti ti-receipt-tax"></i>
                <div data-i18n="ยืม-คืน">ยืม-คืน</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="report/borrow" class="menu-link">
                <i class="menu-icon tf-icons ti ti-receipt-tax"></i>
                <div data-i18n="รายงานการยืมรายเดือน">รายงานการยืมรายเดือน</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="report/return" class="menu-link">
                <i class="menu-icon tf-icons ti ti-receipt-tax"></i>
                <div data-i18n="รายงานการคืนรายเดือน">รายงานการคืนรายเดือน</div>
            </a>
        </li> --}}
    </ul>
</aside>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var currentUrl = window.location.pathname;
        var links = document.querySelectorAll(".menu-link");

        links.forEach(function (link) {
            var href = link.getAttribute("href");

            // เช็คว่าปัจจุบันอยู่ที่หน้าที่ลิงก์ไปหา (แม้จะมี /edit หรือ /1 เป็นต้น)
            if (currentUrl.startsWith(href)) {
                var li = link.closest("li.menu-item");
                if (li) {
                    li.classList.add("active");

                    // เปิดเมนูแม่ ถ้ามี
                    var parentToggle = li.closest("ul.menu-sub");
                    if (parentToggle) {
                        var parentMenu = parentToggle.closest("li.menu-item");
                        if (parentMenu) {
                            parentMenu.classList.add("open", "active");
                        }
                    }
                }
            }
        });
    });
</script>