@php
    $isRequest = $pigment->status === \App\Models\Pigment::STATUS_REQUEST;
    $statusCls = [
        'request'  => 'bg-label-warning',
        'approved' => 'bg-label-success',
        'reject'   => 'bg-label-danger',
    ][$pigment->status] ?? 'bg-label-secondary';
    $ro = $isRequest ? '' : 'disabled';
    $r_mdate    = $pigment->order_date ? substr($pigment->order_date, 0, 10) : '';
    $r_custwant = $pigment->want_date  ? substr($pigment->want_date, 0, 10)  : '';
@endphp

<form id="pg_edit_form">
    <input type="hidden" name="id" value="{{ $pigment->id }}">

    {{-- ── ข้อมูลอ้างอิง (อ่านอย่างเดียว) ── --}}
    <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
        <span class="badge bg-label-success">PIGMENT</span>
        <span class="text-muted small">Order No.: <strong class="text-body">{{ $pigment->orderno ?? '-' }}</strong></span>
        <span class="ms-auto">สถานะ: <span class="badge {{ $statusCls }}">{{ $pigment->statusLabel() }}</span></span>
    </div>

    @unless($isRequest)
        <div class="alert alert-secondary py-2">
            <i class="ti ti-lock me-1"></i>รายการนี้ถูกดำเนินการแล้ว ไม่สามารถแก้ไข/อนุมัติได้
        </div>
    @endunless

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Cust No.</label>
            <input type="text" class="form-control" name="custno" value="{{ $pigment->custno }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">วันที่สั่ง</label>
            <input type="text" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป" name="mdate" value="{{ $r_mdate }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">วันที่ต้องการรับ</label>
            <input type="text" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป" name="custwant" value="{{ $r_custwant }}" {{ $ro }}>
        </div>
        <div class="col-md-12 mb-3">
            <label class="form-label">Item No. <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="itemno" value="{{ $pigment->itemno }}" {{ $ro }}>
        </div>
    </div>

    <hr class="my-2">

    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">ยอดคงเหลือวันนี้ (Balance)</label>
            <input type="number" step="any" class="form-control" name="balance" value="{{ $pigment->balance }}" {{ $ro }}>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">ยอดใช้ย้อนหลัง 2 เดือน</label>
            <input type="number" step="any" class="form-control" name="retrospective" value="{{ $pigment->retrospective }}" {{ $ro }}>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">น้ำหนักที่จะใช้ (weight_request)</label>
            <input type="number" step="any" class="form-control" name="weight_request" value="{{ $pigment->weight_request }}" {{ $ro }}>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">น้ำหนักที่จะผลิต (weight_production)</label>
            <input type="number" step="any" class="form-control" name="weight_production" value="{{ $pigment->weight_production }}" {{ $ro }}>
        </div>
    </div>

    {{-- ── ผลการอนุมัติ (แสดงเฉพาะรายการที่อนุมัติแล้ว/ไม่อนุมัติ — อ่านอย่างเดียว) ── --}}
    @unless($isRequest)
        <hr class="my-2">
        <div class="row">
            <div class="col-12">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">สถานะ</th>
                            <th class="text-center">ผู้อนุมัติ</th>
                            <th class="text-center">วันที่อนุมัติ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center"><span class="badge {{ $statusCls }}">{{ $pigment->statusLabel() }}</span></td>
                            <td class="text-center">{{ $pigment->approver?->name ?? '-' }}</td>
                            <td class="text-center">{{ $pigment->approve_date ? date('d/m/Y H:i', strtotime($pigment->approve_date)) : '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if($pigment->result_planning_id)
            <div class="row mt-2">
                <div class="col-12">
                    <div class="alert alert-success mb-0 py-2">
                        <i class="ti ti-checks me-1"></i>สร้างแผนการผลิตแล้ว (Planning ID: {{ $pigment->result_planning_id }})
                    </div>
                </div>
            </div>
        @endif
    @endunless
</form>

<div class="d-flex justify-content-end gap-2 pt-2 border-top">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="ti ti-x me-1"></i>ปิด
    </button>
    @if($isRequest)
        <button type="button" class="btn btn-danger btn_reject" data-id="{{ $pigment->id }}">
            <i class="ti ti-x me-1"></i>ไม่อนุมัติ
        </button>
        <button type="button" class="btn btn-success btn_approve" data-id="{{ $pigment->id }}">
            <i class="ti ti-check me-1"></i>อนุมัติ
        </button>
        <button type="button" class="btn btn-primary" id="btn_pg_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึกข้อมูล
        </button>
    @endif
</div>

<script>
    // ปรับหัว modal ตามสถานะ — รออนุมัติ = "แก้ไข" (เขียว), อนุมัติแล้ว = "รายละเอียด" (เขียว),
    // ไม่อนุมัติ = "รายละเอียด" (แดง) เพื่อสื่อว่ารายการอ่านอย่างเดียว ไม่ใช่แก้ได้
    (function () {
        var header = {
            'request':  { title: '<i class="ti ti-pencil me-1"></i>แก้ไข Pigment',              bg: '#28a745' },
            'approved': { title: '<i class="ti ti-file-description me-1"></i>รายละเอียด Pigment (อนุมัติแล้ว)', bg: '#28a745' },
            'reject':   { title: '<i class="ti ti-file-description me-1"></i>รายละเอียด Pigment (ไม่อนุมัติ)',  bg: '#dc3545' }
        }['{{ $pigment->status }}'] || { title: '<i class="ti ti-pencil me-1"></i>แก้ไข Pigment', bg: '#28a745' };

        var $mh = $('#editModal .modal-header');
        $mh.css('background-color', header.bg);
        $mh.find('.modal-title').html(header.title);
    })();

    // flatpickr: แสดง d/m/Y เหมือนกันทุกเครื่อง แต่ค่าจริง (input.value) ยังเป็น Y-m-d
    // → $('#pg_edit_form').serialize() ส่ง Y-m-d ให้ server เหมือนเดิม ไม่ต้องแก้ฝั่ง PHP
    // (partial ถูกฉีดด้วย .html() → script นี้รันตอนถูกฉีด)
    $('#pg_edit_form .flatpickr-date').each(function () {
        if (this._flatpickr) return;
        // รายการที่ดำเนินการแล้ว (disabled) → แสดงอย่างเดียว ห้ามเปิดปฏิทิน/แก้ไข
        var readOnly = this.disabled || this.readOnly;
        var fp = flatpickr(this, {
            dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y',
            allowInput: true, disableMobile: true, clickOpens: !readOnly
        });
        if (readOnly && fp.altInput) {
            fp.altInput.setAttribute('readonly', 'readonly');
            fp.altInput.classList.add('bg-light');
        }
    });
</script>
