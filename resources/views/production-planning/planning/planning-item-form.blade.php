{{-- สีพื้นหลังคอลัมน์ "วันที่ต้องการรับ" (custwant) ในตารางย่อย Semi/Pigment — ชุดสีเดียวกับหน้ารายการวางแผน --}}
<style>
    #table_semi .sp-custwant,
    #table_pigment .sp-custwant {
        background-color: #f8d7da !important;
        color: #842029 !important;
    }
    /* พื้นหลังช่องกรอกวันที่ต้องการรับ (custwant) แดง / วันที่วางแผนผลิต (inplan) น้ำเงิน
       flatpickr ใช้ altInput:true → ช่องที่ผู้ใช้เห็นคือ altInput (sibling ถัดจาก input ตัวจริง ไม่มี name)
       จึงต้องระบายทั้ง input ตัวจริง (ถูกซ่อน) และ altInput ที่ตามมา; ใช้ !important สู้ CSS ของธีม */
    #planning_item_form input[name="custwant"],
    #planning_item_form input[name="custwant"] + input {
        background-color: #f8d7da !important;
        color: #842029 !important;
    }
    #planning_item_form input[name="inplan"],
    #planning_item_form input[name="inplan"] + input {
        background-color: #cfe2ff !important;
        color: #084298 !important;
    }
</style>
@php
    // plan_type: ใช้ของ planning item ก่อน ถ้าไม่มีดึงจาก parent header
    $default_plan_type = $planning_item?->plan_type ?? $parent_header?->plan_type ?? '';
    // Item No.: แก้ไข = ค่าเดิมของ item / เพิ่มใหม่ = itemno ของ item ล่าสุดใน header เดียวกัน
    $default_itemno    = $planning_item ? ($planning_item->itemno ?? '') : ($last_itemno ?? '');
    // ค่า default สำหรับแถว semi/pigment ใหม่
    $default_mdate     = $parent_header?->mdate ? substr($parent_header->mdate, 0, 10) : '';
    $default_custno    = $parent_header?->company  ?? '';   // custno = company ของ header แม่
    // ตัวเลือก Company ดึงจากตาราง department (value = name) — fallback เป็นค่าเดิมถ้าไม่มีข้อมูลส่งมา
    $companies = isset($departments) ? $departments->pluck('name')->toArray() : ['CP', 'MB', 'DB', 'SPP'];

    // สีของ badge ตามสถานะ semi/pigment
    $spStatusCls = [
        'request'  => 'bg-label-warning',
        'approved' => 'bg-label-success',
        'reject'   => 'bg-label-danger',
    ];

    // ประวัติวันที่ส่งสินค้าเดิม (senddate_log) — เก็บคั่นด้วย comma เรียงตามลำดับการเปลี่ยน
    $senddate_logs = array_values(array_filter(array_map('trim', explode(',', $planning_item?->senddate_log ?? ''))));

    // เวลาที่เปลี่ยน senddate ล่าสุด (senddate_changed_at) — ค่าเดียว โชว์รวมด้านบน modal (ไม่ผูกรายบรรทัด)
    $senddate_changed_at = $planning_item?->senddate_changed_at;

    // ออเดอร์ (header แม่) ถูกปิดแล้วหรือยัง — ถ้าปิดแล้ว modal นี้จะเป็นโหมดอ่านอย่างเดียว (กดได้แค่ยกเลิก)
    $order_closed = ($parent_header?->end_order ?? 'N') === 'Y';
@endphp

