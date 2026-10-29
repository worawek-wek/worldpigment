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
                <div class="modal-body px-5 py-3" style="background-color: #f8f9fb;">

                    <div class="card shadow-sm mb-0"
                        style="border: 1px solid #dcd8f5; border-bottom-width: 0;">

                        <div class="card-header py-2 px-3 d-flex align-items-center"
                            style="background-color: #dedaff; border-bottom: 2px solid #6c5ce7; border-radius: 0.375rem 0.375rem 0 0;">
                            <h6 class="mb-0 fw-semibold" style="color: #3a309d;">
                                <i class="ti ti-package me-1"></i>
                                ใบส่ง ต.ย. ให้ลูกค้า
                            </h6>
                        </div>

                        <div class="card-body py-2 px-3">

                            {{-- ─── อ้างอิงใบนำส่งเทียบสี (CM) — 1 CM มีได้หลายใบส่ง ต.ย. ─── --}}
                            {{-- แถวช่องเดียว → label ซ้าย ช่องกรอกขวา (โครงกลาง .of-form, 07/10/2569) --}}
                            <div class="row gx-3 gy-2 mb-2 pb-2 border-bottom of-form" style="--of-lw: 225px;">
                                <div class="col-md-6 of-row">
                                    <label class="form-label small fw-semibold" style="color:#4b3fb8;">
                                        {{-- text-nowrap: ไม่ให้ "(อ้างอิง)" ตกบรรทัด — --of-lw ต้องกว้างพอสำหรับข้อความนี้ --}}
                                        <span class="text-nowrap"><i class="ti ti-link me-1"></i>เลขที่ใบนำส่งเทียบสี (อ้างอิง)</span> <br> <span class="text-muted fw-normal">(เช่น 26/0001)</span>
                                    </label>
                                    <div class="of-ctl">
                                        <input type="text" name="SendNo" class="form-control">
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: ข้อมูลลูกค้า ─── --}}
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="d-flex align-items-center mb-2 ps-2"
                                    style="border-left: 3px solid #6c5ce7;">
                                    <i class="ti ti-user-circle me-2" style="color: #4b3fb8;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #4b3fb8;">
                                        ข้อมูลลูกค้า
                                    </span>
                                </div>

                                <div class="row gx-3 gy-2 align-items-end">
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">รหัสลูกค้า <span class="text-muted fw-normal">(เช่น 00004)</span></label>
                                        <input type="text" name="custno" class="form-control">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">Sale No. <span class="text-muted fw-normal"><br>(เช่น NEW15)</span></label>
                                        <input type="text" name="sale" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">
                                            <span class="badge bg-label-secondary me-1">TH</span>
                                            ชื่อบริษัท (ไทย)
                                        </label>
                                        <input type="text" name="custname" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">
                                            <span class="badge bg-label-secondary me-1">EN</span>
                                            ชื่อบริษัท (อังกฤษ)
                                        </label>
                                        <input type="text" name="custnameEN" class="form-control">
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: เอกสารปิดงาน (อยู่ใต้ข้อมูลลูกค้า) ─── --}}
                            <div class="py-2 px-3 rounded mb-2" style="background-color: #fffaf0; border: 1px dashed #ffc107;">
                                <div class="d-flex align-items-center mb-2 ps-2"
                                    style="border-left: 3px solid #f0a500;">
                                    <i class="ti ti-receipt me-2" style="color: #8a6100;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #8a6100;">
                                        เอกสารปิดงาน
                                    </span>
                                </div>

                                <div class="row gx-3 gy-2 align-items-end">
                                    {{-- สั้นลงครึ่งหนึ่ง col-md-6 → 3 (16/10/2569 ตามที่ผู้ใช้สั่ง) --}}
                                    <div class="col-md-3">
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

                            {{-- ─── กลุ่ม: รายการตัวอย่าง — เพิ่มได้หลายแถว 1 แถว = ใบส่ง ต.ย. 1 ใบ (29/10/2569 ตามที่ผู้ใช้สั่ง) ───
                                 รวม 4 ช่อง รายละเอียด / Resin (Match) / PHR / Lot No. (เดิมอยู่คนละกลุ่ม) ไว้ในตารางเดียว
                                 · แถวแรก (.sd-item-first) ใช้ name เดิม → fillForm / ดึงจากใบอ้างอิง / แก้ไข / คัดลอก ทำงานเหมือนเดิม
                                 · แถวที่เพิ่ม (#sd_item_tpl) ไม่มี name — ตอนสร้างใหม่ JS รวบทุกแถวส่งเป็น sd_items (JSON)
                                 · โหมดแก้ไข = แก้ 1 ใบ จึงซ่อนปุ่มเพิ่ม/ลบแถว (#sd_items_add / .sd-item-del) --}}
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="d-flex align-items-center mb-2 ps-2"
                                    style="border-left: 3px solid #6c5ce7;">
                                    <i class="ti ti-list-details me-2" style="color: #4b3fb8;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #4b3fb8;">
                                        รายการตัวอย่าง
                                    </span>
                                    <span class="text-muted small ms-2 sd-items-hint">(1 แถว = ใบส่ง ต.ย. 1 ใบ — ข้อมูลช่องอื่นเหมือนกันทุกใบ)</span>
                                    <button type="button" id="sd_items_add" class="btn btn-sm btn-label-primary ms-auto">
                                        <i class="ti ti-plus me-1"></i> เพิ่มแถว
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-0" id="sd_items_table">
                                        <thead class="table-light">
                                            <tr class="small">
                                                <th class="text-center" style="width:40px;">#</th>
                                                <th>รายละเอียด <span class="text-muted fw-normal">(รายละเอียดผลิตภัณฑ์)</span></th>
                                                <th style="width:22%;">Resin (Match) <span class="text-muted fw-normal">(เช่น PVC, ABS, PE)</span></th>
                                                <th style="width:12%;">PHR <span class="text-muted fw-normal">(0.0000)</span></th>
                                                <th style="width:20%;">Lot No. <span class="text-muted fw-normal">(เช่น 690112-1-2/01)</span></th>
                                                <th class="text-center sd-item-del-col" style="width:48px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="sd_items_body">
                                            <tr class="sd-item sd-item-first">
                                                <td class="text-center sd-item-no">1</td>
                                                <td><input type="text" name="TestDesc" data-f="TestDesc" maxlength="60" class="form-control form-control-sm"></td>
                                                <td><input type="text" name="ResinMatch" data-f="ResinMatch" maxlength="30" class="form-control form-control-sm"></td>
                                                <td><input type="number" step="0.0001" name="PHR" data-f="PHR" class="form-control form-control-sm text-end"></td>
                                                <td><input type="text" name="lotno" data-f="lotno" maxlength="20" class="form-control form-control-sm"><div class="invalid-feedback sd-lot-msg"></div></td>
                                                <td class="text-center sd-item-del-col">
                                                    <button type="button" class="btn btn-sm btn-icon btn-label-danger sd-item-del" title="ลบแถว">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                {{-- แม่แบบแถวที่เพิ่ม — ไม่มี name (ไม่ถูกส่งตรง ๆ) --}}
                                <template id="sd_item_tpl">
                                    <tr class="sd-item">
                                        <td class="text-center sd-item-no"></td>
                                        <td><input type="text" data-f="TestDesc" maxlength="60" class="form-control form-control-sm"></td>
                                        <td><input type="text" data-f="ResinMatch" maxlength="30" class="form-control form-control-sm"></td>
                                        <td><input type="number" step="0.0001" data-f="PHR" class="form-control form-control-sm text-end"></td>
                                        <td><input type="text" data-f="lotno" maxlength="20" class="form-control form-control-sm"><div class="invalid-feedback sd-lot-msg"></div></td>
                                        <td class="text-center sd-item-del-col">
                                            <button type="button" class="btn btn-sm btn-icon btn-label-danger sd-item-del" title="ลบแถว">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </div>

                            {{-- ─── กลุ่ม: รายละเอียดผลิตภัณฑ์ (ย้ายจากฟอร์ม CM) ─── --}}
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="d-flex align-items-center mb-2 ps-2"
                                    style="border-left: 3px solid #6c5ce7;">
                                    <i class="ti ti-flask me-2" style="color: #4b3fb8;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #4b3fb8;">
                                        รายละเอียดผลิตภัณฑ์
                                    </span>
                                </div>

                                {{-- ความกว้างแต่ละช่องปรับตามเนื้อหา (ไม่ยืดเต็มคอลัมน์) — ใช้ w-100 คั่นเพื่อขึ้นบรรทัดใหม่ --}}
                                {{-- align-items-end: label ยาวไม่เท่ากันในแถว → ช่องกรอกชิดล่างเรียงตรงกัน --}}
                                {{-- of-form: ใช้กับแถว 2 และ 3 (label ซ้าย ช่องกรอกขวา) — แถว 1 ไม่มี of-row จึงยังเป็น label อยู่บน --}}
                                <div class="row gx-3 gy-2 align-items-end of-form" style="--of-lw: 150px;">
                                    {{-- แถว 1: น้ำหนัก + Standard — Resin (Match) / PHR ย้ายไปตาราง "รายการตัวอย่าง" (29/10/2569) --}}
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">น้ำหนัก (กรัม) <span class="text-muted fw-normal"><br>(เช่น 100)</span></label>
                                        {{-- จำนวนเต็ม (กรัม) — คอมมาอัตโนมัติ, ถอดคอมมาก่อน submit --}}
                                        <input type="text" name="Wage" class="form-control text-end js-comma"
                                            data-decimals="0" inputmode="numeric" autocomplete="off">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Standard <span class="text-muted fw-normal">(เช่น PT 494 C)</span></label>
                                        {{-- แก้ไขได้แล้ว (16/10/2569 ตามที่ผู้ใช้สั่ง) — เดิม readonly + พื้นเทา
                                             ยังเติมค่าตั้งต้นจากใบนำส่งเทียบสีเมื่อกรอกเลขที่อ้างอิง (fillSdColorInfo) --}}
                                        <input type="text" name="STD" class="form-control">
                                    </div>
                                    <div class="w-100"></div>

                                    {{-- แถว 2: เลขที่ใบรายงานผล — label ซ้าย ช่องกรอกขวา (07/10/2569) · Lot No. ย้ายไปตาราง "รายการตัวอย่าง" (29/10/2569) --}}
                                    <div class="col-md-5 of-row">
                                        <label class="form-label small">เลขที่ใบรายงานผล <span class="text-muted fw-normal">(เช่น 26/0001/2/4)</span></label>
                                        <div class="of-ctl">
                                            <input type="text" name="rptno" class="form-control">
                                        </div>
                                    </div>
                                    <div class="w-100"></div>

                                    {{-- แถว 3: หมายเหตุ (ย้ายมาจากกลุ่มการยกเลิก/Sales) — label ซ้าย ช่องกรอกขวา (07/10/2569) --}}
                                    <div class="col-12 of-row">
                                        <label class="form-label small">
                                            <i class="ti ti-note me-1"></i>
                                            หมายเหตุ <span class="text-muted fw-normal">(หมายเหตุเพิ่มเติม)</span>
                                        </label>
                                        <div class="of-ctl">
                                            <textarea name="remark" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="w-100"></div>

                                    {{-- แถว 4: ข้อมูลที่ลูกค้าอนุมัติ (เน้นในกล่องพื้นฟ้าอ่อน) --}}
                                    <div class="col-12">
                                        <div class="py-2 px-3 rounded"
                                            style="background-color: #e7f3fe; border: 1px solid #b6d9f5;">
                                            <div class="d-flex align-items-center mb-2 ps-1"
                                                style="border-left: 3px solid #2f8ee0;">
                                                <i class="ti ti-circle-check me-2" style="color: #1c6dbf;"></i>
                                                <span class="fw-semibold small" style="color: #1c6dbf;">ข้อมูลที่ลูกค้าอนุมัติ</span>
                                            </div>
                                            <div class="row gx-3 gy-2 align-items-end">
                                                <div class="col-md-6">
                                                    <label class="form-label small mb-1">ลูกค้าอนุมัติเบอร์ตั้ง <span class="text-muted fw-normal">(เช่น CP1Y583PVC)</span></label>
                                                    <input type="text" name="CodeNo" class="form-control border-primary-subtle">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small mb-1">Lot ที่ลูกค้าอนุมัติ <span class="text-muted fw-normal">(เช่น 690112-1-2/01)</span></label>
                                                    <input type="text" name="ApprovedLot" class="form-control border-primary-subtle">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: การยกเลิก / Sales ─── --}}
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="d-flex align-items-center mb-2 ps-2"
                                    style="border-left: 3px solid #6c5ce7;">
                                    <i class="ti ti-alert-circle me-2" style="color: #4b3fb8;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #4b3fb8;">
                                        ข้อมูลการขาย / การยกเลิก
                                    </span>
                                </div>

                                <div class="row gx-3 gy-2 align-items-end">
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
                <div class="modal-footer justify-content-between flex-wrap gap-2">
                    <div class="d-flex gap-2 flex-wrap">
                        {{-- ลบใบส่ง ต.ย. — แสดงเฉพาะโหมดแก้ไข (JS ถอด d-none ให้ตอนเปิดจากปุ่มแก้ไข) --}}
                        {{-- ปิดไว้ 28/10/2569 ตามที่ผู้ใช้สั่ง "เอาปุ่มลบออกให้หมด" — เปิดคืน: ถอด @if(false) / @endif --}}
                        @if (false)
                        <button type="button" id="btn_delete_sd" class="btn btn-label-danger d-none">
                            <i class="ti ti-trash me-1"></i>
                            ลบใบส่ง ต.ย.
                        </button>
                        @endif
                    </div>
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
