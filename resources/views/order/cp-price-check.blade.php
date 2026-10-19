{{-- ═══ ฟอร์มตรวจราคาเม็ด CP ก่อนบันทึกใบสั่งซื้อ (cp_itemprice) — 16/10/2569 ตามที่ผู้ใช้สั่ง ═══
     ผังตามฟอร์ม Access เดิม · ขึ้นเมื่อกด "บันทึกการรับคำสั่งซื้อ" ของใบ WI / ผลิตที่ CP / ลูกค้าใน config('order.cp_check_customers')
     กด "บันทึกการรับคำสั่งซื้อ" ในฟอร์มนี้ → บันทึกใบสั่งซื้อ + cp_itemprice ในครั้งเดียว (OrderController::save)

     ⚠ ไม่ใช่ Bootstrap modal ตัวที่ 2 โดยตั้งใจ — วางเป็นม่านซ้อน "ภายใน" #orderModal
       (modal ซ้อนกัน 2 ตัว focus trap ของ Bootstrap จะแย่งโฟกัสกัน พิมพ์ไม่ได้ และ event shown/hidden
        ของตัวลูกจะไหลขึ้นไปเรียก handler ของฟอร์มใบสั่งซื้อ) · JS อยู่ที่ cpCheck*() ใน order/index.blade.php
     ใช้ซ้ำในฟอร์มอนุมัติใบสั่งซื้อแบบอ่านอย่างเดียว (cpCheckView, 19/10/2569) — JS ย้ายม่านนี้ไปไว้ใน
       #orderApprovalModal ตอนเปิดดู แล้วย้ายกลับ #orderModal ตอนเปิดเพื่อกรอก (cpCheckSetMode) --}}
<style>
    #cpCheckDialog { position: fixed; inset: 0; z-index: 1100; background: rgba(34, 48, 62, .55);
                     display: flex; align-items: center; justify-content: center; padding: 1rem; overflow-y: auto; }
    #cpCheckDialog .cp-box { width: 100%; max-width: 860px; background: #dfe3f3; border-radius: .5rem;
                             box-shadow: 0 .5rem 2rem rgba(0,0,0,.35); }
    #cpCheckDialog .cp-head { display: flex; align-items: center; justify-content: space-between;
                              padding: .6rem 1rem; background: #fff; border-radius: .5rem .5rem 0 0; }
    #cpCheckDialog .cp-body { padding: 1rem 1.25rem; }
    #cpCheckDialog .cp-auto { background: #ffd8b5 !important; font-weight: 600; }
    #cpCheckDialog .cp-resin { background: #c6f6fb !important; }
    #cpCheckDialog .form-control { text-align: right; }
    #cpCheckDialog .form-control.cp-left { text-align: left; }
    #cpCheckDialog .cp-lbl { font-weight: 600; white-space: nowrap; }
    #cpCheckDialog .cp-from { border: 1px solid #fff; border-radius: .35rem; padding: .5rem .75rem; }
    #cpCheckDialog .cp-result { background: #ffd8b5; border-radius: .35rem; font-size: 1.35rem; font-weight: 700;
                                text-align: center; padding: .6rem; }
    #cpCheckDialog .cp-result.cp-fail { background: #ffc9c9; color: #b42318; }
</style>

