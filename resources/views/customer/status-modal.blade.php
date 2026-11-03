{{--
    ขอเปลี่ยนแปลง ข้อมูล Black List / เปลี่ยนแปลงเครดิต — 02/11/2569 (รวมเครดิต 03/11/2569)
    รวมฟอร์ม Access "Black List 17.05.53" + "ขออนุมัติเครดิต" · ใบขอเก็บที่ appvcredit
    ขั้นตอนแบบฟอร์ม MK ขออนุมัติราคาพิเศษ: ขอ → ใบรออนุมัติ → กรอกรหัสเข้าโหมดอนุมัติ (รหัสเดียวกับฟอร์ม MK) → ติ๊กอนุมัติ → เขียนลง customer
    ช่องข้อมูลลูกค้า (ช่องสีขาวของฟอร์มเดิม) แสดงอย่างเดียว
    include หลัง layout/inc_js (ใช้ jQuery / flatpickr / Swal / enhanceSelects)
--}}
@php($csStatuses = \App\Http\Controllers\CustomerStatusController::STATUSES)
@php($csTerms = \App\Http\Controllers\CustomerStatusController::termOptions())

<style>
    #customerStatusModal .cs-body { background: #f3f6f5; }
    #customerStatusModal .cs-box {
        background: #fff; border: 1px solid #dfe6e4; border-radius: .5rem; padding: .85rem 1rem;
    }
    #customerStatusModal .cs-box-title { font-weight: 700; margin-bottom: .6rem; }
    #customerStatusModal .cs-info { --of-lw: 92px; }
    #customerStatusModal .cs-info .form-control[readonly] { background: #fff; }
    #customerStatusModal .cs-sale-label { color: #d32f2f !important; }
    #customerStatusModal .cs-status .form-check { margin-bottom: .45rem; }
    #customerStatusModal .cs-status .form-check-label { font-size: 1rem; cursor: pointer; }
    #customerStatusModal .cs-status .form-check.cs-current .form-check-label::after {
        content: ' (ปัจจุบัน)'; font-size: .75rem; color: #8a94a6;
    }
    #customerStatusModal .cs-status .form-check-input:checked + .form-check-label {
        font-weight: 700; background: #fff3a6; padding: 0 .35rem; border-radius: .25rem;
    }
    #customerStatusModal .cs-black-box { --of-lw: 120px; }
    #customerStatusModal .cs-black-box .form-label { color: #2c4fb8; }
    /* กล่องเครดิต — โทนม่วงตามฟอร์มเดิม */
    #customerStatusModal .cs-credit { --of-lw: 150px; background: #f6f1fd; border-color: #ddd0f3; }
    #customerStatusModal .cs-credit .cs-box-title { color: #6a3fb5; }
    #customerStatusModal .cs-credit .form-label { color: #2c4fb8; }
    #customerStatusModal .cs-credit .cs-input-yellow { background: #fffde7; }
    #customerStatusModal .cs-appv-label { color: #c58a00; font-size: 1.1rem; }
    #customerStatusModal .flatpickr-wrapper { display: block; width: 100%; }
    #customerStatusModal .cs-nav { background: #e8f2f1; border-radius: .4rem; padding: .3rem .5rem; }
    #customerStatusModal #cs_history th { background: #eef3f2; font-size: .8rem; white-space: nowrap; }
    #customerStatusModal #cs_history td { font-size: .82rem; }
</style>

