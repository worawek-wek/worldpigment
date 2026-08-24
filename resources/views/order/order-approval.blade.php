{{--
    อนุมัติใบสั่งซื้อ — แปลงผังมาจากฟอร์ม Access "morderAPPV"
    คิว = ใบที่ยังไม่อนุมัติ ไม่รวมใบสั่งทำสต๊อก และไม่รวมใบจอง R (ดู OrderApprovalController)
    ค่าทั้งหมดเติมด้วย JS (ดู fillOrderApproval ใน order/index.blade.php)

    ติ๊ก "อนุมัติ" = ถามยืนยันแล้วเขียน morder.appv (-1) + morder.appvDT (เวลาปัจจุบัน)
    ใบที่อนุมัติแล้วจะหลุดจากคิว ฟอร์มจึงเลื่อนไปใบถัดไปให้เอง
--}}
<div class="modal-body px-4 py-4 oa-body">

    {{-- ── แถว 1: เอกสาร ── --}}
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label">วัน-เวลา</label>
            <input type="text" id="oa_Mdate" class="form-control" readonly>
        </div>
        <div class="col-md-2">
            <label class="form-label">เลขที่ใบสั่ง</label>
            <input type="text" id="oa_Orderno" class="form-control fw-bold text-primary" readonly>
        </div>
        <div class="col-md-2">
            <label class="form-label">แผนกที่ผลิต</label>
            <input type="text" id="oa_Company" class="form-control" readonly>
        </div>
        <div class="col-md-3">
            <label class="form-label">PO</label>
            <input type="text" id="oa_PO" class="form-control" readonly>
        </div>
        <div class="col-md-2 text-md-end">
            <button type="button" class="btn btn-label-secondary w-100" onclick="orderApprovalRefresh()">
                <i class="ti ti-refresh me-1"></i>Refresh
            </button>
        </div>
    </div>

    {{-- ── แถว 2: ลูกค้า / ผู้บันทึก / ผู้ขาย / สต๊อก ── --}}
    <div class="row g-3 align-items-end mt-1">
        <div class="col-md-2">
            <label class="form-label">รหัสลูกค้า</label>
            <input type="text" id="oa_Custno" class="form-control" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">&nbsp;</label>
            <input type="text" id="oa_Custname" class="form-control text-primary fw-semibold" readonly>
        </div>
        <div class="col-md-2">
            <label class="form-label">ผู้บันทึก</label>
            <input type="text" id="oa_Emp" class="form-control" readonly>
        </div>
        <div class="col-md-1">
            <label class="form-label">ผู้ขาย</label>
            <input type="text" id="oa_sale" class="form-control text-center" readonly>
        </div>
        <div class="col-md-3">
            <label class="form-label">น.น.Stock คงเหลือปัจจุบัน</label>
            <input type="text" id="oa_HMStore" class="form-control text-end" readonly>
        </div>
    </div>

    {{-- ── แถว 3: เงื่อนไขการส่ง ── --}}
    <div class="row g-3 align-items-end mt-1">
        <div class="col-md-5">
            <label class="form-label d-block">เงื่อนไขบนใบสั่ง</label>
            <div class="oa-checkrow">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="oa_Send" disabled>
                    <label class="form-check-label" for="oa_Send">ส่งก่อนได้</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="oa_RP" disabled>
                    <label class="form-check-label" for="oa_RP">RP</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="oa_Spec" disabled>
                    <label class="form-check-label" for="oa_Spec">Spec</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="oa_Cer" disabled>
                    <label class="form-check-label" for="oa_Cer">Cer</label>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">สถานที่ส่ง</label>
            <input type="text" id="oa_DVpoint" class="form-control" readonly>
        </div>
        <div class="col-md-3">
            <label class="form-label">ส่งลูกค้าภายใน (เดือน)</label>
            <input type="text" id="oa_SendCust" class="form-control text-end" readonly>
        </div>
    </div>

    {{-- ── ตารางรายการ (คลิกแถวเพื่อดูราคาของเบอร์นั้น) ── --}}
    <div class="mt-3">
        <div class="table-responsive oa-grid">
            <table class="table table-sm table-bordered align-middle mb-0" id="oaItemsTable">
                <thead>
                    <tr>
                        <th style="width:140px;">รหัสสินค้า</th>
                        <th style="width:180px;">ชื่อสินค้า</th>
                        <th style="width:110px;">Lot No</th>
                        <th style="width:90px;" class="text-end">S</th>
                        <th style="width:90px;" class="text-end">P</th>
                        <th style="width:110px;" class="text-center">กำหนดทบทวน</th>
                        <th>หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody id="oaItems"></tbody>
            </table>
        </div>
        <div class="form-text mt-1">
            <i class="ti ti-info-circle me-1"></i>คลิกแถวเพื่อดูราคาอ้างอิงของเบอร์นั้น (S = สต๊อก, P = ผลิต)
        </div>
    </div>

    {{-- ── แผงราคาอ้างอิงของเบอร์ที่เลือก ── --}}
    <div class="row g-3 mt-2">
        <div class="col-lg-8">
            <div class="mb-2 d-flex align-items-center gap-2">
                <label class="form-label mb-0" style="width:60px;">REM1:</label>
                <input type="text" id="oa_rem1" class="form-control form-control-sm" readonly>
            </div>
            <div class="mb-2 d-flex align-items-center gap-2">
                <label class="form-label mb-0" style="width:60px;">REM2:</label>
                <input type="text" id="oa_rem2" class="form-control form-control-sm" readonly>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 text-danger" style="width:60px;">ผู้บริหาร:</label>
                {{-- ⚠ ยังไม่มีคอลัมน์เก็บใน morder — พิมพ์ได้แต่ยังไม่บันทึก (รอผู้ใช้ระบุที่เก็บ) --}}
                <input type="text" id="oa_mdnote" class="form-control form-control-sm oa-hl-green"
                    title="ยังไม่ยืนยันคอลัมน์ที่เก็บ — ค่าที่พิมพ์ยังไม่ถูกบันทึก">
            </div>
        </div>
        <div class="col-lg-4">
            <label class="form-label">ราคาที่กำหนดไว้</label>
            <input type="text" id="oa_fixed_price" class="form-control text-end fw-bold oa-hl-blue" readonly>
        </div>
    </div>

    {{-- ── ราคา 3 ช่อง (กลุ่ม A / B / C) ── --}}
    <div class="row g-2 mt-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label">ราคา1 <span class="text-muted fw-normal small">(A · 1,000 kg. up)</span></label>
            <input type="text" id="oa_price1" class="form-control text-end" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">ราคา2 <span class="text-muted fw-normal small">(B · 500 kg. up)</span></label>
            <input type="text" id="oa_price2" class="form-control text-end fw-bold oa-hl-blue" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">ราคา3 <span class="text-muted fw-normal small">(C · under 500 kg.)</span></label>
            <input type="text" id="oa_price3" class="form-control text-end" readonly>
        </div>
    </div>

    {{-- ── แถวสรุป + อนุมัติ ── --}}
    <div class="row g-3 mt-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">เทอม / ส่วนลดเงินสด</label>
            <input type="text" id="oa_term" class="form-control text-danger fw-semibold" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold text-danger">ราคาขายครั้งนี้</label>
            <input type="text" id="oa_price" class="form-control text-end fw-bold oa-sell" readonly>
        </div>
        <div class="col-md-2">
            <label class="form-label d-block">&nbsp;</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="oa_appv" onclick="orderApprovalApprove(event)">
                <label class="form-check-label fw-bold" for="oa_appv">อนุมัติ</label>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label">วัน-เวลา อนุมัติ</label>
            <input type="text" id="oa_appvDT" class="form-control" readonly>
        </div>
    </div>

    {{-- ── ตัวเดินระเบียน (เหมือนแถบล่างของฟอร์ม Access) ── --}}
    <div class="oa-nav mt-4">
        <span class="fw-semibold me-2">ระเบียน:</span>
        <button type="button" class="btn btn-sm btn-label-secondary" onclick="oaGo(0)" title="แรกสุด">
            <i class="ti ti-chevrons-left"></i>
        </button>
        <button type="button" class="btn btn-sm btn-label-secondary" onclick="oaStep(-1)" title="ก่อนหน้า">
            <i class="ti ti-chevron-left"></i>
        </button>
        <input type="number" id="oa_pos" class="form-control form-control-sm text-center" style="width:80px;"
            min="1" onchange="oaGo(this.value - 1)">
        <button type="button" class="btn btn-sm btn-label-secondary" onclick="oaStep(1)" title="ถัดไป">
            <i class="ti ti-chevron-right"></i>
        </button>
        <button type="button" class="btn btn-sm btn-label-secondary" onclick="oaGo(-1)" title="ท้ายสุด">
            <i class="ti ti-chevrons-right"></i>
        </button>
        <span class="ms-2">จาก <span id="oa_total" class="fw-bold">0</span></span>
    </div>

</div>
