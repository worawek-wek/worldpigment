{{-- ฟอร์มเพิ่ม/แก้ไขกลุ่มราคา (zcolorrate) — โหลดผ่าน AJAX เข้า modal 19/09/2569 --}}
<form id="colorrate_master_form">
    @csrf
    {{-- ตารางนี้ไม่มี id — ใช้ colorno เดิมเป็นตัวชี้แถว (ว่าง = เพิ่มใหม่) --}}
    <input type="hidden" name="id" value="{{ $item?->colorno }}">

    <div class="mb-3">
        <label class="form-label" for="cr_colorno">
            รหัสสินค้า <span class="text-danger">*</span>
            @if ($item)
                <span class="text-muted fw-normal small">(เป็นคีย์ของตาราง แก้ไม่ได้ — ถ้าผิดต้องลบแล้วเพิ่มใหม่)</span>
            @else
                <span class="text-muted fw-normal small">(สูงสุด 12 ตัวอักษร)</span>
            @endif
        </label>
        <input type="text" class="form-control text-uppercase" id="cr_colorno" name="colorno"
            value="{{ $item?->colorno }}" maxlength="12" autocomplete="off"
            {{ $item ? 'readonly' : '' }}>
    </div>

    <div class="row g-3">
        @foreach ($groups as $g)
            @php
                // key ของคอลัมน์ในตาราง = rate_A / rate_B / rate_C
                $col = 'rate_' . $g['group'];
            @endphp
            <div class="col-md-4">
                <label class="form-label" for="cr_{{ $col }}">
                    ราคากลุ่ม {{ $g['group'] }}
                    <span class="d-block text-muted fw-normal small">{{ $g['label'] }}</span>
                </label>
                <input type="text" class="form-control text-end js-comma" id="cr_{{ $col }}"
                    name="{{ $col }}" inputmode="decimal" autocomplete="off"
                    value="{{ $item?->$col !== null ? number_format((float) $item->$col, 2) : '' }}">
            </div>
        @endforeach
    </div>

    <div class="mb-3 mt-3">
        <label class="form-label" for="cr_RDate">วันที่ปรับราคา</label>
        <input type="text" class="form-control flatpickr-colorrate" id="cr_RDate" name="RDate"
            autocomplete="off"
            value="{{ $item?->RDate ? \Carbon\Carbon::parse($item->RDate)->format('d/m/Y') : now()->format('d/m/Y') }}">
        <small class="text-muted">ไม่กรอก = ใช้วันที่ปัจจุบัน</small>
    </div>

    <div class="alert alert-warning py-2 small mb-3">
        <i class="ti ti-alert-triangle me-1"></i>
        ราคาที่ตั้งไว้นี้ถูกใช้เป็น <strong>ราคาขั้นต่ำ</strong> ตอนบันทึกใบสั่งซื้อ —
        ถ้าราคาขายต่ำกว่าค่าของกลุ่มที่เข้าเกณฑ์ จะบันทึกไม่ได้จนกว่าจะขออนุมัติราคาพิเศษ
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="btn_colorrate_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึก
        </button>
    </div>
</form>
