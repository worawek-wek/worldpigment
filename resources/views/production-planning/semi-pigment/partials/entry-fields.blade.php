{{--
    ฟอร์มฟิลด์ Semi/Pigment (ใช้ร่วมกันระหว่าง modal เพิ่ม/แก้ไขในหน้า Planning Item
    และ modal "สร้างแผน" ในหน้า Planning)

    ตัวแปรที่รับ:
      - $prefix          : prefix ของ id แต่ละ input (เช่น 'sp' หรือ 'cs')  [ดีฟอลต์ 'sp']
      - $companies       : array ตัวเลือก Company                            [ดีฟอลต์ []]
      - $custnoReadonly  : true = ช่อง Cust No. อ่านอย่างเดียว               [ดีฟอลต์ false]
--}}
@php
    $prefix         = $prefix ?? 'sp';
    $companies      = $companies ?? [];
    $custnoReadonly = $custnoReadonly ?? false;
@endphp
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">แผนกผลิต (Company)</label>
        <select class="form-select" id="{{ $prefix }}_company">
            <option value="">-- เลือก --</option>
            @foreach($companies as $c)
                <option value="{{ $c }}">{{ $c }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">แผนกที่สั่ง (Cust No.)</label>
        <input type="text" class="form-control{{ $custnoReadonly ? ' bg-light' : '' }}"
               id="{{ $prefix }}_custno" placeholder="รหัสลูกค้า" {{ $custnoReadonly ? 'readonly' : '' }}>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">วันที่สั่ง (Order Date)</label>
        <input type="date" class="form-control" id="{{ $prefix }}_mdate">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">วันที่ต้องการรับ (Want Date)</label>
        <input type="date" class="form-control" id="{{ $prefix }}_custwant">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">รหัสสินค้า (Item No.) <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="{{ $prefix }}_itemno" placeholder="Item No.">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">ล๊อด (Lot No.)</label>
        <input type="text" class="form-control" id="{{ $prefix }}_lot_no" placeholder="ล็อตที่">
    </div>

    {{-- <div class="col-md-8 mb-3 p-3 rounded" style="background-color: #f8cdcd; border: 1px dashed #e93508;"> --}}
    <div class="col-md-8 mb-3 p-3 " >
        <div class="row">
            <div class="col-md-6">
            <label class="form-label">ขาด Semi Code</label>
            <input type="text" class="form-control" id="{{ $prefix }}_semi_code" placeholder="รหัสกึ่งสำเร็จรูป">
        </div>
        <div class="col-md-6">
            <label class="form-label">ขาดแม่สี (Primary Color)</label>
            <input type="text" class="form-control" id="{{ $prefix }}_primary_color" placeholder="แม่สี">
        </div>
        </div>
    </div>

    <div class="col-md-4 mb-3 p-3">
        <label class="form-label">เลขที่ใบเบิกออกใบแดง (Red Bill)</label>
        <input type="text" class="form-control" id="{{ $prefix }}_red_bill_code" placeholder="เลขที่ใบเบิก">
    </div>
</div>

<hr class="my-2">

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">ยอดคงเหลือวันนี้ (Balance)</label>
        <input type="number" step="any" class="form-control" id="{{ $prefix }}_balance" placeholder="0.00">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">ยอดใช้ย้อนหลัง 2 เดือน (Retro)</label>
        <input type="number" step="any" class="form-control" id="{{ $prefix }}_retrospective" placeholder="0.00">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">น้ำหนักที่จะใช้ (Weight Request)</label>
        <input type="number" step="any" class="form-control" id="{{ $prefix }}_weight_request" placeholder="0.00">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">ผลิตเพิ่ม (Increase Production)</label>
        <input type="number" step="any" class="form-control" id="{{ $prefix }}_increase_production" placeholder="0.00">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">น้ำหนักที่จะผลิต (W Production)</label>
        <input type="number" step="any" class="form-control" id="{{ $prefix }}_weight_production" placeholder="0.00">
    </div>
</div>
