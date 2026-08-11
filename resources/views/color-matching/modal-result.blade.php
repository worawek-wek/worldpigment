{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- Modal: ผลการทดสอบตัวอย่างสี (Test Result) — 10/08/2569              --}}
{{-- ใช้กับใบส่ง ต.ย. (SD) แต่ละใบ                                       --}}
{{-- Trigger: viewTestResult(id) จากปุ่มในตาราง                          --}}
{{--                                                                     --}}
{{-- แปลงมาจากฟอร์ม "ผลการทดสอบตัวอย่างสี" ของโปรแกรม Access เดิม        --}}
{{-- ข้อมูลลงคอลัมน์ testmain: TyResp (ตัวเลือก) / Resp (ระบุ) /         --}}
{{-- Respdate (วันที่ทราบผล)                                             --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
@php
    $trOptions = config('color_matching.test_result_options', []);
    // เหตุผลที่ไม่สั่งซื้อ (A–H) — แยกออกมาแสดงในกรอบสีชมพูเหมือนฟอร์ม Access
    $trReject  = array_filter($trOptions, fn ($o) => $o['group'] === 'reject');
@endphp

<div class="modal modalHeadDecor fade" id="testResultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-clipboard-check me-1"></i>
                    ผลการทดสอบตัวอย่างสี
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="form_test_result">
                @csrf
                {{-- id ของแถว testmain (ใบส่ง ต.ย.) ที่กำลังบันทึกผล --}}
                <input type="hidden" name="_pk" value="">

                <!-- Body -->
                <div class="modal-body px-4 py-4" style="background-color: #f8f9fb;">

                    {{-- ─── ข้อมูลใบส่ง ต.ย. (อ่านอย่างเดียว — แก้ที่ฟอร์มใบส่ง ต.ย.) ─── --}}
                    <div class="card shadow-sm mb-3" style="border: 1px solid #dcd8f5;">
                        <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between"
                            style="background-color: #efedfd; border-bottom: 2px solid #6c5ce7; border-radius: 0.375rem 0.375rem 0 0;">
                            <h6 class="mb-0 fw-semibold" style="color: #3a309d;">
                                <i class="ti ti-package me-1"></i>ใบส่ง ต.ย. ให้ลูกค้า
                            </h6>
                            <span class="badge bg-label-secondary fw-normal">อ่านอย่างเดียว</span>
                        </div>

                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="text-muted small mb-1">เลขที่ใบส่ง ต.ย. ให้ลูกค้า</div>
                                    <div class="fw-bold fs-5" style="color:#4b3fb8;" id="tr_Testno">—</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small mb-1">อ้างอิงใบเทียบสี</div>
                                    <div class="fw-medium" id="tr_SendNo">—</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small mb-1">วันที่เบิก</div>
                                    <div class="fw-medium" id="tr_TestDate">—</div>
                                </div>

                                <div class="col-12"><hr class="my-1"></div>

                                <div class="col-md-2">
                                    <div class="text-muted small mb-1">รหัสลูกค้า</div>
                                    <div class="fw-medium" id="tr_custno">—</div>
                                </div>
                                <div class="col-md-7">
                                    <div class="text-muted small mb-1">ชื่อบริษัท</div>
                                    <div class="fw-medium" id="tr_custname">—</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small mb-1">Sale</div>
                                    <div class="fw-medium" id="tr_sale">—</div>
                                </div>

                                <div class="col-md-9">
                                    <div class="text-muted small mb-1">รายละเอียด</div>
                                    <div class="fw-medium" id="tr_TestDesc">—</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small mb-1">น้ำหนัก (กรัม)</div>
                                    <div class="fw-medium text-danger" id="tr_Wage">—</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ─── ผลการทดสอบ ─── --}}
                    <div class="card shadow-sm mb-0 tr-card" style="border: 1px solid #f5c2d6;">
                        <div class="card-header py-2 px-3 d-flex align-items-center"
                            style="background-color: #fdeaf1; border-bottom: 2px solid #d6336c; border-radius: 0.375rem 0.375rem 0 0;">
                            <h6 class="mb-0 fw-semibold" style="color: #a01b4c;">
                                <i class="ti ti-checkup-list me-1"></i>ผลการทดสอบ
                            </h6>
                        </div>

                        <div class="card-body p-3">

                            {{-- แถวบน: ยังไม่ตอบ / สั่งซื้อแล้ว + วันที่ทราบผล --}}
                            <div class="row g-3 align-items-end mb-3 pb-3 border-bottom">
                                <div class="col-md-8">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($trOptions as $code => $opt)
                                            @continue($opt['group'] === 'reject')
                                            <label class="tr-choice tr-choice-{{ $opt['group'] }} flex-fill">
                                                <input class="form-check-input me-2" type="radio"
                                                    name="TyResp" value="{{ $code }}">
                                                <span class="fw-semibold">{{ $opt['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1 fw-semibold">
                                        <i class="ti ti-calendar-event me-1"></i>วันที่ทราบผล
                                    </label>
                                    <input type="text" name="Respdate" class="form-control flatpickr-date"
                                        placeholder="วว/ดด/ปปปป">
                                </div>
                            </div>

                            {{-- กรอบ "ไม่สั่งซื้อ เพราะ" (A–H) — สีชมพูตามฟอร์ม Access เดิม --}}
                            <div class="p-3 rounded mb-3" style="border: 2px solid #e83e8c; background-color: #fff7fa;">
                                <div class="fw-semibold mb-2" style="color: #d6336c;">
                                    <i class="ti ti-circle-x me-1"></i>ไม่สั่งซื้อ เพราะ
                                </div>

                                <div class="row g-2">
                                    @foreach ($trReject as $code => $opt)
                                        <div class="col-md-6">
                                            <label class="tr-choice tr-choice-reject w-100">
                                                <input class="form-check-input me-2" type="radio"
                                                    name="TyResp" value="{{ $code }}">
                                                <span>
                                                    <span class="fw-bold me-1">{{ $code }}.</span>{{ $opt['label'] }}
                                                    @if ($opt['specify'])
                                                        <span class="text-muted small">ระบุ .........</span>
                                                    @endif
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- ช่อง "ระบุ" — คอลัมน์เดียวกัน (Resp) ใช้ร่วมทุกตัวเลือก ตามโครงสร้าง DB เดิม --}}
                            <div>
                                <label class="form-label small mb-1 fw-semibold">
                                    <i class="ti ti-pencil me-1"></i>ระบุเพิ่มเติม
                                    <span class="text-muted fw-normal">(สูงสุด 30 ตัวอักษร)</span>
                                </label>
                                <input type="text" name="Resp" class="form-control" maxlength="30">
                                <div class="form-text" id="tr_resp_hint">
                                    เลือกผลการทดสอบด้านบนก่อน — บางตัวเลือกต้องระบุรายละเอียดเพิ่ม
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer justify-content-end flex-wrap gap-2">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        ปิด
                    </button>
                    <button type="submit" class="btn px-5 text-white" style="background-color:#d6336c;">
                        <i class="ti ti-device-floppy me-1"></i>
                        ตกลง
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<style>
    /* ─── หัว modal ผลการทดสอบ ใช้โทนชมพู (ต่างจาก CM เขียว / SD ม่วง) ─── */
    #testResultModal .modal-title { background-color: #d6336c; }
    #testResultModal .modal-title::after { border-top-color: #d6336c; }

    /* ─── ทำให้ body เลื่อนได้จริง ───────────────────────────────────
       Bootstrap ของ .modal-dialog-scrollable คาดว่า .modal-body เป็นลูก
       โดยตรงของ .modal-content (ซึ่งเป็น flex column) แต่ฟอร์มนี้มี <form>
       คั่นกลาง → ความสูงไม่ถูกจำกัด, overflow-y:auto ของ body เลยไม่ทำงาน
       และโดน overflow:hidden ของ .modal-content ตัดท้ายทิ้ง (เลื่อนลงไม่ได้)
       แก้โดยให้ <form> เป็น flex column ที่รับความสูงต่อจาก .modal-content */
    #testResultModal .modal-content > form {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;        /* ขาดบรรทัดนี้ flex item จะไม่ยอมหดต่ำกว่าเนื้อหา */
        overflow: hidden;
    }
    #testResultModal .modal-body {
        overflow-y: auto;
        min-height: 0;
    }
    /* หัว + ท้าย ตรึงอยู่กับที่ ไม่เลื่อนตามเนื้อหา */
    #testResultModal .modal-header,
    #testResultModal .modal-footer {
        flex-shrink: 0;
    }

    /* ─── ตัวเลือกผลการทดสอบ: ทั้งกล่องคลิกได้ + ไฮไลต์ตัวที่เลือก ─── */
    .tr-choice {
        display: flex;
        align-items: center;
        padding: .5rem .75rem;
        border: 1px solid #d9dee3;
        border-radius: .375rem;
        background-color: #fff;
        cursor: pointer;
        transition: background-color .15s, border-color .15s;
        margin-bottom: 0;
    }
    .tr-choice:hover { background-color: #f6f6f9; }
    .tr-choice .form-check-input { margin-top: 0; flex-shrink: 0; }

    /* ยังไม่ตอบ = เทา, สั่งซื้อแล้ว = เขียว, ไม่สั่งซื้อ = ชมพู */
    .tr-choice-pending:has(input:checked) { border-color: #6e6e78; background-color: #eceef1; }
    .tr-choice-ordered:has(input:checked) { border-color: #28a745; background-color: #e6f7ea; }
    .tr-choice-reject:has(input:checked)  { border-color: #e83e8c; background-color: #fde8f1; }
</style>