<div class="modal-header" style="background-color: #3A8EBA; padding: 1rem 1.5rem;">
    <h5 class="modal-title text-white mb-0" style="font-size:1.5rem; font-weight:600;">
        <i class="ti ti-{{ $planning_item ? 'pencil' : 'plus' }} me-1"></i>
        {{ $planning_item ? 'แก้ไข Planning Item' : 'เพิ่ม Planning Item' }}
        @if($parent_header)
            <span class="badge bg-white text-primary ms-2 fw-normal" style="font-size:1.1rem">
                {{ $parent_header->planning_code }}
            </span>
        @endif
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    @if($order_closed)
    {{-- ออเดอร์ถูกปิดแล้ว → แจ้งเตือน + ทุกช่อง/ปุ่มจะถูก disable (ดูสคริปต์ท้ายไฟล์) --}}
    <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
        <i class="ti ti-lock me-2"></i>
        <span>ออเดอร์นี้ถูกปิดแล้ว (End Order) — ไม่สามารถแก้ไขได้ กดได้เฉพาะปุ่ม "ยกเลิก"</span>
    </div>
    @endif
    <form id="planning_item_form">
        <input type="hidden" name="planning_id"        value="{{ $planning_item?->id ?? '' }}">
        <input type="hidden" name="planning_header_id" value="{{ $planning_header_id ?? '' }}">

        {{-- ── ข้อมูลหลัก ── --}}
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">รหัสสินค้า (Item No.) <span class="text-danger">*</span></label>
                <input type="text" name="itemno"
                       value="{{ $default_itemno }}"
                       class="form-control" placeholder="กรอก Item No.">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">ล๊อค (Lot)</label>
                <input type="text" name="lot"
                       value="{{ $planning_item?->lot ?? '' }}"
                       class="form-control" placeholder="Lot">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Plan Type</label>
                {{-- auto-fill จาก planning_header.plan_type --}}
                <input type="text" name="plan_type"
                       value="{{ $default_plan_type }}"
                       class="form-control bg-light" readonly
                       placeholder="ประเภทแผน (จาก Header)">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">น้ำหนักสั่งตาม Order (Quantity)</label>
                {{-- เพิ่มใหม่: ดึง netqty จาก header มาตั้งต้น / แก้ไข: ใช้ค่าเดิมของ item --}}
                {{-- js-number-format: จัดรูปแบบหลักพันสดขณะพิมพ์ (เก็บค่าดิบ server ตัดจุลภาคเอง) --}}
                <input type="text" name="quantity" inputmode="decimal"
                       value="{{ $planning_item ? number_format((float) ($planning_item->quantity ?? 0), 2) : number_format((float) ($parent_header?->netqty ?? 0), 2) }}"
                       class="form-control js-number-format" placeholder="0.00">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">น้ำหนัก TP (Weight)</label>
                <input type="text" name="weight" inputmode="decimal"
                       value="{{ number_format((float) ($planning_item?->weight ?? 0), 2) }}"
                       class="form-control js-number-format" placeholder="0.00">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">น้ำหนักที่ผลิตได้ (Weight Produced)</label>
                <input type="text" name="weight_produced" inputmode="decimal"
                       value="{{ $planning_item && $planning_item->weight_produced !== null ? number_format((float) $planning_item->weight_produced, 2) : '' }}"
                       class="form-control js-number-format" placeholder="0.00">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">เลขที่ใบเบิกออกใบแดง (Red Bill)</label>
                {{-- เพิ่มใหม่: ดึง orderno จาก header มาตั้งต้น / แก้ไข: ใช้ค่าเดิมของ item --}}
                <input type="text" name="red_bill_code"
                       value="{{ $planning_item ? ($planning_item->red_bill_code ?? '') : ($parent_header?->orderno ?? '') }}"
                       class="form-control" placeholder="เลขที่ใบเบิกออกใบแดง">
            </div>
            {{-- เพิ่มใหม่: รอบการผลิต (คอลัมน์ cycles ของ tb_planning) --}}
            <div class="col-md-4 mb-3">
                <label class="form-label">รอบการผลิต (Cycles)</label>
                <input type="text" name="cycles" maxlength="25"
                       value="{{ $planning_item ? ($planning_item->cycles ?? '') : '' }}"
                       class="form-control" placeholder="รอบการผลิต">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">วันที่สั่ง (mdate)</label>
                <input type="text" name="mdate"
                    value="{{ $planning_item?->mdate ? substr($planning_item->mdate, 0, 10) : '' }}"
                    class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">วันที่ต้องการรับ (custwant)</label>
                {{-- data-original เก็บค่าเดิม (Y-m-d) ไว้เทียบว่ามีการแก้ไขไหม → ถ้าเปลี่ยนต้องยืนยันก่อนบันทึก --}}
                <input type="text" name="custwant"
                    value="{{ $planning_item?->custwant ? substr($planning_item->custwant, 0, 10) : '' }}"
                    data-original="{{ $planning_item?->custwant ? substr($planning_item->custwant, 0, 10) : '' }}"
                    class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label d-flex align-items-center justify-content-between">
                    <span>วันที่กำหนดทบทวน (senddate)</span>
                    @if($planning_item && count($senddate_logs))
                        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="btn_senddate_log">
                            <i class="ti ti-history me-1"></i>ประวัติ
                            <span class="badge bg-label-secondary ms-1">{{ count($senddate_logs) }}</span>
                        </button>
                    @endif
                </label>
                <input type="text" name="senddate"
                    value="{{ $planning_item?->senddate ? substr($planning_item->senddate, 0, 10) : '' }}"
                    class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">หมายเหตุ MK (Remark)</label>
                <input type="text" name="remark"
                       value="{{ $planning_item?->remark ?? '' }}"
                       class="form-control" placeholder="หมายเหตุ">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">ขาดวัตถุดิบ (Shortage Remark)</label>
                <input type="text" name="shortage_remark"
                       value="{{ $planning_item?->shortage_remark ?? '' }}"
                       class="form-control" placeholder="รายละเอียดวัตถุดิบที่ขาด">
            </div>
        </div>

        <div class="row mt-2"><hr /></div>


        <div class="row p-3 rounded" style="background-color: #e5f4ff; border: 1px dashed #3f50e2;">
            @php
                // แผนกปัจจุบันของ item: ใช้ของ item ก่อน ถ้าว่างจึง fallback ไปที่ header
                $current_company = $planning_item?->company ?: ($parent_header?->company ?? '');
            @endphp
            <div class="col-md-4 mb-3">
                <label class="form-label">แผนก (Company)</label>
                <select name="company" id="planning_item_company" class="form-select">
                    @foreach($companies as $company_name)
                        <option value="{{ $company_name }}" {{ $current_company === $company_name ? 'selected' : '' }}>
                            {{ $company_name }}
                        </option>
                    @endforeach
                    {{-- เผื่อค่าเดิมที่บันทึกไว้ไม่อยู่ในรายชื่อแผนกปัจจุบัน --}}
                    @if($current_company !== '' && !in_array($current_company, $companies))
                        <option value="{{ $current_company }}" selected>{{ $current_company }}</option>
                    @endif
                </select>
                <small class="text-muted">เปลี่ยนแผนกเพื่อย้ายรายการนี้ไปให้แผนกอื่นทำ (เครื่องจักร/สถานะจะโหลดใหม่)</small>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">เครื่องจักร (Machine No.)</label>
                <select name="machine_no" id="planning_item_machine" class="form-control">
                    <option value="">เลือกเครื่องจักร</option>
                    @foreach($machines as $machine)
                        {{-- value = รหัสเครื่อง (MBX) เท่านั้น ส่วนความเร็วรอบเป็นแค่ข้อความแสดงผล --}}
                        <option value="{{ $machine->MBX }}" {{ $planning_item && $planning_item->machine_no === $machine->MBX ? 'selected' : '' }}>
                            {{ $machine->displayLabel() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">สถานะการวางแผน (Planning Status)</label>
                @php $current_status = $planning_item?->planning_status ?? ''; @endphp
                <select name="planning_status" id="planning_item_status" class="form-select">
                    <option value="">เลือกสถานะ</option>
                    @foreach($planning_statuses as $status)
                        <option value="{{ $status->name }}" {{ $current_status === $status->name ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                    {{-- เผื่อค่าเดิมที่บันทึกไว้ไม่อยู่ในรายการของแผนกนี้แล้ว --}}
                    @if($current_status !== '' && !$planning_statuses->contains('name', $current_status))
                        <option value="{{ $current_status }}" selected>{{ $current_status }}</option>
                    @endif
                </select>
            </div>
        </div>

        {{-- <div class="row mt-2"><hr /></div> --}}

        <div class="row p-3 rounded mt-2" style="background-color: #fff1ca; border: 1px dashed #ffc107;">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">วันที่วางแผนผลิต (Inplan)</label>
                    <input type="text" name="inplan"
                        value="{{ $planning_item?->inplan ? substr($planning_item->inplan, 0, 10) : '' }}"
                        class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">กะการผลิต (Work Shift)</label>
                    @php $current_shift = $planning_item?->work_shift ?? ''; @endphp
                    <select name="work_shift" class="form-select">
                        <option value="">เลือกกะ</option>
                        @foreach(['A', 'B', 'C'] as $shift)
                            <option value="{{ $shift }}" {{ $current_shift === $shift ? 'selected' : '' }}>{{ $shift }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label d-flex align-items-center justify-content-between mb-1">
                        <span>พนักงานผู้รับผิดชอบ</span>
                        {{-- เพิ่มพนักงานใหม่ทันทีจากฟอร์มนี้ (เปิด modal ซ้อน ใช้ฟอร์มเดียวกับหน้าจัดการพนักงาน)
                             แสดงเฉพาะบัญชีที่มีสิทธิ์เมนู "พนักงาน" — เพราะ endpoint employee.edit/store ถูกกันสิทธิ์
                             ตาม namespace employee อยู่แล้ว (ไม่มีสิทธิ์กดแล้วจะได้ 403) --}}
                        @if(\App\Services\AccessControl::menuVisible('Employee'))
                            <button type="button" id="btn_add_employee_inline"
                                class="btn btn-sm btn-label-primary py-0 px-1 lh-1" title="เพิ่มพนักงานใหม่">
                                <i class="ti ti-plus"></i>
                            </button>
                        @endif
                    </label>
                    @php $current_empno = $planning_item?->empno ?? ''; @endphp
                    <select name="empno" id="planning_item_empno" class="form-select">
                        <option value="">เลือกพนักงาน</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->empno }}" {{ $current_empno === $e->empno ? 'selected' : '' }}>
                                {{ trim($e->empname.' '.$e->empsur) }}
                            </option>
                        @endforeach
                        {{-- fallback: พนักงานเดิมไม่อยู่ในลิสต์แผนกปัจจุบัน (ย้ายแผนก/ปิดใช้งาน) --}}
                        @if($current_empno !== '' && !$employees->contains('empno', $current_empno))
                            <option value="{{ $current_empno }}" selected>
                                {{ $selected_emp ? trim($selected_emp->empname.' '.$selected_emp->empsur) : $current_empno }} (เดิม)
                            </option>
                        @endif
                    </select>
                </div>
            </div>
        </div>

        {{-- <div class="row mt-2"><hr /></div> --}}

        {{-- ── การ์ดสีน้ำเงิน: สถานะวิธีการผลิต (บันทึกลง tb_planning_prod_method) ── --}}
        <div class="row p-3 rounded mt-2" style="background-color: rgb(211, 250, 160); border: 1px dashed #33cc05;">
            <div class="col-12 d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0 text-primary">
                    <i class="ti ti-clipboard-list me-1"></i>สถานะวิธีการผลิต
                </h6>
                <button type="button" id="btn_add_prod_method" class="btn btn-sm btn-primary">
                    <i class="ti ti-plus me-1"></i>เพิ่ม
                </button>
            </div>
            <div class="col-12">
                {{-- หัวคอลัมน์ (แสดงครั้งเดียว) --}}
                <div class="row g-2 fw-semibold small text-muted mb-1 d-none d-md-flex">
                    <div class="col-md-4">วิธีการผลิต</div>
                    <div class="col-md-3">วันที่</div>
                    <div class="col-md-2">เวลาเริ่ม</div>
                    <div class="col-md-2">เวลาที่เสร็จ</div>
                    <div class="col-md-1"></div>
                </div>
                @php
                    // สร้างตัวเลือก <option> ของวิธีการผลิต (ใช้ซ้ำในทุกแถวที่ render ฝั่ง server)
                    $prodMethodOptions = function ($selected = null) use ($prod_methods) {
                        $out = '<option value="">เลือกวิธีการผลิต</option>';
                        foreach ($prod_methods as $pm) {
                            $sel = ((string) $selected === (string) $pm->id) ? 'selected' : '';
                            $out .= '<option value="'.$pm->id.'" '.$sel.'>'.e($pm->name).'</option>';
                        }
                        return $out;
                    };
                @endphp
                <div id="prod_method_rows">
                    @forelse($prod_method_rows as $row)
                        <div class="row g-2 align-items-center mb-2 prod-method-row">
                            <div class="col-md-4">
                                <select name="prod_method_id[]" class="form-select form-select-sm">{!! $prodMethodOptions($row->prod_method_id) !!}</select>
                            </div>
                            <div class="col-md-3"><input type="text" name="prod_method_date[]" class="form-control form-control-sm flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป" value="{{ $row->work_date ? substr($row->work_date, 0, 10) : '' }}"></div>
                            <div class="col-md-2"><input type="time" name="prod_method_start[]" class="form-control form-control-sm" value="{{ $row->start_time ? substr($row->start_time, 0, 5) : '' }}"></div>
                            <div class="col-md-2"><input type="time" name="prod_method_end[]" class="form-control form-control-sm" value="{{ $row->end_time ? substr($row->end_time, 0, 5) : '' }}"></div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-outline-danger btn_remove_prod_method" title="ลบ">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="row g-2 align-items-center mb-2 prod-method-row">
                            <div class="col-md-4">
                                <select name="prod_method_id[]" class="form-select form-select-sm">{!! $prodMethodOptions() !!}</select>
                            </div>
                            <div class="col-md-3"><input type="text" name="prod_method_date[]" class="form-control form-control-sm flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป"></div>
                            <div class="col-md-2"><input type="time" name="prod_method_start[]" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><input type="time" name="prod_method_end[]" class="form-control form-control-sm"></div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-outline-danger btn_remove_prod_method" title="ลบ">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- <div class="row mt-2"><hr /></div> --}}

        <div class="row p-3 rounded mt-2" style="background-color: rgb(241, 211, 250);  border: 1px dashed #9e05bd;">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">วันที่เริ่มผลิต (Start Date)</label>
                    <input type="text" name="start_date"
                        value="{{ $planning_item?->start_date ? substr($planning_item->start_date, 0, 10) : '' }}"
                        class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">เวลาที่เริ่มผลิต (Start Time)</label>
                    <input type="time" name="start_time"
                        value="{{ $planning_item?->start_time ? substr($planning_item->start_time, 0, 5) : '' }}"
                        class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">วันที่ผลิตเสร็จ (End Date)</label>
                    <input type="text" name="end_date"
                        value="{{ $planning_item?->end_date ? substr($planning_item->end_date, 0, 10) : '' }}"
                        class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">เวลาที่ผลิตเสร็จ (End Time)</label>
                    <input type="time" name="end_time"
                        value="{{ $planning_item?->end_time ? substr($planning_item->end_time, 0, 5) : '' }}"
                        class="form-control">
                </div>
            </div>
        </div>

        {{-- <div class="row my-2"><hr /></div> --}}

        <div class="row p-3 rounded mt-2" >
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">วันที่ส่ง Qc (QC Date)</label>
                    <input type="text" name="qc_date"
                        value="{{ $planning_item?->qc_date ? substr($planning_item->qc_date, 0, 10) : '' }}"
                        class="form-control flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">เวลาที่ส่ง Qc (QC Time)</label>
                    <input type="time" name="qc_time"
                        value="{{ $planning_item?->qc_time ?? '' }}"
                        class="form-control" placeholder="HH:MM">
                </div>
                <div class="col-md-4 mb-3">
                <label class="form-label">สถานะ Qc (QC Status) </label>
                    <select name="qc_status" class="form-select">
                        <option value="">เลือกสถานะ</option>
                        <option value="PASSED" @if($planning_item?->qc_status === 'PASSED') selected @endif>ผ่าน</option>
                        <option value="FAILED" @if($planning_item?->qc_status === 'FAILED') selected @endif>ไม่ผ่าน</option>
                        <option value="PENDINGREVISION" @if($planning_item?->qc_status === 'PENDINGREVISION') selected @endif>รอสูตรปรับแก้</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row my-2"><hr /></div>

        <div class="row p-3 rounded" style="background-color: #eaffd9; border: 1px dashed #04ac2e;">
            <div class="row">
                <div class="col-md-6 mb-3">
                    @php
                        $item_end_job     = ($planning_item?->end_job ?? 'N') === 'Y';
                        $semi_jobs_done   = $item_semi_jobs_done ?? true;
                        // ปิดใช้งานเฉพาะตอน "ยังไม่จบงาน และงาน Semi ยังไม่ครบ" (จบงานอยู่แล้วยังปลดได้เสมอ)
                        $end_job_disabled = !$item_end_job && !$semi_jobs_done;
                    @endphp
                    <div class="p-2 rounded mt-4" style="background-color: #f8adad; border: 1px dashed #f72020;">
                        <div class="form-check">
                            {{-- hidden ส่งค่า N เมื่อไม่ติ๊ก (checkbox N มาก่อน, ค่า Y จะ override เมื่อติ๊ก) --}}
                            <input type="hidden" name="end_job" value="N">
                            <input type="checkbox" class="form-check-input" id="planning_item_end_job"
                                name="end_job" value="Y"
                                {{ $item_end_job ? 'checked' : '' }}
                                {{ $end_job_disabled ? 'disabled' : '' }}>
                            <label class="form-check-label" for="planning_item_end_job">จบงาน (End Job)</label>
                        </div>
                        @if($end_job_disabled)
                            <div class="form-text fw-bold mt-1" style="color: #7a0000;">
                                <i class="ti ti-alert-triangle me-1"></i>ต้องปิดออเดอร์ (End Order) ของแผน Semi ให้ครบทุกใบก่อน
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">วันเวลาที่บรรจุเสร็จ (Packing Datetime)</label>
                    {{-- flatpickr แบบมีเวลา: ช่องที่เห็นแสดง d/m/Y H:i (dd/mm/yyyy + เวลา) แต่ค่าจริงส่ง Y-m-d H:i --}}
                    <input type="text" name="packing_datetie"
                        value="{{ $planning_item?->packing_datetie ? \Carbon\Carbon::parse($planning_item->packing_datetie)->format('Y-m-d H:i') : '' }}"
                        class="form-control flatpickr-datetime" autocomplete="off" placeholder="วว/ดด/ปปปป ชช:นน">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">น้ำหนักบรรจุได้ (Weight Packing)</label>
                    <input type="text" name="weight_packing" inputmode="decimal"
                        value="{{ $planning_item && $planning_item->weight_packing !== null ? number_format((float) $planning_item->weight_packing, 2) : '' }}"
                        class="form-control js-number-format" placeholder="0.00">
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">หมายเหตุการบรรจุ (Pack Remark)</label>
                    <input type="text" name="pack_remark"
                        value="{{ $planning_item?->pack_remark ?? '' }}"
                        class="form-control" placeholder="หมายเหตุการบรรจุ">
                </div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Semi ── --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h6 class="mb-0 text-primary d-inline">
                    <i class="ti ti-box me-1"></i>Semi
                </h6>
                @if($parent_orderno)
                <small class="text-muted ms-2">
                    อ้างอิง Order: <strong>{{ $parent_orderno }}</strong>
                </small>
                @endif
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_semi_row">
                <i class="ti ti-plus me-1"></i>เพิ่ม Semi
            </button>
        </div>
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered align-middle" id="table_semi">
                <thead class="table-primary">
                    <tr>
                        <th class="text-center" style="width:36px">#</th>
                        <th style="min-width:110px">Company</th>
                        <th style="min-width:120px">วันที่สั่ง</th>
                        <th style="min-width:120px" class="sp-custwant">วันที่ต้องการรับ</th>
                        <th style="min-width:100px">Cust No.</th>
                        <th style="min-width:130px">Item No.</th>
                        <th style="min-width:80px">Quantity</th>
                        <th class="text-center" style="min-width:90px">สถานะ</th>
                        <th class="text-center" style="min-width:90px">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="tbody_semi">
                    @forelse($semi_list as $i => $row)
                        @php
                            $st       = $row['status'] ?? 'request';
                            $isLocked = $st !== 'request';
                            $stCls    = $spStatusCls[$st] ?? 'bg-label-secondary';
                            $stLabel  = $row['status_label'] ?? 'รออนุมัติ';
                        @endphp
                        @if($isLocked)
                        <tr class="locked-row table-light">
                            <td class="text-center"><span class="row-num">{{ $i + 1 }}</span></td>
                            <td>{{ $row['company'] ?? '-' }}</td>
                            <td>{{ !empty($row['mdate'])    ? \Carbon\Carbon::parse($row['mdate'])->format('d/m/Y')    : '-' }}</td>
                            <td class="sp-custwant">{{ !empty($row['custwant']) ? \Carbon\Carbon::parse($row['custwant'])->format('d/m/Y') : '-' }}</td>
                            <td>{{ $row['custno']   ?? '-' }}</td>
                            <td>{{ $row['itemno']   ?? '-' }}</td>
                            <td>{{ $row['weight_request'] ?? '-' }}</td>
                            <td class="text-center"><span class="badge {{ $stCls }}">{{ $stLabel }}</span></td>
                            {{-- จัดการ: อนุมัติแล้ว → ถ้าสร้างแผนแล้วแสดงสถานะการผลิต (คลิกดูต้นไม้แผน), ถ้ายังไม่สร้างแสดง "ยังไม่สร้างแผน" --}}
                            <td class="text-center">
                                @if($st === 'approved' && !empty($row['result_planning_id']))
                                    <button type="button" class="btn btn-sm btn-label-primary btn_view_plan_tree"
                                            data-sp-id="{{ $row['id'] }}"
                                            title="ดูสถานะแผนการผลิตที่สร้างจาก Semi นี้">
                                        <i class="ti ti-sitemap me-1"></i>{{ $row['plan_status'] ?: 'ดูแผนการผลิต' }}
                                    </button>
                                    {{-- สถานะปิดออเดอร์ของแผน Semi นี้ (end_order ของ tb_planning_header) — เกณฑ์ปิด end_job ใบแม่ --}}
                                    <div class="mt-1">
                                        @if(($row['plan_end_order'] ?? 'N') === 'Y')
                                            <span class="badge bg-label-success">ปิดออเดอร์</span>
                                        @else
                                            <span class="badge bg-label-warning">ยังไม่ปิดออเดอร์</span>
                                        @endif
                                    </div>
                                @elseif($st === 'approved')
                                    <span class="badge bg-label-secondary" title="อนุมัติแล้ว แต่ยังไม่ได้สร้างแผนการผลิต">
                                        <i class="ti ti-clock me-1"></i>ยังไม่สร้างแผน
                                    </span>
                                @else
                                    <i class="ti ti-lock text-muted" title="ดำเนินการแล้ว แก้ไขไม่ได้"></i>
                                @endif
                            </td>
                        </tr>
                        @else
                        @include('production-planning.planning.partials.sp-row', ['row' => $row, 'i' => $i, 'default_custno' => $default_custno])
                        @endif
                    @empty
                    <tr class="empty-row">
                        <td colspan="9" class="text-center text-muted py-2">ยังไม่มีรายการ Semi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── Pigment: ยกเลิกชั่วคราว 25/08/2569 — ครอบด้วย @if(false) เพื่อซ่อนทั้งส่วน (เส้นคั่น + ตาราง)
             เปิดคืน: เปลี่ยน @if(false) เป็น @if(true) หรือถอด @if(false)/@endif ออก
             backend (tb_pigment / PigmentController / routes) ไม่ถูกแตะ --}}
        @if(false)
        <hr class="my-3">

        {{-- ── Pigment ── --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h6 class="mb-0 text-success d-inline">
                    <i class="ti ti-color-swatch me-1"></i>Pigment
                </h6>
                @if($parent_orderno)
                <small class="text-muted ms-2">
                    อ้างอิง Order: <strong>{{ $parent_orderno }}</strong>
                </small>
                @endif
            </div>
            <button type="button" class="btn btn-sm btn-outline-success" id="btn_add_pigment_row">
                <i class="ti ti-plus me-1"></i>เพิ่ม Pigment
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle" id="table_pigment">
                <thead class="table-success">
                    <tr>
                        <th class="text-center" style="width:36px">#</th>
                        <th style="min-width:120px">วันที่สั่ง</th>
                        <th style="min-width:120px" class="sp-custwant">วันที่ต้องการรับ</th>
                        <th style="min-width:100px">Cust No.</th>
                        <th style="min-width:130px">Item No.</th>
                        <th style="min-width:80px">Quantity</th>
                        <th class="text-center" style="min-width:90px">สถานะ</th>
                        <th class="text-center" style="min-width:90px">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="tbody_pigment">
                    @forelse($pigment_list as $i => $row)
                        @php
                            $st       = $row['status'] ?? 'request';
                            $isLocked = $st !== 'request';
                            $stCls    = $spStatusCls[$st] ?? 'bg-label-secondary';
                            $stLabel  = $row['status_label'] ?? 'รออนุมัติ';
                        @endphp
                        @if($isLocked)
                        <tr class="locked-row table-light">
                            <td class="text-center"><span class="row-num">{{ $i + 1 }}</span></td>
                            <td>{{ !empty($row['mdate'])    ? \Carbon\Carbon::parse($row['mdate'])->format('d/m/Y')    : '-' }}</td>
                            <td class="sp-custwant">{{ !empty($row['custwant']) ? \Carbon\Carbon::parse($row['custwant'])->format('d/m/Y') : '-' }}</td>
                            <td>{{ $row['custno']   ?? '-' }}</td>
                            <td>{{ $row['itemno']   ?? '-' }}</td>
                            <td>{{ $row['weight_request'] ?? '-' }}</td>
                            <td class="text-center"><span class="badge {{ $stCls }}">{{ $stLabel }}</span></td>
                            <td class="text-center"><i class="ti ti-lock text-muted" title="ดำเนินการแล้ว แก้ไขไม่ได้"></i></td>
                        </tr>
                        @else
                        @include('production-planning.planning.partials.pigment-row', ['row' => $row, 'i' => $i, 'default_custno' => $default_custno])
                        @endif
                    @empty
                    <tr class="empty-row">
                        <td colspan="8" class="text-center text-muted py-2">ยังไม่มีรายการ Pigment</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif {{-- /Pigment (ยกเลิกชั่วคราว 25/08/2569) --}}

    </form>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="ti ti-x me-1"></i>ยกเลิก
    </button>
    <button type="button" class="btn btn-primary" id="btn_save_planning_item">
        <i class="ti ti-device-floppy me-1"></i>บันทึก
    </button>
</div>

{{-- ── Modal ประวัติวันที่กำหนดทบทวน (senddate_log) ── --}}
<div class="modal fade" id="senddate_log_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:300px;">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#3A8EBA; padding:.75rem 1.25rem;">
                <h6 class="modal-title text-white mb-0">
                    <i class="ti ti-history me-1"></i>ประวัติวันที่กำหนดทบทวน
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-1 small text-muted">
                    วันที่กำหนดทบทวนปัจจุบัน:
                    <strong>{{ $planning_item?->senddate ? \Carbon\Carbon::parse($planning_item->senddate)->format('d/m/Y') : '-' }}</strong>
                </div>
                @if($senddate_changed_at)
                    <div class="mb-2 small text-muted">
                        <i class="ti ti-clock-edit me-1"></i>แก้ไขล่าสุด:
                        <strong>{{ \Carbon\Carbon::parse($senddate_changed_at)->format('d/m/Y H:i') }}</strong>
                    </div>
                @endif
                @if(count($senddate_logs))
                    <ul class="list-group list-group-flush">
                        @foreach($senddate_logs as $i => $log_date)
                            <li class="list-group-item d-flex align-items-center px-0">
                                <span class="badge bg-label-secondary me-2">{{ $i + 1 }}</span>
                                <span>{{ $log_date !== '' ? \Carbon\Carbon::parse($log_date)->format('d/m/Y') : '-' }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-2 small text-muted">
                        <i class="ti ti-info-circle me-1"></i>เรียงตามลำดับการเปลี่ยนแปลง (เก่า → ใหม่)
                    </div>
                @else
                    <div class="text-center text-muted py-3">ยังไม่มีประวัติการเปลี่ยนแปลง</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Modal เพิ่ม Semi / Pigment ── --}}
{{-- backdrop=static + keyboard=false: ปิด modal ได้เฉพาะปุ่ม ยกเลิก / กากบาท (กันคลิกนอก+ESC) --}}
<div class="modal fade" id="sp_entry_modal" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#3A8EBA; padding:.75rem 1.25rem;">
                <h6 class="modal-title text-white mb-0">
                    <i class="ti ti-plus me-1"></i><span id="sp_entry_title">เพิ่ม Semi</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="sp_entry_target" value="">
                @include('production-planning.semi-pigment.partials.entry-fields', [
                    'prefix'         => 'sp',
                    'companies'      => $companies,
                    'custnoReadonly' => true,
                ])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>ยกเลิก
                </button>
                <button type="button" class="btn btn-primary" id="btn_sp_entry_save">
                    <i class="ti ti-device-floppy me-1"></i>บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal เพิ่ม / แก้ไข Pigment: ยกเลิกชั่วคราว 25/08/2569 (ครอบด้วย @if(false)) ── --}}
