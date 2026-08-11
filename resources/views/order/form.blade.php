{{--
    ฟอร์มบันทึกใบสั่งซื้อ — แปลงผังมาจากฟอร์ม Access "บันทึกคำสั่งซื้อ"
    ค่าทั้งหมดเติมด้วย JS (ดู fillOrderForm ใน order/index.blade.php)
    การบันทึกยังไม่เปิดใช้งานในเฟสนี้ — รอยืนยันกติกาการเดินเลขที่ใบสั่ง + คอลัมน์ที่แก้ได้
--}}
<div class="modal-body px-4 py-4">

    {{-- ── แถบประเภทใบสั่งซื้อ (radio) — 2 ตัวอักษรหน้าเลขที่ใบสั่ง ── --}}
    <div class="of-typebar">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="of-typebar-title">บันทึกคำสั่งซื้อ</div>
                <div class="of-typegrid">
                    @foreach ($type_rows as $rowIdx => $types)
                        <div class="of-typerow">
                            @foreach ($types as $t)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="order_type_form"
                                        id="o_type_{{ $t }}" value="{{ $t }}" onchange="onOrderTypeChange('{{ $t }}')">
                                    <label class="form-check-label" for="o_type_{{ $t }}">{{ $t }}</label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="button" class="btn btn-light" onclick="orderNew()">
                <i class="ti ti-plus me-1"></i>เพิ่มใบสั่งซื้อใหม่
            </button>
        </div>
    </div>

    <div class="row g-3 mt-1">

        {{-- ═══════════ คอลัมน์ซ้าย: ข้อมูลใบสั่งซื้อ ═══════════ --}}
        <div class="col-xl-5">
            <div class="of-sec h-100">
                <div class="of-sec-title"><i class="ti ti-file-text"></i>ข้อมูลใบสั่งซื้อ</div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label">เลขที่ใบสั่ง</label>
                        <input type="text" id="o_Orderno" class="form-control fw-bold text-primary" maxlength="15" readonly>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">วันที่</label>
                        <input type="text" id="o_Mdate" class="form-control" readonly>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">ผลิตที่</label>
                        <select id="o_Company" class="form-select">
                            <option value="">—</option>
                            @foreach ($companies as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">P/O No.</label>
                        <input type="text" id="o_PO" class="form-control" maxlength="25">
                    </div>

                    <div class="col-sm-4">
                        <label class="form-label">รหัสลูกค้า</label>
                        <input type="text" id="o_Custno" class="form-control" maxlength="10"
                            oninput="lookupOrderCustomer(this.value)">
                    </div>
                    <div class="col-sm-8">
                        <label class="form-label">ชื่อลูกค้า</label>
                        <input type="text" id="o_Custname" class="form-control text-primary fw-semibold" readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label">
                            สถานที่ส่ง
                            <span class="text-muted fw-normal small">(ไม่ระบุ = ส่งตามที่อยู่ลูกค้า)</span>
                        </label>
                        <select id="o_DVpoint" class="form-select">
                            <option value="">— ไม่ระบุ —</option>
                        </select>
                    </div>

                    <div class="col-sm-4">
                        <label class="form-label">ผู้บันทึก</label>
                        <input type="text" id="o_Emp" class="form-control" maxlength="20">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">รหัสผู้ขาย</label>
                        <input type="text" id="o_supno" class="form-control" maxlength="2">
                    </div>
                    <div class="col-sm-5">
                        <label class="form-label">
                            itype
                            <i class="ti ti-info-circle text-muted" title="ประเภทอุตสาหกรรมของลูกค้า (ตาราง c_type)"></i>
                        </label>
                        <input type="text" id="o_itype" class="form-control bg-light" readonly>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">เลขที่ใบจอง</label>
                        <input type="text" id="o_RsvNo" class="form-control" maxlength="20">
                    </div>

                    {{-- checkbox ชุดล่าง — ใน Access เก็บเป็น -1 = ติ๊ก --}}
                    <div class="col-12">
                        <div class="of-checkrow">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="o_Send">
                                <label class="form-check-label" for="o_Send">ส่งก่อนได้</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="o_RP">
                                <label class="form-check-label" for="o_RP">RP</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="o_Spec">
                                <label class="form-check-label" for="o_Spec">SPEC</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="o_Cer">
                                <label class="form-check-label" for="o_Cer">CER</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="o_MSDS">
                                <label class="form-check-label" for="o_MSDS">MSDS</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════ คอลัมน์กลาง: สั่งทำสต๊อก + ราคา ═══════════ --}}
        <div class="col-xl-4">
            <div class="of-sec of-sec-stock">
                <div class="of-sec-title"><i class="ti ti-building-warehouse"></i>กรณีสั่งทำสต๊อก</div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">กำหนดส่งครบ วันที่</label>
                        <input type="text" id="o_sendend" class="form-control flatpickr-date" autocomplete="off"
                            placeholder="วว/ดด/ปปปป">
                    </div>
                    <div class="col-12">
                        <label class="form-label">ส่งลูกค้าภายใน</label>
                        <div class="input-group">
                            <input type="number" id="o_SendCust" class="form-control text-end" step="1">
                            <span class="input-group-text bg-success text-white">เดือน</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">นน.ในคลังคงเหลือ</label>
                        <div class="input-group">
                            <input type="number" id="o_HMStore" class="form-control text-end" step="0.01">
                            <span class="input-group-text">ก.ก.</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">จะส่งมอบเดือนละ</label>
                        <div class="input-group">
                            <input type="number" id="o_sendmth" class="form-control text-end" step="0.01">
                            <span class="input-group-text">ก.ก.</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- กล่องราคา — อ่านจากตารางราคา (uprice / appvreq / zcustprice) ทั้งหมด แก้ที่นี่ไม่ได้ --}}
            <div class="of-sec of-sec-price mt-3">
                <div class="of-sec-title"><i class="ti ti-tag"></i>ราคา</div>

                <div class="row g-2 align-items-center">
                    <div class="col-6">
                        <label class="form-label mb-0 small">
                            ราคาต้องไม่ต่ำกว่า
                            <i class="ti ti-info-circle text-muted"
                                title="อนุมานจากฟอร์มเดิมว่าเท่ากับราคาช่อง 2 — รอยืนยันกติกาจริง"></i>
                        </label>
                    </div>
                    <div class="col-6">
                        <input type="text" id="o_min_price" class="form-control form-control-sm text-end of-warn" readonly>
                    </div>

                    <div class="col-6"><label class="form-label mb-0 small">ราคาอนุมัติ</label></div>
                    <div class="col-6">
                        <input type="text" id="o_appv_price" class="form-control form-control-sm text-end" readonly>
                    </div>

                    <div class="col-6"><label class="form-label mb-0 small">ยืนราคาถึง</label></div>
                    <div class="col-6">
                        <input type="text" id="o_valid_to" class="form-control form-control-sm text-end" readonly>
                    </div>

                    <div class="col-6"><label class="form-label mb-0 small">ราคาที่กำหนดไว้</label></div>
                    <div class="col-6">
                        <input type="text" id="o_fixed_price" class="form-control form-control-sm text-end" readonly>
                    </div>

                    <div class="col-6"><label class="form-label mb-0 small">ราคาช่อง 2</label></div>
                    <div class="col-6">
                        <input type="text" id="o_price2" class="form-control form-control-sm text-end" readonly>
                    </div>

                    <div class="col-6"><label class="form-label mb-0 fw-bold">ราคาขาย</label></div>
                    <div class="col-6">
                        <input type="text" id="o_price" class="form-control form-control-sm text-end fw-bold of-sell" readonly>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════ คอลัมน์ขวา: สินค้า + ปุ่มบันทึก ═══════════ --}}
        <div class="col-xl-3">
            <div class="of-sec of-sec-item h-100 d-flex flex-column">
                <div class="of-sec-title"><i class="ti ti-package"></i>สินค้า</div>

                <div class="mb-3">
                    <label class="form-label">รหัสสินค้า</label>
                    <input type="text" id="o_itemno" class="form-control of-hl-yellow fw-semibold" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">น้ำหนักรวม</label>
                    <div class="input-group">
                        <input type="number" id="o_netqty" class="form-control text-end" step="0.01">
                        <span class="input-group-text">ก.ก.</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        กลุ่มราคา
                        <i class="ti ti-info-circle text-muted" title="ยังไม่ยืนยันแหล่งข้อมูล — รอผู้ใช้ระบุ"></i>
                    </label>
                    <input type="text" id="o_price_group" class="form-control of-hl-green" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        ขั้นต่ำ
                        <i class="ti ti-info-circle text-muted" title="ยังไม่ยืนยันแหล่งข้อมูล — รอผู้ใช้ระบุ"></i>
                    </label>
                    <input type="text" id="o_min_qty" class="form-control of-hl-yellow text-end" readonly>
                </div>

                <div class="mt-auto">
                    <button type="button" class="btn btn-primary w-100" onclick="saveOrder()">
                        <i class="ti ti-device-floppy me-1"></i>บันทึกการรับคำสั่งซื้อ
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ ตารางรายการ (suborder) ═══════════ --}}
    <div class="of-sec mt-3">
        <div class="of-sec-title"><i class="ti ti-list-details"></i>รายการในใบสั่งซื้อ</div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0" id="orderItemsTable">
                <thead class="table-light text-center">
                    <tr>
                        <th rowspan="2" style="width:44px;">#</th>
                        <th colspan="2">น้ำหนัก (ก.ก.)</th>
                        <th rowspan="2">กำหนดที่ลูกค้าต้องการ</th>
                        <th rowspan="2">กำหนดส่งทบทวน</th>
                        <th rowspan="2">วันที่ผลิตเสร็จ</th>
                        <th rowspan="2">วันที่ลูกค้าได้รับ</th>
                        <th rowspan="2">เลขที่ใบส่ง</th>
                        <th rowspan="2" style="min-width:280px;">หมายเหตุ</th>
                    </tr>
                    <tr>
                        <th style="width:110px;">สต๊อก</th>
                        <th style="width:110px;">ผลิต</th>
                    </tr>
                </thead>
                <tbody id="orderItems"></tbody>
                <tfoot class="table-light">
                    <tr>
                        <th class="text-end" colspan="2">รวม</th>
                        <th class="text-end" id="o_total_prod">0.00</th>
                        <th colspan="6"></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- รายการหมายเหตุสำเร็จรูป (ตาราง ordrem) — ฟอร์มเดิมเป็น dropdown ในช่องหมายเหตุ --}}
        <datalist id="ordremList">
            @foreach ($remarks as $rem)
                <option value="{{ $rem }}"></option>
            @endforeach
        </datalist>
    </div>

</div>
