<form id="machine_form">
    @csrf
    <input type="hidden" name="id" value="{{ $machine?->id }}">

    <div class="mb-3">
        @php
            $deptNames   = $departments->pluck('name')->all();
            $currentDept = $machine?->dept;
        @endphp
        <label class="form-label" for="machine_dept">
            แผนก (Dept) <span class="text-danger">*</span>
        </label>
        <select class="form-select" id="machine_dept" name="dept">
            <option value="">เลือกแผนก</option>
            {{-- แถวเดิมที่ dept ไม่ตรงกับแผนกใน master (เช่น SP, TN) แสดงค่าเดิมไว้ให้เห็น/เลือกใหม่ --}}
            @if($currentDept && !in_array($currentDept, $deptNames))
                <option value="{{ $currentDept }}" selected>{{ $currentDept }} (เดิม)</option>
            @endif
            @foreach($departments as $dept)
                <option value="{{ $dept->name }}" {{ $currentDept === $dept->name ? 'selected' : '' }}>
                    {{ $dept->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label" for="machine_MBX">
            ชื่อ/รหัสเครื่องจักร (MBX) <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control" id="machine_MBX" name="MBX" maxlength="20"
            value="{{ $machine?->MBX }}" placeholder="กรอกชื่อ/รหัสเครื่องจักร">
    </div>

    <div class="mb-3">
        <label class="form-label" for="machine_speed_rpm">ความเร็วรอบ (Speed RPM)</label>
        <input type="text" class="form-control" id="machine_speed_rpm" name="speed_rpm" maxlength="255"
            value="{{ $machine?->speed_rpm }}" placeholder="กรอกความเร็วรอบ (ถ้ามี)">
    </div>

    <div class="mb-3">
        <label class="form-label" for="machine_group">ประเภท/กลุ่ม (Group)</label>
        <input type="text" class="form-control" id="machine_group" name="group" maxlength="50"
            value="{{ $machine?->group }}" placeholder="กรอกประเภท/กลุ่ม (ถ้ามี)">
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="btn_machine_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึก
        </button>
    </div>
</form>
