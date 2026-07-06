<div class="row g-3">
    <div class="row">
        <div class="col-md-6">
            <label class="form-label text-muted small d-block">ประเภท</label>
            <span class="badge bg-label-success">PIGMENT</span>
        </div>
        <div class="col-md-6" style="text-align: right">
            <label class="form-label text-muted small d-block">เลขที่ใบสั่ง (Order No.)</label>
            <span class="fw-semibold">{{ $pigment->orderno ?? '-' }}</span>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-4">
            <label class="form-label text-muted small d-block">รหัสลูกค้า</label>
            <span class="fw-semibold">{{ $pigment->custno ?? '-' }}</span>
        </div>
        <div class="col-md-4">
            <label class="form-label text-muted small d-block">วันที่สั่ง</label>
            <span class="fw-semibold">{{ $pigment->order_date ? date('d/m/Y', strtotime($pigment->order_date)) : '-' }}</span>
        </div>
        <div class="col-md-4">
            <label class="form-label text-muted small d-block">วันที่ต้องการรับ</label>
            <span class="fw-semibold">{{ $pigment->want_date ? date('d/m/Y', strtotime($pigment->want_date)) : '-' }}</span>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <label class="form-label text-muted small d-block">Item No.</label>
            <span class="fw-semibold">{{ $pigment->itemno ?? '-' }}</span>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-3">
            <label class="form-label text-muted small d-block">ยอดคงเหลือวันนี้ (Balance)</label>
            <span class="fw-semibold">{{ $pigment->balance !== null ? number_format((float) $pigment->balance, 2) : '-' }}</span>
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small d-block">ยอดใช้ย้อนหลัง 2 เดือน</label>
            <span class="fw-semibold">{{ $pigment->retrospective !== null ? number_format((float) $pigment->retrospective, 2) : '-' }}</span>
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small d-block">น้ำหนักที่จะใช้ (Weight Request)</label>
            <span class="fw-semibold">{{ $pigment->weight_request !== null ? number_format((float) $pigment->weight_request, 2) : '-' }}</span>
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small d-block">น้ำหนักที่จะผลิต (Weight Production)</label>
            <span class="fw-semibold">{{ $pigment->weight_production !== null ? number_format((float) $pigment->weight_production, 2) : '-' }}</span>
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
                        <td class="text-center">{{ $pigment->statusLabel() }}</td>
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
</div>