@if(false)
{{-- ── Modal เพิ่ม / แก้ไข Pigment (แยกจาก Semi, อ้างอิงตาราง tb_pigment) ── --}}
{{-- backdrop=static + keyboard=false: ปิด modal ได้เฉพาะปุ่ม ยกเลิก / กากบาท (กันคลิกนอก+ESC) --}}
<div class="modal fade" id="pigment_entry_modal" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#28a745; padding:.75rem 1.25rem;">
                <h6 class="modal-title text-white mb-0">
                    <i class="ti ti-color-swatch me-1"></i><span id="pigment_entry_title">เพิ่ม Pigment</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @include('production-planning.pigment.partials.entry-fields', [
                    'prefix'         => 'pg',
                    'custnoReadonly' => true,
                ])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>ยกเลิก
                </button>
                <button type="button" class="btn btn-success" id="btn_pigment_entry_save">
                    <i class="ti ti-device-floppy me-1"></i>บันทึก
                </button>
            </div>
        </div>
    </div>
</div>
@endif {{-- /Modal Pigment (ยกเลิกชั่วคราว 25/08/2569) --}}

{{-- ── Modal แสดงสถานะแผนการผลิตที่สร้างจาก Semi (recursive tree) ── --}}
<div class="modal fade" id="plan_tree_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#3A8EBA; padding:.75rem 1.25rem;">
                <h6 class="modal-title text-white mb-0">
                    <i class="ti ti-sitemap me-1"></i>สถานะแผนการผลิตจาก Semi
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="plan_tree_body">
                <div class="text-center text-muted py-4">
                    <div class="spinner-border spinner-border-sm me-2"></div>กำลังโหลด...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ── helper กลางสำหรับ flatpickr (ใช้ร่วมทั้ง IIFE ของ Semi และ Pigment) ──