<div id="cpCheckDialog" class="d-none" role="dialog" aria-modal="true" aria-labelledby="cpCheckTitle">
    <div class="cp-box">
        <div class="cp-head">
            <h5 class="mb-0" id="cpCheckTitle"><i class="ti ti-calculator me-1"></i><span id="cpCheckTitleText">ตรวจสอบราคาเม็ด CP ก่อนบันทึก</span></h5>
            <button type="button" class="btn-close" onclick="cpCheckClose()" aria-label="Close"></button>
        </div>

        <div class="cp-body">
            {{-- 3 ช่องบน — ดึงจากฟอร์มใบสั่งซื้ออัตโนมัติ --}}
            <div class="row g-2 align-items-center mb-3">
                <div class="col-md-4 d-flex align-items-center gap-2">
                    <span class="cp-lbl">เลขที่ใบสั่ง :</span>
                    <input type="text" id="cp_Orderno" class="form-control form-control-sm cp-auto cp-left" readonly>
                </div>
                <div class="col-md-4 d-flex align-items-center gap-2">
                    <span class="cp-lbl">รหัสสินค้า :</span>
                    <input type="text" id="cp_itemno" class="form-control form-control-sm cp-auto cp-left" readonly>
                </div>
                <div class="col-md-4 d-flex align-items-center gap-2">
                    <span class="cp-lbl">ราคาขายครั้งนี้</span>
                    <input type="text" id="cp_OrderPrice" class="form-control form-control-sm cp-auto" readonly>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-7">
                    <table class="w-100" style="border-collapse: separate; border-spacing: 0 .35rem;">
                        <tr>
                            <td class="cp-lbl">1. ใบเสนอราคาล่าสุดลงวันที่</td>
                            <td style="width: 140px;"><input type="text" id="cp_Qdate" class="form-control form-control-sm" autocomplete="off"></td>
                        </tr>
                        <tr id="cp_qdate_warn" class="d-none">
                            <td colspan="2" class="text-danger fw-semibold">ใบราคาเกิน 90 วัน เช็คราคาใหม่</td>
                        </tr>
                        <tr>
                            <td class="cp-lbl">2. ค่าแรง+ค่าสี ครั้งนี้</td>
                            <td><input type="text" id="cp_wage" class="form-control form-control-sm js-comma cp-calc" autocomplete="off"></td>
                        </tr>
                        <tr>
                            <td class="cp-lbl">3. รหัสเม็ด #1</td>
                            <td><input type="text" id="cp_Resin1Code" class="form-control form-control-sm" maxlength="20" autocomplete="off"></td>
                        </tr>
                        <tr>
                            <td class="cp-lbl ps-4">ค่าเม็ด #1 ครั้งนี้</td>
                            <td><input type="text" id="cp_Resin1Price" class="form-control form-control-sm js-comma cp-resin cp-calc" autocomplete="off"></td>
                        </tr>
                        <tr>
                            <td class="cp-lbl ps-4">% เม็ด #1</td>
                            <td><div class="input-group input-group-sm">
                                <input type="text" id="cp_Resin1Per" class="form-control js-comma cp-resin cp-calc" data-decimals="0" autocomplete="off">
                                <span class="input-group-text">%</span></div></td>
                        </tr>
                        <tr>
                            <td class="cp-lbl ps-4">รหัสเม็ด #2</td>
                            <td><input type="text" id="cp_Resin2Code" class="form-control form-control-sm" maxlength="20" autocomplete="off"></td>
                        </tr>
                        <tr>
                            <td class="cp-lbl ps-4">ค่าเม็ด #2 ครั้งนี้</td>
                            <td><input type="text" id="cp_Resin2Price" class="form-control form-control-sm js-comma cp-resin cp-calc" autocomplete="off"></td>
                        </tr>
                        <tr>
                            <td class="cp-lbl ps-4">% เม็ด #2</td>
                            <td><div class="input-group input-group-sm">
                                <input type="text" id="cp_Resin2Per" class="form-control js-comma cp-resin cp-calc" data-decimals="0" autocomplete="off">
                                <span class="input-group-text">%</span></div></td>
                        </tr>
                    </table>
                </div>

                <div class="col-lg-5">
                    <div class="cp-from">
                        <div class="fw-semibold mb-2">ใช้เม็ดจาก</div>
                        @foreach ([1 => 'A. ใช้เม็ดลูกค้า ราคา = 0 บาท',
                                   2 => 'B. ใช้เม็ดซื้อจากลูกค้า ราคา ..... บาท',
                                   3 => 'C. ใช้เม็ดในสต๊อค ราคาล่าสุด ..... บาท',
                                   4 => 'D. ใช้เม็ดซื้อใหม่ ถามจากจัดซื้อ ราคาล่าสุด'] as $k => $label)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="cp_ResinFrom" id="cp_ResinFrom{{ $k }}" value="{{ $k }}">
                                <label class="form-check-label" for="cp_ResinFrom{{ $k }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ข้อ 4 / ข้อ 5 — คำนวณอัตโนมัติ (server คำนวณซ้ำตอนบันทึก) --}}
            <div class="row g-2 align-items-center mt-2">
                <div class="col-md-6 cp-lbl">4. ราคาขาย หัก ราคาเม็ด =</div>
                <div class="col-md-2"><input type="text" id="cp_wageCal" class="form-control form-control-sm cp-auto" readonly tabindex="-1"></div>
                <div class="col-md-4 fw-semibold" id="cp_wageCal_note"></div>

                <div class="col-md-6 cp-lbl">5. ราคาสูตร ตามใบเสนอราคา หัก ราคาขาย =</div>
                <div class="col-md-2"><input type="text" id="cp_Diff" class="form-control form-control-sm cp-auto" readonly tabindex="-1"></div>
                <div class="col-md-4 fw-semibold" id="cp_Diff_note"></div>
            </div>

            <div id="cp_status" class="cp-result mt-3">—</div>
        </div>

        <div class="d-flex justify-content-end gap-2 px-3 pb-3">
            <button type="button" id="btnCpCheckCancel" class="btn btn-label-secondary" onclick="cpCheckClose()">ยกเลิก</button>
            <button type="button" id="btnCpCheckSave" class="btn btn-primary" onclick="cpCheckConfirm()">
                <i class="ti ti-device-floppy me-1"></i>บันทึกการรับคำสั่งซื้อ
            </button>
        </div>
    </div>
</div>
