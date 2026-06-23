@php
    $typeCls = $sp->type === 'semi' ? 'bg-label-primary' : 'bg-label-success';
@endphp
<div class="row g-3">
    <div class="row">
        <div class="col-md-6">
            <label class="form-label text-muted small d-block">ประเภท</label>
            <span class="badge {{ $typeCls }}">{{ strtoupper($sp->type) }}</span>
        </div>
        <div class="col-md-6" style="text-align: right">
            <label class="form-label text-muted small d-block">เลขที่ใบสั่ง (Order No.)</label>
            <span class="fw-semibold">{{ $sp->orderno ?? '-' }}</span>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-3">
            <label class="form-label text-muted small d-block">Company</label>
            <span class="fw-semibold">{{ $sp->company ?? '-' }}</span>
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small d-block">รหัสลูกค้า</label>
            <span class="fw-semibold">{{ $sp->custno ?? '-' }}</span>
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small d-block">วันที่สั่ง</label>
            <span class="fw-semibold">{{ $sp->order_date ? date('Y-m-d', strtotime($sp->order_date)) : '-' }}</span>
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small d-block">วันที่ต้องการรับ</label>
            <span class="fw-semibold">{{ $sp->want_date ? date('Y-m-d', strtotime($sp->want_date)) : '-' }}</span>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <label class="form-label text-muted small d-block">Item No.</label>
            <span class="fw-semibold">{{ $sp->itemno ?? '-' }}</span>
        </div>
        <div class="col-md-4">
            <label class="form-label text-muted small d-block">Semi Code</label>
            <span class="fw-semibold">{{ $sp->semi_code ?? '-' }}</span>
        </div>
        <div class="col-md-4">
            <label class="form-label text-muted small d-block">แม่สี (Primary Color)</label>
            <span class="fw-semibold">{{ $sp->primary_color ?? '-' }}</span>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-3">
            <label class="form-label text-muted small d-block">Lot No.</label>
            <span class="fw-semibold">{{ $sp->lot_no ?? '-' }}</span>
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small d-block">เลขที่ใบเบิกออกใบแดง</label>
            <span class="fw-semibold">{{ $sp->red_bill_code ?? '-' }}</span>
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small d-block">ยอดคงเหลือวันนี้ (Balance)</label>
            <span class="fw-semibold">{{ $sp->balance !== null ? number_format((float) $sp->balance, 2) : '-' }}</span>
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small d-block">ยอดใช้ย้อนหลัง 2 เดือน</label>
            <span class="fw-semibold">{{ $sp->retrospective !== null ? number_format((float) $sp->retrospective, 2) : '-' }}</span>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <label class="form-label text-muted small d-block">น้ำหนักที่จะใช้ (Weight Request)</label>
            <span class="fw-semibold">{{ $sp->weight_request !== null ? number_format((float) $sp->weight_request, 2) : '-' }}</span>
        </div>
        <div class="col-md-4">
            <label class="form-label text-muted small d-block">ผลิตเพิ่ม (Increase)</label>
            <span class="fw-semibold">{{ $sp->increase_production !== null ? number_format((float) $sp->increase_production, 2) : '-' }}</span>
        </div>
        <div class="col-md-4">
            <label class="form-label text-muted small d-block">น้ำหนักที่จะผลิต (Weight Production)</label>
            <span class="fw-semibold">{{ $sp->weight_production !== null ? number_format((float) $sp->weight_production, 2) : '-' }}</span>
        </div>
    </div>

    <div class="row mt-3">
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
                        <td class="text-center">{{ $sp->statusLabel() }}</td>
                        <td class="text-center">{{ $sp->approver_code ?? '-' }}</td>
                        <td class="text-center">{{ $sp->approve_date ? date('Y-m-d H:i', strtotime($sp->approve_date)) : '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if($sp->result_planning_id)
    <div class="row mt-2">
        <div class="col-12">
            <div class="alert alert-success mb-0 py-2">
                <i class="ti ti-checks me-1"></i>สร้างแผนการผลิตแล้ว (Planning ID: {{ $sp->result_planning_id }})
            </div>
        </div>
    </div>
    @endif
</div>
