{{--
    ขออนุมัติราคาพิเศษ (MD) — แปลงผังมาจากฟอร์ม Access "MK ขออนุมัติราคาพิเศษ"
    ค่าทั้งหมดเติมด้วย JS (ดู fillApprovalForm ใน order/index.blade.php)
    เฟสนี้อ่านอย่างเดียว — ปุ่ม เพิ่ม/บันทึก, ลบรายการ, พิมพ์ ยังไม่เปิดใช้งาน
--}}
<div class="modal-body px-4 py-4 pa-body">

    {{-- ── แถวบน: รหัสผ่าน MD + ปุ่มตรวจสอบ/ประวัติ ── --}}
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">
                รหัสผ่าน MD <span class="text-muted fw-normal">(เพื่ออนุมัติ)</span>
                <i class="ti ti-info-circle text-muted" title="ยังไม่ยืนยันที่เก็บรหัสผ่าน — รอผู้ใช้ระบุ"></i>
            </label>
            <input type="password" id="a_mdpass" class="form-control" autocomplete="off">
        </div>
        <div class="col-md-8">
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

            {{-- ราคา 3 ช่อง (กลุ่ม A / B / C ตามปริมาณสั่งซื้อ) --}}
            <div class="row g-3 mt-1 align-items-end">
                <div class="col-12">
                    <label class="form-label">ราคา 3 ช่อง</label>
                    <div class="d-flex flex-wrap align-items-end gap-2">
                        <div class="pa-pricebox" id="a_box1">
                            <div class="pa-pricebox-cap">A · 1,000 kg. up</div>
                            <input type="text" id="a_price1" class="form-control text-end" readonly>
                        </div>
                        <div class="pa-pricebox" id="a_box2">
                            <div class="pa-pricebox-cap">B · 500 kg. up</div>
                            <input type="text" id="a_price2" class="form-control text-end" readonly>
                        </div>
                        <div class="pa-pricebox" id="a_box3">
                            <div class="pa-pricebox-cap">C · under 500 kg.</div>
                            <input type="text" id="a_price3" class="form-control text-end" readonly>
                        </div>

                        {{-- 2 ช่องขวาบนฟอร์มเดิม — ยังไม่ทราบแหล่งข้อมูล --}}
                        <div class="pa-pricebox">
                            <div class="pa-pricebox-cap">
                                3-4 nn
                                <i class="ti ti-info-circle text-muted" title="ยังไม่ยืนยันแหล่งข้อมูล — รอผู้ใช้ระบุ"></i>
                            </div>
                            <input type="text" id="a_price_34" class="form-control text-end pa-hl-yellow" readonly>
                        </div>
                        <div class="pa-pricebox">
                            <div class="pa-pricebox-cap">
                                1-2 nn
                                <i class="ti ti-info-circle text-muted" title="ยังไม่ยืนยันแหล่งข้อมูล — รอผู้ใช้ระบุ"></i>
                            </div>
                            <input type="text" id="a_price_12" class="form-control text-end pa-hl-pink" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold text-danger">ราคาขายครั้งนี้</label>
                    <div class="input-group">
                        {{-- input ธรรมดา (ไม่ใช่ type=number) เพื่อให้ใส่คอมมาคั่นหลักพันได้ — อ่านค่าด้วย numVal() --}}
                        <input type="text" id="a_price" class="form-control text-end fw-bold pa-sell js-comma"
                            inputmode="decimal" autocomplete="off" placeholder="0.00">
                        <span class="input-group-text">บาท</span>
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label">จำนวนสั่งซื้อ</label>
                    <div class="input-group">
                        <input type="text" id="a_weight" class="form-control text-end js-comma"
                            inputmode="decimal" autocomplete="off" placeholder="0.00"
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
                    <input type="text" id="a_validto" class="form-control flatpickr-date" autocomplete="off"
                        placeholder="วว/ดด/ปปปป">
                </div>
                <div class="col-md-5">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <button type="button" class="btn btn-primary" onclick="approvalSave()">
                            <i class="ti ti-plus me-1"></i>เพิ่ม / บันทึก
                        </button>
                        <button type="button" class="btn btn-label-danger" onclick="approvalSave()">
                            <i class="ti ti-trash me-1"></i>ลบ รายการ
                        </button>
                        <button type="button" class="btn btn-label-info" onclick="approvalSave()">
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
