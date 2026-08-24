@php
    use App\Http\Controllers\CustomerController;

    $isNew = $customer === null;

    // ค่าปัจจุบันของช่องต่าง ๆ (ลูกค้าใหม่ = ว่างทั้งหมด)
    $v = fn ($field) => $customer ? $customer->{$field} : null;

    // รหัสผู้ขายที่บันทึกไว้อาจไม่อยู่ในรายการตัวเลือก (พนักงานขายเก่า) — เติมเข้าไปเองกันค่าหาย
    $saleCurrent = trim((string) $v('sale'));
    $saleList    = collect($sales);
    if ($saleCurrent !== '' && !$saleList->contains(fn ($s) => (string) $s['sale'] === $saleCurrent)) {
        $saleList = $saleList->push(['sale' => $saleCurrent, 'label' => $saleCurrent]);
    }

    // ข้อมูล Blacklist — แสดงอย่างเดียว (ค่าใน DB มีทั้ง -1 / -3 / 0 / 2 / 5 ซึ่งยังไม่ทราบความหมายครบ)
    $blackValue = $v('black');
    $isBlack    = (int) $blackValue === -1;
    $hasBlackInfo = $isBlack
        || trim((string) $v('blackrem')) !== ''
        || trim((string) $v('blackdate')) !== '';
@endphp

