@php
    $typeCls   = $sp->type === 'semi' ? 'bg-label-primary' : 'bg-label-success';
    $isRequest = $sp->status === \App\Models\SemiPigment::STATUS_REQUEST;
    $statusCls = [
        'request'  => 'bg-label-warning',
        'approved' => 'bg-label-success',
        'reject'   => 'bg-label-danger',
    ][$sp->status] ?? 'bg-label-secondary';
    $ro = $isRequest ? '' : 'disabled';
    $r_mdate    = $sp->order_date ? substr($sp->order_date, 0, 10) : '';
    $r_custwant = $sp->want_date  ? substr($sp->want_date, 0, 10)  : '';
@endphp

<form id="sp_edit_form">
    <input type="hidden" name="id" value="{{ $sp->id }}">

    {{-- ── ข้อมูลอ้างอิง (อ่านอย่างเดียว) ── --}}
    <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
        <span class="badge {{ $typeCls }}">{{ strtoupper($sp->type) }}</span>
        <span class="text-muted small">Order No.: <strong class="text-body">{{ $sp->orderno ?? '-' }}</strong></span>
        <span class="ms-auto">สถานะ: <span class="badge {{ $statusCls }}">{{ $sp->statusLabel() }}</span></span>
    </div>

    @unless($isRequest)
        <div class="alert alert-secondary py-2">
            <i class="ti ti-lock me-1"></i>รายการนี้ถูกดำเนินการแล้ว ไม่สามารถแก้ไข/อนุมัติได้
        </div>
    @endunless

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Company</label>
            <select class="form-select" name="company" {{ $ro }}>
                <option value="">-- เลือก --</option>
                @foreach($companies as $c)
                    <option value="{{ $c }}" {{ $sp->company === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Cust No.</label>
            <input type="text" class="form-control" name="custno" value="{{ $sp->custno }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Item No. <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="itemno" value="{{ $sp->itemno }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">วันที่สั่ง</label>
            <input type="date" class="form-control" name="mdate" value="{{ $r_mdate }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">วันที่ต้องการรับ</label>
            <input type="date" class="form-control" name="custwant" value="{{ $r_custwant }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Semi Code</label>
            <input type="text" class="form-control" name="semi_code" value="{{ $sp->semi_code }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">แม่สี (Primary Color)</label>
            <input type="text" class="form-control" name="primary_color" value="{{ $sp->primary_color }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Lot No.</label>
            <input type="text" class="form-control" name="lot_no" value="{{ $sp->lot_no }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">เลขที่ใบเบิกออกใบแดง (Red Bill)</label>
            <input type="text" class="form-control" name="red_bill_code" value="{{ $sp->red_bill_code }}" {{ $ro }}>
        </div>
    </div>

    <hr class="my-2">

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">ยอดคงเหลือวันนี้ (Balance)</label>
            <input type="number" step="any" class="form-control" name="balance" value="{{ $sp->balance }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">ยอดใช้ย้อนหลัง 2 เดือน</label>
            <input type="number" step="any" class="form-control" name="retrospective" value="{{ $sp->retrospective }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">น้ำหนักที่จะใช้ (weight_request)</label>
            <input type="number" step="any" class="form-control" name="weight_request" value="{{ $sp->weight_request }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">ผลิตเพิ่ม (Increase)</label>
            <input type="number" step="any" class="form-control" name="increase_production" value="{{ $sp->increase_production }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">น้ำหนักที่จะผลิต (weight_production)</label>
            <input type="number" step="any" class="form-control" name="weight_production" value="{{ $sp->weight_production }}" {{ $ro }}>
        </div>
    </div>
</form>

<div class="d-flex justify-content-end gap-2 pt-2 border-top">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="ti ti-x me-1"></i>ปิด
    </button>
    @if($isRequest)
        <button type="button" class="btn btn-danger btn_reject" data-id="{{ $sp->id }}">
            <i class="ti ti-x me-1"></i>ไม่อนุมัติ
        </button>
        <button type="button" class="btn btn-success btn_approve" data-id="{{ $sp->id }}">
            <i class="ti ti-check me-1"></i>อนุมัติ
        </button>
        <button type="button" class="btn btn-primary" id="btn_sp_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึกข้อมูล
        </button>
    @endif
</div>
