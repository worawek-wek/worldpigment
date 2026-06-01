{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- Modal: ใบนำส่งเทียบสี (Color Matching Delivery Doc)                 --}}
{{-- Trigger: data-bs-target="#colorMatchingModal"                       --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal modalHeadDecor fade" id="colorMatchingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-file-text me-1"></i>
                    ใบนำส่งเทียบสี (Color Matching)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="form_color_matching" enctype="multipart/form-data">
                @csrf

                <!-- Body -->
                <div class="modal-body px-5 py-4" style="background-color: #f8f9fb;">

                    <div class="card shadow-sm mb-3"
                        style="border: 1px solid #cfe4e3; border-bottom-width: 0;">

                        <div class="card-header py-2 px-3 d-flex align-items-center"
                            style="background-color: #e8f6f6; border-bottom: 2px solid #54BAB9; border-radius: 0.375rem 0.375rem 0 0;">
                            <h6 class="mb-0 fw-semibold" style="color: #1f5d5c;">
                                <i class="ti ti-file-text me-1"></i>
                                ใบนำส่งเทียบสี
                            </h6>
                        </div>

                        <div class="card-body p-4">

                            {{-- ─── กลุ่ม: ข้อมูลลูกค้า ─── --}}
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #54BAB9;">
                                    <i class="ti ti-user-circle me-2" style="color: #2a8a89;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #2a8a89;">
                                        ข้อมูลลูกค้า
                                    </span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">รหัสลูกค้า</label>
                                        <input type="text" name="customer_code" class="form-control" value="00221">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">
                                            <span class="badge bg-label-secondary me-1">TH</span>
                                            ชื่อบริษัท (ไทย)
                                        </label>
                                        <input type="text" name="customer_name_th" class="form-control"
                                            value="บริษัท เมทเทิล พลาสติก จำกัด">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">
                                            <span class="badge bg-label-secondary me-1">EN</span>
                                            ชื่อบริษัท (อังกฤษ)
                                        </label>
                                        <input type="text" name="customer_name_en" class="form-control"
                                            value="Metal Plastic Co., Ltd.">
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: ข้อมูลใบนำส่ง ─── --}}
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #54BAB9;">
                                    <i class="ti ti-file-invoice me-2" style="color: #2a8a89;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #2a8a89;">
                                        ข้อมูลใบนำส่ง
                                    </span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">เลขที่ใบนำส่งเทียบสี</label>
                                        <input type="text" name="doc_no" class="form-control" value="68/0255">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">วันที่ส่งเทียบสี</label>
                                        <input type="date" name="doc_date" class="form-control" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">ประเภทงาน</label>
                                        <select name="job_type" class="form-select">
                                            <option>เป่าฟิมล์</option>
                                            <option>เป่าขวด</option>
                                            <option>EXT</option>
                                            <option>ROLL</option>
                                            <option selected>INJ</option>
                                            <option>CY</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1 text-danger">
                                            <i class="ti ti-asterisk-simple"></i>
                                            ปรับแก้ไขครั้งที่
                                        </label>
                                        <select name="revision" class="form-select">
                                            <option>New</option>
                                            <option>Revise 1</option>
                                            <option>Revise 2</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: ข้อมูลสี ─── --}}
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #54BAB9;">
                                    <i class="ti ti-palette me-2" style="color: #2a8a89;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #2a8a89;">
                                        ข้อมูลสี
                                    </span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">สี</label>
                                        <select name="color" class="form-select">
                                            <option>DB PINK-Y AS50%+ABS50%</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">คุณสมบัติ</label>
                                        <select name="property" class="form-select">
                                            <option></option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">นำไปทำชิ้นงาน</label>
                                        <select name="application" class="form-select">
                                            <option>ตลับแป้ง</option>
                                            <option>สายไฟ</option>
                                            <option>สายรัด</option>
                                            <option>หลอดโฟม</option>
                                            <option>หนังเทียม</option>
                                            <option>อะไหล่รถยนต์</option>
                                            <option>แฮนด์รถจักรยาน</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: การติดตามงาน ─── --}}
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #54BAB9;">
                                    <i class="ti ti-clipboard-check me-2" style="color: #2a8a89;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #2a8a89;">
                                        การติดตามงาน
                                    </span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">ผู้รับเอกสาร</label>
                                        <select name="receiver" class="form-select">
                                            <option>วารุณี</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">เลขที่ใบรายงานผล</label>
                                        <input type="text" name="report_no" class="form-control bg-label-secondary" value="68/0255">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">รอวัตถุดิบ</label>
                                        <input type="text" name="waiting_material" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">กำหนดเทียบสีเสร็จ</label>
                                        <input type="date" name="due_date" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small mb-1">
                                            <i class="ti ti-note me-1"></i>
                                            หมายเหตุ
                                        </label>
                                        <input type="text" name="remark" class="form-control">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer justify-content-between flex-wrap gap-2">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-label-primary">
                            <i class="ti ti-search me-1"></i>
                            ค้นหารหัสสี
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                            ปิด
                        </button>
                        <button type="submit" class="btn btn-success px-5">
                            <i class="ti ti-device-floppy me-1"></i>
                            บันทึกใบนำส่งเทียบสี
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>
</div>
