<style>
    .active .menu-link i {
        color: #ffffff !important; /* เปลี่ยนสีของไอคอนใน <li> ที่มีคลาส active */
    }
</style>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand">
        <a href="javascript:void(0)" class="app-brand-link">
            <img src="assets/img/illustrations/main.png" alt="" class="mw-100" height="100%">
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">

        @php
            $pageName = request()->route()->getName();
        @endphp

        @foreach (config('menu') as $menuKey => $menu)
            {{-- กรณีเป็น Header --}}
            @if(isset($menu['type']) && $menu['type'] === 'header')
                <li class="menu-header small">
                    <span class="menu-header-text">{{ $menu['title'] }}</span>
                </li>
                @continue
            @endif

            @php
                $menu_role = isset($menu['role']) ? $menu['role'] : '';
                $menu_name = isset($menu['route_name']) ? $menu['route_name'] : '';
                $menu_toggle = isset($menu['sub_menu']) ? 'menu-toggle' : '';
                $menu_active = $pageName == $menu_name ? 'active' : '';

                if(isset($menu['sub_menu'])){
                    foreach ($menu['sub_menu'] as $subMenuKey => $subMenu){
                        if($subMenu['route_name'] == $pageName){
                            if($subMenu['menu_parent'] == $menuKey){
                                $menu_active = 'open';
                            }
                        }
                    }
                }
            @endphp
            @if($menu_role == 'superadmin')
                @if(Auth::guard('admin')->user()->hasRole('superadmin'))
                <li class="menu-item {{ $menu_active }}">
                    <a href="{{ isset($menu['route_name']) ? route($menu['route_name']) : 'javascript:void(0);' }}"
                        class="menu-link {{ $menu_toggle }}">
                        <i class="menu-icon tf-icons ti {{ isset($menu['icon']) ? $menu['icon'] : '' }}"></i>
                        <div>{{ $menu['title'] }}</div>
                    </a>
                    @if(isset($menu['sub_menu']))
                        <ul class="menu-sub">
                            @foreach ($menu['sub_menu'] as $subMenuKey => $subMenu)
                                @php
                                    $submenu_name = isset($subMenu['route_name']) ? $subMenu['route_name'] : '';
                                    $submenu_active = $pageName == $submenu_name ? 'active' : '';
                                    $menu_toggle = isset($subMenu['sub_menu']) ? 'menu-toggle' : '';
                                @endphp
                                <li class="menu-item {{ $submenu_active}}">
                                    <a href="{{ route($submenu_name) }}" class="menu-link">
                                        <div>{{ $subMenu['title'] }}</div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
                @endif
            @else
                <li class="menu-item {{ $menu_active }}">
                    <a href="{{ isset($menu['route_name']) ? route($menu['route_name']) : 'javascript:void(0);' }}"
                        class="menu-link {{ $menu_toggle }}">
                        <i class="menu-icon tf-icons ti {{ isset($menu['icon']) ? $menu['icon'] : '' }}"></i>
                        <div>{{ $menu['title'] }}</div>
                    </a>
                    @if(isset($menu['sub_menu']))
                        <ul class="menu-sub">
                            @foreach ($menu['sub_menu'] as $subMenuKey => $subMenu)
                                @php
                                    $submenu_name = isset($subMenu['route_name']) ? $subMenu['route_name'] : '';
                                    $submenu_active = $pageName == $submenu_name ? 'active' : '';
                                    $menu_toggle = isset($subMenu['sub_menu']) ? 'menu-toggle' : '';
                                @endphp
                                <li class="menu-item {{ $submenu_active}}">
                                    <a href="{{ route($submenu_name) }}" class="menu-link">
                                        <div>{{ $subMenu['title'] }}</div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endif
        @endforeach

        <!-- Misc -->
        {{-- <li class="menu-header small text-uppercase">
            <span class="menu-header-text" data-i18n="Misc">Misc</span>
        </li>
        <li class="menu-item">
            <a href="https://pixinvent.ticksy.com/" target="_blank" class="menu-link">
                <i class="menu-icon tf-icons ti ti-lifebuoy"></i>
                <div data-i18n="Support">Support</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="https://demos.pixinvent.com/vuexy-html-admin-template/documentation/" target="_blank"
                class="menu-link">
                <i class="menu-icon tf-icons ti ti-file-description"></i>
                <div data-i18n="Documentation">Documentation</div>
            </a>
        </li> --}}
    </ul>
</aside>

<div class="menu-mobile-toggler d-xl-none rounded-1">
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
        <i class="ti tabler-menu icon-base"></i>
        <i class="ti tabler-chevron-right icon-base"></i>
    </a>
</div>


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
