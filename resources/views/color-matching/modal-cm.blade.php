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
                {{-- _mode = 'create' หรือ 'edit' (set by JS เมื่อเปิด modal) --}}
                <input type="hidden" name="_mode" value="create">
                {{-- _pk = SendNo เดิม ตอน edit --}}
                <input type="hidden" name="_pk" value="">

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
                                        <label class="form-label small mb-1">เลขที่ใบนำส่งเทียบสี <span class="text-muted fw-normal">(เช่น 26/0001)</span></label>
                                        <input type="text" name="SendNo" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">วันที่ส่งเทียบสี</label>
                                        <input type="text" name="DsendT"
                                            class="form-control flatpickr-date"
                                            value="{{ date('d/m/Y') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">ประเภทงาน</label>
                                        <select name="Type_Work" class="form-select">
                                            <option value="">-- เลือก --</option>
                                            @foreach ($options['Type_Work'] as $opt)
                                                <option>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1 text-danger">
                                            <i class="ti ti-asterisk-simple"></i>
                                            ปรับแก้ไขครั้งที่
                                        </label>
                                        <select name="Adj" class="form-select">
                                            @foreach ($options['Adj'] as $opt)
                                                <option>{{ $opt }}</option>
                                            @endforeach
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
                                        <label class="form-label small mb-1">สี <span class="text-muted fw-normal">(ชื่อสี เช่น DB PINK-Y AS50%+ABS50%)</span></label>
                                        <input type="text" name="color" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">คุณสมบัติ</label>
                                        <select name="pop" class="form-select select2-tags">
                                            <option value="">-- เลือก --</option>
                                            @foreach ($options['pop'] as $opt)
                                                <option>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">นำไปทำชิ้นงาน (Model)</label>
                                        <select name="Model" class="form-select">
                                            <option value="">-- เลือก --</option>
                                            @foreach ($options['Model'] as $opt)
                                                <option>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: วันที่ดำเนินการ และผู้เทียบสี ─── --}}
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #54BAB9;">
                                    <i class="ti ti-calendar-event me-2" style="color: #2a8a89;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #2a8a89;">
                                        วันที่ดำเนินการ และผู้เทียบสี
                                    </span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Start Date <span class="text-muted fw-normal">(วว/ดด/ปปปป)</span></label>
                                        <input type="text" name="startdate" class="form-control flatpickr-date">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Sample Date <span class="text-muted fw-normal">(วว/ดด/ปปปป)</span></label>
                                        <input type="text" name="SampleDate" class="form-control flatpickr-date">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Ready Date <span class="text-muted fw-normal">(วว/ดด/ปปปป)</span></label>
                                        <input type="text" name="ReadyDate" class="form-control flatpickr-date">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Color Matcher <span class="text-muted fw-normal">(ชื่อพนักงาน)</span></label>
                                        <input type="text" name="ColorMatcher" class="form-control">
                                    </div>
                                </div>
                            </div>

                            {{-- ─── กลุ่ม: รายละเอียดผลิตภัณฑ์ ─── --}}
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-3 ps-2"
                                    style="border-left: 3px solid #54BAB9;">
                                    <i class="ti ti-flask me-2" style="color: #2a8a89;"></i>
                                    <span class="fw-semibold" style="font-size: 0.95rem; color: #2a8a89;">
                                        รายละเอียดผลิตภัณฑ์
                                    </span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-7">
                                        <label class="form-label small mb-1">รายละเอียด <span class="text-muted fw-normal">(รายละเอียดผลิตภัณฑ์)</span></label>
                                        <input type="text" name="TestDesc" class="form-control">
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
                                        <label class="form-label small mb-1">Standard <span class="text-muted fw-normal">(เช่น PT 494 C)</span></label>
                                        <input type="text" name="STD" class="form-control">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label small mb-1">Resin (Match) <span class="text-muted fw-normal">(เช่น PVC, ABS, PE)</span></label>
                                        <input type="text" name="ResinMatch" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">PHR <span class="text-muted fw-normal">(0.0000)</span></label>
                                        <input type="number" step="0.0001" name="PHR" class="form-control text-end">
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
                                        <label class="form-label small mb-1">ผู้รับเอกสาร <span class="text-muted fw-normal">(ชื่อพนักงาน)</span></label>
                                        <input type="text" name="TNname" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">เลขที่ใบรายงานผล <span class="text-muted fw-normal">(เช่น 26/0001/2/4)</span></label>
                                        <input type="text" name="rptno" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">รอวัตถุดิบ <span class="text-muted fw-normal">(ระบุวัตถุดิบที่รอ)</span></label>
                                        <input type="text" name="RminWating" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">กำหนดเทียบสีเสร็จ <span class="text-muted fw-normal">(วว/ดด/ปปปป)</span></label>
                                        <input type="text" name="TNDate" class="form-control flatpickr-date">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small mb-1">
                                            <i class="ti ti-note me-1"></i>
                                            หมายเหตุ <span class="text-muted fw-normal">(หมายเหตุเพิ่มเติม)</span>
                                        </label>
                                        <input type="text" name="Mems" class="form-control">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer justify-content-between flex-wrap gap-2">
                    <div class="d-flex gap-2 flex-wrap"></div>
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
