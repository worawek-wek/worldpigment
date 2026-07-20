<form id="prod_method_master_form">
    @csrf
    <input type="hidden" name="id" value="{{ $method?->id }}">

    <div class="mb-3">
        <label class="form-label" for="prod_method_name">
            ชื่อวิธีการผลิต <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control" id="prod_method_name" name="name"
            value="{{ $method?->name }}" placeholder="กรอกชื่อวิธีการผลิต">
    </div>

    <div class="mb-3">
        @php $currentDept = $method?->dept; $deptNames = $departments->pluck('name')->all(); @endphp
        <label class="form-label" for="prod_method_dept">แผนก (Dept)</label>
        <select class="form-select" id="prod_method_dept" name="dept">
            <option value="">-- เลือกแผนก --</option>
            {{-- ค่าเดิมที่ไม่อยู่ในรายชื่อแผนกปัจจุบัน (ปิดใช้งาน/ลบ) ให้แสดงไว้ --}}
            @if($currentDept && !in_array($currentDept, $deptNames))
                <option value="{{ $currentDept }}" selected>{{ $currentDept }} (เดิม)</option>
            @endif
            @foreach($departments as $department)
                <option value="{{ $department->name }}" {{ $currentDept === $department->name ? 'selected' : '' }}>
                    {{ $department->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label" for="prod_method_sort">ลำดับ (Sort)</label>
        <input type="number" class="form-control" id="prod_method_sort" name="sort"
            value="{{ $method?->sort ?? 0 }}" placeholder="0">
    </div>

    <div class="mb-3">
        <label class="form-label d-block">สถานะการใช้งาน</label>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch"
                id="prod_method_is_active" name="is_active" value="Y"
                {{ ($method?->is_active ?? 'Y') === 'Y' ? 'checked' : '' }}>
            <label class="form-check-label" for="prod_method_is_active">เปิดใช้งาน</label>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="btn_prod_method_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึก
        </button>
    </div>
</form>
