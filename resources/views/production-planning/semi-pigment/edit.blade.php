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
            <label class="form-label">แผนกผลิต (Company)</label>
            <select class="form-select" name="company" {{ $ro }}>
                <option value="">-- เลือก --</option>
                @foreach($companies as $c)
                    <option value="{{ $c }}" {{ $sp->company === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">แผนกที่ใช้ (Cust No.)</label>
            <input type="text" class="form-control" name="custno" value="{{ $sp->custno }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Semi No. (Item No) <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="itemno" value="{{ $sp->itemno }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">วันที่สั่ง</label>
            <input type="text" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป" name="mdate" value="{{ $r_mdate }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">วันที่ต้องการรับ</label>
            <input type="text" class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป" name="custwant" value="{{ $r_custwant }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">ขาด Semi Code</label>
            <input type="text" class="form-control" name="semi_code" value="{{ $sp->semi_code }}" {{ $ro }}
            placeholder="รหัสกึ่งสำเร็จรูป (ปิดชั่วคราว)" disabled>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">แม่สีหลัก (Primary Color)</label>
            <input type="text" class="form-control" name="primary_color" value="{{ $sp->primary_color }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Lot No.</label>
            <input type="text" class="form-control" name="lot_no" value="{{ $sp->lot_no }}" {{ $ro }}>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">เลขที่ใบเบิกออกใบแดง (Red Bill)</label>
            <input type="text" class="form-control" name="red_bill_code" value="{{ $sp->red_bill_code }}" {{ $ro }}
            placeholder="เลขที่ใบเบิก (ปิดชั่วคราว)" disabled>
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

    {{-- ── ข้อมูลการอนุมัติ (แสดงเฉพาะรายการที่ดำเนินการแล้ว — ย้ายมาจาก detail modal เดิม) ── --}}
    @unless($isRequest)
        <hr class="my-2">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-2">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">ผู้อนุมัติ</th>
                        <th class="text-center">วันที่อนุมัติ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">{{ $sp->statusLabel() }}</td>
                        <td class="text-center">{{ $sp->approver?->name ?? '-' }}</td>
                        <td class="text-center">{{ $sp->approve_date ? date('d/m/Y H:i', strtotime($sp->approve_date)) : '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($sp->result_planning_id)
            <div class="alert alert-success mb-0 py-2">
                <i class="ti ti-checks me-1"></i>สร้างแผนการผลิตแล้ว (Planning ID: {{ $sp->result_planning_id }})
            </div>
        @endif
    @endunless
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

<script>
    // flatpickr: แสดง d/m/Y เหมือนกันทุกเครื่อง แต่ค่าจริง (input.value) ยังเป็น Y-m-d
    // → $('#sp_edit_form').serialize() ส่ง Y-m-d ให้ server เหมือนเดิม ไม่ต้องแก้ฝั่ง PHP
    // (partial ถูกฉีดด้วย .html() → script นี้รันตอนถูกฉีด)
    $('#sp_edit_form .flatpickr-date').each(function () {
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

    // น้ำหนักที่จะผลิต = น้ำหนักที่จะใช้ + ผลิตเพิ่ม (คำนวณอัตโนมัติ แต่แก้เองได้)
    // เหมือน Modal เพิ่ม Semi ในหน้า planning (planning/index.blade.php)
    // ผูกตรงกับ input ของฟอร์มที่เพิ่งถูกฉีด (ไม่ใช้ delegated กัน handler สะสมทุกครั้งที่เปิด modal)
    // ไม่คำนวณตอนโหลด → ไม่ทับค่า weight_production เดิมที่บันทึกไว้ จะคำนวณเฉพาะเมื่อผู้ใช้แก้ 2 ช่องต้นทาง
    (function () {
        var $form = $('#sp_edit_form');
        var $req  = $form.find('[name="weight_request"]');
        var $inc  = $form.find('[name="increase_production"]');
        var $prod = $form.find('[name="weight_production"]');
        if (!$req.length || !$inc.length || !$prod.length) return;

        var spProdManual = false;
        function spNum(v) { v = parseFloat(v); return isNaN(v) ? 0 : v; }
        function spRound(v) { return Math.round(v * 100) / 100; }
        function spRecalc() {
            if (spProdManual) return;
            var req = ($req.val() || '').trim();
            var inc = ($inc.val() || '').trim();
            if (req === '' && inc === '') { $prod.val(''); return; }
            $prod.val(spRound(spNum(req) + spNum(inc)));
        }

        $req.add($inc).on('input', function () { spProdManual = false; spRecalc(); });
        $prod.on('input', function () { spProdManual = true; });
    })();
</script>
