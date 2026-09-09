{{-- Modal บันทึกผล QC --}}
<div class="modal-header">
    <h5 class="modal-title"><i class="ti ti-clipboard-check me-1"></i>บันทึกผล QC</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <input type="hidden" id="qc_planning_id" value="{{ $job->id }}">

    <div class="row g-2 mb-3">
        <div class="col-6">
            <div class="small text-muted">รหัสสี</div>
            <div class="fw-semibold">{{ $job->itemno ?: '-' }}</div>
        </div>
        <div class="col-6">
            <div class="small text-muted">รหัสเครื่อง</div>
            <div class="fw-semibold">{{ $job->machine_no ?: '-' }}</div>
        </div>
        <div class="col-6">
            <div class="small text-muted">เลขที่ใบเบิก</div>
            <div class="fw-semibold">{{ $job->red_bill_code ?: '-' }}</div>
        </div>
        <div class="col-6">
            <div class="small text-muted">ผล QC ปัจจุบัน</div>
            <div class="fw-semibold">
                @if(optional($job->qcStatus)->name)
                    <span class="badge bg-label-success">{{ $job->qcStatus->name }}</span>
                @else
                    ยังไม่ตรวจ
                @endif
            </div>
        </div>
    </div>

    <label class="form-label">เลือกผล QC</label>
    <select id="qc_status_select" class="form-select">
        <option value="">-- เลือกผล QC --</option>
        @forelse($statuses as $st)
            <option value="{{ $st->id }}" @selected((int) $job->qc_status === (int) $st->id)>{{ $st->name }}</option>
        @empty
            <option value="" disabled>ยังไม่มีสถานะ QC ในระบบ</option>
        @endforelse
    </select>
    @if($statuses->isEmpty())
        <div class="text-warning small mt-2"><i class="ti ti-alert-triangle me-1"></i>ยังไม่มีสถานะ QC ที่กำหนดไว้ (เพิ่มได้ที่เมนู "สถานะ QC")</div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
    <button type="button" class="btn btn-primary" onclick="submitResult()" @disabled($statuses->isEmpty())>
        <i class="ti ti-device-floppy me-1"></i>บันทึก
    </button>
</div>