// altInput: ช่องที่เห็นแสดง d/m/Y เหมือนกันทุกเครื่อง แต่ค่าจริงใน input (ตาม name/id) ยังเป็น Y-m-d
// → serialize/.val() ส่ง Y-m-d ผ่าน validation 'nullable|date' ของ server เหมือนเดิม ไม่ต้องแก้ PHP
// static: true → ฝังปฏิทินไว้ติด input ในฟอร์ม (ไม่ append ไป <body>) — กันปฏิทินพลิกเด้งขึ้นบน
//   เวลาช่องอยู่ค่อนล่างของ modal (flatpickr คำนวณตำแหน่งจาก viewport แล้วพลิกขึ้น)
window.wpFpDateOptions = {
    dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', allowInput: true, disableMobile: true, static: true
};
// ── option สำหรับช่องวันที่ "แบบมีเวลา" (เช่น วันเวลาที่บรรจุเสร็จ) ──
// ช่องที่เห็นแสดง d/m/Y H:i แต่ค่าจริงเป็น Y-m-d H:i (Carbon ฝั่ง server parse ได้เหมือนเดิม)
// static: true → ฝังปฏิทินไว้ติด input ในฟอร์ม (ไม่ append ไป <body>)
//   กันอาการหน้าจอ/modal เลื่อนขึ้นเวลาปรับเวลา (flatpickr re-position/re-focus ชนกับ Bootstrap modal)
window.wpFpDateTimeOptions = {
    enableTime: true, time_24hr: true,
    dateFormat: 'Y-m-d H:i', altInput: true, altFormat: 'd/m/Y H:i',
    allowInput: true, disableMobile: true, static: true
};
// ผูก flatpickr ให้ทุก .flatpickr-date ภายใน scope (กัน init ซ้ำด้วย _flatpickr)
window.wpInitDateFields = function (scope) {
    $(scope).find('.flatpickr-date').addBack('.flatpickr-date').each(function () {
        if (!this._flatpickr) flatpickr(this, window.wpFpDateOptions);
    });
};
// ตั้งค่าวันที่ (รับ Y-m-d) ให้อัปเดตทั้งช่องที่แสดง (d/m/Y) และค่าจริง — ว่าง = เคลียร์
window.wpSetDateField = function (sel, val) {
    var el = $(sel)[0];
    if (!el) return;
    if (el._flatpickr) {
        if (val) el._flatpickr.setDate(String(val).substr(0, 10), false, 'Y-m-d');
        else el._flatpickr.clear();
    } else {
        el.value = val ? String(val).substr(0, 10) : '';
    }
};

