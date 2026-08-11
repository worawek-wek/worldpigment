{{-- Modal ดูรายละเอียดงาน (อ่านอย่างเดียว) — Worker --}}
<div class="modal-header">
    <h5 class="modal-title"><i class="ti ti-eye me-1"></i>รายละเอียดงาน (อ่านอย่างเดียว)</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="small text-muted">รหัสสี</div>
            <div class="fw-semibold">{{ $job->itemno ?: '-' }}</div>
        </div>
        <div class="col-md-4">
            <div class="small text-muted">เลขที่ใบเบิก</div>
            <div class="fw-semibold">{{ $job->red_bill_code ?: '-' }}</div>
        </div>
        <div class="col-md-4">
            <div class="small text-muted">รหัสเครื่อง</div>
            <div class="fw-semibold">{{ $job->machine_no ?: '-' }}</div>
        </div>
        <div class="col-md-4">
            <div class="small text-muted">จำนวน</div>
            <div class="fw-semibold">{{ $job->quantity !== null ? number_format($job->quantity, 2) : '-' }}</div>
        </div>
        <div class="col-md-4">
            <div class="small text-muted">Lot</div>
            <div class="fw-semibold">{{ $job->lot ?: '-' }}</div>
        </div>
        <div class="col-md-4">
            <div class="small text-muted">แผนก</div>
            <div class="fw-semibold">{{ $company ?: '-' }}</div>
        </div>
        <div class="col-md-4">
            <div class="small text-muted">วันที่ (Inplan)</div>
            <div class="fw-semibold">{{ $job->inplan ? \Carbon\Carbon::parse($job->inplan)->format('d/m/Y') : '-' }}</div>
        </div>
        <div class="col-md-4">
            <div class="small text-muted">เลขที่ออเดอร์</div>
            <div class="fw-semibold">{{ optional($job->planning_header)->orderno ?: '-' }}</div>
        </div>
        <div class="col-md-4">
            <div class="small text-muted">สถานะปัจจุบัน</div>
            <div class="fw-semibold">
                @if($job->planning_status)
                    <span class="badge bg-label-info">{{ $job->planning_status }}</span>
                @else
                    <span class="badge bg-label-secondary">ยังไม่ระบุ</span>
                @endif
            </div>
        </div>
    </div>

    <hr class="my-3">

    <h6 class="mb-2"><i class="ti ti-history me-1"></i>ประวัติการเปลี่ยนสถานะ</h6>
    @if($logs->isEmpty())
        <div class="text-muted small">ยังไม่มีประวัติการเปลี่ยนสถานะ</div>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:150px;">เวลา</th>
                        <th>จากสถานะ</th>
                        <th>เป็นสถานะ</th>
                        <th style="width:120px;">โดย</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->changed_at ? \Carbon\Carbon::parse($log->changed_at)->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ $log->old_status ?: '-' }}</td>
                            <td>{{ $log->new_status ?: '-' }}</td>
                            <td>{{ $log->changed_by ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
</div>
