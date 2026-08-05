<form id="temp_master_form">
    @csrf
    <input type="hidden" name="id" value="{{ $temp?->id }}">

    <div class="mb-3">
        <label class="form-label" for="temp_name">
            ชื่อ <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control" id="temp_name" name="name"
            value="{{ $temp?->Temp1 }}" placeholder="กรอกชื่อ">
    </div>

    <div class="mb-3">
        <label class="form-label" for="temp_sort">ลำดับ (Sort)</label>
        <input type="number" class="form-control" id="temp_sort" name="sort"
            value="{{ $temp?->sort ?? 0 }}" placeholder="0">
    </div>

    <div class="mb-3">
        <label class="form-label d-block">สถานะการใช้งาน</label>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch"
                id="temp_is_active" name="is_active" value="Y"
                {{ ($temp?->is_active ?? 'Y') === 'Y' ? 'checked' : '' }}>
            <label class="form-check-label" for="temp_is_active">เปิดใช้งาน</label>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="btn_temp_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึก
        </button>
    </div>
</form>
