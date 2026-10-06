{{--
    อนุมัติใบสั่งซื้อ — แปลงผังมาจากฟอร์ม Access "morderAPPV"
    คิว = ใบที่ยังไม่อนุมัติ ไม่รวมใบสั่งทำสต๊อก และไม่รวมใบจอง R (ดู OrderApprovalController)
    ค่าทั้งหมดเติมด้วย JS (ดู fillOrderApproval ใน order/index.blade.php)

    2 มุมมองใน modal เดียว (25/08/2569):
      #oaListView    รายการใบที่รออนุมัติ — เปิดฟอร์มมาเจอหน้านี้ก่อน คลิกแถวเพื่อเข้าใบนั้น
      #oaDetailView  ฟอร์มอนุมัติของใบที่เลือก (ผังเดิมจาก Access + ตัวเดินระเบียน)

    ติ๊ก "อนุมัติ" = ถามยืนยันแล้วเขียน morder.appv (-1) + morder.appvDT (เวลาปัจจุบัน)
    ใบที่อนุมัติแล้วจะหลุดจากคิว → กลับมาหน้ารายการที่โหลดใหม่แล้ว
--}}
<div class="modal-body px-4 py-4 oa-body">

    {{-- ════════ มุมมอง 1: รายการใบที่รออนุมัติ ════════ --}}
    <div id="oaListView">

        <div class="row g-2 align-items-end mb-3">
            <div class="col-md-6">
                <label class="form-label">ค้นหา</label>
                <input type="search" id="oa_search" class="form-control"
                    oninput="oaRenderQueue()" autocomplete="off">
                <div class="form-text">เลขที่ใบสั่ง · รหัส/ชื่อลูกค้า · แผนก</div>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="me-2">รออนุมัติ <span id="oa_queue_count" class="fw-bold text-danger">0</span> ใบ</span>
                <button type="button" class="btn btn-label-secondary" onclick="orderApprovalRefresh()">
                    <i class="ti ti-refresh me-1"></i>Refresh
                </button>
            </div>
        </div>

        <div class="table-responsive oa-grid">
            <table class="table table-sm table-bordered align-middle mb-0" id="oaQueueTable">
                <thead>
                    <tr>
                        <th style="width:50px;" class="text-center">#</th>
                        <th style="width:120px;">เลขที่ใบสั่ง</th>
                        <th style="width:140px;">วัน-เวลา</th>
                        <th style="width:90px;" class="text-center">แผนก</th>
                        <th style="width:90px;">รหัสลูกค้า</th>
                        <th>ชื่อลูกค้า</th>
                        <th style="width:110px;" class="text-end">ราคาขาย</th>
                        <th style="width:120px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="oaQueueRows"></tbody>
            </table>
        </div>
        <div class="form-text mt-1">
            <i class="ti ti-info-circle me-1"></i>คลิกที่ใบสั่งซื้อเพื่อเปิดฟอร์มอนุมัติ
        </div>

    </div>

    {{-- ════════ มุมมอง 2: ฟอร์มอนุมัติของใบที่เลือก ════════ --}}
    <div id="oaDetailView" class="d-none of-form">

    <div class="mb-3 d-flex justify-content-between align-items-center gap-2">
        <button type="button" class="btn btn-label-secondary" onclick="oaShowList()">
            <i class="ti ti-arrow-left me-1"></i>กลับไปรายการที่รออนุมัติ
        </button>
        <button type="button" class="btn btn-label-secondary" onclick="orderApprovalRefresh()">
            <i class="ti ti-refresh me-1"></i>Refresh
        </button>
    </div>

    {{-- 06/10/2569: ช่องในฟอร์มนี้เปลี่ยนเป็น "label อยู่ซ้าย ต่อด้วยช่องกรอก" แบบเดียวกับฟอร์มใบสั่งซื้อ
         (โครง .of-row / .of-ctl — CSS กลางอยู่ที่ layout/inc_header · of-form อยู่ที่ #oaDetailView)
         ปุ่ม Refresh ย้ายขึ้นไปอยู่แถวเดียวกับปุ่ม "กลับไปรายการ" · id ของทุกช่องคงเดิม --}}

    {{-- ── แถว 1: เอกสาร ── --}}
    <div class="row gx-3 gy-2">
        <div class="col-md-3 of-row">
            <label class="form-label">วัน-เวลา</label>
            <div class="of-ctl">
                <input type="text" id="oa_Mdate" class="form-control" readonly>
            </div>
        </div>
        <div class="col-md-3 of-row">
            <label class="form-label">เลขที่ใบสั่ง</label>
            <div class="of-ctl">
                <input type="text" id="oa_Orderno" class="form-control fw-bold text-primary" readonly>
            </div>
        </div>
        <div class="col-md-3 of-row">
            <label class="form-label">แผนกที่ผลิต</label>
            <div class="of-ctl">
                <input type="text" id="oa_Company" class="form-control" readonly>
            </div>
        </div>
        <div class="col-md-3 of-row of-row-auto">
            <label class="form-label">PO</label>
            <div class="of-ctl">
                <input type="text" id="oa_PO" class="form-control" readonly>
            </div>
        </div>
    </div>

    {{-- ── แถว 2: ลูกค้า / ผู้บันทึก ── --}}
    <div class="row gx-3 gy-2 mt-0">
        <div class="col-md-3 of-row">
            <label class="form-label">รหัสลูกค้า</label>
            <div class="of-ctl">
                <input type="text" id="oa_Custno" class="form-control" readonly>
            </div>
        </div>
        <div class="col-md-6 of-row">
            <label class="form-label">ชื่อลูกค้า</label>
            <div class="of-ctl">
                <input type="text" id="oa_Custname" class="form-control text-primary fw-semibold" readonly>
            </div>
        </div>
        <div class="col-md-3 of-row of-row-auto">
            <label class="form-label">ผู้บันทึก</label>
            <div class="of-ctl">
                <input type="text" id="oa_Emp" class="form-control" readonly>
            </div>
        </div>
    </div>

    {{-- ── แถว 3: ผู้ขาย / สต๊อก ── --}}
    <div class="row gx-3 gy-2 mt-0">
        <div class="col-md-3 of-row">
            <label class="form-label">ผู้ขาย</label>
            <div class="of-ctl">
                <input type="text" id="oa_sale" class="form-control text-center" readonly>
            </div>
        </div>
        <div class="col-md-5 of-row of-row-auto">
            <label class="form-label">น.น.Stock คงเหลือปัจจุบัน</label>
            <div class="of-ctl">
                <input type="text" id="oa_HMStore" class="form-control text-end" readonly>
            </div>
        </div>
        <div class="col-md-4 of-row of-row-auto">
            <label class="form-label">ส่งลูกค้าภายใน (เดือน)</label>
            <div class="of-ctl">
                <input type="text" id="oa_SendCust" class="form-control text-end" readonly>
            </div>
        </div>
    </div>

    {{-- ── แถว 4: เงื่อนไขการส่ง ── --}}
    <div class="row gx-3 gy-2 mt-0">
        <div class="col-md-6 of-row">
            <label class="form-label">เงื่อนไขบนใบสั่ง</label>
            <div class="of-ctl">
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
        </div>
        <div class="col-md-6 of-row of-row-auto">
            <label class="form-label">สถานที่ส่ง</label>
            <div class="of-ctl">
                <input type="text" id="oa_DVpoint" class="form-control" readonly>
            </div>
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
    <div class="row gx-3 gy-2 mt-2">
        <div class="col-lg-8 of-form-sm" style="--of-lw: 64px;">
            <div class="of-row mb-2">
                <label class="form-label">REM1:</label>
                <div class="of-ctl">
                    <input type="text" id="oa_rem1" class="form-control form-control-sm" readonly>
                </div>
            </div>
            <div class="of-row mb-2">
                <label class="form-label">REM2:</label>
                <div class="of-ctl">
                    <input type="text" id="oa_rem2" class="form-control form-control-sm" readonly>
                </div>
            </div>
            <div class="of-row">
                <label class="form-label text-danger">ผู้บริหาร:</label>
                <div class="of-ctl">
                    {{-- ⚠ ยังไม่มีคอลัมน์เก็บใน morder — พิมพ์ได้แต่ยังไม่บันทึก (รอผู้ใช้ระบุที่เก็บ) --}}
                    <input type="text" id="oa_mdnote" class="form-control form-control-sm oa-hl-green"
                        title="ยังไม่ยืนยันคอลัมน์ที่เก็บ — ค่าที่พิมพ์ยังไม่ถูกบันทึก">
                </div>
            </div>
        </div>
        <div class="col-lg-4 of-row of-row-auto">
            <label class="form-label">ราคาที่กำหนดไว้</label>
            <div class="of-ctl">
                <input type="text" id="oa_fixed_price" class="form-control text-end fw-bold oa-hl-blue" readonly>
            </div>
        </div>
    </div>

    {{-- ── ราคา 3 ช่อง (กลุ่ม A / B / C) — คงเป็น label อยู่บน (กล่องราคาเรียงแนวนอน กระชับอยู่แล้ว) ── --}}
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
    <div class="row gx-3 gy-2 mt-2">
        <div class="col-md-4 of-row of-row-auto">
            <label class="form-label">เทอม / ส่วนลดเงินสด</label>
            <div class="of-ctl">
                <input type="text" id="oa_term" class="form-control text-danger fw-semibold" readonly>
            </div>
        </div>
        <div class="col-md-3 of-row of-row-auto">
            <label class="form-label fw-bold text-danger">ราคาขายครั้งนี้</label>
            <div class="of-ctl">
                <input type="text" id="oa_price" class="form-control text-end fw-bold oa-sell" readonly>
            </div>
        </div>
        <div class="col-md-2 d-flex align-items-center">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="oa_appv" onclick="orderApprovalApprove(event)">
                <label class="form-check-label fw-bold" for="oa_appv">อนุมัติ</label>
            </div>
        </div>
        <div class="col-md-3 of-row of-row-auto">
            <label class="form-label">วัน-เวลา อนุมัติ</label>
            <div class="of-ctl">
                <input type="text" id="oa_appvDT" class="form-control" readonly>
            </div>
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

    </div>{{-- /#oaDetailView --}}

</div>
