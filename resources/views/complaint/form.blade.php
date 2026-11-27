{{-- ฟอร์ม Complaint & NCR — ผังตามฟอร์ม Access "NC : ฟอร์ม" (ผู้ใช้สั่ง 26/11/2569 "แก้ไขฟอร์มใหม่ทั้งหมด อ้างอิงจากรูปนี้")
     ช่องที่กากบาทในรูป ไม่อยู่บนฟอร์ม: แผนกที่เกิด NC (Dept) · ข้อกำหนด ISO ที่ผิด (shall) · ผู้แก้ไข (CorrBY) · ผู้ป้องกัน (ProtBy)
     ส่วนย่อยล่าง (treat) มีแค่ 4 ช่องตามรูป: เงิน claim · เลขที่ใบแก้ไข (TreatNo) · ค่าวัตถุดิบ (TreatRaw) · น.น.แก้ไข (TreatKG)
     ช่องอื่นที่ไม่อยู่ในรูป store() ไม่แตะ — ข้อมูลเดิมยังอยู่ --}}
@php
    $v   = fn ($col) => $nc ? $nc->{$col} : null;
    $t   = fn ($col) => $treat ? $treat->{$col} : null;
    $num = fn ($x, $d = 2) => ($x === null || $x === '') ? '' : number_format((float) $x, $d);
    $d   = fn ($x) => $x ? date('d/m/y', strtotime((string) $x)) : '';
    $ncrNo = $nc ? (\App\Models\Nc::isRealNcrNo($nc->NCRNo) ? trim($nc->NCRNo) : '-') : '-';
