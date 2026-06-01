{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- ส่วนที่ 1: ใบนำส่งเทียบสี                                       --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="card shadow-sm mb-4"
    style="border: 1px solid #cfe4e3; border-bottom-width: 0;">

    <div class="card-header py-2 px-3 d-flex align-items-center"
        style="background-color: #e8f6f6; border-bottom: 2px solid #54BAB9; border-radius: 0.375rem 0.375rem 0 0;">
        <span class="badge me-2 fw-bold"
            style="background-color: #54BAB9; color: #fff;">1</span>
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
                    <input type="text"
                        name="cm_customer_code"
                        class="form-control"
                        value="00221">
                </div>

                <div class="col-md-5">
                    <label class="form-label small mb-1">
                        <span class="badge bg-label-secondary me-1">TH</span>
                        ชื่อบริษัท (ไทย)
                    </label>
                    <input type="text"
                        name="cm_customer_name_th"
                        class="form-control"
                        value="บริษัท เมทเทิล พลาสติก จำกัด">
                </div>

                <div class="col-md-5">
                    <label class="form-label small mb-1">
                        <span class="badge bg-label-secondary me-1">EN</span>
                        ชื่อบริษัท (อังกฤษ)
                    </label>
                    <input type="text"
                        name="cm_customer_name_en"
                        class="form-control"
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
                    <input type="text"
                        name="cm_doc_no"
                        class="form-control"
                        value="68/0255">
                </div>

                <div class="col-md-3">
                    <label class="form-label small mb-1">วันที่ส่งเทียบสี</label>
                    <input type="date"
                        name="cm_doc_date"
                        class="form-control"
                        value="{{ date('Y-m-d') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label small mb-1">ประเภทงาน</label>
                    <select name="cm_job_type" class="form-select">
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
                    <select name="cm_revision" class="form-select">
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
                    <select name="cm_color" class="form-select">
                        <option>DB PINK-Y AS50%+ABS50%</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small mb-1">คุณสมบัติ</label>
                    <select name="cm_property" class="form-select">
                        <option></option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small mb-1">นำไปทำชิ้นงาน</label>
                    <select name="cm_application" class="form-select">
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
                    <select name="cm_receiver" class="form-select">
                        <option>วารุณี</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small mb-1">เลขที่ใบรายงานผล</label>
                    <input type="text"
                        name="cm_report_no"
                        class="form-control bg-label-secondary"
                        value="68/0255">
                </div>

                <div class="col-md-3">
                    <label class="form-label small mb-1">รอวัตถุดิบ</label>
                    <input type="text"
                        name="cm_waiting_material"
                        class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label small mb-1">กำหนดเทียบสีเสร็จ</label>
                    <input type="date"
                        name="cm_due_date"
                        class="form-control">
                </div>

                <div class="col-12">
                    <label class="form-label small mb-1">
                        <i class="ti ti-note me-1"></i>
                        หมายเหตุ
                    </label>
                    <input type="text"
                        name="cm_remark"
                        class="form-control">
                </div>

            </div>
        </div>

    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- ส่วนที่ 2: ใบส่ง ต.ย. ให้ลูกค้า                                 --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="card shadow-sm mb-3"
    style="border: 1px solid #dcd8f5; border-bottom-width: 0;">

    <div class="card-header py-2 px-3 d-flex align-items-center"
        style="background-color: #efedfd; border-bottom: 2px solid #6c5ce7; border-radius: 0.375rem 0.375rem 0 0;">
        <span class="badge me-2 fw-bold"
            style="background-color: #6c5ce7; color: #fff;">2</span>
        <h6 class="mb-0 fw-semibold" style="color: #3a309d;">
            <i class="ti ti-package me-1"></i>
            ใบส่ง ต.ย. ให้ลูกค้า
        </h6>
    </div>

    <div class="card-body p-4">

        {{-- ─── กลุ่ม: ข้อมูลลูกค้า (Section 2) ─── --}}
        <div class="mb-4 pb-3 border-bottom">
            <div class="d-flex align-items-center mb-3 ps-2"
                style="border-left: 3px solid #6c5ce7;">
                <i class="ti ti-user-circle me-2" style="color: #4b3fb8;"></i>
                <span class="fw-semibold" style="font-size: 0.95rem; color: #4b3fb8;">
                    ข้อมูลลูกค้า
                </span>
                <small class="ms-2 text-muted" style="font-size: 0.75rem;">
                    (สำหรับเอกสารส่งให้ลูกค้า)
                </small>
            </div>

            <div class="row g-3">

                <div class="col-md-2">
                    <label class="form-label small mb-1">รหัสลูกค้า</label>
                    <input type="text"
                        name="sd_customer_code"
                        class="form-control"
                        value="00221">
                </div>

                <div class="col-md-5">
                    <label class="form-label small mb-1">
                        <span class="badge bg-label-secondary me-1">TH</span>
                        ชื่อบริษัท (ไทย)
                    </label>
                    <input type="text"
                        name="sd_customer_name_th"
                        class="form-control"
                        value="บริษัท เมทเทิล พลาสติก จำกัด">
                </div>

                <div class="col-md-5">
                    <label class="form-label small mb-1">
                        <span class="badge bg-label-secondary me-1">EN</span>
                        ชื่อบริษัท (อังกฤษ)
                    </label>
                    <input type="text"
                        name="sd_customer_name_en"
                        class="form-control"
                        value="Metal Plastic Co., Ltd.">
                </div>

            </div>
        </div>

        {{-- ─── กลุ่ม: วันที่ดำเนินการ ─── --}}
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
                    <input type="date" name="sd_start_date" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label small mb-1">Sample Date</label>
                    <input type="date" name="sd_sample_date" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label small mb-1">Ready Date</label>
                    <input type="date" name="sd_ready_date" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label small mb-1">Color Matcher</label>
                    <input type="text"
                        name="sd_color_matcher"
                        class="form-control"
                        value="เมทตา">
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
                    <input type="text"
                        name="sd_detail"
                        class="form-control bg-label-secondary"
                        value="DB PINK-Y AS50%+ABS50% สี/">
                </div>

                <div class="col-md-2">
                    <label class="form-label small mb-1">ประเภท</label>
                    <select name="sd_type" class="form-select">
                        <option>2</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small mb-1">รหัสสินค้า</label>
                    <input type="text"
                        name="sd_product_code"
                        class="form-control bg-label-secondary">
                </div>

                <div class="col-md-5">
                    <label class="form-label small mb-1">สีผง</label>
                    <input type="text" name="sd_powder_color" class="form-control">
                </div>

                <div class="col-md-5">
                    <label class="form-label small mb-1">Resin (Match)</label>
                    <input type="text"
                        name="sd_resin"
                        class="form-control bg-label-secondary"
                        value="AS50%+ABS50%=">
                </div>

                <div class="col-md-2">
                    <label class="form-label small mb-1">PHR</label>
                    <input type="number"
                        name="sd_phr"
                        class="form-control text-end"
                        value="1.0000">
                </div>

                <div class="col-md-4">
                    <label class="form-label small mb-1">Lot No.</label>
                    <input type="text"
                        name="sd_lot_no"
                        class="form-control bg-dark text-white"
                        value="680731/55">
                </div>

                <div class="col-md-4">
                    <label class="form-label small mb-1">น้ำหนัก (กรัม)</label>
                    <input type="number"
                        name="sd_weight"
                        class="form-control"
                        value="100">
                </div>

                <div class="col-md-4">
                    <label class="form-label small mb-1">ตัวอย่างลูกค้า</label>
                    <select name="sd_customer_sample" class="form-select">
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
                    <input type="text"
                        name="sd_saleman_code"
                        class="form-control"
                        value="1">
                </div>

                <div class="col-md-4">
                    <div class="form-check p-2 rounded border border-danger-subtle bg-danger-subtle">
                        <input class="form-check-input" type="checkbox" name="sd_cancel">
                        <label class="form-check-label text-danger fw-semibold ms-1">
                            cancel / วัตถุดิบแก้ไข Lot
                        </label>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label small mb-1">สาเหตุที่ยกเลิก</label>
                    <input type="text"
                        name="sd_cancel_reason"
                        class="form-control bg-label-secondary">
                </div>

                <div class="col-12">
                    <label class="form-label small mb-1">
                        <i class="ti ti-note me-1"></i>
                        หมายเหตุ
                    </label>
                    <textarea name="sd_remark" class="form-control" rows="2"></textarea>
                </div>

            </div>
        </div>

        {{-- ─── กลุ่ม: เอกสารปิดงาน (ล่างสุด) ─── --}}
        <div class="p-3 rounded"
            style="background-color: #fffaf0; border: 1px dashed #ffc107;">
            <div class="d-flex align-items-center mb-3 ps-2"
                style="border-left: 3px solid #f0a500;">
                <i class="ti ti-receipt me-2" style="color: #8a6100;"></i>
                <span class="fw-semibold" style="font-size: 0.95rem; color: #8a6100;">
                    เอกสารปิดงาน
                </span>
            </div>

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label small mb-1 text-danger">
                        <i class="ti ti-asterisk-simple"></i>
                        เลขที่ใบส่ง ต.ย. ให้ลูกค้า
                    </label>
                    <input type="text"
                        name="sd_doc_no"
                        class="form-control"
                        value="52871-DB">
                </div>

                <div class="col-md-6">
                    <label class="form-label small mb-1">วันที่เบิก</label>
                    <input type="date" name="sd_withdraw_date" class="form-control">
                </div>

            </div>
        </div>

    </div>
</div>
