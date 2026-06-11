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
                                        <i class="ti ti-link me-1"></i>เลขที่ใบนำส่งเทียบสี (อ้างอิง)
                                    </label>
                                    <input type="text" name="SendNo" class="form-control"
                                        placeholder="เช่น 26/0001">
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
                                        <label class="form-label small mb-1">รหัสลูกค้า</label>
                                        <input type="text" name="custno" class="form-control"
                                            placeholder="เช่น 00004">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">
                                            <span class="badge bg-label-secondary me-1">TH</span>
                                            ชื่อบริษัท (ไทย)
                                        </label>
                                        <input type="text" name="custname" class="form-control"
                                            placeholder="ชื่อบริษัทภาษาไทย">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">
                                            <span class="badge bg-label-secondary me-1">EN</span>
                                            ชื่อบริษัท (อังกฤษ)
                                        </label>
                                        <input type="text" name="custnameEN" class="form-control"
                                            placeholder="ชื่อบริษัทภาษาอังกฤษ">
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: วันที่ดำเนินการ และผู้เทียบสี ─── --}}
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #6c5ce7;">
                                    <i class="ti ti-calendar-event me-2" style="color: #4b3fb8;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #4b3fb8;">
                                        วันที่ดำเนินการ และผู้เทียบสี
                                    </span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Start Date</label>
                                        <input type="text" name="startdate" class="form-control flatpickr-date"
                                            placeholder="วว/ดด/ปปปป">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Sample Date</label>
                                        <input type="text" name="SampleDate" class="form-control flatpickr-date"
                                            placeholder="วว/ดด/ปปปป">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Ready Date</label>
                                        <input type="text" name="ReadyDate" class="form-control flatpickr-date"
                                            placeholder="วว/ดด/ปปปป">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Color Matcher</label>
                                        <input type="text" name="ColorMatcher" class="form-control"
                                            placeholder="ชื่อพนักงาน">
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: รายละเอียดผลิตภัณฑ์ ─── --}}
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #6c5ce7;">
                                    <i class="ti ti-flask me-2" style="color: #4b3fb8;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #4b3fb8;">
                                        รายละเอียดผลิตภัณฑ์
                                    </span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-7">
                                        <label class="form-label small mb-1">รายละเอียด</label>
                                        <input type="text" name="TestDesc" class="form-control"
                                            placeholder="รายละเอียดผลิตภัณฑ์">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">ประเภท</label>
                                        <select name="TestType" class="form-select">
                                            <option value="">-- เลือก --</option>
                                            <option value="1">1 : CP</option>
                                            <option value="2">2 : สีผง</option>
                                            <option value="3">3 : สีเม็ด</option>
                                            <option value="4">4 : Pigment</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">รหัสสินค้า</label>
                                        <input type="text" name="CodeNo" class="form-control"
                                            placeholder="เช่น CP1Y583PVC">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">
                                            สีผง <small class="text-muted">(จะ map เป็น color เดียวกับ CM)</small>
                                        </label>
                                        <input type="text" name="powder_color" class="form-control"
                                            placeholder="ชื่อสีผง เช่น DB PINK-Y">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">Resin (Match)</label>
                                        <input type="text" name="ResinMatch" class="form-control"
                                            placeholder="เช่น PVC, ABS, PE">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">PHR</label>
                                        <input type="number" step="0.0001" name="PHR" class="form-control text-end"
                                            placeholder="0.0000">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">Lot No.</label>
                                        <input type="text" name="lotno" class="form-control bg-dark text-white"
                                            placeholder="เช่น 690112-1-2/01">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">น้ำหนัก (กรัม)</label>
                                        <input type="number" name="Wage" class="form-control"
                                            placeholder="เช่น 100">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">Standard</label>
                                        <input type="text" name="STD" class="form-control"
                                            placeholder="เช่น PT 494 C">
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
                                        <label class="form-label small mb-1">Saleman Code</label>
                                        <input type="text" name="sale" class="form-control" maxlength="2"
                                            placeholder="เช่น A1">
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
                                        <input type="text" name="CancalRes" class="form-control bg-label-secondary"
                                            placeholder="ระบุสาเหตุที่ยกเลิก">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small mb-1">
                                            <i class="ti ti-note me-1"></i>
                                            หมายเหตุ
                                        </label>
                                        <textarea name="remark" class="form-control" rows="2"
                                            placeholder="หมายเหตุเพิ่มเติม"></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: เอกสารปิดงาน (ล่างสุด) ─── --}}
                            <div class="p-3 rounded" style="background-color: #fffaf0; border: 1px dashed #ffc107;">
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
                                            เลขที่ใบส่ง ต.ย. ให้ลูกค้า
                                        </label>
                                        <input type="text" name="Testno" class="form-control" required
                                            placeholder="เช่น 53495-CP">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">วันที่เบิก</label>
                                        <input type="text" name="TestDate" class="form-control flatpickr-date"
                                            placeholder="วว/ดด/ปปปป">
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
