<form id="department_form">
    @csrf
    <input type="hidden" name="id" value="{{ $department?->id }}">

    <div class="mb-3">
        <label class="form-label" for="department_name">
            ชื่อแผนก <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control" id="department_name" name="name"
            value="{{ $department?->name }}" placeholder="กรอกชื่อแผนก">
    </div>

    <div class="mb-3">
        <label class="form-label" for="department_description">รายละเอียด</label>
        <textarea class="form-control" id="department_description" name="description" rows="3"
            placeholder="รายละเอียดแผนก (ถ้ามี)">{{ $department?->description }}</textarea>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="btn_department_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึก
        </button>
    </div>
</form>
