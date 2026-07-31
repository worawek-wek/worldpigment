<style>
    .active .menu-link i {
        color: #ffffff !important; /* เปลี่ยนสีของไอคอนใน <li> ที่มีคลาส active */
    }
    /* ปุ่ม toggle (พับเมนู) สี teal ตามธีมระบบ */
    .app-brand .layout-menu-toggle i {
        color: #54BAB9 !important;
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
                // ── กรองสิทธิ์: ซ่อนเมนูที่บัญชีปัจจุบันไม่มีสิทธิ์ ──
                $visibleSubMenu = [];
                if (isset($menu['sub_menu'])) {
                    foreach ($menu['sub_menu'] as $subMenuKey => $subMenu) {
                        // กลุ่มย่อยที่มีเมนูย่อยอีกชั้น (ระดับ 3): แสดงถ้ามีลูกที่มองเห็น ≥ 1 หรือ key กลุ่มได้สิทธิ์
                        if (isset($subMenu['sub_menu'])) {
                            $hasVisibleChild = false;
                            foreach ($subMenu['sub_menu'] as $childKey => $child) {
                                if (\App\Services\AccessControl::menuVisible($childKey)) {
                                    $hasVisibleChild = true;
                                    break;
                                }
                            }
                            if (\App\Services\AccessControl::menuVisible($subMenuKey) || $hasVisibleChild) {
                                $visibleSubMenu[$subMenuKey] = $subMenu;
                            }
                        } elseif (\App\Services\AccessControl::menuVisible($subMenuKey)) {
                            $visibleSubMenu[$subMenuKey] = $subMenu;
                        }
                    }
                }

                // เมนูหลัก: มีเมนูย่อย → แสดงถ้ามีลูกที่มองเห็นอย่างน้อย 1 หรือ key แม่ได้สิทธิ์
                //            ไม่มีเมนูย่อย → แสดงตามสิทธิ์ของ key ตัวเอง
                if (isset($menu['sub_menu'])) {
                    $canShowMenu = \App\Services\AccessControl::menuVisible($menuKey) || count($visibleSubMenu) > 0;
                } else {
                    $canShowMenu = \App\Services\AccessControl::menuVisible($menuKey);
                }
            @endphp

            @continue(!$canShowMenu)

            @php
                $menu_role = isset($menu['role']) ? $menu['role'] : '';
                $menu_name = isset($menu['route_name']) ? $menu['route_name'] : '';
                $menu_toggle = isset($menu['sub_menu']) ? 'menu-toggle' : '';
                $menu_active = $pageName == $menu_name ? 'active' : '';

                if(isset($menu['sub_menu'])){
                    foreach ($visibleSubMenu as $subMenuKey => $subMenu){
                        if(isset($subMenu['route_name']) && $subMenu['route_name'] == $pageName){
                            if(($subMenu['menu_parent'] ?? null) == $menuKey){
                                $menu_active = 'open';
                            }
                        }
                        // เมนูย่อยระดับ 3: ถ้าหน้าปัจจุบันตรงกับลูกในกลุ่ม ให้เปิดเมนูแม่ด้วย
                        if(isset($subMenu['sub_menu'])){
                            foreach($subMenu['sub_menu'] as $childKey => $child){
                                if(isset($child['route_name']) && $child['route_name'] == $pageName){
                                    $menu_active = 'open';
                                }
                            }
                        }
                    }
                }
            @endphp
            @if($menu_role == 'superadmin')
                @if(Auth::guard('admin')->user()->hasRole('superadmin'))
                <li class="menu-item {{ $menu_active }}">
                    <a href="{{ isset($menu['url']) ? url($menu['url']) : (isset($menu['route_name']) ? route($menu['route_name']) : 'javascript:void(0);') }}"
                        class="menu-link {{ $menu_toggle }}">
                        <i class="menu-icon tf-icons ti {{ isset($menu['icon']) ? $menu['icon'] : '' }}"></i>
                        <div>{{ $menu['title'] }}</div>
                    </a>
                    @if(isset($menu['sub_menu']))
                        <ul class="menu-sub">
                            @foreach ($visibleSubMenu as $subMenuKey => $subMenu)
                                @if(isset($subMenu['sub_menu']))
                                    {{-- กลุ่มย่อยที่มีเมนูย่อยอีกชั้น (ระดับ 3) --}}
                                    @php
                                        $visibleChild = [];
                                        foreach ($subMenu['sub_menu'] as $childKey => $child) {
                                            if (\App\Services\AccessControl::menuVisible($childKey)) {
                                                $visibleChild[$childKey] = $child;
                                            }
                                        }
                                        $group_open = false;
                                        foreach ($visibleChild as $childKey => $child) {
                                            if (isset($child['route_name']) && $child['route_name'] == $pageName) {
                                                $group_open = true;
                                            }
                                        }
                                    @endphp
                                    @if(count($visibleChild) > 0)
                                        <li class="menu-item {{ $group_open ? 'open active' : '' }}">
                                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                                <div>{{ $subMenu['title'] }}</div>
                                            </a>
                                            <ul class="menu-sub">
                                                @foreach ($visibleChild as $childKey => $child)
                                                    @php
                                                        $child_name = isset($child['route_name']) ? $child['route_name'] : '';
                                                        $child_active = $pageName == $child_name ? 'active' : '';
                                                    @endphp
                                                    <li class="menu-item {{ $child_active }}">
                                                        <a href="{{ $child_name ? route($child_name) : 'javascript:void(0);' }}" class="menu-link">
                                                            <div>{{ $child['title'] }}</div>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                    @endif
                                @else
                                    @php
                                        $submenu_name = isset($subMenu['route_name']) ? $subMenu['route_name'] : '';
                                        $submenu_active = $pageName == $submenu_name ? 'active' : '';
                                    @endphp
                                    <li class="menu-item {{ $submenu_active}}">
                                        <a href="{{ $submenu_name ? route($submenu_name) : 'javascript:void(0);' }}" class="menu-link">
                                            <div>{{ $subMenu['title'] }}</div>
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </li>
                @endif
            @else
                <li class="menu-item {{ $menu_active }}">
                    <a href="{{ isset($menu['url']) ? url($menu['url']) : (isset($menu['route_name']) ? route($menu['route_name']) : 'javascript:void(0);') }}"
                        class="menu-link {{ $menu_toggle }}">
                        <i class="menu-icon tf-icons ti {{ isset($menu['icon']) ? $menu['icon'] : '' }}"></i>
                        <div>{{ $menu['title'] }}</div>
                    </a>
                    @if(isset($menu['sub_menu']))
                        <ul class="menu-sub">
                            @foreach ($visibleSubMenu as $subMenuKey => $subMenu)
                                @if(isset($subMenu['sub_menu']))
                                    {{-- กลุ่มย่อยที่มีเมนูย่อยอีกชั้น (ระดับ 3) --}}
                                    @php
                                        $visibleChild = [];
                                        foreach ($subMenu['sub_menu'] as $childKey => $child) {
                                            if (\App\Services\AccessControl::menuVisible($childKey)) {
                                                $visibleChild[$childKey] = $child;
                                            }
                                        }
                                        $group_open = false;
                                        foreach ($visibleChild as $childKey => $child) {
                                            if (isset($child['route_name']) && $child['route_name'] == $pageName) {
                                                $group_open = true;
                                            }
                                        }
                                    @endphp
                                    @if(count($visibleChild) > 0)
                                        <li class="menu-item {{ $group_open ? 'open active' : '' }}">
                                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                                <div>{{ $subMenu['title'] }}</div>
                                            </a>
                                            <ul class="menu-sub">
                                                @foreach ($visibleChild as $childKey => $child)
                                                    @php
                                                        $child_name = isset($child['route_name']) ? $child['route_name'] : '';
                                                        $child_active = $pageName == $child_name ? 'active' : '';
                                                    @endphp
                                                    <li class="menu-item {{ $child_active }}">
                                                        <a href="{{ $child_name ? route($child_name) : 'javascript:void(0);' }}" class="menu-link">
                                                            <div>{{ $child['title'] }}</div>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                    @endif
                                @else
                                    @php
                                        $submenu_name = isset($subMenu['route_name']) ? $subMenu['route_name'] : '';
                                        $submenu_active = $pageName == $submenu_name ? 'active' : '';
                                    @endphp
                                    <li class="menu-item {{ $submenu_active}}">
                                        <a href="{{ $submenu_name ? route($submenu_name) : 'javascript:void(0);' }}" class="menu-link">
                                            <div>{{ $subMenu['title'] }}</div>
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endif
        @endforeach
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
