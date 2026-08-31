{{--
    ฟอร์มบันทึกใบสั่งซื้อ — แปลงผังมาจากฟอร์ม Access "บันทึกคำสั่งซื้อ"
    ค่าทั้งหมดเติมด้วย JS (ดู fillOrderForm ใน order/index.blade.php)
    การบันทึกยังไม่เปิดใช้งานในเฟสนี้ — รอยืนยันกติกาการเดินเลขที่ใบสั่ง + คอลัมน์ที่แก้ได้
--}}
<div class="modal-body px-4 py-4">

    {{-- ── แถบประเภทใบสั่งซื้อ (radio) — 2 ตัวอักษรหน้าเลขที่ใบสั่ง ── --}}
    {{-- of-narrow = จำกัดความกว้างส่วนหัวฟอร์มไม่ให้ยืดตาม modal ที่กว้าง 95vw
         (ตารางรายการด้านล่างไม่ใส่ class นี้ จึงยังกว้างเต็ม modal) --}}
    <div class="of-typebar of-narrow">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                {{-- จัดปุ่มเป็นกลุ่มละ 2 (C / H / W) แล้วเว้นช่องไฟระหว่างกลุ่ม --}}
                <div class="of-typegrid">
                    @foreach ($type_rows as $types)
                        <div class="of-typerow">
                            @foreach (array_chunk($types, 2) as $pair)
                                <div class="of-typepair">
                                    @foreach ($pair as $t)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="order_type_form"
                                                id="o_type_{{ $t }}" value="{{ $t }}" onchange="onOrderTypeChange('{{ $t }}')">
                                            <label class="form-check-label" for="o_type_{{ $t }}">{{ $t }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="button" class="btn of-btn-new" onclick="orderNew()">
                <i class="ti ti-plus me-1"></i>เพิ่มใบสั่งซื้อใหม่
            </button>
        </div>
    </div>

    <div class="row g-3 mt-1 of-narrow">

        {{-- ═══════════ คอลัมน์ซ้าย: ข้อมูลใบสั่งซื้อ ═══════════ --}}
        <div class="col-xl-5">
            <div class="of-sec h-100">
                <div class="of-sec-title"><i class="ti ti-file-text"></i>ข้อมูลใบสั่งซื้อ</div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label">เลขที่ใบสั่ง</label>
                        <input type="text" id="o_Orderno" class="form-control fw-bold text-primary" maxlength="15"
                            autocomplete="off" readonly>
                        {{-- โชว์เฉพาะตอนเพิ่งเปิดฟอร์ม (โหมด idle) --}}
                        <div class="form-text d-none" id="o_orderno_hint">
                            <i class="ti ti-corner-down-left me-1"></i>กรอกเลขที่ใบเดิมแล้วกด Enter เพื่อแก้ไขใบนั้น
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">วันที่</label>
                        <input type="text" id="o_Mdate" class="form-control flatpickr-datetime"
                            autocomplete="off" placeholder="วว/ดด/ปปปป ชม:นท">
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

                    {{-- รหัสลูกค้า / ชื่อลูกค้า อยู่คนละแถวกัน
                         (รหัสลูกค้าคง col-sm-4 เท่าเดิม ส่วนชื่อลูกค้าเป็น col-12 จึงตกไปอยู่แถวถัดไปเอง) --}}
                    <div class="col-sm-4">
                        <label class="form-label">รหัสลูกค้า</label>
                        <input type="text" id="o_Custno" class="form-control" maxlength="10"
                            oninput="lookupOrderCustomer(this.value)">
                    </div>
                    <div class="col-12">
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
                        <label class="form-label">
                            รหัสผู้ขาย
                            <i class="ti ti-info-circle text-muted" title="ดึงจากพนักงานขายประจำลูกค้า (customer.sale)"></i>
                        </label>
                        <input type="text" id="o_supno" class="form-control bg-light" maxlength="2" readonly>
                    </div>
                    <div class="col-sm-5">
                        <label class="form-label">
                            itype
                            {{-- ใบสั่งที่ขึ้นต้นด้วย W ต้องมี itype — โชว์ * เฉพาะตอนนั้น (syncItypeRequired) --}}
                            <span id="o_itype_req" class="text-danger fw-bold d-none"
                                title="ใบสั่งที่ขึ้นต้นด้วย W ต้องระบุ itype">*</span>
                            <i class="ti ti-info-circle text-muted"
                                title="ประเภทสินค้าที่สั่ง — กดที่ช่องเพื่อเลือก (เลือกได้ข้อเดียว)"></i>
                        </label>
                        {{--
                            ช่อง itype — กดแล้วกางรายการให้ติ๊กเลือก (ตัวเลือกจาก config/order.php → itypes)
                            เลือกได้ "ข้อเดียว" ตามที่ผู้ใช้กำหนด: ติ๊กข้อใหม่แล้วข้อเก่าจะหลุดเอง
                            ⚠ ยังไม่มีที่เก็บใน DB → ค่าที่เลือกไม่ถูกบันทึก (ดูหมายเหตุใน config/order.php)
                        --}}
                        <div class="dropdown">
                            <input type="text" id="o_itype" class="form-control bg-light" readonly
                                data-bs-toggle="dropdown" data-bs-display="static"
                                style="cursor:pointer;" placeholder="— กดเพื่อเลือก —">
                            <ul class="dropdown-menu w-100 py-2" id="o_itype_menu">
                                @foreach (config('order.itypes', []) as $it)
                                    <li>
                                        <div class="form-check px-3 mx-2">
                                            <input class="form-check-input o-itype-opt" type="checkbox"
                                                id="o_itype_{{ $it['key'] }}" value="{{ $it['key'] }}"
                                                data-label="{{ $it['label'] }}">
                                            <label class="form-check-label" for="o_itype_{{ $it['key'] }}">
                                                {{ $it['label'] }}
                                            </label>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">เลขที่ใบจอง</label>
                        <input type="text" id="o_RsvNo" class="form-control" maxlength="20">
                    </div>

                    {{-- checkbox ชุดล่าง — ใน Access เก็บเป็น -1 = ติ๊ก
                         RP / CER / MSDS ติ๊กให้ตามค่าประจำลูกค้า (customer) เมื่อเลือกลูกค้า — แก้ทับได้
                         ส่งก่อนได้ / SPEC ไม่มีค่าประจำลูกค้า ต้องติ๊กเอง --}}
                    <div class="col-12">
                        <label class="form-label d-block">
                            เงื่อนไขบนใบสั่ง
                            <i class="ti ti-info-circle text-muted"
                                title="RP / CER / MSDS ตั้งให้ตามข้อมูลลูกค้าเมื่อเลือกลูกค้า — แก้ทับได้"></i>
                        </label>
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
                            <input type="text" id="o_HMStore" class="form-control text-end js-comma"
                                inputmode="decimal" autocomplete="off" placeholder="0.00">
                            <span class="input-group-text">ก.ก.</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">จะส่งมอบเดือนละ</label>
                        <div class="input-group">
                            <input type="text" id="o_sendmth" class="form-control text-end js-comma"
                                inputmode="decimal" autocomplete="off" placeholder="0.00">
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
                            <i class="ti ti-info-circle text-muted" title="= ราคาช่อง 2 จากระบบกำหนดราคา"></i>
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

                    <div class="col-6">
                        <label class="form-label mb-0 small">
                            ราคาที่กำหนดไว้
                            <i class="ti ti-info-circle text-muted" title="= ราคาช่อง 1 จากระบบกำหนดราคา"></i>
                        </label>
                    </div>
                    <div class="col-6">
                        <input type="text" id="o_fixed_price" class="form-control form-control-sm text-end" readonly>
                    </div>

                    <div class="col-6"><label class="form-label mb-0 small">ราคาช่อง 2</label></div>
                    <div class="col-6">
                        <input type="text" id="o_price2" class="form-control form-control-sm text-end" readonly>
                    </div>

                    {{-- ราคาขาย = ช่องเดียวในกล่องนี้ที่ผู้ใช้พิมพ์เอง --}}
                    <div class="col-6"><label class="form-label mb-0 fw-bold" for="o_price">ราคาขาย</label></div>
                    <div class="col-6">
                        <input type="text" id="o_price" class="form-control form-control-sm text-end fw-bold js-comma"
                            inputmode="decimal" autocomplete="off" placeholder="0.00">
                    </div>

                    {{-- บอกที่มา/เหตุผลของค่าในกล่องนี้ — กันสับสนเวลาช่องว่างเพราะไม่มีข้อมูล --}}
                    <div class="col-12">
                        <div class="of-price-note" id="o_price_note"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════ คอลัมน์ขวา: สินค้า + ปุ่มบันทึก ═══════════ --}}
        <div class="col-xl-3">
            <div class="of-sec of-sec-item h-100 d-flex flex-column">
                <div class="of-sec-title"><i class="ti ti-package"></i>สินค้า</div>

                <div class="mb-3">
                    <label class="form-label">
                        รหัสสินค้า
                        <i class="ti ti-info-circle text-muted" title="ดึงจากรหัสสินค้าที่กรอกในตารางรายการ"></i>
                    </label>
                    <input type="text" id="o_itemno" class="form-control of-hl-yellow fw-semibold" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">น้ำหนักรวม</label>
                    <div class="input-group">
                        <input type="text" id="o_netqty" class="form-control text-end js-comma"
                            inputmode="decimal" autocomplete="off" placeholder="0.00"
                            oninput="refreshOrderPrice()">
                        <span class="input-group-text">ก.ก.</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        กลุ่มราคา
                        <i class="ti ti-info-circle text-muted"
                            title="คำนวณจากน้ำหนักรวม — A = 1,000 kg.up / B = 500 kg.up / C = under 500 kg."></i>
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
                    <button type="button" id="btnSaveOrder" class="btn btn-primary w-100" onclick="saveOrder()">
                        <i class="ti ti-device-floppy me-1"></i>บันทึกการรับคำสั่งซื้อ
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ ตารางรายการ (suborder) — ช่องกรอก เพิ่ม/ลบแถวได้ ═══════════ --}}
    <div class="of-sec mt-3">
        <div class="of-sec-title d-flex justify-content-between align-items-center">
            <span><i class="ti ti-list-details"></i>รายการในใบสั่งซื้อ</span>
            <button type="button" class="btn btn-sm btn-label-warning" onclick="addOrderItem()">
                <i class="ti ti-plus me-1"></i>เพิ่มแถว
            </button>
        </div>

        {{-- แถบเตือนแบบฟอร์มเดิม: สีที่ไม่ได้สั่งมานานต้อง Match ใหม่ --}}
        <div class="of-matchwarn d-none" id="o_match_warn">
            สีที่สั่งซื้อล่าสุดเกิน 3 ปี จะต้อง Match ใหม่
            <span class="fw-normal ms-2" id="o_match_detail"></span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0" id="orderItemsTable">
                <thead class="text-center">
                    <tr>
                        <th rowspan="2" style="width:40px;">#</th>
                        <th rowspan="2" style="min-width:150px;">รหัสสินค้า</th>
                        <th rowspan="2" style="width:80px;">รหัส</th>
                        <th rowspan="2" style="min-width:150px;">ชื่อสินค้า</th>
                        <th rowspan="2" style="min-width:110px;">Lotno.</th>
                        <th colspan="2">น้ำหนัก (ก.ก.)</th>
                        <th rowspan="2" style="min-width:120px;">กำหนดที่ลูกค้า<br>ต้องการ</th>
                        <th rowspan="2" style="min-width:120px;">กำหนดส่ง<br>ทบทวน</th>
                        <th rowspan="2" style="min-width:120px;">วันที่ผลิตเสร็จ</th>
                        <th rowspan="2" style="min-width:120px;">วันที่ลูกค้าได้รับ</th>
                        <th rowspan="2" style="min-width:110px;">เลขที่ใบส่ง</th>
                        <th rowspan="2" style="min-width:260px;">หมายเหตุ</th>
                        <th rowspan="2" style="width:46px;">ลบ</th>
                    </tr>
                    <tr>
                        <th style="width:105px;">S</th>
                        <th style="width:105px;">P</th>
                    </tr>
                </thead>
                <tbody id="orderItems"></tbody>
                <tfoot>
                    <tr>
                        <th class="text-end" colspan="5">รวม</th>
                        <th class="text-end" id="o_total_stock">0.00</th>
                        <th class="text-end" id="o_total_prod">0.00</th>
                        <th colspan="7"></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- ตัวเลือกของช่อง "รหัส" (suborder.nold) --}}
        <datalist id="noldList">
            @foreach ($nold_options as $n)
                <option value="{{ $n }}"></option>
            @endforeach
        </datalist>

        {{-- รายการหมายเหตุสำเร็จรูป (ตาราง ordrem) — ฟอร์มเดิมเป็น dropdown ในช่องหมายเหตุ --}}
        <datalist id="ordremList">
            @foreach ($remarks as $rem)
                <option value="{{ $rem }}"></option>
            @endforeach
        </datalist>
    </div>

</div>