<div class="modal modalHeadDecor fade" id="customerStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-ban me-1"></i>ขอเปลี่ยนแปลง ข้อมูล Black List / เปลี่ยนแปลงเครดิต</h5>
                {{-- ขึ้นใบขอใหม่เสมอ แม้ลูกค้ามีใบรออนุมัติอยู่แล้ว (03/11/2569) --}}
                <button type="button" id="cs_btnNew" class="btn btn-sm btn-success ms-auto" style="margin-right: 4rem;" onclick="csNew()">
                    <i class="ti ti-plus me-1"></i>สร้างใบขอใหม่
                </button>
                {{-- โหมดสร้างใหม่ — สลับมาแสดงแทนปุ่มบน ใช้กลับไปใบที่เปิดดูอยู่ก่อนหน้า --}}
                <button type="button" id="cs_btnBack" class="btn btn-sm btn-label-secondary ms-auto d-none" style="margin-right: 4rem;" onclick="csBack()">
                    <i class="ti ti-arrow-left me-1"></i>กลับไปใบที่รออนุมัติ
                </button>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body cs-body">
                {{-- โหมดอนุมัติ --}}
                <div class="cs-box mb-3">
                    <div id="cs_mdLockBox" class="d-flex flex-wrap align-items-end gap-2">
                        <div>
                            <label class="form-label mb-1" for="cs_mdpass">กรอกรหัสเพื่อเข้าสู่โหมดอนุมัติ</label>
                            <input type="password" id="cs_mdpass" class="form-control form-control-sm" style="width: 200px;" autocomplete="new-password">
                        </div>
                        <button type="button" class="btn btn-sm btn-warning" onclick="csUnlock()">
                            <i class="ti ti-lock-open me-1"></i>เข้าสู่โหมดอนุมัติ
                        </button>
                    </div>
                    <div id="cs_mdUnlockedBox" class="d-none d-flex align-items-center gap-2">
                        <span class="badge bg-success"><i class="ti ti-shield-check me-1"></i>อยู่ในโหมดอนุมัติ</span>
                        <button type="button" class="btn btn-sm btn-label-secondary" onclick="csLock()">
                            <i class="ti ti-lock me-1"></i>ออกจากโหมดอนุมัติ
                        </button>
                    </div>
                </div>

                <div id="cs_reqState" class="alert d-none py-2 mb-3"></div>

                {{-- ข้อมูลลูกค้า — แสดงอย่างเดียว --}}
                <div class="cs-box of-form cs-info mb-3">
                    <div class="row gx-3 gy-2">
                        <div class="col-md-6 of-row">
                            <label class="form-label fw-bold text-primary" for="cs_custno">รหัสลูกค้า</label>
                            <div class="of-ctl"><input type="text" id="cs_custno" class="form-control fw-bold" maxlength="6"></div>
                        </div>
                        <div class="col-md-6"></div>

                        <div class="col-md-12 of-row"><label class="form-label">NAME</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="name" readonly></div></div>
                        <div class="col-md-6 of-row"><label class="form-label">NO</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="no" readonly></div></div>
                        <div class="col-md-6 of-row"><label class="form-label">ROAD</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="road" readonly></div></div>
                        <div class="col-md-6 of-row"><label class="form-label">AMPHUR</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="amphur" readonly></div></div>
                        <div class="col-md-6 of-row"><label class="form-label">CITY</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="city" readonly></div></div>
                        <div class="col-md-6 of-row"><label class="form-label">ZIP</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="zip" readonly></div></div>
                        <div class="col-md-6 of-row"><label class="form-label">TEL</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="tel" readonly></div></div>
                        <div class="col-md-6 of-row"><label class="form-label cs-sale-label">SALE</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="sale" readonly></div></div>
                        <div class="col-md-6 of-row"><label class="form-label">TERM</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="term" readonly></div></div>
                        <div class="col-md-12 of-row"><label class="form-label">REMARK</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="remark" readonly></div></div>
                        <div class="col-md-6 of-row"><label class="form-label">ZONE</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="zone" readonly></div></div>
                        <div class="col-md-6 of-row"><label class="form-label">CONDITION</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="condition" readonly></div></div>
                        <div class="col-md-6 of-row"><label class="form-label">CASHDISC</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="cashdisc" readonly></div></div>
                        <div class="col-md-6 of-row"><label class="form-label">TYPE</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="type_text" readonly></div></div>
                        <div class="col-md-6 of-row"><label class="form-label">FAX</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="fax" readonly></div></div>
                        <div class="col-md-6 of-row"><label class="form-label">FLprt</label><div class="of-ctl"><input type="text" class="form-control cs-f" data-f="flprt" readonly></div></div>
                        <div class="col-md-12 of-row">
                            <label class="form-label">สถานะปัจจุบัน</label>
                            <div class="of-ctl"><input type="text" class="form-control cs-f" data-f="current_text" readonly></div>
                        </div>
                    </div>
                </div>

                {{-- สถานะ Black List ที่ขอ --}}
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="cs-box cs-status h-100">
                            <div class="cs-box-title">สถานะ</div>
                            @foreach ($csStatuses as $val => $s)
                                <div class="form-check" data-val="{{ $val }}">
                                    <input class="form-check-input" type="radio" name="cs_black"
                                        id="cs_black_{{ $loop->index }}" value="{{ $val }}">
                                    <label class="form-check-label text-{{ $s['color'] }}" for="cs_black_{{ $loop->index }}">{{ $s['label'] }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="cs-box of-form cs-black-box h-100">
                            <div class="row gx-3 gy-2">
                                <div class="col-12 of-row">
                                    <label class="form-label" for="cs_blackdate">Black List Date</label>
                                    <div class="of-ctl" style="max-width: 200px;"><input type="text" id="cs_blackdate" class="form-control"></div>
                                </div>
                                <div class="col-12 of-row">
                                    <label class="form-label" for="cs_blackrem">Black Remark</label>
                                    <div class="of-ctl">
                                        <textarea id="cs_blackrem" class="form-control text-danger" rows="3" maxlength="50"></textarea>
                                        <div class="form-text">สูงสุด 50 ตัวอักษร</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- เปลี่ยนแปลงเครดิต + อนุมัติ (ผังตามฟอร์ม "ขออนุมัติเครดิต" เดิม) --}}
                <div class="cs-box cs-credit of-form mt-3">
                    <div class="cs-box-title"><i class="ti ti-credit-card me-1"></i>เปลี่ยนแปลงเครดิต</div>
                    <div class="row gx-3 gy-2">
                        <div class="col-md-7 of-row">
                            <label class="form-label" for="cs_term_new">Term (ใหม่)</label>
                            <div class="of-ctl">
                                <select id="cs_term_new" class="form-select select2-tags" data-placeholder="ไม่เปลี่ยน">
                                    <option value=""></option>
                                    @foreach ($csTerms as $t)
                                        <option value="{{ $t->name_th }}">{{ $t->name_th }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">ว่าง = ไม่เปลี่ยน · พิมพ์ค่าที่ไม่มีในรายการเองได้</div>
                            </div>
                        </div>
                        <div class="col-md-5 of-row of-row-auto">
                            <label class="form-label" for="cs_cashdisc_new">ส่วนลด (ใหม่)</label>
                            <div class="of-ctl">
                                <div class="input-group" style="max-width: 160px;">
                                    <input type="text" id="cs_cashdisc_new" class="form-control text-end js-comma cs-input-yellow">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 of-row">
                            <label class="form-label" for="cs_remark">สาเหตุที่ขอเปลี่ยน (MK)</label>
                            <div class="of-ctl"><textarea id="cs_remark" class="form-control cs-input-yellow" rows="2" maxlength="100"></textarea></div>
                        </div>
                        <div class="col-12 of-row">
                            <label class="form-label text-warning" for="cs_appv_rem">หมายเหตุ (ผู้บริหาร)</label>
                            <div class="of-ctl">
                                <textarea id="cs_appv_rem" class="form-control" rows="2" maxlength="100" disabled
                                    title="กรอกได้เฉพาะโหมดอนุมัติ"></textarea>
                            </div>
                        </div>
                        <div class="col-12 of-row">
                            <div class="form-label"></div>
                            <div class="of-ctl">
                                <div class="form-check" title="ต้องกรอกรหัสเพื่อเข้าสู่โหมดอนุมัติก่อน">
                                    <input class="form-check-input" type="checkbox" id="cs_appv" disabled>
                                    <label class="form-check-label fw-bold cs-appv-label" for="cs_appv">อนุมัติ</label>
                                    <span class="small text-muted ms-1">(ปรับข้อมูลลูกค้าทันทีเมื่อบันทึก)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ประวัติใบขอของลูกค้ารายนี้ (appvcredit รวมของโปรแกรมเดิม) --}}
                <div class="cs-box mt-3">
                    <div class="cs-box-title"><i class="ti ti-history me-1"></i>ประวัติการขอเปลี่ยนแปลง</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" id="cs_history">
                            <thead>
                                <tr>
                                    <th>วันที่ขอ</th><th>ผู้ขอ</th><th>Term</th><th>ส่วนลด</th><th>สถานะ</th>
                                    <th>สาเหตุ (MK)</th><th>หมายเหตุ (ผู้บริหาร)</th><th>ผลอนุมัติ</th>
                                </tr>
                            </thead>
                            <tbody><tr><td colspan="8" class="text-center text-muted">—</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                {{-- ตัวเดินระเบียน: ใบที่รออนุมัติ --}}
                <div class="cs-nav d-flex align-items-center gap-1">
                    <span class="small fw-medium me-1">รออนุมัติ</span>
                    <button type="button" class="btn btn-sm btn-icon btn-label-secondary" onclick="csGo(0)"><i class="ti ti-player-skip-back"></i></button>
                    <button type="button" class="btn btn-sm btn-icon btn-label-secondary" onclick="csStep(-1)"><i class="ti ti-chevron-left"></i></button>
                    <span class="small px-1"><span id="cs_pos">-</span> / <span id="cs_total">0</span></span>
                    <button type="button" class="btn btn-sm btn-icon btn-label-secondary" onclick="csStep(1)"><i class="ti ti-chevron-right"></i></button>
                    <button type="button" class="btn btn-sm btn-icon btn-label-secondary" onclick="csGo(csQueue.length - 1)"><i class="ti ti-player-skip-forward"></i></button>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" id="cs_btnDelete" class="btn btn-label-danger d-none" onclick="csDelete()">
                        <i class="ti ti-trash me-1"></i>ลบใบขอ
                    </button>
                    <button type="button" class="btn btn-label-secondary" onclick="csReload()">
                        <i class="ti ti-arrow-back-up me-1"></i>ย้อนค่าเดิม
                    </button>
                    <button type="button" id="cs_btnSave" class="btn btn-primary" onclick="csSave()" disabled>
                        <i class="ti ti-device-floppy me-1"></i><span id="cs_btnSaveText">บันทึกใบขอ</span>
                    </button>
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var CS_URL = "{{ url('customer/status') }}";
    var CS_TOKEN = "{{ csrf_token() }}";
    var CS_PENDING_COLOR = {{ \App\Http\Controllers\CustomerStatusController::STATUS_PENDING_COLOR }};
    var csMdUnlocked = false;
    var csQueue = [], csIndex = -1;
    var csLoaded = null;          // ผลของ /data ล่าสุด
    var csTimer = null, csSeq = 0;
    var csFp = null;
    // edit = ดู/แก้ใบรออนุมัติ (ไม่มีใบ = บันทึกแล้วขึ้นใบใหม่) · new = ปุ่ม "สร้างใบขอใหม่" — ขึ้นใบใหม่เสมอ
    var csMode = 'edit';
    var csReqDate = null;         // ReqDate ของใบที่เปิดอยู่ (null = ยังไม่มีใบ)
    var csBackTo = null;          // ใบในคิวที่เปิดดูอยู่ก่อนกด "สร้างใบขอใหม่"

    // สลับโหมด + สลับปุ่มบนหัว modal (สร้างใบขอใหม่ ⇄ กลับไปใบที่รออนุมัติ)
    function csSetMode(mode) {
        csMode = mode;
        $('#cs_btnNew').toggleClass('d-none', mode === 'new');
        $('#cs_btnBack').toggleClass('d-none', mode !== 'new');
    }

    // ออกจากโหมดสร้างใหม่ — กลับไปใบเดิม (ยังอยู่ในคิว) หรือใบแรกของคิว
    function csBack() {
        csSetMode('edit');
        var i = -1;
        if (csBackTo) {
            $.each(csQueue, function (k, r) {
                if (r.custno === csBackTo.custno && r.ReqDate === csBackTo.ReqDate) { i = k; return false; }
            });
        }
        if (i < 0 && csQueue.length) i = 0;
        if (i >= 0) { csGo(i); return; }
        $('#cs_custno').val('');
        csFill(null);
    }

    $(function () {
        csFp = flatpickr('#cs_blackdate', { dateFormat: 'd/m/Y', allowInput: true, static: true });
        enhanceSelects('#customerStatusModal');
        csLoadQueue();   // badge จำนวนใบรออนุมัติบนปุ่มหัวหน้า
    });

    // เปิดฟอร์ม — ส่ง custno = เปิดลูกค้ารายนั้น · ไม่ส่ง = เด้งเข้าใบแรกของคิวรออนุมัติ (ถ้ามี)
    function customerStatusOpen(custno) {
        csSetMode('edit');
        csClear();
        $('#cs_custno').val(custno || '');
        $('#customerStatusModal').modal('show');
        $.get(CS_URL + '/md-state').done(function (r) { csSetMdMode(r.unlocked); });
        csLoadQueue(function () {
            if (custno) { csLoad(custno); csSyncIndex(custno); }
            else if (csQueue.length) csGo(0);
        });
    }

    // ปุ่ม "สร้างใบขอใหม่" — ล้างฟอร์มทั้งหมดรวมรหัสลูกค้า (กันสับสนกับใบที่เปิดอยู่) แล้วให้กรอกรหัสลูกค้าใหม่
    function csNew() {
        // จำใบที่เปิดดูอยู่ไว้ให้ปุ่ม "กลับไปใบที่รออนุมัติ"
        csBackTo = csQueue[csIndex] || null;
        csSetMode('new');
        csIndex = -1;
        csRenderNav();
        ++csSeq;                       // ทิ้งผลโหลดที่ยังค้างอยู่
        clearTimeout(csTimer);
        $('#cs_custno').val('');
        csFill(null);
        $('#cs_custno').trigger('focus');
    }

    $('#customerStatusModal').on('shown.bs.modal', function () { $('#cs_custno').trigger('focus'); });

    // ── โหมดอนุมัติ ─────────────────────────────────────────
    function csSetMdMode(unlocked) {
        unlocked = !!unlocked;
        if (unlocked && !csMdUnlocked) $('#cs_mdpass').val('');
        csMdUnlocked = unlocked;
        $('#cs_mdLockBox').toggleClass('d-none', unlocked);
        $('#cs_mdUnlockedBox').toggleClass('d-none', !unlocked);
        var ready = unlocked && !!(csLoaded && csLoaded.customer);
        $('#cs_appv, #cs_appv_rem').prop('disabled', !ready);
        if (!unlocked) $('#cs_appv').prop('checked', false);
        csSyncSaveText();
    }
    function csUnlock() {
        var pass = ($('#cs_mdpass').val() || '').trim();
        if (!pass) { Swal.fire({ icon: 'warning', title: 'ยังไม่ได้กรอกรหัส', target: '#customerStatusModal', heightAuto: false }); return; }
        $.post(CS_URL + '/unlock', { _token: CS_TOKEN, md_password: pass })
            .done(function (res) {
                csSetMdMode(true);
                Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false, target: '#customerStatusModal', heightAuto: false });
            })
            .fail(function (xhr) {
                Swal.fire({ icon: 'error', title: 'เข้าสู่โหมดอนุมัติไม่สำเร็จ',
                    text: (xhr.responseJSON && xhr.responseJSON.message) || 'รหัสผ่านไม่ถูกต้อง',
                    target: '#customerStatusModal', heightAuto: false });
                $('#cs_mdpass').val('').trigger('focus');
            });
    }
    function csLock() {
        $.post(CS_URL + '/lock', { _token: CS_TOKEN }).always(function () { csSetMdMode(false); });
    }
    $(document).on('keydown', '#cs_mdpass', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); csUnlock(); }
    });

    // ── คิวใบรออนุมัติ ──────────────────────────────────────
    function csLoadQueue(after) {
        $.get(CS_URL + '/pending').done(function (res) {
            csQueue = res.rows || [];
            $('#csQueueBadge').text(csQueue.length).toggleClass('d-none', csQueue.length === 0);
            csRenderNav();
            if (after) after();
        });
    }
    // ชี้ตำแหน่งในคิว — ระบุ reqDate = ใบนั้น · ไม่ระบุ = ใบแรกของลูกค้ารายนั้น
    function csSyncIndex(custno, reqDate) {
        csIndex = -1;
        $.each(csQueue, function (i, r) {
            if (String(r.custno) === String(custno) && (!reqDate || r.ReqDate === reqDate)) { csIndex = i; return false; }
        });
        csRenderNav();
    }
    function csRenderNav() {
        $('#cs_total').text(csQueue.length);
        $('#cs_pos').text(csIndex >= 0 ? csIndex + 1 : '-');
    }
    function csGo(i) {
        if (!csQueue.length) return;
        i = Math.max(0, Math.min(csQueue.length - 1, i));
        csIndex = i;
        csSetMode('edit');
        csRenderNav();
        $('#cs_custno').val(csQueue[i].custno);
        csLoad(csQueue[i].custno, csQueue[i].ReqDate);
    }
    function csStep(d) { csGo(csIndex < 0 ? 0 : csIndex + d); }

    // ── โหลดข้อมูลลูกค้า ───────────────────────────────────
    $(document).on('input', '#cs_custno', function () {
        clearTimeout(csTimer);
        var code = $(this).val().trim();
        csTimer = setTimeout(function () { csSyncIndex(code); csLoad(code); }, 400);
    });

    function csReload() { csLoad($('#cs_custno').val().trim(), csReqDate); }

    function csLoad(code, reqDate) {
        var seq = ++csSeq;
        if (!code) { csFill(null); return; }
        $.get(CS_URL + '/data', { custno: code, req_date: reqDate || '' }).done(function (res) {
            if (seq !== csSeq) return;
            csFill(res);
            csSetMdMode(res.md_unlocked);
        }).fail(function (xhr, status) {
            if (seq !== csSeq || status === 'abort') return;
            csFill(null);
            var msg = (xhr.responseJSON && xhr.responseJSON.message) || ('HTTP ' + xhr.status);
            $('#cs_reqState').removeClass('d-none alert-warning alert-success alert-secondary').addClass('alert-danger')
                .text('โหลดข้อมูลลูกค้า ' + code + ' ไม่สำเร็จ — ' + msg);
        });
    }

    // ตั้งค่า select Term (ใหม่) — ค่าที่ไม่มีในรายการ (ค่าเดิม/พิมพ์เอง) เพิ่ม option ให้ก่อน
    function csSetTerm(v) {
        var $s = $('#cs_term_new');
        v = v == null ? '' : String(v);
        if (v !== '' && !$s.find('option').filter(function () { return this.value === v; }).length) {
            $s.append($('<option>').val(v).text(v));
        }
        $s.val(v).trigger('change');
    }

    function csClear() {
        csLoaded = null;
        csReqDate = null;
        $('.cs-f').val('');
        $('input[name="cs_black"]').prop('checked', false);
        $('#cs_blackrem, #cs_cashdisc_new, #cs_remark, #cs_appv_rem').val('');
        csSetTerm('');
        if (csFp) csFp.clear();
        $('#cs_appv').prop('checked', false);
        $('#customerStatusModal .cs-status .form-check').removeClass('cs-current');
        $('#cs_reqState').removeClass('alert-danger alert-warning alert-success alert-secondary').addClass('d-none');
        $('#cs_history tbody').html('<tr><td colspan="8" class="text-center text-muted">—</td></tr>');
        $('#cs_btnDelete').addClass('d-none');
        $('#cs_btnSave').prop('disabled', true);
        csSyncSaveText();
    }

    function csFill(res) {
        var keep = $('#cs_custno').val();
        csClear();
        $('#cs_custno').val(keep);
        if (!res || !res.customer) {
            if (keep) {
                $('#cs_reqState').removeClass('d-none alert-warning alert-success').addClass('alert-secondary')
                    .text('ไม่พบรหัสลูกค้า ' + keep);
            } else if (csMode === 'new') {
                $('#cs_reqState').removeClass('d-none alert-warning alert-secondary').addClass('alert-success')
                    .html('<i class="ti ti-plus me-1"></i>สร้างใบขอใหม่ — กรอกรหัสลูกค้าก่อน');
            }
            return;
        }
        csLoaded = res;
        var c = res.customer;
        $('.cs-f').each(function () {
            var f = $(this).data('f'), v = c[f];
            if (f === 'type_text') v = c.type == null ? '' : c.type + (c.type_name ? ' — ' + c.type_name : '');
            if (f === 'current_text') v = c.status_label + (c.blackdate ? '  ·  ' + c.blackdate : '') + (c.blackrem ? '  ·  ' + c.blackrem : '');
            $(this).val(v == null ? '' : v);
        });
        if (c.black !== null) {
            $('#customerStatusModal .cs-status .form-check[data-val="' + parseInt(c.black, 10) + '"]').addClass('cs-current');
        }

        // ค่าตั้งต้นของสถานะ = ค่าปัจจุบันของลูกค้า
        var black = c.black, bdate = c.blackdate, brem = c.blackrem;
        // โหมดสร้างใหม่ — ไม่ดึงใบรออนุมัติเดิมมา (ใบเดิมยังอยู่ในคิว)
        var r = csMode === 'new' ? null : res.request;
        csReqDate = r ? r.ReqDate : null;
        var others = (res.pending_count || 0) - (r ? 1 : 0);
        var othersText = others > 0 ? ' · ลูกค้ารายนี้มีใบรออนุมัติอื่นอีก ' + others + ' ใบ (ดูได้จากปุ่มลูกศรด้านล่าง)' : '';
        if (csMode === 'new') {
            $('#cs_reqState').removeClass('d-none alert-secondary alert-warning').addClass('alert-success')
                .html('<i class="ti ti-plus me-1"></i>สร้างใบขอใหม่ — กรอกสิ่งที่ต้องการเปลี่ยนแล้วกด "เพิ่มใบขอ"' + escHtml(othersText));
        } else if (r) {
            // มีใบรออนุมัติ — โชว์ค่าที่ขอไว้ (ใบที่ไม่ได้ขอเปลี่ยนสถานะ = คงค่าปัจจุบัน)
            if (r.BlackNewVal !== null) { black = r.BlackNewVal; bdate = r.BlackDateNew; brem = r.BlackRemNew; }
            csSetTerm(r.TermNew);
            $('#cs_cashdisc_new').val(r.CashDiscNew === null ? '' : commaFmt(r.CashDiscNew, 2));
            $('#cs_remark').val(r.Remark || '');
            $('#cs_appv_rem').val(r.AppvRem || '');
            $('#cs_reqState').removeClass('d-none alert-secondary alert-success').addClass('alert-warning')
                .html('<i class="ti ti-clock me-1"></i>ใบขอรออนุมัติ — ขอเมื่อ ' + escHtml(r.ReqDateText)
                    + (r.ReqByName ? ' โดย ' + escHtml(r.ReqByName) : '') + ' · กดบันทึกจะแก้ทับใบนี้'
                    + ' · ต้องการขอเพิ่ม กด "สร้างใบขอใหม่"' + escHtml(othersText));
            $('#cs_btnDelete').removeClass('d-none');
        } else {
            $('#cs_reqState').removeClass('d-none alert-warning alert-success').addClass('alert-secondary')
                .html('<i class="ti ti-info-circle me-1"></i>ยังไม่มีใบขอ — กรอกสิ่งที่ต้องการเปลี่ยนแล้วกด "เพิ่มใบขอ" เพื่อส่งขออนุมัติ');
        }
        csSyncSaveText();
        if (black !== null) $('input[name="cs_black"][value="' + parseInt(black, 10) + '"]').prop('checked', true);
        if (bdate) csFp.setDate(bdate, false, 'd/m/Y');
        $('#cs_blackrem').val(brem || '');

        var rows = (res.history || []).map(function (h) {
            var result = h.approved
                ? '<span class="badge bg-label-success">อนุมัติ</span>'
                    + (h.appv_at ? ' <small>' + escHtml(h.appv_at) + ' ' + escHtml(h.appv_by || '') + '</small>' : '')
                : '<span class="badge bg-label-warning">รออนุมัติ</span>';
            return '<tr><td class="text-nowrap">' + escHtml(h.req_date) + '</td><td>' + escHtml(h.req_by || '') + '</td>'
                + '<td>' + escHtml(h.term) + '</td><td>' + escHtml(h.cashdisc) + '</td><td>' + escHtml(h.status) + '</td>'
                + '<td>' + escHtml(h.remark || '') + '</td><td>' + escHtml(h.appv_rem || '') + '</td><td>' + result + '</td></tr>';
        });
        $('#cs_history tbody').html(rows.length ? rows.join('')
            : '<tr><td colspan="8" class="text-center text-muted">ยังไม่เคยขอเปลี่ยนแปลง</td></tr>');

        $('#cs_btnSave').prop('disabled', false);
    }

    // เปลี่ยนสถานะ → Black List Date = วันนี้ (ตามฟอร์มเดิม Black_AfterUpdate)
    $(document).on('change', 'input[name="cs_black"]', function () {
        if (csFp) csFp.setDate(new Date(), false);
        if (parseInt($(this).val(), 10) === CS_PENDING_COLOR) {
            Swal.fire({ icon: 'info', title: 'กรุณาใส่เบอร์ที่ค้างส่งที่ช่อง Black Remark ด้วยค่ะ',
                target: '#customerStatusModal', heightAuto: false })
                .then(function () { $('#cs_blackrem').trigger('focus'); });
        }
    });

    $(document).on('change', '#cs_appv', csSyncSaveText);
    function csSyncSaveText() {
        var isNew = csMode === 'new' || !csReqDate;
        $('#cs_btnSaveText').text($('#cs_appv').is(':checked')
            ? (isNew ? 'เพิ่มและอนุมัติ' : 'บันทึกและอนุมัติ')
            : (isNew ? 'เพิ่มใบขอ' : 'บันทึกการแก้ไข'));
    }

    // ── บันทึก / อนุมัติ ────────────────────────────────────
    function csSave() {
        if (!csLoaded || !csLoaded.customer) return;
        var c = csLoaded.customer;
        var appv = $('#cs_appv').is(':checked');
        var black = $('input[name="cs_black"]:checked').val();
        var term = $('#cs_term_new').val() || '';
        var disc = $('#cs_cashdisc_new').val();

        var lines = [];
        if (term) lines.push('Term: ' + escHtml(c.term || '-') + ' → <b>' + escHtml(term) + '</b>');
        if (disc !== '') lines.push('ส่วนลด: ' + escHtml(c.cashdisc == null ? '-' : c.cashdisc) + ' → <b>' + escHtml(disc) + '%</b>');
        if (black !== undefined && String(parseInt(black, 10)) !== String(c.black)) {
            lines.push('สถานะ: ' + escHtml(c.status_label) + ' → <b>' + escHtml($('input[name="cs_black"]:checked').next('label').text()) + '</b>');
        }

        Swal.fire({
            icon: appv ? 'warning' : 'question',
            title: appv ? 'อนุมัติการเปลี่ยนแปลง?' : 'บันทึกใบขอเปลี่ยนแปลง?',
            html: escHtml(c.code) + ' ' + escHtml(c.name || '') + (lines.length ? '<br>' + lines.join('<br>') : '')
                + (appv ? '<br><small class="text-danger">ข้อมูลลูกค้าจะเปลี่ยนทันที</small>' : ''),
            showCancelButton: true, confirmButtonText: appv ? 'อนุมัติ' : 'บันทึก', cancelButtonText: 'ยกเลิก',
            target: '#customerStatusModal', heightAuto: false
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $('#cs_btnSave').prop('disabled', true);
            $.post(CS_URL + '/save', {
                _token: CS_TOKEN, custno: c.code,
                new: csMode === 'new' ? 1 : 0, req_date: csMode === 'new' ? '' : (csReqDate || ''),
                term_new: term, cashdisc_new: disc === '' ? '' : numOf(disc),
                remark: $('#cs_remark').val(), appv_rem: $('#cs_appv_rem').val(),
                new_black: black === undefined ? '' : black,
                new_blackdate: $('#cs_blackdate').val(), new_blackrem: $('#cs_blackrem').val(),
                appv: appv ? 1 : 0
            }).done(function (res) {
                Swal.fire({ icon: 'success', title: res.message, timer: 1800, showConfirmButton: false, target: '#customerStatusModal', heightAuto: false });
                csSetMode('edit');   // เพิ่มเสร็จ → เปิดใบที่เพิ่งสร้างในโหมดแก้ไข
                csAfterWrite(c.code, res.req_date);
            }).fail(function (xhr) {
                var j = xhr.responseJSON || {};
                if (j.md_locked) csSetMdMode(false);
                Swal.fire({ icon: 'error', title: 'บันทึกไม่สำเร็จ', text: j.message || 'เกิดข้อผิดพลาด', target: '#customerStatusModal', heightAuto: false });
                $('#cs_btnSave').prop('disabled', false);
            });
        });
    }

    function csDelete() {
        if (!csLoaded || !csLoaded.customer) return;
        var code = csLoaded.customer.code;
        Swal.fire({ icon: 'warning', title: 'ลบใบขอที่รออนุมัติของลูกค้า ' + code + '?',
            showCancelButton: true, confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก', confirmButtonColor: '#d33',
            target: '#customerStatusModal', heightAuto: false
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.post(CS_URL + '/delete', { _token: CS_TOKEN, custno: code, req_date: csReqDate || '' })
                .done(function (res) {
                    Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false, target: '#customerStatusModal', heightAuto: false });
                    csAfterWrite(code);
                })
                .fail(function (xhr) {
                    Swal.fire({ icon: 'error', title: (xhr.responseJSON && xhr.responseJSON.message) || 'ลบไม่สำเร็จ', target: '#customerStatusModal', heightAuto: false });
                });
        });
    }

    // หลังเขียนข้อมูล: โหลดคิว + ลูกค้ารายเดิมใหม่ + ตารางรายการ (สถานะ Blacklist / เครดิตในตารางอาจเปลี่ยน)
    function csAfterWrite(code, reqDate) {
        csLoadQueue(function () { csSyncIndex(code, reqDate); });
        csLoad(code, reqDate);
        if (typeof loadData === 'function') loadData(page);
    }
</script>
