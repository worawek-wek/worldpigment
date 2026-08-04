@php $isEdit = (bool) $employee; @endphp
<form id="employee_form">
    @csrf

    {{-- แก้ไข: ส่งคีย์เดิมมาด้วย (empno ในฟอร์มถูก readonly) --}}
    @if($isEdit)
        <input type="hidden" name="edit_empno" value="{{ $employee->empno }}">
    @endif

    <div class="mb-3">
        <label class="form-label" for="employee_empno">
            รหัสพนักงาน (Emp No.) <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control{{ $isEdit ? ' bg-light' : '' }}" id="employee_empno" name="empno"
            value="{{ $employee?->empno }}" maxlength="4" placeholder="กรอกรหัสพนักงาน (สูงสุด 4 ตัว)"
            {{ $isEdit ? 'readonly' : '' }}>
    </div>

    <div class="mb-3">
        <label class="form-label" for="employee_empname">
            ชื่อ <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control" id="employee_empname" name="empname"
            value="{{ $employee?->empname }}" maxlength="10" placeholder="กรอกชื่อ">
    </div>

    <div class="mb-3">
        <label class="form-label" for="employee_empsur">นามสกุล</label>
        <input type="text" class="form-control" id="employee_empsur" name="empsur"
            value="{{ $employee?->empsur }}" maxlength="20" placeholder="กรอกนามสกุล">
    </div>

    <div class="mb-3">
        <label class="form-label" for="employee_supno">รหัสสังกัด (Sup No.)</label>
        <input type="text" class="form-control" id="employee_supno" name="supno"
            value="{{ $employee?->supno }}" maxlength="2" placeholder="รหัสสังกัด (สูงสุด 2 ตัว)">
    </div>

    <div class="mb-3">
        <label class="form-label" for="employee_department">แผนก</label>
        <select class="form-select" id="employee_department" name="dept">
            <option value="">-- เลือกแผนก --</option>
            @foreach($departments as $department)
                <option value="{{ $department->name }}"
                    {{ ($employee?->dept ?? '') === $department->name ? 'selected' : '' }}>
                    {{ $department->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label" for="employee_role">สิทธิ์การใช้งาน (Role)</label>
        <select class="form-select" id="employee_role" name="role_id">
            <option value="">-- เลือก Role --</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}"
                    {{ (string)($employee?->role_id ?? '') === (string)$role->id ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label" for="employee_user">ชื่อผู้ใช้ (User)</label>
        <input type="text" class="form-control" id="employee_user" name="user"
            value="{{ $employee?->user }}" maxlength="50" placeholder="ชื่อผู้ใช้สำหรับเข้าระบบ" autocomplete="off">
    </div>

    <div class="mb-3">
        <label class="form-label" for="employee_password">รหัสผ่าน (Password)</label>
        <input type="password" class="form-control" id="employee_password" name="password"
            maxlength="255" placeholder="{{ $isEdit ? 'เว้นว่างถ้าไม่เปลี่ยนรหัสผ่าน' : 'กรอกรหัสผ่าน (อย่างน้อย 4 ตัว)' }}"
            autocomplete="new-password">
        @if($isEdit)
            <small class="text-muted">เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่านเดิม</small>
        @endif
    </div>

    <div class="mb-3">
        <label class="form-label" for="employee_signature">ลายเซ็น (Signature)</label>
        <input type="file" class="form-control" id="employee_signature" name="signature" accept="image/*">
        <small class="text-muted">รองรับไฟล์รูปภาพ (jpeg, png, jpg, gif, webp) ขนาดไม่เกิน 2MB{{ $isEdit ? ' — เว้นว่างไว้หากไม่ต้องการเปลี่ยนลายเซ็นเดิม' : '' }}</small>
        <div class="mt-2" id="employee_signature_box" style="{{ $employee?->signature ? '' : 'display:none;' }}">
            <img id="employee_signature_preview" alt="ลายเซ็น" class="img-thumbnail d-block"
                style="max-width: 250px;"
                src="{{ $employee?->signature ? '/upload/employees/' . $employee->signature : '' }}">
            <button type="button" class="btn btn-sm btn-outline-danger mt-1" id="btn_remove_signature">
                <i class="ti ti-trash me-1"></i>ลบรูปลายเซ็น
            </button>
        </div>
        {{-- flag สั่งลบลายเซ็นเดิมตอนบันทึก (1 = ลบ) --}}
        <input type="hidden" name="remove_signature" id="employee_remove_signature" value="0">
    </div>

    <div class="mb-3">
        <label class="form-label d-block">สถานะการใช้งาน</label>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch"
                id="employee_is_active" name="is_active" value="Y"
                {{ ($employee?->is_active ?? 'Y') === 'Y' ? 'checked' : '' }}>
            <label class="form-check-label" for="employee_is_active">เปิดใช้งาน</label>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="btn_employee_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึก
        </button>
    </div>
</form>