(function () {
    // ── ค่า default จาก planning_header แม่ ──
    var DEFAULT_MDATE  = '{{ $default_mdate }}';
    var DEFAULT_CUSTNO = '{{ $default_custno }}';

    // ── ค่าสำหรับบันทึก Semi/Pigment ลงฐานข้อมูลทันทีผ่าน modal ──
    var CSRF             = '{{ csrf_token() }}';
    var PLANNING_ID      = '{{ $planning_item?->id ?? '' }}';
    var URL_ENTRY_STORE  = '{{ route('production.semipigment.entry.store') }}';
    var URL_ENTRY_UPDATE = '{{ route('production.semipigment.entry.update') }}';
    var URL_ENTRY_DELETE = '{{ route('production.semipigment.entry.delete') }}';
    var URL_DEPT_OPTIONS = '{{ route('production.planning.dept-options') }}';

    // ── flatpickr (helper กลางนิยามไว้ก่อน IIFE) — scope เฉพาะฟอร์ม/modal เพื่อไม่ชนกับหน้า index ──
    var initDateFields = window.wpInitDateFields;
    var setDateField   = window.wpSetDateField;
    initDateFields('#planning_item_form'); // ช่องวันที่ในฟอร์มหลัก
    // ช่องวันที่แบบมีเวลา (packing_datetie) — ผูก flatpickr พร้อมเวลา
    $('#planning_item_form').find('.flatpickr-datetime').each(function () {
        if (!this._flatpickr) flatpickr(this, window.wpFpDateTimeOptions);
    });

    // ── เปลี่ยนแผนก (Company) → โหลดเครื่องจักร/สถานะของแผนกใหม่ แล้วล้างค่าที่เลือกไว้ ──
    // (ค่า machine_no/planning_status เดิมเป็นของแผนกเก่า จึงต้องเลือกใหม่)
    $('#planning_item_company').on('change', function () {
        var company = $(this).val() || '';
        var $machine = $('#planning_item_machine');
        var $status  = $('#planning_item_status');
        var $emp     = $('#planning_item_empno');

        $machine.prop('disabled', true);
        $status.prop('disabled', true);
        $emp.prop('disabled', true);

        $.ajax({
            type: 'GET', url: URL_DEPT_OPTIONS, dataType: 'json',
            data: { company: company },
            success: function (res) {
                var machineOpts = '<option value="">เลือกเครื่องจักร</option>';
                (res.machines || []).forEach(function (m) {
                    // m = { code, label } — บันทึกเฉพาะ code ส่วน label มีความเร็วรอบต่อท้าย
                    machineOpts += '<option value="' + esc(m.code) + '">' + esc(m.label) + '</option>';
                });
                $machine.html(machineOpts).val('');

                var statusOpts = '<option value="">เลือกสถานะ</option>';
                (res.statuses || []).forEach(function (s) {
                    statusOpts += '<option value="' + esc(s) + '">' + esc(s) + '</option>';
                });
                $status.html(statusOpts).val('');

                // พนักงานผู้รับผิดชอบของแผนกใหม่ — ล้างค่าที่เลือกไว้ (เป็นของแผนกเก่า)
                var empOpts = '<option value="">เลือกพนักงาน</option>';
                (res.employees || []).forEach(function (e) {
                    empOpts += '<option value="' + esc(e.empno) + '">' + esc(e.name) + '</option>';
                });
                $emp.html(empOpts).val('');
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'โหลดข้อมูลแผนกไม่สำเร็จ กรุณาลองใหม่' });
            },
            complete: function () {
                $machine.prop('disabled', false);
                $status.prop('disabled', false);
                $emp.prop('disabled', false);
            }
        });
    });

    // ── การ์ดสีน้ำเงิน "สถานะวิธีการผลิต": เพิ่ม/ลบแถว (บันทึกลง tb_planning_prod_method) ──
    var PROD_METHOD_OPTIONS = @json($prod_methods);
    function prodMethodSelectHtml() {
        var opts = '<option value="">เลือกวิธีการผลิต</option>';
        (PROD_METHOD_OPTIONS || []).forEach(function (pm) {
            opts += '<option value="' + esc(pm.id) + '">' + esc(pm.name) + '</option>';
        });
        return '<select name="prod_method_id[]" class="form-select form-select-sm">' + opts + '</select>';
    }
    function prodMethodRowHtml() {
        return '<div class="row g-2 align-items-center mb-2 prod-method-row">'
            + '<div class="col-md-4">' + prodMethodSelectHtml() + '</div>'
            + '<div class="col-md-3"><input type="text" name="prod_method_date[]" class="form-control form-control-sm flatpickr-date" autocomplete="off" placeholder="วว/ดด/ปปปป"></div>'
            + '<div class="col-md-2"><input type="time" name="prod_method_start[]" class="form-control form-control-sm"></div>'
            + '<div class="col-md-2"><input type="time" name="prod_method_end[]" class="form-control form-control-sm"></div>'
            + '<div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger btn_remove_prod_method" title="ลบ"><i class="ti ti-trash"></i></button></div>'
            + '</div>';
    }
    $('#btn_add_prod_method').on('click', function () {
        var $row = $(prodMethodRowHtml()).appendTo('#prod_method_rows');
        initDateFields($row); // ผูก flatpickr ให้ช่องวันที่ของแถวใหม่
    });
    // ลบแถว; ถ้าเหลือแถวเดียวให้ล้างค่าแทนการลบ (คงไว้อย่างน้อย 1 แถว)
    $('#prod_method_rows').on('click', '.btn_remove_prod_method', function () {
        var $rows = $('#prod_method_rows .prod-method-row');
        if ($rows.length <= 1) {
            // ช่องวันที่เป็น flatpickr → เคลียร์ผ่าน instance เพื่อล้างทั้งช่องที่แสดงและค่าจริง
            $(this).closest('.prod-method-row').find('input').each(function () {
                if (this._flatpickr) this._flatpickr.clear();
                else this.value = '';
            });
        } else {
            $(this).closest('.prod-method-row').remove();
        }
    });

    // ── ติ๊ก "จบงาน (End Job)" → เติมวันเวลาที่บรรจุเสร็จเป็นเวลาปัจจุบัน (เฉพาะเมื่อช่องยังว่าง) ──
    // ช่อง packing_datetie เป็น flatpickr (altInput) → ต้องตั้ง/ล้างผ่าน instance เพื่ออัปเดตทั้งช่องที่แสดงและค่าจริง
    $('#planning_item_end_job').on('change', function () {
        var packingEl = $('input[name="packing_datetie"]')[0];
        if (!packingEl) return;
        var fp = packingEl._flatpickr;
        if (this.checked) {
            // ติ๊ก → เติมเวลาปัจจุบัน (เฉพาะเมื่อช่องยังว่าง)
            var current = (fp ? fp.input.value : packingEl.value) || '';
            if (current.trim() === '') {
                if (fp) fp.setDate(new Date(), true); // true = trigger change ให้ altInput อัปเดต
                else packingEl.value = '';
            }
        } else {
            // ปลดติ๊ก → ล้างค่าช่องบรรจุ
            if (fp) fp.clear();
            else packingEl.value = '';
        }
    });

    function toast(icon, text) {
        Swal.fire({ icon: icon, title: text, toast: true, position: 'top-end',
            timer: 1800, showConfirmButton: false });
    }

    function renumber(tbodyId) {
        $('#' + tbodyId + ' tr:not(.empty-row)').each(function (i) {
            $(this).find('.row-num').text(i + 1);
        });
    }

    // escape ค่าให้ปลอดภัยเมื่อนำไปแสดงเป็น HTML / attribute
    function esc(v) {
        return $('<div>').text(v == null ? '' : v).html();
    }

    // ฟิลด์ที่เก็บไว้ใน hidden input (ไม่แสดงเป็นคอลัมน์ในตาราง)
    var HIDDEN_FIELDS = ['semi_code', 'primary_color', 'balance', 'lot_no',
        'retrospective', 'increase_production', 'weight_production', 'red_bill_code'];

    // แปลงวันที่ YYYY-MM-DD → DD/MM/YYYY สำหรับแสดงผล (ค่าจริงยังเก็บใน hidden input เป็น YYYY-MM-DD)
    function fmtDate(s) {
        s = (s || '').substr(0, 10);
        if (!s) return '';
        var p = s.split('-');
        return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : s;
    }

    // แถวแสดงผล (อ่านอย่างเดียว) + เก็บค่าจริงไว้ใน hidden input เพื่อให้ readRow อ่านได้
    function displayRow(d) {
        function cell(field, text) {
            // คอลัมน์ custwant ให้พื้นหลังแดงเหมือนหน้ารายการวางแผน
            var cls = field === 'custwant' ? ' class="sp-custwant"' : '';
            return '<td' + cls + '>' + (text === '' ? '-' : esc(text)) +
                '<input type="hidden" data-field="' + field + '" value="' + esc(d[field] || '') + '"></td>';
        }
        function hid(field) {
            return '<input type="hidden" data-field="' + field + '" value="' + esc(d[field] || '') + '">';
        }
        var hiddenInputs = HIDDEN_FIELDS.map(hid).join('');

        return '<tr data-id="' + esc(d.id || '') + '">' +
            '<td class="text-center"><span class="row-num">1</span>' + hiddenInputs + '</td>' +
            cell('company',  d.company  || '') +
            cell('mdate',    fmtDate(d.mdate)) +
            cell('custwant', fmtDate(d.custwant)) +
            cell('custno',   d.custno   || '') +
            cell('itemno',   d.itemno   || '') +
            cell('weight_request', d.weight_request || '') +
            '<td class="text-center"><span class="badge bg-label-warning">รออนุมัติ</span></td>' +
            '<td class="text-center text-nowrap">' +
                '<button type="button" class="btn btn-sm btn-warning btn-icon btn_edit_row" title="แก้ไข"><i class="ti ti-pencil ti-sm"></i></button> ' +
                '<button type="button" class="btn btn-sm btn-danger btn-icon btn_remove_row" title="ลบ"><i class="ti ti-trash ti-sm"></i></button>' +
            '</td>' +
            '</tr>';
    }

    function addRow(tbodyId, data) {
        var $tbody = $('#' + tbodyId);
        $tbody.find('.empty-row').remove();
        $tbody.append(displayRow(data));
        renumber(tbodyId);
    }

    function checkEmpty(tbodyId, label) {
        if ($('#' + tbodyId + ' tr').length === 0) {
            $('#' + tbodyId).append(
                '<tr class="empty-row"><td colspan="9" class="text-center text-muted py-2">ยังไม่มีรายการ ' + label + '</td></tr>'
            );
        }
    }

    // ทุกฟิลด์ของแถว semi/pigment (ทั้งที่แสดงผลและ hidden)
    var ALL_FIELDS = ['company', 'mdate', 'custwant', 'custno', 'itemno', 'weight_request'].concat(HIDDEN_FIELDS);

    // อ่านค่าทุกฟิลด์จากแถว (อ่านจาก input/select ที่มี data-field)
    function readRow($tr) {
        var d = {};
        ALL_FIELDS.forEach(function (f) {
            d[f] = $tr.find('[data-field="' + f + '"]').val() || '';
        });
        return d;
    }

    // ── Modal เพิ่ม Semi / Pigment ──
    // ย้าย modal ออกไปเป็น sibling ที่ body (กัน stacked modal ซ้อนใน .modal-content แล้วเพี้ยน)
    $('body').children('#sp_entry_modal').remove();
    var $entryModal = $('#sp_entry_modal').appendTo('body');
    initDateFields($entryModal); // ผูก flatpickr ช่องวันที่ใน modal Semi (ย้ายไป body แล้ว)

    // แถวที่กำลังแก้ไขผ่าน modal (null = โหมดเพิ่มใหม่)
    var $editingRow = null;

    // ── น้ำหนักที่จะผลิต = น้ำหนักที่จะใช้ + ผลิตเพิ่ม ──
    // คำนวณอัตโนมัติเมื่อแก้ "น้ำหนักที่จะใช้/ผลิตเพิ่ม"
    // แต่ถ้าผู้ใช้พิมพ์แก้ช่อง "น้ำหนักที่จะผลิต" เอง จะล็อกค่านั้นไว้ (ไม่ถูกคำนวณทับ)
    // จนกว่าจะแก้ "น้ำหนักที่จะใช้/ผลิตเพิ่ม" อีกครั้ง จึงกลับมาคำนวณทับ
    var spProdManual = false;

    function spNum(v) {
        v = parseFloat(v);
        return isNaN(v) ? 0 : v;
    }

    function spRound(v) {
        return Math.round(v * 100) / 100;
    }

    function recalcWeightProduction() {
        if (spProdManual) return; // ผู้ใช้แก้เอง → คงค่าไว้ ไม่เขียนทับ
        var req = ($('#sp_weight_request').val() || '').trim();
        var inc = ($('#sp_increase_production').val() || '').trim();
        if (req === '' && inc === '') {
            $('#sp_weight_production').val('');
            return;
        }
        $('#sp_weight_production').val(spRound(spNum(req) + spNum(inc)));
    }

    // แก้น้ำหนักที่จะใช้ / ผลิตเพิ่ม → กลับมาคำนวณทับอัตโนมัติ
    $('#sp_weight_request, #sp_increase_production').on('input', function () {
        spProdManual = false;
        recalcWeightProduction();
    });

    // ผู้ใช้พิมพ์แก้น้ำหนักที่จะผลิตเอง → ล็อกค่าไว้ ใช้ค่านี้ตอนบันทึก
    $('#sp_weight_production').on('input', function () {
        spProdManual = true;
    });

    function fillModal(d) {
        $('#sp_company').val(d.company || '');
        $('#sp_custno').val(d.custno || DEFAULT_CUSTNO);
        setDateField('#sp_mdate', d.mdate);       // flatpickr: อัปเดตทั้งช่องที่แสดงและค่าจริง
        setDateField('#sp_custwant', d.custwant);
        $('#sp_itemno').val(d.itemno || '');
        $('#sp_semi_code').val(d.semi_code || '');
        $('#sp_primary_color').val(d.primary_color || '');
        $('#sp_lot_no').val(d.lot_no || '');
        $('#sp_red_bill_code').val(d.red_bill_code || '');
        $('#sp_balance').val(d.balance || '');
        $('#sp_retrospective').val(d.retrospective || '');
        $('#sp_weight_request').val(d.weight_request || '');
        $('#sp_increase_production').val(d.increase_production || '');
        $('#sp_itemno').removeClass('is-invalid');

        // เปิด modal มา: ถ้ามีค่าน้ำหนักที่จะผลิตเดิม (เคยแก้/บันทึกไว้) ให้คงค่านั้นไว้
        // ถ้าไม่มีค่าเดิม ให้คำนวณจากน้ำหนักที่จะใช้ + ผลิตเพิ่ม
        var storedProd = (d.weight_production != null ? String(d.weight_production) : '').trim();
        if (storedProd !== '') {
            spProdManual = true;
            $('#sp_weight_production').val(storedProd);
        } else {
            spProdManual = false;
            recalcWeightProduction();
        }
    }

    function openAddModal(target, label) {
        $editingRow = null;
        $('#sp_entry_target').val(target);
        $('#sp_entry_title').text('เพิ่ม ' + label);
        $('#btn_sp_entry_save').html('<i class="ti ti-plus me-1"></i>เพิ่มลงตาราง');
        fillModal({ mdate: DEFAULT_MDATE, custno: DEFAULT_CUSTNO });
        bootstrap.Modal.getOrCreateInstance($entryModal[0]).show();
        setTimeout(function () { $('#sp_itemno').trigger('focus'); }, 300);
    }

    function openEditModal($tr) {
        $editingRow = $tr;
        var label = $tr.closest('tbody').attr('id') === 'tbody_pigment' ? 'Pigment' : 'Semi';
        $('#sp_entry_title').text('แก้ไข ' + label);
        $('#btn_sp_entry_save').html('<i class="ti ti-check me-1"></i>อัปเดตในตาราง');
        fillModal(readRow($tr));
        bootstrap.Modal.getOrCreateInstance($entryModal[0]).show();
        setTimeout(function () { $('#sp_itemno').trigger('focus'); }, 300);
    }

    // ── Add button → เปิด modal (โหมดเพิ่ม) ── (Pigment แยกไปจัดการใน IIFE ของตัวเองด้านล่าง)
    $('#btn_add_semi_row').on('click', function () { openAddModal('tbody_semi', 'Semi'); });

    // ── Edit buttons → เปิด modal (โหมดแก้ไข) ──
    $('#tbody_semi').on('click', '.btn_edit_row', function () {
        openEditModal($(this).closest('tr'));
    });

    // ── บันทึกจาก modal → บันทึกลงฐานข้อมูลทันที (เพิ่ม/แก้ไข) ──
    $entryModal.find('#btn_sp_entry_save').on('click', function () {
        var $btn = $(this);
        var itemno = ($('#sp_itemno').val() || '').trim();

        if (!itemno) {
            $('#sp_itemno').addClass('is-invalid').trigger('focus');
            return;
        }
        // ไม่ตรวจสอบค่าว่างของ ขาด Semi Code / ขาดแม่สี (Primary Color) แล้ว ปล่อยว่างได้
        $('#sp_itemno').removeClass('is-invalid');

        var isEdit  = !!($editingRow && $editingRow.length);
        var tbodyId = isEdit ? $editingRow.closest('tbody').attr('id') : $('#sp_entry_target').val();
        var type    = tbodyId === 'tbody_pigment' ? 'pigment' : 'semi';

        // ต้องบันทึก Planning Item ก่อน (มี planning_id) จึงจะเพิ่ม/แก้ไข Semi/Pigment ได้
        if (!PLANNING_ID) {
            Swal.fire({
                icon: 'warning',
                title: 'ยังบันทึกไม่ได้',
                text: 'กรุณาบันทึก Planning Item ก่อน แล้วจึงเพิ่ม Semi/Pigment'
            });
            return;
        }

        // บันทึกค่าที่ user กรอกในช่องตรงๆ ไม่คำนวณใหม่ก่อนบันทึก
        var weightRequest    = $('#sp_weight_request').val() || '';
        var weightProduction = $('#sp_weight_production').val() || '';

        var payload = {
            _token:              CSRF,
            planning_id:         PLANNING_ID,
            type:                type,
            company:             $('#sp_company').val()  || '',
            mdate:               $('#sp_mdate').val()    || '',
            custwant:            $('#sp_custwant').val() || '',
            custno:              $('#sp_custno').val()   || '',
            itemno:              itemno,
            semi_code:           $('#sp_semi_code').val() || '',
            primary_color:       $('#sp_primary_color').val() || '',
            lot_no:              $('#sp_lot_no').val() || '',
            red_bill_code:       $('#sp_red_bill_code').val() || '',
            balance:             $('#sp_balance').val() || '',
            retrospective:       $('#sp_retrospective').val() || '',
            weight_request:      weightRequest,
            increase_production: $('#sp_increase_production').val() || '',
            weight_production:   weightProduction
        };

        var url = URL_ENTRY_STORE;
        if (isEdit) {
            url = URL_ENTRY_UPDATE;
            payload.id = $editingRow.data('id');
        }

        $btn.prop('disabled', true);
        $.ajax({
            type: 'POST', url: url, dataType: 'json', data: payload,
            success: function (res) {
                if (res.status == 200) {
                    if (isEdit) {
                        $editingRow.replaceWith($(displayRow(res.data)));
                        renumber(tbodyId);
                        $editingRow = null;
                    } else {
                        addRow(tbodyId, res.data);
                    }
                    bootstrap.Modal.getInstance($entryModal[0]).hide();
                    toast('success', res.message || 'บันทึกสำเร็จ');
                } else {
                    Swal.fire({ icon: 'warning', title: 'ผิดพลาด', text: res.message || 'บันทึกไม่สำเร็จ' });
                }
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด',
                    text: (xhr.responseJSON && xhr.responseJSON.message) || 'เกิดข้อผิดพลาด กรุณาลองใหม่' });
            },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    // คง scroll-lock ของ body ไว้เมื่อปิด modal ย่อยแต่ modal หลักยังเปิด
    $entryModal.on('hidden.bs.modal', function () {
        if ($('.modal.show').length) {
            $('body').addClass('modal-open');
        }
    });

    // เก็บกวาด modal ที่ย้ายไป body เมื่อปิดฟอร์มหลัก
    $('#planning_item_form').closest('.modal')
        .off('hidden.bs.modal.spentry')
        .on('hidden.bs.modal.spentry', function () {
            bootstrap.Modal.getInstance($entryModal[0])?.dispose();
            $entryModal.remove();
        });

    // ── Remove buttons → ลบออกจากฐานข้อมูลทันที ──
    function removeRow($tr, tbodyId, label) {
        var id = $tr.data('id');

        function done() {
            $tr.remove();
            renumber(tbodyId);
            checkEmpty(tbodyId, label);
        }

        // แถวที่ยังไม่มี id (เผื่อกรณีพิเศษ) — ลบจากตารางอย่างเดียว
        if (!id) { done(); return; }

        Swal.fire({
            icon: 'warning',
            title: 'ยืนยันการลบ',
            text: 'ต้องการลบรายการ ' + label + ' นี้หรือไม่?',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#d33'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'POST', url: URL_ENTRY_DELETE, dataType: 'json',
                data: { _token: CSRF, id: id },
                success: function (res) {
                    if (res.status == 200) {
                        done();
                        toast('success', res.message || 'ลบสำเร็จ');
                    } else {
                        Swal.fire({ icon: 'warning', title: 'ผิดพลาด', text: res.message || 'ลบไม่สำเร็จ' });
                    }
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด',
                        text: (xhr.responseJSON && xhr.responseJSON.message) || 'เกิดข้อผิดพลาด กรุณาลองใหม่' });
                }
            });
        });
    }

    $('#tbody_semi').on('click', '.btn_remove_row', function () {
        removeRow($(this).closest('tr'), 'tbody_semi', 'Semi');
    });

    // ── modal ประวัติ senddate ──
    // ย้าย modal ออกไปเป็น sibling ที่ body (Bootstrap จัดการ stacked modal แบบ sibling ได้ดีกว่าซ้อนใน .modal-content)
    // ลบตัวเก่าที่ค้างจากการเปิดฟอร์มครั้งก่อนออกก่อน กัน id ซ้ำ
    $('body').children('#senddate_log_modal').remove();
    var $logModal = $('#senddate_log_modal').appendTo('body');

    $('#btn_senddate_log').on('click', function () {
        bootstrap.Modal.getOrCreateInstance($logModal[0]).show();
    });

    // เมื่อปิด modal ประวัติ แต่ modal หลักยังเปิดอยู่ ให้คง scroll-lock ของ body ไว้
    $logModal.on('hidden.bs.modal', function () {
        if ($('.modal.show').length) {
            $('body').addClass('modal-open');
        }
    });

    // เมื่อปิดฟอร์มหลัก เก็บกวาด modal ประวัติที่ย้ายไป body ทิ้ง (namespace กัน handler ซ้อนสะสม)
    $('#planning_item_form').closest('.modal')
        .off('hidden.bs.modal.senddatelog')
        .on('hidden.bs.modal.senddatelog', function () {
            bootstrap.Modal.getInstance($logModal[0])?.dispose();
            $logModal.remove();
        });
})();

