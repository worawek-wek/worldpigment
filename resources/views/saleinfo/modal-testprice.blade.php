{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- Modal: Test Price (จอเก่าชื่อ "TEST PRICE 27.03.2013")                --}}
{{-- Trigger: data-bs-target="#testPriceModal"                            --}}
{{-- กรอกได้แค่ Customer / Test No. / Lot Test — ที่เหลืออ่านอย่างเดียว     --}}
{{-- ต่อข้อมูลแล้ว 25/08/2569 → GET saleinfo/test-price                     --}}
{{--   ใบเทส = access_testmai (TestNo / Lotno / TDecs / CResin / Resin / TNet) --}}
{{--   "ตั้งเบอร์เป็น" = access_compo.PdCode ของ TestNo นั้น                 --}}
{{--   ชื่อลูกค้า = customer.name (CName ในไฟล์ Access เป็น "?" ถาวร)        --}}
{{--   ราคา 1/2/3 + DB = ProductPriceService::quote(เบอร์ที่ตั้ง, TNet)      --}}
{{-- ⚠ section Price Quotation ยังไม่ทำ (ยังคง wip)                         --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal modalHeadDecor fade" id="testPriceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-flask me-1"></i>
                    Test Price
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            {{-- ไม่มี <form> — ฟอร์มนี้ไม่มีการบันทึก กัน Enter เผลอ submit --}}
            <div class="modal-body px-4 py-4" style="background-color: #f8f9fb;">

                {{-- ─── กลุ่ม: ลูกค้า / เลขที่เทส (ช่องที่กรอกได้) ─── --}}
                <div class="card shadow-sm mb-3" style="border: 1px solid #e3e5ea;">
                    <div class="card-body p-3">

                        <div class="row g-3 align-items-end mb-3">
                            <div class="col-md-4">
                                <label for="tp_customer" class="form-label small mb-1 fw-semibold">
                                    Customer <span class="text-muted fw-normal">(เช่น 41008)</span>
                                </label>
                                <input type="text" id="tp_customer" class="form-control" autocomplete="off">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small mb-1 text-muted">ชื่อลูกค้า</label>
                                <div id="tp_cust_name" class="form-control-plaintext fw-medium ps-2 text-truncate">—</div>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <button type="button" class="btn btn-success w-100" id="tp_refresh">
                                    <i class="ti ti-refresh me-1"></i>
                                    Refresh
                                </button>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="tp_testno" class="form-label small mb-1 fw-semibold">
                                    Test No. <span class="text-muted fw-normal">(เช่น 25/0077/4)</span>
                                </label>
                                <input type="text" id="tp_testno" class="form-control" autocomplete="off">
                            </div>
                            <div class="col-md-3">
                                <label for="tp_lottest" class="form-label small mb-1 fw-semibold">
                                    Lot Test <span class="text-muted fw-normal">(เช่น 680717-2/77)</span>
                                </label>
                                <input type="text" id="tp_lottest" class="form-control" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1 text-danger">ตั้งเบอร์เป็น</label>
                                <div id="tp_setcode" class="form-control fw-semibold text-truncate"
                                    style="background-color: #f6b98a; border-color: #e08a1e;">—</div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ─── กลุ่ม: ผลที่ค้นได้ (อ่านอย่างเดียว) ─── --}}
                <div class="card shadow-sm mb-3" style="border: 1px solid #e3e5ea;">
                    <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between"
                        style="background-color: #f4f5f7; border-bottom: 2px solid #b6b9c2; border-radius: 0.375rem 0.375rem 0 0;">
                        <h6 class="mb-0 fw-semibold text-body">
                            <i class="ti ti-file-description me-1"></i>
                            ข้อมูลเทส
                        </h6>
                        {{-- ใบที่ค้นเจอ: Test No. + วันที่เทส (เติมด้วย JS) --}}
                        <span class="small text-muted" id="tp_meta"></span>
                    </div>

                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label small mb-1">Sample</label>
                            <input type="text" id="tp_sample" class="form-control" readonly tabindex="-1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small mb-1">Resin ที่ลูกค้าใช้</label>
                            <input type="text" id="tp_resin_cust" class="form-control" readonly tabindex="-1">
                        </div>
                        <div class="mb-0">
                            <label class="form-label small mb-1">Resin (Match)</label>
                            <input type="text" id="tp_resin_match" class="form-control" readonly tabindex="-1">
                        </div>
                    </div>
                </div>

                {{-- ─── Price Quotation + SEARCH ─── --}}
                <div class="card shadow-sm mb-3 wip" style="border: 1px solid #cfe3c4;">
                    <div class="card-header py-2 px-3"
                        style="background-color: #8fd18f; border-bottom: 2px solid #5aa85a; border-radius: 0.375rem 0.375rem 0 0;">
                        <h6 class="mb-0 fw-semibold" style="color: #1e4d1e;">
                            <i class="ti ti-file-invoice me-1"></i>
                            Price Quotation
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-9">
                                <input type="text" id="tp_quotation" class="form-control" readonly tabindex="-1">
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-label-secondary w-100" id="tp_search">
                                    <i class="ti ti-search me-1"></i>
                                    SEARCH
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── Price 1/2/3 + DB tier ─── --}}
                {{-- คิดจากต้นทุนสูตรของใบเทส (TNet) ด้วยเครื่องคิดราคาตัวเดียวกับทั้งระบบ --}}
                <div class="card shadow-sm mb-3" style="border: 1px solid #e3d3c4;">
                    <div class="card-body p-3" style="background-color: #f6e6dc;">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small mb-1 fw-semibold">Price 1</label>
                                <input type="text" id="tp_price_1" class="form-control text-end fw-semibold"
                                    readonly tabindex="-1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1 fw-semibold">Price 2</label>
                                <input type="text" id="tp_price_2" class="form-control text-end fw-semibold"
                                    readonly tabindex="-1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1 fw-semibold">Price 3</label>
                                <input type="text" id="tp_price_3" class="form-control text-end fw-semibold"
                                    readonly tabindex="-1">
                                {{-- ป้าย DB ตัวแรกเกาะอยู่กับ Price 3 (ตามจอเก่า) --}}
                                <span class="badge w-100 mt-1 py-1"
                                    style="background-color: #ffff9e; color: #4d4d00;">DB 5 Kg.up</span>
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-4 offset-md-4">
                                <input type="text" id="tp_db_3_4" class="form-control text-end fw-semibold"
                                    readonly tabindex="-1" style="background-color: #d9dade;">
                                <span class="badge bg-label-success w-100 mt-1 py-1">DB 3 - 4 Kg.</span>
                            </div>
                            <div class="col-md-4">
                                <input type="text" id="tp_db_1_2" class="form-control text-end fw-semibold"
                                    readonly tabindex="-1" style="background-color: #d9dade;">
                                <span class="badge bg-label-success w-100 mt-1 py-1">DB 1 - 2 Kg.</span>
                            </div>
                        </div>

                        {{-- ที่มาของราคา: ต้นทุนสูตร · เงื่อนไขที่เข้า · สูตรคูณ/หาร/บวก
                             คิดราคาไม่ได้ก็บอกเหตุผลตรงนี้ ไม่ปล่อยช่องว่างเงียบ ๆ --}}
                        <div class="saleinfo-price-note mt-3" id="tp_price_note"></div>

                    </div>
                </div>

                {{-- ─── โน้ตจากจอเก่า (คัดตามตัวอักษร ยังไม่ได้เอาไปคำนวณ) ─── --}}
                <div class="p-3 rounded" style="background-color: #fdf3e3; border: 1px solid #f0dfc0;">
                    <div class="small fw-semibold text-danger mb-0">
                        <i class="ti ti-info-circle me-1"></i>
                        Test No. ที่มี /HML หมายถึง เทสปรับสารโลหะหนักออก
                    </div>
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
