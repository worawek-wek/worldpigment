{{-- ฟอร์มนี้ไม่ใช้ placeholder — ตัวอย่าง/รูปแบบที่ต้องกรอกอยู่ในวงเล็บข้าง label (01/09/2569) --}}
{{-- 06/10/2569: เปลี่ยนเป็น label อยู่ซ้าย ต่อด้วยช่องกรอก แบบฟอร์ม Order
     (โครง .of-form / .of-row / .of-ctl — CSS กลางใน layout/inc_header)
     ตัวอย่างในวงเล็บยังอยู่กับ label — ลงบรรทัดที่ 2 (d-block) --}}
<form id="holiday_master_form" class="of-form" style="--of-lw: 150px;">
    @csrf
    <input type="hidden" name="id" value="{{ $holiday?->id }}">

    <div class="mb-2 of-row">
        {{-- ช่องนี้เลือกจากปฏิทิน (flatpickr) จึงไม่ต้องบอกรูปแบบวันที่กำกับ --}}
        <label class="form-label" for="holiday_date">
            วันที่ <span class="text-danger">*</span>
        </label>
        <div class="of-ctl">
            <input type="text" class="form-control holiday-datepicker" id="holiday_date" name="holiday_date"
                autocomplete="off"
                value="{{ $holiday?->holiday_date ? date('d/m/Y', strtotime($holiday->holiday_date)) : '' }}">
            @if($holiday?->holiday_date)
                {{-- ย้ำวันที่เป็น พ.ศ. + ชื่อวัน กันเลือกผิดวัน --}}
                <small class="text-muted">
                    {{ \App\Models\Holiday::thaiDate($holiday->holiday_date) }}
                    (วัน{{ \App\Models\Holiday::thaiWeekday($holiday->holiday_date) }})
                </small>
            @endif
        </div>
    </div>

    <div class="mb-2 of-row">
        <label class="form-label" for="holiday_name">
            ชื่อวันหยุด <span class="text-danger">*</span>
            <span class="text-muted fw-normal d-block">(เช่น วันขึ้นปีใหม่)</span>
        </label>
        <div class="of-ctl">
            <input type="text" class="form-control" id="holiday_name" name="name"
                value="{{ $holiday?->name }}" maxlength="255">
        </div>
    </div>

    <div class="mb-2 of-row">
        <label class="form-label" for="holiday_type">
            ประเภทวันหยุด <span class="text-danger">*</span>
        </label>
        @php $currentType = $holiday?->type ?? 'public'; @endphp
        <div class="of-ctl">
            <select class="form-select no-enhance" id="holiday_type" name="type">
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" {{ $currentType === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mb-2 of-row">
        <label class="form-label" for="holiday_remark">
            หมายเหตุ
            <span class="text-muted fw-normal d-block">(เช่น ชดเชยวันวิสาขบูชา)</span>
        </label>
        <div class="of-ctl">
            <input type="text" class="form-control" id="holiday_remark" name="remark"
                value="{{ $holiday?->remark }}" maxlength="255">
        </div>
    </div>

    <div class="mb-3 of-row">
        <label class="form-label">สถานะการใช้งาน</label>
        {{-- mt-2 = ให้ switch ตรงแนวกับข้อความ label (label มี padding-top .5rem) --}}
        <div class="of-ctl">
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" role="switch"
                    id="holiday_is_active" name="is_active" value="Y"
                    {{ ($holiday?->is_active ?? 'Y') === 'Y' ? 'checked' : '' }}>
                <label class="form-check-label" for="holiday_is_active">เปิดใช้งาน (นับเป็นวันหยุด)</label>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        {{-- เปิดฟอร์มจากปฏิทิน (ไม่มีปุ่มลบในหน้าปฏิทิน) → ให้ลบได้จากในฟอร์มเลย
             ใช้ class btn_delete ตัวเดียวกับปุ่มลบในตาราง จึงได้ Swal ยืนยัน + โหลดตาราง/ปฏิทินใหม่ให้ในตัว --}}
        @if($showDelete ?? false)
            <button type="button" class="btn btn-label-danger me-auto btn_delete" data-id="{{ $holiday->id }}">
                <i class="ti ti-trash me-1"></i>ลบวันหยุดนี้
            </button>
        @endif
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="btn_holiday_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึก
        </button>
    </div>
</form>
