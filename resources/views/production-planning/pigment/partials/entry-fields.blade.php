{{--
    ฟอร์มฟิลด์ Pigment (ใช้ใน modal เพิ่ม/แก้ไขในหน้า Planning Item)
    แยกจาก Semi — ตัดฟิลด์ที่ไม่ใช้ของ Pigment ออก:
      Company, Semi Code, แม่สี (Primary Color), Lot No., เลขที่ใบเบิกออกใบแดง (Red Bill), ผลิตเพิ่ม (Increase)

    ตัวแปรที่รับ:
      - $prefix          : prefix ของ id แต่ละ input (ดีฟอลต์ 'pg')
      - $custnoReadonly  : true = ช่อง Cust No. อ่านอย่างเดียว (ดีฟอลต์ false)
--}}
@php
    $prefix         = $prefix ?? 'pg';
    $custnoReadonly = $custnoReadonly ?? false;
@endphp
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">แผนกที่สั่ง (Cust No.)</label>
        <input type="text" class="form-control{{ $custnoReadonly ? ' bg-light' : '' }}"
               id="{{ $prefix }}_custno" placeholder="รหัสลูกค้า" {{ $custnoReadonly ? 'readonly' : '' }}>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">วันที่สั่ง (Order Date)</label>
        <input type="text" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป" id="{{ $prefix }}_mdate">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">วันที่ต้องการรับ (Want Date)</label>
        <input type="text" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป" id="{{ $prefix }}_custwant">
    </div>
    <div class="col-md-12 mb-3">
        <label class="form-label">รหัสสินค้า (Item No.) <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="{{ $prefix }}_itemno" placeholder="Item No.">
    </div>
</div>
<hr class="my-2">
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">ยอดคงเหลือวันนี้ (Balance)</label>
        <input type="number" step="any" class="form-control" id="{{ $prefix }}_balance" placeholder="0.00">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">ยอดใช้ย้อนหลัง 2 เดือน (Retro Spective)</label>
        <input type="number" step="any" class="form-control" id="{{ $prefix }}_retrospective" placeholder="0.00">
    </div>

</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">น้ำหนักที่จะใช้ (Weight Request)</label>
        <input type="number" step="any" class="form-control" id="{{ $prefix }}_weight_request" placeholder="0.00">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">น้ำหนักที่ซื้อ (Weight Production)</label>
        <input type="number" step="any" class="form-control" id="{{ $prefix }}_weight_production" placeholder="0.00">
    </div>
</div>