@if(false) {{-- Pigment JS: ยกเลิกชั่วคราว 25/08/2569 — เปิดคืนโดยเปลี่ยนเป็น @if(true) หรือถอด @if(false)/@endif ออก --}}
// ══════════════════════════════════════════════════════════════════════
//  Pigment — แยกจาก Semi โดยสมบูรณ์ (อ้างอิงตาราง tb_pigment ผ่าน PigmentController)
//  ตัดฟิลด์ที่ไม่ใช้ออก: Company, Semi Code, Primary Color, Lot No., Red Bill, Increase
// ══════════════════════════════════════════════════════════════════════
(function () {
    var CSRF             = '{{ csrf_token() }}';
    var PLANNING_ID      = '{{ $planning_item?->id ?? '' }}';
    var DEFAULT_MDATE    = '{{ $default_mdate }}';
    var DEFAULT_CUSTNO   = '{{ $default_custno }}';
    var URL_ENTRY_STORE  = '{{ route('production.pigment.entry.store') }}';
    var URL_ENTRY_UPDATE = '{{ route('production.pigment.entry.update') }}';
    var URL_ENTRY_DELETE = '{{ route('production.pigment.entry.delete') }}';

    var TBODY = 'tbody_pigment';

    function toast(icon, text) {
        Swal.fire({ icon: icon, title: text, toast: true, position: 'top-end',
            timer: 1800, showConfirmButton: false });
    }

    // escape ค่าให้ปลอดภัยเมื่อนำไปแสดงเป็น HTML / attribute
    function esc(v) {
        return $('<div>').text(v == null ? '' : v).html();
    }

    function renumber() {
        $('#' + TBODY + ' tr:not(.empty-row)').each(function (i) {
            $(this).find('.row-num').text(i + 1);
        });
    }

    // แปลงวันที่ YYYY-MM-DD → DD/MM/YYYY สำหรับแสดงผล (ค่าจริงเก็บใน hidden input เป็น YYYY-MM-DD)
    function fmtDate(s) {
        s = (s || '').substr(0, 10);
        if (!s) return '';
        var p = s.split('-');
        return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : s;
    }

    // ฟิลด์ที่เก็บไว้ใน hidden input (ไม่แสดงเป็นคอลัมน์ในตาราง)
    var HIDDEN_FIELDS = ['balance', 'retrospective', 'weight_production'];
    // ทุกฟิลด์ของแถว pigment (ทั้งที่แสดงผลและ hidden)
    var ALL_FIELDS = ['mdate', 'custwant', 'custno', 'itemno', 'weight_request'].concat(HIDDEN_FIELDS);

    // แถวแสดงผล (อ่านอย่างเดียว) + เก็บค่าจริงไว้ใน hidden input เพื่อให้ readRow อ่านได้
    function displayRow(d) {
        function cell(field, text) {
            // คอลัมน์ custwant ให้พื้นหลังแดงเหมือนหน้ารายการวางแผน
            var cls = field === 'custwant' ? ' class="sp-custwant"' : '';
            return '<td' + cls + '>' + (text === '' ? '-' : esc(text)) +
                '<input type="hidden" data-field="' + field + '" value="' + esc(d[field] || '') + '"></td>';
        }
        function hid(field) {
            return '<input type="hidden" data-field="' + field + '" value="' + esc(d[field] || '') + '">';
        }
        var hiddenInputs = HIDDEN_FIELDS.map(hid).join('');

        return '<tr data-id="' + esc(d.id || '') + '">' +
            '<td class="text-center"><span class="row-num">1</span>' + hiddenInputs + '</td>' +
            cell('mdate',    fmtDate(d.mdate)) +
            cell('custwant', fmtDate(d.custwant)) +
            cell('custno',   d.custno   || '') +
            cell('itemno',   d.itemno   || '') +
            cell('weight_request', d.weight_request || '') +
            '<td class="text-center"><span class="badge bg-label-warning">รออนุมัติ</span></td>' +
            '<td class="text-center text-nowrap">' +
                '<button type="button" class="btn btn-sm btn-warning btn-icon btn_edit_row" title="แก้ไข"><i class="ti ti-pencil ti-sm"></i></button> ' +
                '<button type="button" class="btn btn-sm btn-danger btn-icon btn_remove_row" title="ลบ"><i class="ti ti-trash ti-sm"></i></button>' +
            '</td>' +
            '</tr>';
    }

    function addRow(data) {
        var $tbody = $('#' + TBODY);
        $tbody.find('.empty-row').remove();
        $tbody.append(displayRow(data));
        renumber();
    }

    function checkEmpty() {
        if ($('#' + TBODY + ' tr').length === 0) {
            $('#' + TBODY).append(
                '<tr class="empty-row"><td colspan="8" class="text-center text-muted py-2">ยังไม่มีรายการ Pigment</td></tr>'
            );
        }
    }

    // อ่านค่าทุกฟิลด์จากแถว (อ่านจาก input/select ที่มี data-field)
    function readRow($tr) {
        var d = {};
        ALL_FIELDS.forEach(function (f) {
            d[f] = $tr.find('[data-field="' + f + '"]').val() || '';
        });
        return d;
    }

    // ── Modal เพิ่ม / แก้ไข Pigment ──
    // ย้าย modal ออกไปเป็น sibling ที่ body (กัน stacked modal ซ้อนใน .modal-content แล้วเพี้ยน)
    $('body').children('#pigment_entry_modal').remove();
    var $modal = $('#pigment_entry_modal').appendTo('body');
    window.wpInitDateFields($modal); // ผูก flatpickr ช่องวันที่ใน modal Pigment

    // แถวที่กำลังแก้ไขผ่าน modal (null = โหมดเพิ่มใหม่)
    var $editingRow = null;

    // ── น้ำหนักที่จะผลิต: ดีฟอลต์ = น้ำหนักที่จะใช้ (Pigment ไม่มี "ผลิตเพิ่ม") ──
    // ถ้าผู้ใช้พิมพ์แก้ช่อง "น้ำหนักที่จะผลิต" เอง จะล็อกค่านั้นไว้ (ไม่ถูกคำนวณทับ)
    var prodManual = false;

    function recalcWeightProduction() {
        if (prodManual) return;
        $('#pg_weight_production').val($('#pg_weight_request').val() || '');
    }

    $('#pg_weight_request').on('input', function () {
        prodManual = false;
        recalcWeightProduction();
    });
    $('#pg_weight_production').on('input', function () {
        prodManual = true;
    });

    function fillModal(d) {
        $('#pg_custno').val(d.custno || DEFAULT_CUSTNO);
        window.wpSetDateField('#pg_mdate', d.mdate);       // flatpickr: อัปเดตทั้งช่องที่แสดงและค่าจริง
        window.wpSetDateField('#pg_custwant', d.custwant);
        $('#pg_itemno').val(d.itemno || '');
        $('#pg_balance').val(d.balance || '');
        $('#pg_retrospective').val(d.retrospective || '');
        $('#pg_weight_request').val(d.weight_request || '');
        $('#pg_itemno').removeClass('is-invalid');

        // เปิด modal มา: ถ้ามีค่าน้ำหนักที่จะผลิตเดิม (เคยแก้/บันทึกไว้) ให้คงค่านั้นไว้
        // ถ้าไม่มีค่าเดิม ให้คำนวณจากน้ำหนักที่จะใช้
        var storedProd = (d.weight_production != null ? String(d.weight_production) : '').trim();
        if (storedProd !== '') {
            prodManual = true;
            $('#pg_weight_production').val(storedProd);
        } else {
            prodManual = false;
            recalcWeightProduction();
        }
    }

    function openAddModal() {
        $editingRow = null;
        $('#pigment_entry_title').text('เพิ่ม Pigment');
        $('#btn_pigment_entry_save').html('<i class="ti ti-plus me-1"></i>เพิ่มลงตาราง');
        fillModal({ mdate: DEFAULT_MDATE, custno: DEFAULT_CUSTNO });
        bootstrap.Modal.getOrCreateInstance($modal[0]).show();
        setTimeout(function () { $('#pg_itemno').trigger('focus'); }, 300);
    }

    function openEditModal($tr) {
        $editingRow = $tr;
        $('#pigment_entry_title').text('แก้ไข Pigment');
        $('#btn_pigment_entry_save').html('<i class="ti ti-check me-1"></i>อัปเดตในตาราง');
        fillModal(readRow($tr));
        bootstrap.Modal.getOrCreateInstance($modal[0]).show();
        setTimeout(function () { $('#pg_itemno').trigger('focus'); }, 300);
    }

    $('#btn_add_pigment_row').on('click', function () { openAddModal(); });
    $('#tbody_pigment').on('click', '.btn_edit_row', function () {
        openEditModal($(this).closest('tr'));
    });

    // ── บันทึกจาก modal → บันทึกลงฐานข้อมูลทันที (เพิ่ม/แก้ไข) ──
    $modal.find('#btn_pigment_entry_save').on('click', function () {
        var $btn = $(this);
        var itemno = ($('#pg_itemno').val() || '').trim();
        if (!itemno) {
            $('#pg_itemno').addClass('is-invalid').trigger('focus');
            return;
        }
        $('#pg_itemno').removeClass('is-invalid');

        var isEdit = !!($editingRow && $editingRow.length);

        // ต้องบันทึก Planning Item ก่อน (มี planning_id) จึงจะเพิ่ม/แก้ไข Pigment ได้
        if (!PLANNING_ID) {
            Swal.fire({
                icon: 'warning',
                title: 'ยังบันทึกไม่ได้',
                text: 'กรุณาบันทึก Planning Item ก่อน แล้วจึงเพิ่ม Pigment'
            });
            return;
        }

        var payload = {
            _token:            CSRF,
            planning_id:       PLANNING_ID,
            mdate:             $('#pg_mdate').val()    || '',
            custwant:          $('#pg_custwant').val() || '',
            custno:            $('#pg_custno').val()   || '',
            itemno:            itemno,
            balance:           $('#pg_balance').val() || '',
            retrospective:     $('#pg_retrospective').val() || '',
            weight_request:    $('#pg_weight_request').val() || '',
            weight_production: $('#pg_weight_production').val() || ''
        };

        var url = URL_ENTRY_STORE;
        if (isEdit) {
            url = URL_ENTRY_UPDATE;
            payload.id = $editingRow.data('id');
        }

        $btn.prop('disabled', true);
        $.ajax({
            type: 'POST', url: url, dataType: 'json', data: payload,
            success: function (res) {
                if (res.status == 200) {
                    if (isEdit) {
                        $editingRow.replaceWith($(displayRow(res.data)));
                        renumber();
                        $editingRow = null;
                    } else {
                        addRow(res.data);
                    }
                    bootstrap.Modal.getInstance($modal[0]).hide();
                    toast('success', res.message || 'บันทึกสำเร็จ');
                } else {
                    Swal.fire({ icon: 'warning', title: 'ผิดพลาด', text: res.message || 'บันทึกไม่สำเร็จ' });
                }
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด',
                    text: (xhr.responseJSON && xhr.responseJSON.message) || 'เกิดข้อผิดพลาด กรุณาลองใหม่' });
            },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    // คง scroll-lock ของ body ไว้เมื่อปิด modal ย่อยแต่ modal หลักยังเปิด
    $modal.on('hidden.bs.modal', function () {
        if ($('.modal.show').length) {
            $('body').addClass('modal-open');
        }
    });

    // เก็บกวาด modal ที่ย้ายไป body เมื่อปิดฟอร์มหลัก
    $('#planning_item_form').closest('.modal')
        .off('hidden.bs.modal.pigmententry')
        .on('hidden.bs.modal.pigmententry', function () {
            bootstrap.Modal.getInstance($modal[0])?.dispose();
            $modal.remove();
        });

    // ── Remove button → ลบออกจากฐานข้อมูลทันที ──
    $('#tbody_pigment').on('click', '.btn_remove_row', function () {
        var $tr = $(this).closest('tr');
        var id  = $tr.data('id');

        function done() {
            $tr.remove();
            renumber();
            checkEmpty();
        }

        // แถวที่ยังไม่มี id (เผื่อกรณีพิเศษ) — ลบจากตารางอย่างเดียว
        if (!id) { done(); return; }

        Swal.fire({
            icon: 'warning',
            title: 'ยืนยันการลบ',
            text: 'ต้องการลบรายการ Pigment นี้หรือไม่?',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#d33'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'POST', url: URL_ENTRY_DELETE, dataType: 'json',
                data: { _token: CSRF, id: id },
                success: function (res) {
                    if (res.status == 200) {
                        done();
                        toast('success', res.message || 'ลบสำเร็จ');
                    } else {
                        Swal.fire({ icon: 'warning', title: 'ผิดพลาด', text: res.message || 'ลบไม่สำเร็จ' });
                    }
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด',
                        text: (xhr.responseJSON && xhr.responseJSON.message) || 'เกิดข้อผิดพลาด กรุณาลองใหม่' });
                }
            });
        });
    });
})();
@endif {{-- /Pigment JS (ยกเลิกชั่วคราว 25/08/2569) --}}

