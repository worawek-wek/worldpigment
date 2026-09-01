{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- Modal: กำหนดราคา (ราคาสินค้าต่อลูกค้า)                                --}}
{{-- Trigger: data-bs-target="#saleinfoModal" (สร้าง) / viewSaleinfo(id) (แก้ไข) --}}
{{-- โครงช่องอ้างอิงจากจอเก่า (Access) — ปลายทางคือตาราง uprice            --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal modalHeadDecor fade" id="saleinfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-tag me-1"></i>
                    กำหนดราคา
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="form_saleinfo" enctype="multipart/form-data">
                @csrf
                {{-- _mode = 'create' หรือ 'edit' (set by JS ตอนเปิด modal) --}}
                <input type="hidden" name="_mode" value="create">
                {{-- _pk = id ของแถวที่กำลังแก้ไข --}}
                <input type="hidden" name="_pk" value="">

                <!-- Body -->
                <div class="modal-body px-5 py-4" style="background-color: #f8f9fb;">

                    <div class="card shadow-sm mb-3"
                        style="border: 1px solid #f0dfc0; border-bottom-width: 0;">

                        <div class="card-header py-2 px-3 d-flex align-items-center"
                            style="background-color: #fdf3e3; border-bottom: 2px solid #E08A1E; border-radius: 0.375rem 0.375rem 0 0;">
                            <h6 class="mb-0 fw-semibold" style="color: #7a4d05;">
                                <i class="ti ti-tag me-1"></i>
                                ราคาสินค้าของลูกค้า
                            </h6>
                        </div>

                        <div class="card-body p-4">

                            {{-- ─── กลุ่ม: ลูกค้า / สินค้า ─── --}}
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #E08A1E;">
                                    <i class="ti ti-user-circle me-2" style="color: #b26a09;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #b26a09;">
                                        ลูกค้า และสินค้า
                                    </span>
                                </div>

                                <div class="row g-3">
                                    {{-- ซ้าย: ช่องกรอก --}}
                                    <div class="col-lg-7">
                                        <div class="row g-3">
                                            <div class="col-md-5">
                                                <label class="form-label small mb-1">รหัสลูกค้า <span class="text-muted fw-normal">(เช่น 41008)</span></label>
                                                <input type="text" name="CustNo" maxlength="5" class="form-control" required>
                                            </div>
                                            <div class="col-md-7">
                                                <label class="form-label small mb-1 text-danger">
                                                    <i class="ti ti-asterisk-simple"></i>
                                                    ชื่อสินค้า <span class="text-muted fw-normal">(เช่น CP8462B)</span>
                                                </label>
                                                <input type="text" name="st_code" maxlength="17" class="form-control" required>
                                            </div>
                                            <div class="col-md-7 offset-md-5">
                                                <label class="form-label small mb-1">รหัสสินค้า <span class="text-muted fw-normal">(เช่น CP8462B)</span></label>
                                                <input type="text" name="ITEMNO" maxlength="17" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ขวา: ข้อมูลลูกค้า (อ่านอย่างเดียว — auto-fill จากรหัสลูกค้า) --}}
                                    <div class="col-lg-5">
                                        <div class="p-3 rounded h-100" style="background-color: #f4f5f7; border: 1px solid #e3e5ea;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="small fw-semibold text-muted">ข้อมูลลูกค้า</span>
                                                <span id="saleinfo_cust_group" class="badge bg-label-secondary d-none">ในเครือ</span>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-4 small text-muted">code</div>
                                                <div class="col-8 small fw-medium" id="saleinfo_cust_code">—</div>
                                                <div class="col-4 small text-muted">NAME</div>
                                                <div class="col-8 small fw-medium" id="saleinfo_cust_name">—</div>
                                                <div class="col-4 small text-muted">ROAD</div>
                                                <div class="col-8 small fw-medium" id="saleinfo_cust_road">—</div>
                                                <div class="col-4 small text-muted">เงื่อนไข</div>
                                                <div class="col-8 small fw-medium" id="saleinfo_cust_term">—</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: ราคา ─── --}}
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #E08A1E;">
                                    <i class="ti ti-currency-baht me-2" style="color: #b26a09;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #b26a09;">
                                        ราคา
                                    </span>
                                </div>

                                <div class="row g-3 align-items-end">
                                    {{-- wip: `uprice` ไม่มีคอลัมน์ NotifyDate → ช่องนี้ยังไม่ถูกบันทึก (29/08/2569)
                                         รอลูกค้ายืนยันว่าจะเพิ่มคอลัมน์เข้า uprice หรือตัดช่องนี้ออกจากฟอร์ม
                                         เสร็จแล้ว: ลบคำว่า wip + คืน 'NotifyDate' เข้า SaleinfoController::COLUMNS --}}
                                    <div class="col-md-3 wip">
                                        <label class="form-label small mb-1">วันที่แจ้งปรับ <span class="text-muted fw-normal">(วว/ดด/ปปปป)</span></label>
                                        <input type="text" name="NotifyDate" class="form-control flatpickr-date"
                                            title="ยังไม่บันทึก — ตาราง uprice ไม่มีคอลัมน์นี้ รอลูกค้ายืนยัน">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">วันที่เริ่มราคาใหม่ <span class="text-muted fw-normal">(วว/ดด/ปปปป)</span></label>
                                        <input type="text" name="DATE" class="form-control flatpickr-date">
                                    </div>
                                    {{-- wip: `uprice` ไม่มีคอลัมน์ MOQ → ช่องนี้ยังไม่ถูกบันทึก (29/08/2569) --}}
                                    <div class="col-md-3 wip">
                                        <label class="form-label small mb-1">MOQ <span class="text-muted fw-normal">(kg)</span></label>
                                        {{-- input ธรรมดา + คอมมาอัตโนมัติ (ถอดคอมมาก่อน submit ด้วย stripCommaFields) --}}
                                        <input type="text" name="MOQ" class="form-control text-end js-comma"
                                            inputmode="decimal" autocomplete="off" placeholder="0.00"
                                            title="ยังไม่บันทึก — ตาราง uprice ไม่มีคอลัมน์นี้ รอลูกค้ายืนยัน">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1 text-danger">
                                            <i class="ti ti-asterisk-simple"></i>
                                            ราคา หรือ ค่าแรง+ค่าสี <span class="text-muted fw-normal">(เช่น 46.72)</span>
                                        </label>
                                        <input type="text" name="PRICE" class="form-control text-end js-comma"
                                            inputmode="decimal" autocomplete="off" placeholder="0.00" required>
                                    </div>
                                    {{-- ปิดไว้ก่อน: NoAcp เป็นคอลัมน์ใน uprice ของเดิม (มีค่า 1 อยู่ 114 จาก 60,603 แถว)
                                         แต่ "ไม่รับ Order เบอร์นี้" เป็นการเดาความหมายจากชื่อคอลัมน์ — ไม่มีช่องนี้ในจอเก่า
                                         รอลูกค้ายืนยันความหมายก่อนค่อยเปิดใช้ (คอลัมน์ NoAcp ใน uprice ยังอยู่ ค่าเดิมไม่ถูกแตะ)
                                    <div class="col-md-6">
                                        <div class="form-check p-2 rounded border border-danger-subtle bg-danger-subtle ms-2">
                                            <input class="form-check-input" type="checkbox" name="NoAcp" value="1" id="saleinfo_noacp">
                                            <label for="saleinfo_noacp" class="form-check-label text-danger fw-semibold ms-1">
                                                ไม่รับ Order เบอร์นี้ (NoAcp)
                                            </label>
                                        </div>
                                    </div>
                                    --}}

                                    <div class="col-12">
                                        <label class="form-label small mb-1">
                                            <i class="ti ti-note me-1"></i>
                                            หมายเหตุ <span class="text-muted fw-normal">(เช่น เฉพาะเบอร์คิดราคาพิเศษ เช็คราคาก่อนเปิด ORDER)</span>
                                        </label>
                                        <input type="text" name="REM1" maxlength="100" class="form-control">
                                    </div>
                                    {{-- ปิดไว้: หมายเหตุเพิ่มเติม / ประวัติการปรับราคา (REM2)
                                         เลิกใช้เพราะทำตาราง "ประวัติการปรับราคา" ขึ้นมาแทนแล้ว
                                         (คอลัมน์ REM2 ใน uprice ยังอยู่ ค่าเดิมไม่ถูกแตะต้อง)
                                    <div class="col-12">
                                        <label class="form-label small mb-1">
                                            หมายเหตุเพิ่มเติม / ประวัติการปรับราคา
                                            <span class="text-muted fw-normal">(เช่น เดิม 46.72.- ปรับลด 08/12/25 @44.62.-)</span>
                                        </label>
                                        <input type="text" name="REM2" class="form-control">
                                    </div>
                                    --}}
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: บรรจุภัณฑ์ และผู้อนุมัติ ─── --}}
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #E08A1E;">
                                    <i class="ti ti-package me-2" style="color: #b26a09;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #b26a09;">
                                        บรรจุภัณฑ์ และผู้อนุมัติ
                                    </span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">ระบุข้างบรรจุภัณฑ์ <span class="text-muted fw-normal">(เช่น PP AZ 864 (NH-361L))</span></label>
                                        <input type="text" name="PackRem" maxlength="85" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">Label DB <span class="text-muted fw-normal">(ข้อความบนฉลาก)</span></label>
                                        <input type="text" name="Label" maxlength="85" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">ผู้บริหาร (ผู้อนุมัติราคา)</label>
                                        <input type="text" name="Author" maxlength="50" class="form-control">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ─── ประวัติการปรับราคา — แสดงเมื่อรหัสลูกค้า + รหัสสินค้า ตรงกับที่เคยบันทึกไว้ ─── --}}
                    {{-- โหลดผ่าน AJAX (saleinfo/history) เมื่อกรอกครบ — ดู index.blade.php --}}
                    <div id="saleinfo_history_card" class="card shadow-sm mb-3 d-none saleinfo-history">
                        <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-semibold d-flex align-items-center" style="color: #7a4d05;">
                                <i class="ti ti-history me-1"></i>
                                ประวัติการปรับราคา
                            </h6>
                            <span class="badge rounded-pill" id="saleinfo_history_count"
                                style="background-color: #E08A1E; color: #fff;">0 รายการ</span>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center wip">วันที่แจ้งปรับ</th>
                                            <th class="text-center">วันที่เริ่มราคาใหม่</th>
                                            <th>รหัสสินค้า</th>
                                            <th class="text-end wip">MOQ (kg)</th>
                                            <th class="text-end">ราคา หรือ<br>ค่าแรง+ค่าสี</th>
                                            <th>หมายเหตุ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="saleinfo_history_body">
                                        {{-- เติมด้วย JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ─── ราคาตามขนาดบรรจุ (DB) — คำนวณชุดเดียวกับจอ "ค้นหาราคาสินค้า" (25/08/2569) ─── --}}
                    {{-- ดึงจาก saleinfo/price-lookup → ProductPriceService::lookup(รหัสสินค้า)          --}}
                    {{-- ราคา 1 = ราคาทุน × คูณ ÷ หาร + บวก · ราคา 2/3 และ DB คูณต่อกันเป็นขั้น ๆ       --}}
                    {{-- ⚠ ค่าสีทั้งสิ้น / % สี ยังไม่รู้สูตร — คง wip ไว้เฉพาะกล่องนั้น                --}}
                    <div class="card shadow-sm mb-0 saleinfo-dbprice">
                        <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-semibold d-flex align-items-center" style="color: #7a4d05;">
                                <i class="ti ti-calculator me-1"></i>
                                คำนวนราคา
                            </h6>
                            <span class="small text-muted">คำนวณจากรหัสสินค้า</span>
                        </div>

                        <div class="card-body p-4">
                            {{-- ทั้ง section เป็น grid เดียว: ราคา 3 ช่อง/แถว + กล่องค่าสี span 2 แถวทางขวา --}}
                            <div class="dbprice-layout">
                                <div class="dbprice-tier">
                                    <div class="dbprice-tier-label">ราคา 1</div>
                                    <div class="dbprice-value">
                                        <span class="dbprice-unit">฿</span>
                                        <input type="text" name="db_price_1" class="dbprice-input" placeholder="—" readonly tabindex="-1">
                                    </div>
                                </div>
                                <div class="dbprice-tier">
                                    <div class="dbprice-tier-label">ราคา 2</div>
                                    <div class="dbprice-value">
                                        <span class="dbprice-unit">฿</span>
                                        <input type="text" name="db_price_2" class="dbprice-input" placeholder="—" readonly tabindex="-1">
                                    </div>
                                </div>
                                <div class="dbprice-tier">
                                    <div class="dbprice-tier-label">ราคา 3</div>
                                    <div class="dbprice-value">
                                        <span class="dbprice-unit">฿</span>
                                        <input type="text" name="db_price_3" class="dbprice-input" placeholder="—" readonly tabindex="-1">
                                    </div>
                                </div>
                                <div class="dbprice-tier">
                                    <div class="dbprice-tier-label">DB 3 - 4 Kg.</div>
                                    <div class="dbprice-value">
                                        <span class="dbprice-unit">฿</span>
                                        <input type="text" name="db_price_3_4" class="dbprice-input" placeholder="—" readonly tabindex="-1">
                                    </div>
                                </div>
                                <div class="dbprice-tier">
                                    <div class="dbprice-tier-label">DB 1 - 2 Kg.</div>
                                    <div class="dbprice-value">
                                        <span class="dbprice-unit">฿</span>
                                        <input type="text" name="db_price_1_2" class="dbprice-input" placeholder="—" readonly tabindex="-1">
                                    </div>
                                </div>

                                {{-- กล่องค่าสี — วางขวา span 2 แถว (คุมด้วย CSS .dbprice-colorbox)
                                     ยังเป็น wip: ยังไม่รู้สูตร/ที่มาของ "ค่าสีทั้งสิ้น" กับ "% สี" --}}
                                <div class="dbprice-colorbox wip">
                                    <div class="dbprice-colorcell">
                                        <div class="dbprice-colorlabel">ค่าสีทั้งสิ้น</div>
                                        <div class="dbprice-colorval text-danger" id="saleinfo_color_cost">—</div>
                                    </div>
                                    <div class="dbprice-colorcell">
                                        <div class="dbprice-colorlabel">% สี</div>
                                        <div class="dbprice-colorval" id="saleinfo_color_pct">—</div>
                                    </div>
                                </div>
                            </div>

                            {{-- ที่มาของราคา: ราคาทุน · เงื่อนไขที่จับคู่ได้ · สูตรคูณ/หาร/บวก
                                 คำนวณไม่ได้ก็บอกเหตุผลตรงนี้ ไม่ปล่อยช่องว่างเงียบ ๆ --}}
                            <div class="saleinfo-price-note mt-3" id="saleinfo_price_note"></div>
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer justify-content-between flex-wrap gap-2">
                    <div class="d-flex gap-2 flex-wrap">
                        {{-- ลบ — แสดงเฉพาะโหมดแก้ไข (JS ถอด d-none ให้) --}}
                        <button type="button" id="btn_delete_saleinfo" class="btn btn-label-danger d-none">
                            <i class="ti ti-trash me-1"></i>
                            ลบราคานี้
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                            ปิด
                        </button>
                        <button type="submit" class="btn btn-success px-5">
                            <i class="ti ti-device-floppy me-1"></i>
                            บันทึกราคา
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>
</div>
