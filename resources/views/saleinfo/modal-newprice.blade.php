{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- Modal: ค้นหาราคาสินค้า (จอเก่าชื่อ "New Price")                       --}}
{{-- Trigger: data-bs-target="#newPriceModal"                             --}}
{{-- อ่านอย่างเดียว — วางรหัสสินค้า → โชว์ราคา ไม่มีปุ่มบันทึก             --}}
{{-- ⚠ ตัวเลขยังไม่ต่อ DB — ยังไม่รู้ที่มาของราคาขาย 1/2/3 (รอลูกค้า)      --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal modalHeadDecor fade" id="newPriceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-search me-1"></i>
                    ค้นหาราคาสินค้า
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            {{-- ไม่มี <form> — ฟอร์มนี้ไม่มีการบันทึก กัน Enter เผลอ submit ไปด้วย --}}
            <div class="modal-body px-4 py-4" style="background-color: #f8f9fb;">

                {{-- ─── รหัสสินค้า (ช่องเดียวที่กรอกได้) ─── --}}
                <div class="mb-4">
                    <label for="np_code" class="form-label small mb-1 fw-semibold">
                        รหัสสินค้า <span class="text-muted fw-normal">(เช่น 1101029)</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-barcode"></i></span>
                        <input type="text" id="np_code" class="form-control" autocomplete="off">
                        <button type="button" class="btn btn-label-secondary" id="np_clear" title="ล้าง">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                </div>

                {{-- ─── ราคาขาย (อ่านอย่างเดียว) ─── --}}
                {{-- wip: ตัวเลขยังไม่ต่อ DB — ยังไม่รู้ที่มา/สูตรของราคาขาย 1/2/3 --}}
                <div class="card shadow-sm mb-3 wip" style="border: 1px solid #e3e5ea;">
                    <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between"
                        style="background-color: #f4f5f7; border-bottom: 2px solid #b6b9c2; border-radius: 0.375rem 0.375rem 0 0;">
                        <h6 class="mb-0 fw-semibold text-body">
                            <i class="ti ti-currency-baht me-1"></i>
                            ราคาขาย
                        </h6>
                        <span class="badge bg-label-warning">รอที่มาของราคาจากลูกค้า</span>
                    </div>

                    <div class="card-body p-3">

                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-4">
                                <label class="form-label small mb-0">ราคาขาย 1</label>
                            </div>
                            <div class="col-5">
                                <input type="text" id="np_price_1" class="form-control text-end fw-semibold text-danger"
                                    readonly tabindex="-1">
                            </div>
                            <div class="col-3"></div>
                        </div>

                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-4">
                                <label class="form-label small mb-0">ราคาขาย 2</label>
                            </div>
                            <div class="col-5">
                                <input type="text" id="np_price_2" class="form-control text-end fw-semibold text-danger"
                                    readonly tabindex="-1">
                            </div>
                            <div class="col-3"></div>
                        </div>

                        {{-- ป้าย DB เกาะอยู่กับราคาขาย 3 แล้วไล่ลงมาอีก 2 กล่อง (ตามจอเก่า) --}}
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-4">
                                <label class="form-label small mb-0">ราคาขาย 3</label>
                            </div>
                            <div class="col-5">
                                <input type="text" id="np_price_3" class="form-control text-end fw-semibold text-danger"
                                    readonly tabindex="-1">
                            </div>
                            <div class="col-3">
                                <span class="badge bg-label-success w-100 py-2">DB 5 Kg.up</span>
                            </div>
                        </div>

                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-4"></div>
                            <div class="col-5">
                                <input type="text" id="np_db_3_4" class="form-control text-end fw-semibold"
                                    readonly tabindex="-1" style="background-color: #d9dade;">
                            </div>
                            <div class="col-3">
                                <span class="badge bg-label-success w-100 py-2">DB 3 - 4 Kg.</span>
                            </div>
                        </div>

                        <div class="row g-2 align-items-center">
                            <div class="col-4"></div>
                            <div class="col-5">
                                <input type="text" id="np_db_1_2" class="form-control text-end fw-semibold"
                                    readonly tabindex="-1" style="background-color: #d9dade;">
                            </div>
                            <div class="col-3">
                                <span class="badge bg-label-success w-100 py-2">DB 1 - 2 Kg.</span>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ─── โน้ตเงื่อนไขราคา (คัดจากจอเก่าตามตัวอักษร ยังไม่ได้เอาไปคำนวณ) ─── --}}
                <div class="p-3 rounded" style="background-color: #fdf3e3; border: 1px solid #f0dfc0;">
                    <div class="small fw-semibold mb-2" style="color: #7a4d05;">
                        <i class="ti ti-info-circle me-1"></i>
                        เงื่อนไขราคา
                    </div>
                    <ul class="small mb-0 ps-3" style="color: #b26a09;">
                        <li>MB ขาย &lt; 25 Kg. = ราคาขาย 3 + 200 บาท</li>
                        <li>CP น.น.ผลิต &lt; 500 Kg. = กรอกที่กำหนดไว้ + 10 บาท</li>
                        <li>New Rate 1-2 +400 / 3-4 +250 (9/5/2565)</li>
                    </ul>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                    ปิด
                </button>
            </div>

        </div>
    </div>
</div>
