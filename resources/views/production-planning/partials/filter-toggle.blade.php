{{--
    แถบหัวตัวกรองแบบพับได้ ของหน้าในเมนู PL-แผนการผลิต (07/11/2569 — ตามแบบหน้าใบเสนอราคา)
    ใช้: @include('production-planning.partials.filter-toggle', ['box' => 'plFilterBox'])
         แล้วครอบตัวกรองด้วย <div class="collapse" id="plFilterBox"><div class="pt-3"> … </div></div>
         และเรียก plInitFilterToggle({...}) ใน script ของหน้า (หลังสร้าง DataTable)

    ช่องกรองที่ต้องการให้ขึ้นในแถบสรุป + นับบนปุ่มล้างตัวกรอง → ใส่ attribute ที่ตัว input/select:
      data-fl-label="ป้ายชื่อ"       (ไม่มี = ไม่นับ)
      data-fl-default="ค่าเริ่มต้น"   (ไม่ระบุ = ค่าว่าง · ค่าเท่ากับค่าเริ่มต้น = ถือว่าไม่ได้กรอง)
      data-fl-prefix="#selectorของ select"  (เติมข้อความตัวเลือกของ select นั้นหน้าป้าย เช่น "Inplan วันที่เริ่ม")
--}}
<div class="d-flex justify-content-end align-items-center gap-2 pl-filter-bar" data-box="#{{ $box }}">
    {{-- สรุปเงื่อนไขที่กรองอยู่ — โชว์เฉพาะตอนพับตัวกรอง --}}
    <div class="d-flex flex-wrap align-items-center gap-1 me-auto pl-filter-summary"></div>
    <button type="button" class="btn btn-label-primary btn-sm pl-filter-toggle"
        data-bs-toggle="collapse" data-bs-target="#{{ $box }}"
        aria-expanded="false" aria-controls="{{ $box }}">
        <i class="ti ti-filter me-1"></i>ตัวกรอง
        <i class="ti ti-chevron-down ms-1 toggle-caret"></i>
    </button>
    <button type="button" class="btn btn-label-secondary btn-sm pl-filter-reset">
        <i class="ti ti-x me-1"></i>ล้างตัวกรอง<span class="filter-count ms-1"></span>
    </button>
</div>
