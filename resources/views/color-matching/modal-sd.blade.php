{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- Modal: ใบส่ง ต.ย. ให้ลูกค้า (Sample Delivery Doc)                    --}}
{{-- Trigger: data-bs-target="#sampleDeliveryModal"                       --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal modalHeadDecor fade" id="sampleDeliveryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-package me-1"></i>
                    ใบส่ง ต.ย. ให้ลูกค้า (Sample Delivery)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="form_sample_delivery" enctype="multipart/form-data">
                @csrf
                {{-- _mode = 'create' หรือ 'edit' (set by JS) --}}
                <input type="hidden" name="_mode" value="create">
                {{-- _pk = SendNo ของ record ที่ใช้อยู่ (จำเป็นสำหรับ SD เพราะอ้างอิงใบ CM) --}}
                <input type="hidden" name="_pk" value="">

                <!-- Body -->
                <div class="modal-body px-5 py-4" style="background-color: #f8f9fb;">

                    <div class="card shadow-sm mb-3"
                        style="border: 1px solid #dcd8f5; border-bottom-width: 0;">

                        <div class="card-header py-2 px-3 d-flex align-items-center"
                            style="background-color: #efedfd; border-bottom: 2px solid #6c5ce7; border-radius: 0.375rem 0.375rem 0 0;">
                            <h6 class="mb-0 fw-semibold" style="color: #3a309d;">
                                <i class="ti ti-package me-1"></i>
                                ใบส่ง ต.ย. ให้ลูกค้า
                            </h6>
                        </div>

                        <div class="card-body p-4">

                            {{-- ─── อ้างอิงใบนำส่งเทียบสี (CM) — 1 CM มีได้หลายใบส่ง ต.ย. ─── --}}
                            <div class="row g-3 mb-4 pb-3 border-bottom">
                                <div class="col-md-4">
                                    <label class="form-label small mb-1 fw-semibold" style="color:#4b3fb8;">
                                        <i class="ti ti-link me-1"></i>เลขที่ใบนำส่งเทียบสี (อ้างอิง) <span class="text-muted fw-normal">(เช่น 26/0001)</span>
                                    </label>
                                    <input type="text" name="SendNo" class="form-control">
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: ข้อมูลลูกค้า ─── --}}
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #6c5ce7;">
                                    <i class="ti ti-user-circle me-2" style="color: #4b3fb8;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #4b3fb8;">
                                        ข้อมูลลูกค้า
                                    </span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">รหัสลูกค้า <span class="text-muted fw-normal">(เช่น 00004)</span></label>
                                        <input type="text" name="custno" class="form-control">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">
                                            <span class="badge bg-label-secondary me-1">TH</span>
                                            ชื่อบริษัท (ไทย)
                                        </label>
                                        <input type="text" name="custname" class="form-control">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">
                                            <span class="badge bg-label-secondary me-1">EN</span>
                                            ชื่อบริษัท (อังกฤษ)
                                        </label>
                                        <input type="text" name="custnameEN" class="form-control">
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: เอกสารปิดงาน (อยู่ใต้ข้อมูลลูกค้า) ─── --}}
                            <div class="p-3 rounded mb-4" style="background-color: #fffaf0; border: 1px dashed #ffc107;">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #f0a500;">
                                    <i class="ti ti-receipt me-2" style="color: #8a6100;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #8a6100;">
                                        เอกสารปิดงาน
                                    </span>
                                </div>

                                <div class="row g-3 align-items-end">
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1 text-danger">
                                            <i class="ti ti-asterisk-simple"></i>
                                            เลขที่ใบส่ง ต.ย. ให้ลูกค้า <span class="text-muted fw-normal">(เช่น 53495-CP)</span>
                                        </label>
                                        <input type="text" name="Testno" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">วันที่เบิก <span class="text-muted fw-normal">(วว/ดด/ปปปป)</span></label>
                                        <input type="text" name="TestDate" class="form-control flatpickr-date">
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: ข้อมูลสี (เหมือนฟอร์ม CM — ดึงจากใบนำส่งเทียบสีตอนป้อน SendNo แต่แก้ไขได้) ─── --}}
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #6c5ce7;">
                                    <i class="ti ti-palette me-2" style="color: #4b3fb8;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #4b3fb8;">
                                        ข้อมูลสี
                                    </span>
                                </div>

                                <div class="row g-3">
                                    {{-- รายละเอียด (ช่องเดียวในกลุ่มข้อมูลสี) --}}
                                    <div class="col-md-8">
                                        <label class="form-label small mb-1">รายละเอียด <span class="text-muted fw-normal">(รายละเอียดผลิตภัณฑ์)</span></label>
                                        <input type="text" name="TestDesc" class="form-control">
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: รายละเอียดผลิตภัณฑ์ (ย้ายจากฟอร์ม CM) ─── --}}
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #6c5ce7;">
                                    <i class="ti ti-flask me-2" style="color: #4b3fb8;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #4b3fb8;">
                                        รายละเอียดผลิตภัณฑ์
                                    </span>
                                </div>

                                {{-- ความกว้างแต่ละช่องปรับตามเนื้อหา (ไม่ยืดเต็มคอลัมน์) — ใช้ w-100 คั่นเพื่อขึ้นบรรทัดใหม่ --}}
                                <div class="row g-3">
                                    {{-- แถว 1: Resin + PHR + น้ำหนัก + Standard --}}
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Resin (Match) <span class="text-muted fw-normal">(เช่น PVC, ABS, PE)</span></label>
                                        <input type="text" name="ResinMatch" class="form-control">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">PHR <span class="text-muted fw-normal">(0.0000)</span></label>
                                        <input type="number" step="0.0001" name="PHR" class="form-control text-end">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">น้ำหนัก (กรัม) <span class="text-muted fw-normal">(เช่น 100)</span></label>
                                        <input type="number" name="Wage" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Standard <span class="text-muted fw-normal">(ดึงจากใบนำส่งเทียบสี)</span></label>
                                        <input type="text" name="STD" class="form-control bg-label-secondary" readonly>
                                    </div>
                                    <div class="w-100"></div>

                                    {{-- แถว 2: เลขที่ใบรายงานผล + Lot No. --}}
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">เลขที่ใบรายงานผล <span class="text-muted fw-normal">(เช่น 26/0001/2/4)</span></label>
                                        <input type="text" name="rptno" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">Lot No. <span class="text-muted fw-normal">(เช่น 690112-1-2/01)</span></label>
                                        <input type="text" name="lotno" class="form-control bg-dark text-white">
                                    </div>
                                    <div class="w-100"></div>

                                    {{-- แถว 3: หมายเหตุ (ย้ายมาจากกลุ่มการยกเลิก/Sales) --}}
                                    <div class="col-12">
                                        <label class="form-label small mb-1">
                                            <i class="ti ti-note me-1"></i>
                                            หมายเหตุ <span class="text-muted fw-normal">(หมายเหตุเพิ่มเติม)</span>
                                        </label>
                                        <textarea name="remark" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="w-100"></div>

                                    {{-- แถว 4: เบอร์ที่อนุมัติ + Lot ที่ลูกค้าอนุมัติ --}}
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">ลูกค้าอนุมัติเบอร์ตั้ง <span class="text-muted fw-normal">(เช่น CP1Y583PVC)</span></label>
                                        <input type="text" name="CodeNo" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">Lot ที่ลูกค้าอนุมัติ <span class="text-muted fw-normal">(เช่น 690112-1-2/01)</span></label>
                                        <input type="text" name="ApprovedLot" class="form-control">
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: การยกเลิก / Sales ─── --}}
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #6c5ce7;">
                                    <i class="ti ti-alert-circle me-2" style="color: #4b3fb8;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #4b3fb8;">
                                        ข้อมูลการขาย / การยกเลิก
                                    </span>
                                </div>

                                <div class="row g-3 align-items-end">
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">Saleman Code <span class="text-muted fw-normal">(เช่น A1)</span></label>
                                        <input type="text" name="sale" class="form-control" maxlength="2">
                                    </div>
                                    <div class="col-md-4 px-4">
                                        <div class="form-check p-2 rounded border border-danger-subtle bg-danger-subtle">
                                            <input class="form-check-input" type="checkbox" name="cancel" value="1" id="cancel">
                                            <label for="cancel" class="form-check-label text-danger fw-semibold ms-1">
                                                cancel / วัตถุดิบแก้ไข Lot
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">สาเหตุที่ยกเลิก</label>
                                        <input type="text" name="CancalRes" class="form-control bg-label-secondary">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer justify-content-end flex-wrap gap-2">
                    {{-- <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-label-primary">
                            <i class="ti ti-link me-1"></i>
                            อ้างอิงใบนำส่งเทียบสี
                        </button>
                    </div> --}}
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                            ปิด
                        </button>
                        <button type="submit" class="btn btn-success px-5">
                            <i class="ti ti-device-floppy me-1"></i>
                            บันทึกใบส่ง ต.ย.
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>
</div>