@endphp
<form id="nc_form" class="nc-access">
    @csrf
    <input type="hidden" name="id" value="{{ $nc?->id }}">

    {{-- แถว 1: NCRNo · วันที่ออก NCR · ปัญหา --}}
    <div class="row gx-2 gy-2">
        <div class="col-md-2">
            <label class="form-label">NCRNo</label>
            <input type="text" class="form-control" name="NCRNo" id="nc_ncrno" maxlength="15" value="{{ $ncrNo }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">วันที่ออก NCR <span class="text-danger">*</span></label>
            <input type="text" class="form-control nc-datepicker" name="NCRDate" autocomplete="off"
                value="{{ $nc ? $d($nc->NCRDate) : date('d/m/y') }}">
        </div>
        <div class="col-md-8">
            <label class="form-label">ปัญหา</label>
            <input type="text" class="form-control" name="Subject" maxlength="200" value="{{ $v('Subject') }}">
        </div>
    </div>

    {{-- แถว 2: ผล · Off Spec · รหัสลูกค้า · ชื่อลูกค้า · เลขที่ใบร้องเรียน --}}
    <div class="row gx-2 gy-2 mt-0">
        <div class="col-md-2">
            <label class="form-label">ผล</label>
            <select class="form-select" name="degree">
                <option value=""></option>
                @foreach($degrees as $key => $label)
                    <option value="{{ $key }}" {{ $v('degree') === $key ? 'selected' : '' }}>{{ $key }} = {{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1 text-center">
            <label class="form-label" for="nc_offspec">Off Spec</label>
            <div class="form-check d-flex justify-content-center mt-1">
                <input class="form-check-input" type="checkbox" name="OffSpec" value="1" id="nc_offspec" {{ $v('OffSpec') ? 'checked' : '' }}>
            </div>
        </div>
        {{-- พิมพ์รหัสหรือชื่อ → รายการแนะนำ (.nc-suggest สร้างโดย JS ใน complaint/index) --}}
        <div class="col-md-2 position-relative">
            <label class="form-label">รหัสลูกค้า</label>
            <input type="text" class="form-control" name="custno" id="nc_custno" maxlength="5" autocomplete="off" value="{{ $v('custno') }}">
            <div class="invalid-feedback" id="nc_cust_msg">ไม่พบรหัสลูกค้านี้</div>
        </div>
        <div class="col-md-4 position-relative">
            <label class="form-label">ชื่อลูกค้า</label>
            <input type="text" class="form-control" name="custname" id="nc_custname" maxlength="50"
                autocomplete="off" value="{{ $v('custname') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">เลขที่ ใบร้องเรียน</label>
            <input type="text" class="form-control" name="ComplainNO" maxlength="10" value="{{ $v('ComplainNO') }}">
        </div>
    </div>

    {{-- แถว 3: รหัสสินค้า · LotNo · น้ำหนัก · สรุปสาเหตุ --}}
    <div class="row gx-2 gy-2 mt-0">
        {{-- พิมพ์รหัส → รายการแนะนำ (uprice) · ชื่อสินค้าใต้ช่อง = แสดงอย่างเดียว ไม่บันทึก --}}
        <div class="col-md-3 position-relative">
            <label class="form-label">รหัสสินค้า</label>
            <input type="text" class="form-control" name="ProdNo" id="nc_prodno" maxlength="30" autocomplete="off" value="{{ $v('ProdNo') }}">
            <div class="form-text nc-prod-name" id="nc_prod_name">{{ $prodName }}</div>
        </div>
        <div class="col-md-3">
            <label class="form-label">LotNo</label>
            <input type="text" class="form-control" name="LotNo" maxlength="50" value="{{ $v('LotNo') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">น้ำหนัก (กก.)</label>
            <input type="text" class="form-control js-comma text-end" name="QTY" value="{{ $num($v('QTY')) }}">
        </div>
        {{-- สรุปสาเหตุ: เลือกจากรายการ A–I (config/complaint.php) หรือพิมพ์เองได้ — 27/11/2569
             คลิก/พิมพ์ → รายการ .nc-suggest ใต้ช่อง (JS ใน complaint/index) · เก็บข้อความตามที่อยู่ในช่อง (varchar 100) --}}
        <div class="col-md-4 position-relative">
            <label class="form-label">สรุปสาเหตุ</label>
            <div class="input-group">
                <input type="text" class="form-control" name="CauseType" id="nc_causetype" maxlength="100" autocomplete="off"
                    value="{{ trim((string) $v('CauseType')) }}"
                    data-options='@json(collect(config('complaint.cause_types'))->map(fn ($desc, $code) => ['code' => (string) $code, 'name' => $desc])->values())'>
                <button type="button" class="btn btn-outline-secondary px-2" id="btn_nc_causetype" tabindex="-1" title="แสดงรายการ">
                    <i class="ti ti-chevron-down"></i>
                </button>
            </div>
            <div class="form-text">เลือกจากรายการ หรือพิมพ์เพิ่มต่อท้ายได้</div>
        </div>
    </div>

    <div class="row gx-2 gy-2 mt-0">
        <div class="col-12">
            <label class="form-label">สาเหตุ</label>
            <input type="text" class="form-control" name="Cause" maxlength="200" value="{{ $v('Cause') }}">
        </div>
        <div class="col-12">
            <label class="form-label">วิธีแก้ไข</label>
            <input type="text" class="form-control" name="Correct" maxlength="200" value="{{ $v('Correct') }}">
        </div>
        <div class="col-12">
            <label class="form-label">วิธีป้องกัน</label>
            <input type="text" class="form-control" name="Protect" maxlength="200" value="{{ $v('Protect') }}">
        </div>
        <div class="col-12">
            <label class="form-label">ความเห็น ผู้บริหาร</label>
            <textarea class="form-control nc-mgr" name="MGRcomment" rows="3" maxlength="200">{{ $v('MGRcomment') }}</textarea>
        </div>
    </div>

    {{-- ส่วนย่อยล่าง (treat) — ผูกกับ NCRNo --}}
    <div class="nc-sub mt-3">
        <div class="nc-sub-head">ส่วนที่แก้ไข <small class="fw-normal">— บันทึกได้เมื่อมี NCRNo แล้ว</small></div>
        <div class="row gx-2 gy-2">
            <div class="col-md-2">
                <label class="form-label">NCRNo</label>
                <input type="text" class="form-control bg-light" id="nc_sub_ncrno" value="{{ $ncrNo }}" readonly tabindex="-1">
            </div>
            <div class="col-md-3">
                <label class="form-label">เงิน claim</label>
                <input type="text" class="form-control js-comma text-end nc-claim" name="claim" value="{{ $num($t('claim')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">เลขที่ใบแก้ไข</label>
                <input type="text" class="form-control" name="TreatNo" maxlength="15" value="{{ $t('TreatNo') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">ค่าวัตถุดิบ</label>
                <input type="text" class="form-control js-comma text-end" name="TreatRaw" value="{{ $num($t('TreatRaw')) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">น.น.แก้ไข</label>
                <input type="text" class="form-control js-comma text-end" name="TreatKG" value="{{ $num($t('TreatKG')) }}">
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="btn_nc_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึก
        </button>
    </div>
</form>
