<form id="holiday_master_form">
    @csrf
    <input type="hidden" name="id" value="{{ $holiday?->id }}">

    <div class="mb-3">
        <label class="form-label" for="holiday_date">
            วันที่ <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control holiday-datepicker" id="holiday_date" name="holiday_date"
            autocomplete="off" placeholder="วว/ดด/ปปปป"
            value="{{ $holiday?->holiday_date ? date('d/m/Y', strtotime($holiday->holiday_date)) : '' }}">
        @if($holiday?->holiday_date)
            {{-- ย้ำวันที่เป็น พ.ศ. + ชื่อวัน กันเลือกผิดวัน --}}
            <small class="text-muted">
                {{ \App\Models\Holiday::thaiDate($holiday->holiday_date) }}
                (วัน{{ \App\Models\Holiday::thaiWeekday($holiday->holiday_date) }})
            </small>
        @endif
    </div>

    <div class="mb-3">
        <label class="form-label" for="holiday_name">
            ชื่อวันหยุด <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control" id="holiday_name" name="name"
            value="{{ $holiday?->name }}" maxlength="255" placeholder="เช่น วันขึ้นปีใหม่">
    </div>

    <div class="mb-3">
        <label class="form-label" for="holiday_type">
            ประเภทวันหยุด <span class="text-danger">*</span>
        </label>
        @php $currentType = $holiday?->type ?? 'public'; @endphp
        <select class="form-select no-enhance" id="holiday_type" name="type">
            @foreach($types as $key => $label)
                <option value="{{ $key }}" {{ $currentType === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label" for="holiday_remark">หมายเหตุ</label>
        <input type="text" class="form-control" id="holiday_remark" name="remark"
            value="{{ $holiday?->remark }}" maxlength="255" placeholder="เช่น ชดเชยวันวิสาขบูชา">
    </div>

    <div class="mb-3">
        <label class="form-label d-block">สถานะการใช้งาน</label>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch"
                id="holiday_is_active" name="is_active" value="Y"
                {{ ($holiday?->is_active ?? 'Y') === 'Y' ? 'checked' : '' }}>
            <label class="form-check-label" for="holiday_is_active">เปิดใช้งาน (นับเป็นวันหยุด)</label>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="btn_holiday_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึก
        </button>
    </div>
</form>
