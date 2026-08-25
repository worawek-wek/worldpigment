<form id="product_master_form">
    @csrf
    <input type="hidden" name="id" value="{{ $product?->id }}">

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="product_code">
                รหัสสินค้า <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="product_code" name="product_code"
                value="{{ $product?->product_code }}" placeholder="กรอกรหัสสินค้า">
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label" for="product_resin">เรซิน (Resin)</label>
            <input type="text" class="form-control" id="product_resin" name="resin"
                value="{{ $product?->resin }}" placeholder="กรอกเรซิน">
        </div>

        <div class="col-md-12 mb-3">
            <label class="form-label" for="product_temp_id">Temp</label>
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

        <div class="col-md-6 mb-3">
            <label class="form-label" for="product_code_field">Code</label>
            <input type="text" class="form-control" id="product_code_field" name="code"
                value="{{ $product?->code }}" placeholder="กรอก Code">
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label" for="product_pack">ขนาดบรรจุ (Packaging)</label>
            <input type="text" class="form-control" id="product_pack" name="pack"
                value="{{ $product?->pack }}" placeholder="กรอกขนาดบรรจุ">
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label" for="product_batch">นน ต่อชุด (Batch)</label>
            <input type="text" class="form-control" id="product_batch" name="batch"
                value="{{ $product?->batch }}" placeholder="กรอก Batch">
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label" for="product_sampling">สุ่มตัวอย่าง (Sampling)</label>
            <input type="text" class="form-control" id="product_sampling" name="sampling"
                value="{{ $product?->sampling }}" placeholder="สุ่มตัวอย่าง">
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="btn_product_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึก
        </button>
    </div>
</form>
