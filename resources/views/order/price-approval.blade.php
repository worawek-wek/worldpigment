{{--
    ขออนุมัติราคาพิเศษ (MD) — แปลงผังมาจากฟอร์ม Access "MK ขออนุมัติราคาพิเศษ"
    ค่าทั้งหมดเติมด้วย JS (ดู fillApprovalForm ใน order/index.blade.php)

    2 ขั้นตอนของฟอร์มนี้:
      1) ขอราคา  — เลือกลูกค้า+สินค้า แล้วกรอกใบขอราคา
                   คู่ที่เคยขอไว้แล้วจะดึงใบล่าสุดมาแสดงให้แก้ทับ (1 คู่ = 1 ใบ)
      2) อนุมัติ — กรอกรหัส → กดปลดล็อก → เลือกลูกค้า+สินค้า
                   ดูใบที่ขอไว้แล้วตัดสินใจติ๊ก "อนุมัติ"

    ⚠ ยังไม่ยืนยันว่ารหัสที่ใช้ปลดล็อกเป็นของ MD โดยเฉพาะ (ฟอร์ม Access เดิมเขียนว่า "รหัสผ่าน MD"
      แต่หาที่เก็บจริงไม่เจอ) — บนจอจึงเรียกแค่ "รหัส" ไม่ผูกกับตำแหน่งใด
    ปุ่ม "พิมพ์" ยังเป็น window.print() — ยังไม่มีแบบฟอร์มกระดาษให้อ้างอิง
