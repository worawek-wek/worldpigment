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
        <input type="hidden" name="semi_json"    id="semi_json"    value="">
        <input type="hidden" name="pigment_json" id="pigment_json" value="">

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
                        <th class="text-center" style="width:46px">ลบ</th>
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
                            <td>{{ substr($row['mdate']    ?? '', 0, 10) ?: '-' }}</td>
                            <td>{{ substr($row['custwant'] ?? '', 0, 10) ?: '-' }}</td>
                            <td>{{ $row['custno']   ?? '-' }}</td>
                            <td>{{ $row['itemno']   ?? '-' }}</td>
                            <td>{{ $row['quantity'] ?? '-' }}</td>
                            <td class="text-center"><span class="badge {{ $stCls }}">{{ $stLabel }}</span></td>
                            <td class="text-center"><i class="ti ti-lock text-muted" title="ดำเนินการแล้ว แก้ไขไม่ได้"></i></td>
                        </tr>
                        @else
                        <tr>
                            <td class="text-center row-num">{{ $i + 1 }}</td>
                            <td>
                                <select class="form-select form-select-sm" data-field="company">
                                    <option value="">-- เลือก --</option>
                                    @foreach($companies as $c)
                                    <option value="{{ $c }}" {{ ($row['company'] ?? '') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="date" class="form-control form-control-sm" data-field="mdate"    value="{{ substr($row['mdate']    ?? '', 0, 10) }}"></td>
                            <td><input type="date" class="form-control form-control-sm" data-field="custwant" value="{{ substr($row['custwant'] ?? '', 0, 10) }}"></td>
                            <td><input type="text" class="form-control form-control-sm bg-light" data-field="custno"   value="{{ $default_custno }}" readonly></td>
                            <td><input type="text" class="form-control form-control-sm" data-field="itemno"   value="{{ $row['itemno']   ?? '' }}" placeholder="Item No."></td>
                            <td><input type="text" class="form-control form-control-sm" data-field="quantity" value="{{ $row['quantity'] ?? '' }}" placeholder="0"></td>
                            <td class="text-center"><span class="badge bg-label-warning">รออนุมัติ</span></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger btn-icon btn_remove_row">
                                    <i class="ti ti-trash ti-sm"></i>
                                </button>
                            </td>
                        </tr>
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
                        <th class="text-center" style="width:46px">ลบ</th>
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
                            <td>{{ substr($row['mdate']    ?? '', 0, 10) ?: '-' }}</td>
                            <td>{{ substr($row['custwant'] ?? '', 0, 10) ?: '-' }}</td>
                            <td>{{ $row['custno']   ?? '-' }}</td>
                            <td>{{ $row['itemno']   ?? '-' }}</td>
                            <td>{{ $row['quantity'] ?? '-' }}</td>
                            <td class="text-center"><span class="badge {{ $stCls }}">{{ $stLabel }}</span></td>
                            <td class="text-center"><i class="ti ti-lock text-muted" title="ดำเนินการแล้ว แก้ไขไม่ได้"></i></td>
                        </tr>
                        @else
                        <tr>
                            <td class="text-center row-num">{{ $i + 1 }}</td>
                            <td>
                                <select class="form-select form-select-sm" data-field="company">
                                    <option value="">-- เลือก --</option>
                                    @foreach($companies as $c)
                                    <option value="{{ $c }}" {{ ($row['company'] ?? '') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="date" class="form-control form-control-sm" data-field="mdate"    value="{{ substr($row['mdate']    ?? '', 0, 10) }}"></td>
                            <td><input type="date" class="form-control form-control-sm" data-field="custwant" value="{{ substr($row['custwant'] ?? '', 0, 10) }}"></td>
                            <td><input type="text" class="form-control form-control-sm bg-light" data-field="custno"   value="{{ $default_custno }}" readonly></td>
                            <td><input type="text" class="form-control form-control-sm" data-field="itemno"   value="{{ $row['itemno']   ?? '' }}" placeholder="Item No."></td>
                            <td><input type="text" class="form-control form-control-sm" data-field="quantity" value="{{ $row['quantity'] ?? '' }}" placeholder="0"></td>
                            <td class="text-center"><span class="badge bg-label-warning">รออนุมัติ</span></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger btn-icon btn_remove_row">
                                    <i class="ti ti-trash ti-sm"></i>
                                </button>
                            </td>
                        </tr>
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
                    <strong>{{ $planning_item?->senddate ? substr($planning_item->senddate, 0, 10) : '-' }}</strong>
                </div>
                @if(count($senddate_logs))
                    <ul class="list-group list-group-flush">
                        @foreach($senddate_logs as $i => $log_date)
                            <li class="list-group-item d-flex align-items-center px-0">
                                <span class="badge bg-label-secondary me-2">{{ $i + 1 }}</span>
                                <span>{{ $log_date }}</span>
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

<script>
(function () {
    // ── ค่า default จาก planning_header แม่ ──
    var DEFAULT_MDATE  = '{{ $default_mdate }}';
    var DEFAULT_CUSTNO = '{{ $default_custno }}';

    var COMPANY_OPTIONS =
        '<option value="">-- เลือก --</option>' +
        '<option value="CP">CP</option>' +
        '<option value="MB">MB</option>' +
        '<option value="DB">DB</option>' +
        '<option value="SPP">SPP</option>';

    function renumber(tbodyId) {
        $('#' + tbodyId + ' tr:not(.empty-row)').each(function (i) {
            $(this).find('.row-num').text(i + 1);
        });
    }

    function newRow() {
        return '<tr>' +
            '<td class="text-center row-num">1</td>' +
            '<td><select class="form-select form-select-sm" data-field="company">' + COMPANY_OPTIONS + '</select></td>' +
            '<td><input type="date" class="form-control form-control-sm" data-field="mdate"    value="' + DEFAULT_MDATE + '"></td>' +
            '<td><input type="date" class="form-control form-control-sm" data-field="custwant" value=""></td>' +
            '<td><input type="text" class="form-control form-control-sm bg-light" data-field="custno"   value="' + DEFAULT_CUSTNO + '" readonly placeholder="Cust No."></td>' +
            '<td><input type="text" class="form-control form-control-sm" data-field="itemno"   value="" placeholder="Item No."></td>' +
            '<td><input type="text" class="form-control form-control-sm" data-field="quantity" value="" placeholder="0"></td>' +
            '<td class="text-center"><span class="badge bg-label-warning">รออนุมัติ</span></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-icon btn_remove_row"><i class="ti ti-trash ti-sm"></i></button></td>' +
            '</tr>';
    }

    function addRow(tbodyId) {
        var $tbody = $('#' + tbodyId);
        $tbody.find('.empty-row').remove();
        $tbody.append(newRow());
        renumber(tbodyId);
    }

    function checkEmpty(tbodyId, label) {
        if ($('#' + tbodyId + ' tr').length === 0) {
            $('#' + tbodyId).append(
                '<tr class="empty-row"><td colspan="9" class="text-center text-muted py-2">ยังไม่มีรายการ ' + label + '</td></tr>'
            );
        }
    }

    function collectRows(tbodyId) {
        var rows = [];
        // ข้ามแถวที่ล็อก (อนุมัติ/ปฏิเสธแล้ว) — เก็บเฉพาะแถวที่รออนุมัติ/แถวใหม่
        $('#' + tbodyId + ' tr:not(.empty-row):not(.locked-row)').each(function () {
            rows.push({
                company:  $(this).find('[data-field="company"]').val()  || '',
                mdate:    $(this).find('[data-field="mdate"]').val()    || '',
                custwant: $(this).find('[data-field="custwant"]').val() || '',
                custno:   $(this).find('[data-field="custno"]').val()   || '',
                itemno:   $(this).find('[data-field="itemno"]').val()   || '',
                quantity: $(this).find('[data-field="quantity"]').val() || ''
            });
        });
        return rows;
    }

    // ── Add buttons ──
    $('#btn_add_semi_row').on('click',    function () { addRow('tbody_semi'); });
    $('#btn_add_pigment_row').on('click', function () { addRow('tbody_pigment'); });

    // ── Remove buttons ──
    $('#tbody_semi').on('click', '.btn_remove_row', function () {
        $(this).closest('tr').remove();
        renumber('tbody_semi');
        checkEmpty('tbody_semi', 'Semi');
    });
    $('#tbody_pigment').on('click', '.btn_remove_row', function () {
        $(this).closest('tr').remove();
        renumber('tbody_pigment');
        checkEmpty('tbody_pigment', 'Pigment');
    });

    // ── hook serialize ก่อน save ──
    $('#planning_item_form').data('serialize_fn', function () {
        $('#semi_json').val(JSON.stringify(collectRows('tbody_semi')));
        $('#pigment_json').val(JSON.stringify(collectRows('tbody_pigment')));
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
