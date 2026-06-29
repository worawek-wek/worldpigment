@php
    // plan_type: ใช้ของ planning item ก่อน ถ้าไม่มีดึงจาก parent header
    $default_plan_type = $planning_item?->plan_type ?? $parent_header?->plan_type ?? '';
    // ค่า default สำหรับแถว semi/pigment ใหม่
    $default_mdate     = $parent_header?->mdate ? substr($parent_header->mdate, 0, 10) : '';
    $default_custno    = $parent_header?->company  ?? '';   // custno = company ของ header แม่
    $companies = ['CP', 'MB', 'DB', 'SPP'];

    // สีของ badge ตามสถานะ semi/pigment
    $spStatusCls = [
        'request'  => 'bg-label-warning',
        'approved' => 'bg-label-success',
        'reject'   => 'bg-label-danger',
    ];

    // ประวัติวันที่ส่งสินค้าเดิม (senddate_log) — เก็บคั่นด้วย comma เรียงตามลำดับการเปลี่ยน
    $senddate_logs = array_values(array_filter(array_map('trim', explode(',', $planning_item?->senddate_log ?? ''))));
@endphp

<div class="modal-header" style="background-color: #3A8EBA; padding: 1rem 1.5rem;">
    <h5 class="modal-title text-white mb-0">
        <i class="ti ti-{{ $planning_item ? 'pencil' : 'plus' }} me-1"></i>
        {{ $planning_item ? 'แก้ไข Planning Item' : 'เพิ่ม Planning Item' }}
        @if($parent_header)
            <span class="badge bg-white text-primary ms-2 fw-normal" style="font-size:.75rem">
                {{ $parent_header->planning_code }}
            </span>
        @endif
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <form id="planning_item_form">
        <input type="hidden" name="planning_id"        value="{{ $planning_item?->id ?? '' }}">
        <input type="hidden" name="planning_header_id" value="{{ $planning_header_id ?? '' }}">

        {{-- ── ข้อมูลหลัก ── --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Item No. <span class="text-danger">*</span></label>
                <input type="text" name="itemno"
                       value="{{ $planning_item?->itemno ?? '' }}"
                       class="form-control" placeholder="กรอก Item No.">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Quantity</label>
                <input type="text" name="quantity"
                       value="{{ $planning_item?->quantity ?? '' }}"
                       class="form-control" placeholder="0">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Lot</label>
                <input type="text" name="lot"
                       value="{{ $planning_item?->lot ?? '' }}"
                       class="form-control" placeholder="Lot">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Weight</label>
                <input type="text" name="weight"
                       value="{{ number_format((float) ($planning_item?->weight ?? 0), 2) }}"
                       class="form-control" placeholder="0.00">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Machine No.</label>
                <select name="machine_no" class="form-control">
                    <option value="">เลือกเครื่องจักร</option>
                    @foreach($machines as $machine)
                        <option value="{{ $machine->MBX }}" {{ $planning_item && $planning_item->machine_no === $machine->MBX ? 'selected' : '' }}>
                            {{ $machine->MBX }}
                        </option>
                    @endforeach
                </select>
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
            <div class="col-md-6 mb-3">
                <label class="form-label">Planning Status</label>
                @php $current_status = $planning_item?->planning_status ?? ''; @endphp
                <select name="planning_status" class="form-select">
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
        <div class="row my-2"><hr /></div>
        <div class="row p-3 rounded" style="background-color: #fffaf0; border: 1px dashed #ffc107;">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">วันที่สั่ง (mdate)</label>
                    <input type="date" name="mdate"
                        value="{{ $planning_item?->mdate ? substr($planning_item->mdate, 0, 10) : '' }}"
                        class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">วันที่ต้องการรับ (custwant)</label>
                    <input type="date" name="custwant"
                        value="{{ $planning_item?->custwant ? substr($planning_item->custwant, 0, 10) : '' }}"
                        class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label d-flex align-items-center justify-content-between">
                        <span>วันที่ส่งสินค้า (senddate)</span>
                        @if($planning_item && count($senddate_logs))
                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="btn_senddate_log">
                                <i class="ti ti-history me-1"></i>ประวัติ
                                <span class="badge bg-label-secondary ms-1">{{ count($senddate_logs) }}</span>
                            </button>
                        @endif
                    </label>
                    <input type="date" name="senddate"
                        value="{{ $planning_item?->senddate ? substr($planning_item->senddate, 0, 10) : '' }}"
                        class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">วันที่วางแผนผลิต (Inplan)</label>
                    <input type="date" name="inplan"
                        value="{{ $planning_item?->inplan ?? '' }}"
                        class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">วันที่เริ่มผลิต (Start Date)</label>
                    <input type="date" name="start_date"
                        value="{{ $planning_item?->start_date ?? '' }}"
                        class="form-control">
                </div>
            </div>
        </div>
        <div class="row my-2"><hr /></div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">วันที่ส่ง Qc (QC Date)</label>
                <input type="date" name="qc_date"
                       value="{{ $planning_item?->qc_date ?? '' }}"
                       class="form-control">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">เวลาที่ส่ง Qc (QC Time)</label>
                <input type="time" name="qc_time"
                       value="{{ $planning_item?->qc_time ?? '' }}"
                       class="form-control" placeholder="HH:MM">
            </div>
            <div class="col-md-4 mb-3">
            <label class="form-label">สถานะ Qc (QC Status) </label>
                <input type="text" name="qc_status"
                       value="{{ $planning_item?->qc_status ?? '' }}"
                       class="form-control" placeholder="สถานะ QC">
            </div>
        </div>
        <div class="row my-2"><hr /></div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">วันเวลาที่บรรจุเสร็จ (Packing Datetime)</label>
                <input type="datetime-local" name="packing_datetie"
                       value="{{ $planning_item?->packing_datetie ?? '' }}"
                       class="form-control" placeholder="วันเวลาจัดแพ็ค">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Remark</label>
                <input type="text" name="remark"
                       value="{{ $planning_item?->remark ?? '' }}"
                       class="form-control" placeholder="หมายเหตุ">
            </div>
        </div>

        <hr class="my-3">

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
                        <th style="min-width:120px">วันที่ต้องการรับ</th>
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
                            <td class="text-center row-num">{{ $i + 1 }}</td>
                            <td>{{ $row['company'] ?? '-' }}</td>
                            <td>{{ !empty($row['mdate'])    ? \Carbon\Carbon::parse($row['mdate'])->format('d/m/Y')    : '-' }}</td>
                            <td>{{ !empty($row['custwant']) ? \Carbon\Carbon::parse($row['custwant'])->format('d/m/Y') : '-' }}</td>
                            <td>{{ $row['custno']   ?? '-' }}</td>
                            <td>{{ $row['itemno']   ?? '-' }}</td>
                            <td>{{ $row['weight_request'] ?? '-' }}</td>
                            <td class="text-center"><span class="badge {{ $stCls }}">{{ $stLabel }}</span></td>
                            <td class="text-center"><i class="ti ti-lock text-muted" title="ดำเนินการแล้ว แก้ไขไม่ได้"></i></td>
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
                        <th style="min-width:110px">Company</th>
                        <th style="min-width:120px">วันที่สั่ง</th>
                        <th style="min-width:120px">วันที่ต้องการรับ</th>
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
                            <td class="text-center row-num">{{ $i + 1 }}</td>
                            <td>{{ $row['company'] ?? '-' }}</td>
                            <td>{{ !empty($row['mdate'])    ? \Carbon\Carbon::parse($row['mdate'])->format('d/m/Y')    : '-' }}</td>
                            <td>{{ !empty($row['custwant']) ? \Carbon\Carbon::parse($row['custwant'])->format('d/m/Y') : '-' }}</td>
                            <td>{{ $row['custno']   ?? '-' }}</td>
                            <td>{{ $row['itemno']   ?? '-' }}</td>
                            <td>{{ $row['weight_request'] ?? '-' }}</td>
                            <td class="text-center"><span class="badge {{ $stCls }}">{{ $stLabel }}</span></td>
                            <td class="text-center"><i class="ti ti-lock text-muted" title="ดำเนินการแล้ว แก้ไขไม่ได้"></i></td>
                        </tr>
                        @else
                        @include('production-planning.planning.partials.sp-row', ['row' => $row, 'i' => $i, 'default_custno' => $default_custno])
                        @endif
                    @empty
                    <tr class="empty-row">
                        <td colspan="9" class="text-center text-muted py-2">ยังไม่มีรายการ Pigment</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

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

{{-- ── Modal ประวัติวันที่ส่งสินค้า (senddate_log) ── --}}
<div class="modal fade" id="senddate_log_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:300px;">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#3A8EBA; padding:.75rem 1.25rem;">
                <h6 class="modal-title text-white mb-0">
                    <i class="ti ti-history me-1"></i>ประวัติวันที่ส่งสินค้า
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2 small text-muted">
                    วันที่ส่งปัจจุบัน:
                    <strong>{{ $planning_item?->senddate ? \Carbon\Carbon::parse($planning_item->senddate)->format('d/m/Y') : '-' }}</strong>
                </div>
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
<div class="modal fade" id="sp_entry_modal" tabindex="-1" aria-hidden="true">
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
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Company</label>
                        <select class="form-select" id="sp_company">
                            <option value="">-- เลือก --</option>
                            @foreach($companies as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Cust No.</label>
                        <input type="text" class="form-control bg-light" id="sp_custno" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">วันที่สั่ง</label>
                        <input type="date" class="form-control" id="sp_mdate">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">วันที่ต้องการรับ</label>
                        <input type="date" class="form-control" id="sp_custwant">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Item No. <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sp_itemno" placeholder="Item No.">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Semi Code</label>
                        <input type="text" class="form-control" id="sp_semi_code" placeholder="รหัสกึ่งสำเร็จรูป">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">แม่สี (Primary Color)</label>
                        <input type="text" class="form-control" id="sp_primary_color" placeholder="แม่สี">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Lot No.</label>
                        <input type="text" class="form-control" id="sp_lot_no" placeholder="ล็อตที่">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">เลขที่ใบเบิกออกใบแดง (Red Bill)</label>
                        <input type="text" class="form-control" id="sp_red_bill_code" placeholder="เลขที่ใบเบิก">
                    </div>
                </div>
                <hr class="my-2">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">ยอดคงเหลือวันนี้ (Balance)</label>
                        <input type="number" step="any" class="form-control" id="sp_balance" placeholder="0.00">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">ยอดใช้ย้อนหลัง 2 เดือน</label>
                        <input type="number" step="any" class="form-control" id="sp_retrospective" placeholder="0.00">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">น้ำหนักที่จะใช้ (weight_request)</label>
                        <input type="number" step="any" class="form-control" id="sp_weight_request" placeholder="0.00">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">ผลิตเพิ่ม (Increase)</label>
                        <input type="number" step="any" class="form-control" id="sp_increase_production" placeholder="0.00">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">น้ำหนักที่จะผลิต (weight_production)</label>
                        <input type="number" step="any" class="form-control" id="sp_weight_production" placeholder="0.00">
                    </div>
                </div>
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

<script>
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
            return '<td>' + (text === '' ? '-' : esc(text)) +
                '<input type="hidden" data-field="' + field + '" value="' + esc(d[field] || '') + '"></td>';
        }
        function hid(field) {
            return '<input type="hidden" data-field="' + field + '" value="' + esc(d[field] || '') + '">';
        }
        var hiddenInputs = HIDDEN_FIELDS.map(hid).join('');

        return '<tr data-id="' + esc(d.id || '') + '">' +
            '<td class="text-center row-num">1' + hiddenInputs + '</td>' +
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

    // แถวที่กำลังแก้ไขผ่าน modal (null = โหมดเพิ่มใหม่)
    var $editingRow = null;

    function fillModal(d) {
        $('#sp_company').val(d.company || '');
        $('#sp_custno').val(d.custno || DEFAULT_CUSTNO);
        $('#sp_mdate').val(d.mdate || '');
        $('#sp_custwant').val(d.custwant || '');
        $('#sp_itemno').val(d.itemno || '');
        $('#sp_semi_code').val(d.semi_code || '');
        $('#sp_primary_color').val(d.primary_color || '');
        $('#sp_lot_no').val(d.lot_no || '');
        $('#sp_red_bill_code').val(d.red_bill_code || '');
        $('#sp_balance').val(d.balance || '');
        $('#sp_retrospective').val(d.retrospective || '');
        $('#sp_weight_request').val(d.weight_request || '');
        $('#sp_increase_production').val(d.increase_production || '');
        $('#sp_weight_production').val(d.weight_production || '');
        $('#sp_itemno').removeClass('is-invalid');
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

    // ── Add buttons → เปิด modal (โหมดเพิ่ม) ──
    $('#btn_add_semi_row').on('click',    function () { openAddModal('tbody_semi', 'Semi'); });
    $('#btn_add_pigment_row').on('click', function () { openAddModal('tbody_pigment', 'Pigment'); });

    // ── Edit buttons → เปิด modal (โหมดแก้ไข) ──
    $('#tbody_semi, #tbody_pigment').on('click', '.btn_edit_row', function () {
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

        var weightRequest    = $('#sp_weight_request').val() || '';
        var weightProduction = $('#sp_weight_production').val() || '';
        // ถ้ายังไม่ระบุน้ำหนักที่จะผลิต ให้ใช้น้ำหนักที่จะใช้เป็นค่าเริ่มต้น
        if (weightProduction === '') weightProduction = weightRequest;

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
    $('#tbody_pigment').on('click', '.btn_remove_row', function () {
        removeRow($(this).closest('tr'), 'tbody_pigment', 'Pigment');
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
</script>