--}}
<div class="modal-body px-4 py-4 pa-body">

    {{-- ── แถวบน: ช่องรหัส (ปลดล็อกโหมดอนุมัติ) + ปุ่มตรวจสอบ/ประวัติ ──
         ฟอร์มนี้มี 2 โหมด: "ขอราคา"  = ค่าเริ่มต้น ช่องอนุมัติถูกล็อก
                            "อนุมัติ" = กรอกรหัสแล้วกดปลดล็อกก่อน
         การปลดล็อกตรวจที่ server + เก็บใน session (ดู PriceApprovalController::unlock) --}}
    <div class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label">
                กรอกรหัสเพื่อเข้าสู่โหมดอนุมัติ
                <i class="ti ti-info-circle text-muted" title="ยังไม่ยืนยันที่เก็บรหัสผ่าน — รอผู้ใช้ระบุ"></i>
            </label>

            {{-- โหมดขอราคา — กรอกรหัสแล้วกดปุ่ม (หรือกด Enter) เพื่อเข้าสู่โหมดอนุมัติ --}}
            <div id="a_mdLockBox">
                <div class="input-group">
                    <input type="password" id="a_mdpass" class="form-control" autocomplete="off">
                    <button type="button" class="btn btn-outline-primary" onclick="approvalUnlock()">
                        <i class="ti ti-lock-open me-1"></i>เข้าสู่โหมดอนุมัติ
                    </button>
                </div>
            </div>

            {{-- โหมดอนุมัติ --}}
            <div id="a_mdUnlockedBox" class="d-none">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success py-2 px-3">
                        <i class="ti ti-lock-open me-1"></i>โหมดอนุมัติ
                    </span>
                    <button type="button" class="btn btn-sm btn-label-secondary" onclick="approvalLock()">
                        <i class="ti ti-lock me-1"></i>ออกจากโหมด
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                <button type="button" class="btn btn-sm btn-label-primary" onclick="approvalOtherItems()">
                    <i class="ti ti-list-search me-1"></i>ตรวจสอบ เบอร์อื่น ...
                </button>
                <button type="button" class="btn btn-sm btn-label-primary" onclick="approvalHistory()">
                    <i class="ti ti-history me-1"></i>ประวัติของเบอร์นี้
                </button>
                <button type="button" class="btn btn-sm btn-label-primary" onclick="approvalOtherCustomers()">
                    <i class="ti ti-building-store me-1"></i>ตรวจสอบเฉพาะร้าน ...
                </button>
                <button type="button" class="btn btn-sm btn-label-primary" onclick="approvalResinHistory()">
                    <i class="ti ti-flask me-1"></i>ประวัติ ราคาเม็ด CP
                </button>
                <button type="button" class="btn btn-sm btn-label-secondary" onclick="approvalRefresh()">
                    <i class="ti ti-refresh me-1"></i>Refresh
                </button>
            </div>
        </div>
    </div>

    <hr class="my-3">

    <div class="row g-3">

        {{-- ═══════════ ซ้าย: ข้อมูลใบขอราคา ═══════════ --}}
        <div class="col-xl-8">

            {{-- บอกว่าคู่ (ลูกค้า, เบอร์) นี้อยู่ขั้นตอนไหน — ยังไม่เคยขอ / รออนุมัติ / อนุมัติแล้ว
                 1 คู่ = 1 ใบที่แก้ได้ (ใบล่าสุด) กดบันทึกจะแก้ทับใบเดิมเสมอ --}}
            <div id="a_reqState" class="alert py-2 px-3 mb-3 small"></div>

            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">วันที่ขอราคา</label>
                    <input type="text" id="a_ReqDate" class="form-control" readonly>
                </div>
            </div>

            <div class="row g-3 mt-1 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">รหัสลูกค้า</label>
                    <input type="text" id="a_custno" class="form-control" maxlength="10"
                        oninput="onApprovalCustChange(this.value)">
                </div>
                <div class="col-md-2">
                    <label class="form-label">
                        # <span class="text-muted fw-normal small">พนักงานขาย</span>
                    </label>
                    <input type="text" id="a_sale" class="form-control bg-light text-center" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">ชื่อลูกค้า</label>
                    <input type="text" id="a_custname" class="form-control text-primary fw-semibold" readonly>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <label class="form-label">รหัสสินค้า</label>
                    <select id="a_itemno" class="form-select" onchange="loadApprovalData()">
                        <option value="">— เลือกลูกค้าก่อน —</option>
                    </select>
                </div>
            </div>

            {{-- ราคา 3 ช่อง (กลุ่ม A / B / C ตามปริมาณสั่งซื้อ)
                 = ราคาขาย 1/2/3 ที่คำนวณจากเมนู "กำหนดราคา" (ProductPriceService) ตามรหัสสินค้า
                 — ไม่ได้เอาค่าที่เคยบันทึกไว้ใน appvreq มาโชว์ --}}
            <div class="row g-3 mt-1 align-items-end">
                <div class="col-12">
                    <label class="form-label">
                        ราคา 3 ช่อง
                        <span class="text-muted fw-normal small">(คำนวณจากเมนูกำหนดราคา)</span>
                    </label>
                    <div class="d-flex flex-wrap align-items-end gap-2">
                        {{-- ไม่มีป้ายกำกับ A/B/C ใต้ช่อง — ดูกลุ่มที่ใช้จริงได้จากกรอบที่ถูกเน้น + ช่อง "กลุ่ม" --}}
                        <div class="pa-pricebox" id="a_box1">
                            <input type="text" id="a_price1" class="form-control text-end" readonly>
                        </div>
                        <div class="pa-pricebox" id="a_box2">
                            <input type="text" id="a_price2" class="form-control text-end" readonly>
                        </div>
                        <div class="pa-pricebox" id="a_box3">
                            <input type="text" id="a_price3" class="form-control text-end" readonly>
                        </div>

                        {{-- 2 ช่องขวาของฟอร์มเดิม = ขั้น DB 3-4 Kg. / DB 1-2 Kg. ของระบบกำหนดราคา
                             ⚠ ตัวคูณยังเป็นค่าเดา (ดู config/product_price.php → tier) --}}
                        <div class="pa-pricebox">
                            <div class="pa-pricebox-cap">
                                DB 3-4 Kg.
                                <i class="ti ti-info-circle text-danger" title="ตัวคูณยังเป็นค่าเดา — รอสูตรจริงจากลูกค้า"></i>
                            </div>
                            <input type="text" id="a_price_34" class="form-control text-end pa-hl-yellow" readonly>
                        </div>
                        <div class="pa-pricebox">
                            <div class="pa-pricebox-cap">
                                DB 1-2 Kg.
                                <i class="ti ti-info-circle text-danger" title="ตัวคูณยังเป็นค่าเดา — รอสูตรจริงจากลูกค้า"></i>
                            </div>
                            <input type="text" id="a_price_12" class="form-control text-end pa-hl-pink" readonly>
                        </div>
                    </div>

                    {{-- ที่มาของราคา (ราคาทุน · เงื่อนไขที่เข้า · สูตร) หรือเหตุผลที่คำนวณไม่ได้
                         — รูปแบบเดียวกับใต้กล่องราคาในใบสั่งซื้อ / หน้าค้นหาราคาสินค้า --}}
                    <div id="a_price_note" class="of-price-note"></div>
                </div>
            </div>

            <div class="row g-3 mt-1 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold text-danger">ราคาขายครั้งนี้</label>
                    <div class="input-group">
                        {{-- input ธรรมดา (ไม่ใช่ type=number) เพื่อให้ใส่คอมมาคั่นหลักพันได้ — อ่านค่าด้วย numVal() --}}
                        <input type="text" id="a_price" class="form-control text-end fw-bold pa-sell js-comma"
                            inputmode="decimal" autocomplete="off">
                        <span class="input-group-text">บาท</span>
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label">จำนวนสั่งซื้อ</label>
                    <div class="input-group">
                        <input type="text" id="a_weight" class="form-control text-end js-comma"
                            inputmode="decimal" autocomplete="off"
                            oninput="highlightPriceGroup()">
                        <span class="input-group-text">ก.ก.</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">กลุ่ม</label>
                    <input type="text" id="a_group" class="form-control text-center fw-bold bg-light" readonly>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="a_costup">
                        <label class="form-check-label" for="a_costup">
                            ต้นทุนวัตถุดิบปรับขึ้น จึงปรับราคาขาย
                            <i class="ti ti-info-circle text-muted" title="ยังไม่ยืนยันคอลัมน์ที่เก็บ — รอผู้ใช้ระบุ"></i>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-12">
                    <label class="form-label">หมายเหตุ การปรับราคา</label>
                    <textarea id="a_remark" class="form-control" rows="3" maxlength="100"></textarea>
                </div>
            </div>

            <div class="row g-3 mt-1 align-items-end">
                <div class="col-md-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="a_Appv">
                        <label class="form-check-label fw-bold" for="a_Appv">อนุมัติ</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">
                        อนุมัติราคาถึง
                        <i class="ti ti-info-circle text-muted" title="เก็บที่ zcustprice.enddate (ยืนราคาถึงวันที่)"></i>
                    </label>
                    <input type="text" id="a_validto" class="form-control flatpickr-date" autocomplete="off">
                </div>
                <div class="col-md-5">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <button type="button" id="btnApprovalSave" class="btn btn-primary" onclick="approvalSave()">
                            <i class="ti ti-plus me-1"></i>เพิ่ม / บันทึก
                        </button>
                        <button type="button" id="btnApprovalDelete" class="btn btn-label-danger" onclick="approvalDelete()">
                            <i class="ti ti-trash me-1"></i>ลบ รายการ
                        </button>
                        <button type="button" class="btn btn-label-info" onclick="approvalPrint()">
                            <i class="ti ti-printer me-1"></i>พิมพ์
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════ ขวา: ราคาที่ตกลงไว้ (uprice) ═══════════ --}}
        <div class="col-xl-4">
            <div class="pa-sidebox h-100">
                <div class="pa-sidebox-title"><i class="ti ti-receipt"></i>ราคาที่ตกลงไว้ล่าสุด</div>

                <div class="mb-2">
                    <label class="form-label small mb-1">ราคา (uprice)</label>
                    <input type="text" id="a_uprice" class="form-control form-control-sm text-end" readonly>
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1">วันที่</label>
                    <input type="text" id="a_uprice_date" class="form-control form-control-sm" readonly>
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1">หมายเหตุ 1</label>
                    <textarea id="a_uprice_rem1" class="form-control form-control-sm" rows="2" readonly></textarea>
                </div>
                <div>
                    <label class="form-label small mb-1">หมายเหตุ 2</label>
                    <textarea id="a_uprice_rem2" class="form-control form-control-sm" rows="3" readonly></textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ ตารางล่าง (zcustprice) + ผลของปุ่มตรวจสอบ/ประวัติ ═══════════ --}}
    <div class="mt-4">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
            <h6 class="mb-0 fw-bold text-primary" id="a_gridTitle">ราคาที่ยืนไว้ของเบอร์นี้</h6>
            <span class="text-muted small" id="a_gridCount"></span>
        </div>
        <div class="table-responsive border rounded">
            <table class="table table-sm table-bordered align-middle mb-0" id="approvalGridTable">
                <thead class="table-light" id="a_gridHead"></thead>
                <tbody id="a_gridBody"></tbody>
            </table>
        </div>
    </div>

</div>