<form id="customer_form" autocomplete="off">
    @csrf
    <input type="hidden" name="mode" value="{{ $isNew ? 'insert' : 'update' }}">

    @if ($isBlack)
        <div class="cf-blackwarn">
            <i class="ti ti-alert-triangle me-1"></i>
            ลูกค้ารายนี้ติด Blacklist
            @if (trim((string) $v('blackrem')) !== '')
                — {{ $v('blackrem') }}
            @endif
        </div>
    @endif

    {{-- ═══ ข้อมูลลูกค้า ═══ --}}
    <div class="cf-sec mb-3">
        <div class="cf-sec-title"><i class="ti ti-address-book"></i> ข้อมูลลูกค้า</div>

        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label">รหัส <span class="text-danger">*</span></label>
                <input type="text" class="form-control cf-hl-code" id="c_code" name="code" maxlength="6"
                    value="{{ $v('code') }}" {{ $isNew ? '' : 'readonly' }}>
                @if ($isNew)
                    <div class="form-text small">กรอกเอง (ไม่เกิน 6 ตัวอักษร)</div>
                @endif
            </div>
            <div class="col-md-6">
                <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="c_name" name="name" maxlength="70" value="{{ $v('name') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">ชื่อ (อังกฤษ)</label>
                <input type="text" class="form-control" id="c_name_en" name="name_en" maxlength="60" value="{{ $name_en }}">
                <div class="form-text small">เก็บที่ตาราง engname</div>
            </div>

            <div class="col-md-3">
                <label class="form-label">เลขที่</label>
                <input type="text" class="form-control" name="no" maxlength="40" value="{{ $v('no') }}">
            </div>
            <div class="col-md-9">
                <label class="form-label">ถนน</label>
                <input type="text" class="form-control" name="road" maxlength="65" value="{{ $v('road') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">อำเภอ / เขต</label>
                <input type="text" class="form-control" name="amphur" maxlength="40" value="{{ $v('amphur') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">จังหวัด</label>
                <input type="text" class="form-control" name="city" maxlength="20" value="{{ $v('city') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">รหัสไปรษณีย์</label>
                <input type="text" class="form-control" name="zip" maxlength="6" value="{{ $v('zip') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">โทร</label>
                <input type="text" class="form-control" name="tel" maxlength="23" value="{{ $v('tel') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Fax</label>
                <input type="text" class="form-control" name="fax" maxlength="12" value="{{ $v('fax') }}">
            </div>
        </div>
    </div>

    {{-- ═══ เงื่อนไขการขาย ═══ --}}
    <div class="cf-sec cf-sec-sale mb-3">
        <div class="cf-sec-title"><i class="ti ti-businessplan"></i> เงื่อนไขการขาย</div>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">เครดิต</label>
                <input type="text" class="form-control" name="term" maxlength="15" value="{{ $v('term') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">ส่วนลด %</label>
                <input type="text" class="form-control" name="cashdisc" value="{{ $v('cashdisc') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">รหัสผู้ขาย</label>
                <select class="form-select" name="sale">
                    <option value="">- ไม่ระบุ -</option>
                    @foreach ($saleList as $s)
                        <option value="{{ $s['sale'] }}" {{ $saleCurrent === (string) $s['sale'] ? 'selected' : '' }}>
                            {{ $s['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">ประเภทลูกค้า</label>
                <select class="form-select" name="type">
                    <option value="">- ไม่ระบุ -</option>
                    @foreach ($types as $t)
                        <option value="{{ $t->type }}" {{ (string) $v('type') === (string) $t->type ? 'selected' : '' }}>
                            {{ $t->type }} — {{ $t->t_namee }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">เลขที่ผู้เสียภาษี</label>
                <input type="text" class="form-control" name="taxid" maxlength="50" value="{{ $v('taxid') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">สาขา (เพื่อเปิดบิล)</label>
                <input type="text" class="form-control" name="branch" maxlength="15" value="{{ $v('Branch') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">เลขผู้เสียภาษีเดิม (10 หลัก)</label>
                <input type="text" class="form-control" name="legal" maxlength="50" value="{{ $v('legal') }}">
            </div>

            <div class="col-md-5">
                <label class="form-label d-block">เอกสารที่ต้องแนบ</label>
                <div class="cf-checkrow">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="c_rp" name="rp" value="1"
                            {{ CustomerController::checked($v('RP')) ? 'checked' : '' }}>
                        <label class="form-check-label" for="c_rp">RP</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="c_cer" name="cer" value="1"
                            {{ CustomerController::checked($v('CER')) ? 'checked' : '' }}>
                        <label class="form-check-label" for="c_cer">CER</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="c_msds" name="msds" value="1"
                            {{ CustomerController::checked($v('MSDS')) ? 'checked' : '' }}>
                        <label class="form-check-label" for="c_msds">MSDS</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="c_po" name="po" value="1"
                            {{ CustomerController::checked($v('PO')) ? 'checked' : '' }}>
                        <label class="form-check-label" for="c_po">PO</label>
                    </div>
                </div>
                <div class="form-text small">ค่าตั้งต้นที่ฟอร์มใบสั่งซื้อดึงไปเติมให้อัตโนมัติ</div>
            </div>
            <div class="col-md-3">
                <label class="form-label">ใบกำกับ / สำเนา</label>
                <input type="text" class="form-control" name="copyinv" maxlength="20" value="{{ $v('CopyINV') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Nickname</label>
                <input type="text" class="form-control cf-hl-yellow" name="nickname" maxlength="30" value="{{ $v('nickname') }}">
                <div class="form-text small">ชื่อที่ติดข้างกล่อง</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">เวลารับของ</label>
                <input type="text" class="form-control" name="custtime" maxlength="20" value="{{ $v('custTime') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">ลงของ เก็บเงิน / เช็ค</label>
                <input type="text" class="form-control" name="cashchq" maxlength="20" value="{{ $v('CashChq') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">หมายเหตุภายใน</label>
                <input type="text" class="form-control" name="cust_desc" maxlength="50" value="{{ $v('cust_desc') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">คำสั่งขณะส่งมอบ</label>
                <input type="text" class="form-control" name="condition" maxlength="50" value="{{ $v('condition') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">หมายเหตุ</label>
                <input type="text" class="form-control" name="remark" maxlength="50" value="{{ $v('remark') }}">
            </div>
        </div>
    </div>

    {{-- ═══ ผู้ติดต่อ (ตาราง contact) ═══ --}}
    <div class="cf-sec cf-sec-contact mb-3">
        <div class="cf-sec-title">
            <i class="ti ti-users"></i> ผู้ติดต่อ
            <button type="button" class="btn btn-sm btn-label-primary ms-auto" id="btn_add_contact">
                <i class="ti ti-plus me-1"></i>เพิ่มแถว
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0" id="contactTable">
                <thead>
                    <tr>
                        <th style="width: 22%;">ชื่อผู้ติดต่อ</th>
                        <th style="width: 18%;">ตำแหน่ง</th>
                        <th style="width: 22%;">เบอร์โทร</th>
                        <th style="width: 18%;">Fax</th>
                        <th>หมายเหตุ</th>
                        <th style="width: 44px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contacts as $i => $ct)
                        <tr class="cf-contact-row">
                            <td><input type="text" class="form-control form-control-sm" maxlength="20"
                                    name="contacts[{{ $i }}][contactname]" value="{{ $ct->contactname }}"></td>
                            <td><input type="text" class="form-control form-control-sm" maxlength="20"
                                    name="contacts[{{ $i }}][position]" value="{{ $ct->position }}"></td>
                            <td><input type="text" class="form-control form-control-sm" maxlength="30"
                                    name="contacts[{{ $i }}][tel]" value="{{ $ct->tel }}"></td>
                            <td><input type="text" class="form-control form-control-sm" maxlength="20"
                                    name="contacts[{{ $i }}][fax]" value="{{ $ct->fax }}"></td>
                            <td><input type="text" class="form-control form-control-sm" maxlength="30"
                                    name="contacts[{{ $i }}][remark]" value="{{ $ct->remark }}"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-icon btn-label-danger cf-del-contact"
                                    title="ลบแถวนี้"><i class="ti ti-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="form-text small mt-2">
            ชื่อผู้ติดต่อเป็นคีย์ของตาราง — แถวที่ไม่กรอกชื่อจะไม่ถูกบันทึก และชื่อซ้ำกันเก็บได้แถวเดียว
        </div>
    </div>

    {{-- ═══ สถานที่ส่ง (ตาราง naddress) ═══ --}}
    <div class="cf-sec cf-sec-dv mb-3">
        <div class="cf-sec-title">
            <i class="ti ti-truck-delivery"></i> สถานที่ส่ง
            <button type="button" class="btn btn-sm btn-label-primary ms-auto" id="btn_add_dv">
                <i class="ti ti-plus me-1"></i>เพิ่มแถว
            </button>
        </div>

        <div id="dvpointList" class="row g-2">
            @foreach ($dvpoints as $dv)
                <div class="col-md-4 cf-dv-row">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" maxlength="20" name="dvpoints[]" value="{{ $dv }}">
                        <button type="button" class="btn btn-label-danger cf-del-dv" title="ลบ">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="form-text small mt-2">
            ตัวเลือกของช่อง "สถานที่ส่ง" ในฟอร์มใบสั่งซื้อ (ไม่เกิน 20 ตัวอักษรต่อรายการ)
        </div>
    </div>

    {{-- ═══ Blacklist (แสดงอย่างเดียว) ═══ --}}
    @if ($hasBlackInfo)
        <div class="cf-sec cf-sec-black mb-3">
            <div class="cf-sec-title"><i class="ti ti-ban"></i> ข้อมูล Blacklist (แก้ไขที่นี่ไม่ได้)</div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">สถานะ</label>
                    <input type="text" class="form-control" value="{{ $isBlack ? 'ติด Blacklist' : 'ปกติ' }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">วันที่</label>
                    <input type="text" class="form-control" readonly
                        value="{{ $v('blackdate') ? \Carbon\Carbon::parse($v('blackdate'))->format('d/m/Y') : '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">หมายเหตุ</label>
                    <input type="text" class="form-control" value="{{ $v('blackrem') }}" readonly>
                </div>
            </div>
            <div class="form-text small mt-2">
                สถานะเครดิต/Blacklist ถูกจัดการผ่านใบขออนุมัติเครดิต (ตาราง appvcredit) จึงไม่เปิดให้แก้ที่ฟอร์มนี้
            </div>
        </div>
    @endif

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="btn_customer_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึก
        </button>
    </div>
</form>