// ══════════════════════════════════════════════════════════════════════
//  Semi → ต้นไม้สถานะแผนการผลิต (recursive)
//  คลิกปุ่มในคอลัมน์ "จัดการ" ของแถว semi ที่อนุมัติแล้ว → เปิด modal แสดงต้นไม้
// ══════════════════════════════════════════════════════════════════════
(function () {
    var URL_PLAN_TREE = '{{ route('production.semipigment.plan-tree') }}';
    var LOADING_HTML  = '<div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm me-2"></div>กำลังโหลด...</div>';

    // ย้าย modal ไปเป็น sibling ที่ body (กัน stacked modal ซ้อนใน .modal-content แล้วเพี้ยน)
    $('body').children('#plan_tree_modal').remove();
    var $treeModal = $('#plan_tree_modal').appendTo('body');

    // ปุ่ม "จัดการ" ของแถว semi ที่อนุมัติแล้ว → โหลดต้นไม้แล้วเปิด modal
    $('#tbody_semi').on('click', '.btn_view_plan_tree', function () {
        var spId = $(this).data('sp-id');
        $('#plan_tree_body').html(LOADING_HTML);
        bootstrap.Modal.getOrCreateInstance($treeModal[0]).show();

        $.ajax({
            type: 'GET', url: URL_PLAN_TREE, dataType: 'json', cache: false,
            data: { id: spId },
            success: function (res) {
                if (res.status === 200) {
                    $('#plan_tree_body').html(res.data);
                } else {
                    $('#plan_tree_body').html('<div class="text-center text-danger py-4">' + (res.message || 'ไม่พบข้อมูล') + '</div>');
                }
            },
            error: function (xhr) {
                $('#plan_tree_body').html('<div class="text-center text-danger py-4">' +
                    ((xhr.responseJSON && xhr.responseJSON.message) || 'เกิดข้อผิดพลาด กรุณาลองใหม่') + '</div>');
            }
        });
    });

    // คง scroll-lock ของ body ไว้เมื่อปิด modal ย่อยแต่ modal หลักยังเปิด
    $treeModal.on('hidden.bs.modal', function () {
        if ($('.modal.show').length) {
            $('body').addClass('modal-open');
        }
    });

    // เก็บกวาด modal ที่ย้ายไป body เมื่อปิดฟอร์มหลัก
    $('#planning_item_form').closest('.modal')
        .off('hidden.bs.modal.plantree')
        .on('hidden.bs.modal.plantree', function () {
            bootstrap.Modal.getInstance($treeModal[0])?.dispose();
            $treeModal.remove();
        });
})();

