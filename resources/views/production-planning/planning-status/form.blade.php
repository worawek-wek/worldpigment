<div class="modal-header" style="background-color: #3A8EBA; padding: 1rem 1.5rem;">
    <h5 class="modal-title text-white mb-0">
        <i class="ti ti-{{ $planning_status ? 'pencil' : 'plus' }} me-1"></i>
        {{ $planning_status ? 'แก้ไขสถานะ Planning' : 'เพิ่มสถานะ Planning' }}
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <form id="planning_status_form">
        <input type="hidden" name="id" value="{{ $planning_status?->id ?? '' }}">

        <div class="mb-3">
            <label class="form-label">แผนก (Dept)</label>
            <select name="dept" class="form-select">
                <option value="">เลือกแผนก</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ (string)($planning_status?->dept ?? '') === (string)$department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">ชื่อสถานะ <span class="text-danger">*</span></label>
            <input type="text" name="name"
                   value="{{ $planning_status?->name ?? '' }}"
                   class="form-control" placeholder="กรอกชื่อสถานะ">
        </div>

        <div class="mb-3">
            <label class="form-label">ลำดับการเรียง (Sort)</label>
            <input type="number" name="sort" min="0"
                   value="{{ $planning_status?->sort ?? 0 }}"
                   class="form-control" placeholder="0">
        </div>

        <div class="mb-3">
            <label class="form-label">สถานะการใช้งาน</label>
            <select name="is_active" class="form-select">
                <option value="Y" {{ ($planning_status?->is_active ?? 'Y') === 'Y' ? 'selected' : '' }}>เปิดใช้งาน</option>
                <option value="N" {{ ($planning_status?->is_active ?? '') === 'N' ? 'selected' : '' }}>ปิดใช้งาน</option>
            </select>
        </div>
    </form>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="ti ti-x me-1"></i>ยกเลิก
    </button>
    <button type="button" class="btn btn-primary" id="btn_save_planning_status">
        <i class="ti ti-device-floppy me-1"></i>บันทึก
    </button>
</div>
