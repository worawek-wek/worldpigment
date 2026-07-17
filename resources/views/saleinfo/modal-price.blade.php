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
                                                <input type="text" name="CustNo" class="form-control" required>
                                            </div>
                                            <div class="col-md-7">
                                                <label class="form-label small mb-1 text-danger">
                                                    <i class="ti ti-asterisk-simple"></i>
                                                    ชื่อสินค้า <span class="text-muted fw-normal">(เช่น CP8462B)</span>
                                                </label>
                                                <input type="text" name="st_code" class="form-control" required>
                                            </div>
                                            <div class="col-md-7 offset-md-5">
                                                <label class="form-label small mb-1">รหัสสินค้า <span class="text-muted fw-normal">(เช่น CP8462B)</span></label>
                                                <input type="text" name="ITEMNO" class="form-control">
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
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">วันที่เริ่มซื้อ <span class="text-muted fw-normal">(วว/ดด/ปปปป)</span></label>
                                        <input type="text" name="DATE" class="form-control flatpickr-date">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1 text-danger">
                                            <i class="ti ti-asterisk-simple"></i>
                                            ราคา (บาท) <span class="text-muted fw-normal">(เช่น 46.72)</span>
                                        </label>
                                        <input type="number" step="0.01" name="PRICE" class="form-control text-end" required>
                                    </div>
                                    {{-- ปิดไว้ก่อน: NoAcp เป็นคอลัมน์ใน uprice ของเดิม (มีค่า 1 อยู่ 114 จาก 60,603 แถว)
                                         แต่ "ไม่รับ Order เบอร์นี้" เป็นการเดาความหมายจากชื่อคอลัมน์ — ไม่มีช่องนี้ในจอเก่า
                                         รอลูกค้ายืนยันความหมายก่อนค่อยเปิดใช้ (คอลัมน์ใน tb_saleinfo ยังอยู่)
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
                                        <input type="text" name="REM1" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small mb-1">
                                            หมายเหตุเพิ่มเติม / ประวัติการปรับราคา
                                            <span class="text-muted fw-normal">(เช่น เดิม 46.72.- ปรับลด 08/12/25 @44.62.-)</span>
                                        </label>
                                        <input type="text" name="REM2" class="form-control">
                                    </div>
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
                                        <input type="text" name="PackRem" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">Label DB <span class="text-muted fw-normal">(ข้อความบนฉลาก)</span></label>
                                        <input type="text" name="Label" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">ผู้บริหาร (ผู้อนุมัติราคา)</label>
                                        <input type="text" name="Author" class="form-control">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ─── ราคาตามขนาดบรรจุ (DB) + ค่าสี — ยังไม่รู้ที่มาของสูตร/ตาราง จึงยังไม่คำนวณ ─── --}}
                    <div class="card shadow-sm mb-0 wip" style="border: 1px solid #e3e5ea;">
                        <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between"
                            style="background-color: #f4f5f7; border-bottom: 2px solid #b6b9c2; border-radius: 0.375rem 0.375rem 0 0;">
                            <h6 class="mb-0 fw-semibold text-body">
                                <i class="ti ti-calculator me-1"></i>
                                ราคาตามขนาดบรรจุ (DB) และค่าสี
                            </h6>
                            <span class="badge bg-label-warning">รอสูตรคำนวณจากลูกค้า</span>
                        </div>

                        <div class="card-body p-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">ราคา 1 <span class="text-muted fw-normal">(DB 25 Kg. up)</span></label>
                                    <input type="number" step="0.01" name="db_price_1" class="form-control text-end" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">ราคา 2 <span class="text-muted fw-normal">(DB 3 - 4 Kg.)</span></label>
                                    <input type="number" step="0.01" name="db_price_2" class="form-control text-end" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">ราคา 3 <span class="text-muted fw-normal">(DB 1 - 2 Kg.)</span></label>
                                    <input type="number" step="0.01" name="db_price_3" class="form-control text-end" readonly>
                                </div>

                                <div class="col-md-3">
                                    <div class="p-3 rounded text-center" style="background-color: #f4f5f7; border: 1px solid #e3e5ea;">
                                        <div class="small text-muted">ค่าสีทั้งสิ้น</div>
                                        <div class="fs-5 fw-bold text-danger" id="saleinfo_color_cost">—</div>
                                        <div class="small text-muted mt-1">% สี</div>
                                        <div class="fw-semibold" id="saleinfo_color_pct">—</div>
                                    </div>
                                </div>
                            </div>
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