// ══════════════════════════════════════════════════════════════════════
//  จัดลำดับการ Tab ในฟอร์มแก้ไข Planning Item ให้ "เรียงลงมาตามลำดับบนจอ"
//  ปัญหาเดิม: ช่องวันที่ใช้ flatpickr (altInput + static) → input จริงถูกซ่อน
//  เป็น type=hidden แล้วห่อด้วย .flatpickr-wrapper ทำให้ลำดับ Tab ของเบราว์เซอร์
//  เพี้ยน/ข้ามบางช่อง (โดยเฉพาะช่องวันที่ที่อยู่ถัดจากช่องเวลา type=time)
//  วิธีแก้: ดัก Tab/Shift+Tab เอง แล้วย้าย focus ไปยัง control ถัดไป/ก่อนหน้า
//  "ตามลำดับใน DOM จริง" (ซึ่งตรงกับลำดับที่เห็นบนจอ) → ไม่มีการข้าม
// ══════════════════════════════════════════════════════════════════════
(function () {
    var $form = $('#planning_item_form');
    if (!$form.length) return;

    // เก็บเฉพาะ input ที่ไม่ใช่ hidden, select, textarea, button (ปุ่มก็อยู่ในลำดับตามปกติ)
    var FOCUS_SEL = 'input:not([type=hidden]), select, textarea, button';

    // control ที่ "มองเห็น + ใช้งานได้" เท่านั้น (ตัดช่องที่ disabled / ถูกซ่อน / อยู่ในปฏิทิน flatpickr)
    function isTabbable(el) {
        if (el.disabled) return false;
        // มองเห็นจริง (flatpickr ซ่อน input ตัวจริงไว้ → offsetParent เป็น null)
        if (el.offsetParent === null && el.getClientRects().length === 0) return false;
        // ข้าม element ภายในปฏิทิน flatpickr (ปุ่มเดือน/ช่องเวลาในปฏิทิน)
        if (el.closest('.flatpickr-calendar')) return false;
        return true;
    }

    function tabbableList() {
        return $form.find(FOCUS_SEL).toArray().filter(isTabbable);
    }

    $form.on('keydown', function (e) {
        // เฉพาะปุ่ม Tab ล้วน ๆ (ไม่รวม Ctrl/Alt/Meta) — Shift ใช้ถอยหลัง
        if ((e.key !== 'Tab' && e.keyCode !== 9) || e.ctrlKey || e.altKey || e.metaKey) return;

        var active = document.activeElement;
        if (!active) return;

        var list = tabbableList();
        var idx  = list.indexOf(active);
        if (idx === -1) return; // focus ไม่ได้อยู่ในฟอร์ม → ปล่อยตามปกติ

        var nextIdx = e.shiftKey ? idx - 1 : idx + 1;
        // สุดรายการแล้ว → ปล่อยให้ Tab ออกไปยังปุ่ม footer (บันทึก/ยกเลิก) ตามปกติ
        if (nextIdx < 0 || nextIdx >= list.length) return;

        e.preventDefault();
        list[nextIdx].focus();
    });
})();

@if($order_closed)
// ══════════════════════════════════════════════════════════════════════
//  ออเดอร์ถูกปิดแล้ว (end_order = Y) → โหมดอ่านอย่างเดียว
//  ปิดการใช้งานทุก input/select/textarea/button ใน modal นี้
//  ยกเว้นปุ่มปิด (X) และปุ่มยกเลิก (data-bs-dismiss="modal") — โดยเฉพาะปุ่มบันทึกจะกดไม่ได้
// ══════════════════════════════════════════════════════════════════════
(function () {
    var $content = $('#planning_item_form').closest('.modal-content');
    $content.find('input, select, textarea, button').each(function () {
        var $el = $(this);
        // คงปุ่มปิด (X) และปุ่มยกเลิกไว้ให้กดได้
        if ($el.hasClass('btn-close') || $el.attr('data-bs-dismiss') === 'modal') return;
        $el.prop('disabled', true);
    });
})();
@endif
</script>
