<form id="workshift_master_form">
    @csrf
    <input type="hidden" name="id" value="{{ $item?->id }}">

    <div class="mb-2">
        <label class="form-label" for="workshift_name">
            กะการผลิต <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control" id="workshift_name" name="name"
            value="{{ $item?->name }}" maxlength="50" placeholder="กรอกชื่อกะการผลิต">
    </div>

    <div class="mb-2">
        <label class="form-label" for="workshift_sort">ลำดับ</label>
        <input type="number" class="form-control" id="workshift_sort" name="sort"
            value="{{ $item?->sort ?? 0 }}" min="0" placeholder="0">
        <small class="text-muted">เลขน้อยแสดงก่อน</small>
    </div>

    <div class="mb-2">
        <label class="form-label d-block">สถานะการใช้งาน</label>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch"
                id="workshift_is_active" name="is_active" value="Y"
                {{ ($item?->is_active ?? 'Y') === 'Y' ? 'checked' : '' }}>
            <label class="form-check-label" for="workshift_is_active">เปิดใช้งาน</label>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="btn_workshift_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึก
        </button>
    </div>
</form>
