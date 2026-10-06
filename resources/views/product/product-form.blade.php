{{-- 06/10/2569: เปลี่ยนเป็น label อยู่ซ้าย ต่อด้วยช่องกรอก แบบฟอร์ม Order
     (โครง .of-form / .of-row / .of-ctl — CSS กลางใน layout/inc_header) --}}
<form id="product_master_form" class="of-form" style="--of-lw: 150px;">
    @csrf
    <input type="hidden" name="id" value="{{ $product?->id }}">

    <div class="row gx-3 gy-2 mb-3">
        <div class="col-md-6 of-row">
            <label class="form-label" for="product_code">
                รหัสสินค้า <span class="text-danger">*</span>
            </label>
            <div class="of-ctl">
                <input type="text" class="form-control" id="product_code" name="product_code"
                    value="{{ $product?->product_code }}" placeholder="กรอกรหัสสินค้า" maxlength="255">
            </div>
        </div>

        <div class="col-md-6 of-row">
            <label class="form-label" for="product_resin">เรซิน (Resin)</label>
            <div class="of-ctl">
                <input type="text" class="form-control" id="product_resin" name="resin"
                    value="{{ $product?->resin }}" placeholder="กรอกเรซิน">
            </div>
        </div>

        {{-- 06/10/2569: ปุ่ม "+ เพิ่ม Temperature" ย้ายจากใน label ไปอยู่ขวาของ select
             (select ห่อใน div.flex-grow-1 ของตัวเอง — enhanceSelects() จะห่อ select เพิ่ม ห้ามเป็นลูกตรงของ flex) --}}
        <div class="col-md-12 of-row">
            <label class="form-label" for="product_temp_id">
                <span>Temp</span>
            </label>
            <div class="of-ctl d-flex align-items-center gap-2">
                <div class="flex-grow-1" style="min-width: 0;">
                    <select class="form-select" id="product_temp_id" name="temp_id">
                        <option value="">- ไม่ระบุ -</option>
                        @foreach ($temps as $temp)
                            <option value="{{ $temp->id }}"
                                {{ (string) $product?->temp_id === (string) $temp->id ? 'selected' : '' }}>
                                {{ $temp->Temp1 }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- เพิ่ม Temperature ใหม่ทันทีจากฟอร์มนี้ (เปิด modal ซ้อน ใช้ฟอร์ม/endpoint เดียวกับหน้าจัดการ Temperature)
                     แสดงเฉพาะบัญชีที่มีสิทธิ์เมนู "Temperature" — เพราะ endpoint temp.edit/store ถูกกันสิทธิ์ตาม namespace temp อยู่แล้ว --}}
                @if(\App\Services\AccessControl::menuVisible('Temp'))
                    <button type="button" id="btn_add_temp_inline"
                        class="btn btn-sm btn-label-primary py-0 px-1 lh-1" title="เพิ่ม Temperature ใหม่">
                        <i class="ti ti-plus"></i>
                    </button>
                @endif
            </div>
        </div>

        <div class="col-md-6 of-row">
            <label class="form-label" for="product_code_field">Code</label>
            <div class="of-ctl">
                <input type="text" class="form-control" id="product_code_field" name="code"
                    value="{{ $product?->code }}" placeholder="กรอก Code">
            </div>
        </div>

        <div class="col-md-6 of-row">
            <label class="form-label" for="product_pack">ขนาดบรรจุ (Packaging)</label>
            <div class="of-ctl">
                <input type="text" class="form-control" id="product_pack" name="pack"
                    value="{{ $product?->pack }}" placeholder="กรอกขนาดบรรจุ">
            </div>
        </div>

        <div class="col-md-6 of-row">
            <label class="form-label" for="product_batch">นน ต่อชุด (Batch)</label>
            <div class="of-ctl">
                <input type="text" class="form-control" id="product_batch" name="batch"
                    value="{{ $product?->batch }}" placeholder="กรอก Batch">
            </div>
        </div>

        <div class="col-md-6 of-row">
            <label class="form-label" for="product_sampling">สุ่มตัวอย่าง (Sampling)</label>
            <div class="of-ctl">
                <input type="text" class="form-control" id="product_sampling" name="sampling"
                    value="{{ $product?->sampling }}" placeholder="สุ่มตัวอย่าง">
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="btn_product_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึก
        </button>
    </div>
</form>
