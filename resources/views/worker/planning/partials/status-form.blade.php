{{-- Modal อัพเดทสถานะงาน (Worker) --}}
<div class="modal-header">
    <h5 class="modal-title"><i class="ti ti-edit me-1"></i>อัพเดทสถานะงาน</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <input type="hidden" id="status_planning_id" value="{{ $job->id }}">

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
            <div class="small text-muted">สถานะปัจจุบัน</div>
            <div class="fw-semibold">{{ $job->planning_status ?: 'ยังไม่ระบุ' }}</div>
        </div>
    </div>

    <label class="form-label">เลือกสถานะใหม่</label>
    <select id="status_select" class="form-select">
        <option value="">-- เลือกสถานะ --</option>
        @forelse($statuses as $st)
            <option value="{{ $st->name }}" @selected($job->planning_status === $st->name)>{{ $st->name }}</option>
        @empty
            <option value="" disabled>ไม่มีสถานะของแผนกนี้</option>
        @endforelse
    </select>
    @if($statuses->isEmpty())
        <div class="text-warning small mt-2"><i class="ti ti-alert-triangle me-1"></i>ยังไม่มีสถานะที่กำหนดไว้สำหรับแผนกของงานนี้</div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
    <button type="button" class="btn btn-primary" onclick="submitStatus()" @disabled($statuses->isEmpty())>
        <i class="ti ti-device-floppy me-1"></i>บันทึก
    </button>
</div>
