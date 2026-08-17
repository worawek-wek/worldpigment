{{-- ─────────────────────────────────────────────────────────────────
     แถบบน (navbar) ถูกถอดออกทั้งแถบแล้ว — เหลือเฉพาะเมนูผู้ใช้
     ที่ลอยตรึงอยู่มุมบนขวาของจอ (fixed) เพื่อคืนพื้นที่แนวตั้งให้เนื้อหา

     ยังคง include ด้วยชื่อเดิม (layout/inc_topmenu) ทุกหน้าจึงไม่ต้องแก้
     ปุ่ม hamburger (เปิด sidebar บนจอเล็ก) ถูกย้ายมาไว้ในกล่องนี้ด้วย
     ถ้าเอาออกจะเปิดเมนูบนมือถือไม่ได้
   ───────────────────────────────────────────────────────────────── --}}
@php
    $authUser = Auth::user();
    $isEmpAccount = $authUser instanceof \App\Models\Emp;
    if ($isEmpAccount) {
        // พนักงาน (ตาราง emp): ชื่อ-นามสกุล และ role/แผนก
        $displayName = trim(($authUser->empname ?? '') . ' ' . ($authUser->empsur ?? ''));
        $displayName = $displayName !== '' ? $displayName : ($authUser->user ?? 'พนักงาน');
        $displaySub  = optional($authUser->role)->name ?? optional($authUser->department)->name ?? 'พนักงาน';
    } else {
        // admin (ตาราง users)
        $displayName = $authUser->name ?? '';
        $displaySub  = optional($authUser->position)->position_name ?? '';
    }
@endphp

@once
<style>
  /* กล่องเมนูผู้ใช้แบบลอยตรึงมุมบนขวา
     z-index 1035 = เหนือเนื้อหาหน้า แต่ยังต่ำกว่า modal/offcanvas ของ Bootstrap (1050+)
     จึงไม่ไปบังหน้าต่าง modal ที่เปิดขึ้นมา */
  .wp-userbar {
    position: fixed;
    top: .75rem;
    right: 1.25rem;
    z-index: 1035;
    display: flex;
    align-items: center;
    gap: .5rem;
  }
  /* ปุ่มวงกลมพื้นขาว + เงา — ให้ยังเห็นชัดเวลาลอยทับเนื้อหาด้านหลัง */
  .wp-userbar .wp-userbar-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .25rem;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 .125rem .5rem rgba(75, 70, 92, .25);
    line-height: 0;
    color: inherit;
  }
  .wp-userbar .wp-userbar-btn > i { padding: .5rem; }
  .wp-userbar .dropdown-toggle::after { display: none; }   /* ซ่อนลูกศรของ dropdown */

  /* ธีมเว้นที่ด้านบนไว้สำหรับ navbar แบบ fixed — 64px (core.css) และ 78px (demo.css)
     ทั้งคู่ใส่ !important มา จึงต้องใช้ selector ตัวเดียวกัน + !important ถึงจะทับได้
     navbar ถูกถอดออกแล้ว ช่องว่างนั้นจึงไม่มีประโยชน์ — ล้างทิ้งให้เนื้อหาขึ้นไปชิดขอบบน */
  .layout-navbar-fixed .layout-wrapper:not(.layout-without-menu) .layout-page,
  .layout-navbar-fixed .layout-wrapper:not(.layout-horizontal):not(.layout-without-menu) .layout-page,
  .layout-page { padding-top: 0 !important; }

  /* แถบเบลอด้านบน — pseudo-element ของธีม (theme-default.css) ที่ทำ blur + ไล่สีขาว
     ไว้ให้เนื้อหาลอดใต้ navbar แบบ fixed สวย ๆ ; ไม่มี navbar แล้วจึงเหลือแต่แถบขุ่นบังเนื้อหา */
  .layout-navbar-fixed .layout-page:before { display: none !important; }
</style>
@endonce

<div class="wp-userbar">
    {{-- ปุ่มเปิด sidebar (จอเล็กเท่านั้น) — class layout-menu-toggle คือตัวที่ theme JS ผูก event ไว้ --}}
    <div class="layout-menu-toggle d-xl-none">
        <a class="wp-userbar-btn" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-sm"></i>
        </a>
    </div>

    <div class="dropdown">
        <a class="wp-userbar-btn dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
            <div class="avatar avatar-online">
                <img src="assets/img/avatars/1.png" alt class="h-auto rounded-circle" />
            </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <div class="dropdown-item" @if(!$isEmpAccount) data-bs-toggle="modal" data-bs-target="#insurance_2" onclick="user_view({{ Auth::id(); }})" @endif>
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                                <img src="assets/img/avatars/1.png" alt class="h-auto rounded-circle" />
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-medium d-block">{{ $displayName }}</span>
                            <small class="text-muted">{{ $displaySub }}</small>
                        </div>
                    </div>
                </div>
            </li>
            <li>
                <div class="dropdown-divider"></div>
            </li>
            <li>
                <a class="dropdown-item" href="/branch/manage">
                    <i class="ti ti-user-check me-2 ti-sm"></i>
                    <span class="align-middle">
                        11111
                    </span>
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="/logout">
                    <i class="ti ti-logout me-2 ti-sm"></i>
                    <span class="align-middle">Log Out</span>
                </a>
            </li>
        </ul>
    </div>
</div>
