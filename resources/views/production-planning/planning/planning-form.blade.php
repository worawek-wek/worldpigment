<style>
    /* คอลัมน์ Inplan พื้นน้ำเงิน — ใช้ !important เพื่อไม่ให้ table-striped/table-hover ทับ (ให้เหมือนหน้ารายการ) */
    .col-inplan {
        background-color: #cfe2ff !important;
        color: #084298 !important;
    }
</style>
<div class="modal-header" style="background-color: #54BAB9; padding: 1rem 1.5rem;">
    <h5 class="modal-title text-white mb-0">
        <i class="ti ti-calendar-stats me-1"></i>
        แผนการผลิต
        @if($planning_header)
            <span class="ms-1 opacity-75">— {{ $planning_header->planning_code }}</span>
            {{-- Badge ปิดออเดอร์ — แสดงเมื่อ end_order = Y (รวมกรณี auto-close หลังจบงานครบ 15/08/2569) --}}
            @if(($planning_header->end_order ?? 'N') === 'Y')
                <span class="badge bg-success ms-2 align-middle"><i class="ti ti-lock me-1"></i>ปิดออเดอร์แล้ว</span>
            @endif
        @endif
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    @if(!$planning_header)
        <div class="text-muted text-center py-4">
            <i class="ti ti-info-circle ti-lg mb-2 d-block"></i>
            ไม่พบข้อมูล Planning Header
        </div>
    @else
        <!-- Planning Header Info -->
        <div class="card mb-4 border border-primary-subtle">
            <div class="card-header bg-primary-subtle py-2">
                <h6 class="mb-0 text-primary">
                    <i class="ti ti-info-circle me-1"></i>ข้อมูลแผนการผลิต
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">Planning Code</div>
                        <div class="fw-semibold">{{ $planning_header->planning_code ?? '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">Order No.</div>
                        <div class="fw-semibold">{{ $planning_header->orderno ?? '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">Company</div>
                        <div class="fw-semibold">{{ $planning_header->company ?? '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">วันที่</div>
                        <div class="fw-semibold">{{ $planning_header->mdate ? \Carbon\Carbon::parse($planning_header->mdate)->format('d/m/Y') : '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">รหัสลูกค้า</div>
                        <div class="fw-semibold">{{ $planning_header->custno ?? '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">Sale No.</div>
                        <div class="fw-semibold">{{ $planning_header->saleno ?? '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">จำนวนสุทธิ</div>
                        <div class="fw-semibold">{{ is_numeric($planning_header->netqty) ? number_format((float) $planning_header->netqty, 2) : '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">วันที่ลูกค้าต้องการ</div>
                        <div class="fw-semibold">{{ $planning_header->custwant ? \Carbon\Carbon::parse($planning_header->custwant)->format('d/m/Y') : '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">วันที่ส่ง</div>
                        <div class="fw-semibold">{{ $planning_header->senddate ? \Carbon\Carbon::parse($planning_header->senddate)->format('d/m/Y') : '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">Plan Type</div>
                        <div class="fw-semibold">{{ $planning_header->plan_type ?? '-' }}</div>
                    </div>
                    @if($planning_header->remark)
                    <div class="col-md-12 mb-2">
                        <div class="text-muted small">หมายเหตุ</div>
                        <div class="fw-semibold">{{ $planning_header->remark }}</div>
                    </div>
                    @endif

                    {{-- ปิดออเดอร์ (end_order): ติ๊กได้ต่อเมื่อ end_job ของทุกรายการที่เกี่ยวข้องเป็น Y ครบ --}}
                    <div class="col-md-12 p-3 rounded" style="background-color: #eaffd9; border: 1px dashed #04ac2e;">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="planning_end_order"
                                   data-planning_header_id="{{ $planning_header->id }}"
                                   value="Y"
                                   {{ ($planning_header->end_order ?? 'N') === 'Y' ? 'checked' : '' }}
                                   {{ (($planning_header->end_order ?? 'N') !== 'Y' && !$all_jobs_done) ? 'disabled' : '' }}>
                            <label class="form-check-label fw-semibold" for="planning_end_order">ปิดออเดอร์ (End Order)</label>
                            @if(($planning_header->end_order ?? 'N') === 'Y')
                                <span class="badge bg-success ms-2"><i class="ti ti-lock me-1"></i>ปิดออเดอร์แล้ว</span>
                            @endif
                        </div>
                        @unless($all_jobs_done)
                            <div class="form-text text-warning">
                                <i class="ti ti-alert-triangle me-1"></i>ต้องจบงาน (End Job) ทุกรายการที่เกี่ยวข้องก่อน จึงจะปิดออเดอร์ได้
                            </div>
                        @endunless
                    </div>

                    {{-- ปิดจบงาน (end_close): ติ๊กแล้วบังคับปิดออเดอร์ (end_order) ตามด้วยเสมอ + ต้องกรอกหมายเหตุ --}}
                    @php $end_closed = ($planning_header->end_close ?? 'N') === 'Y'; @endphp
                    <div class="col-md-12 mt-3 p-3 rounded" style="background-color: #fff3d9; border: 1px dashed #d98c04;">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="planning_end_close"
                                   data-planning_header_id="{{ $planning_header->id }}"
                                   value="Y"
                                   {{ $end_closed ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="planning_end_close">ปิดจบงาน (End Close)</label>
                            @if($planning_header->end_close_date)
                                <span class="small fw-normal text-muted">({{ \Carbon\Carbon::parse($planning_header->end_close_date)->format('d/m/Y H:i') }})</span>
                            @endif
                        </div>
                        <div class="form-text text-muted mb-2">
                            <i class="ti ti-info-circle me-1"></i>เมื่อปิดจบงาน ระบบจะปิดออเดอร์ (End Order) ให้อัตโนมัติ และต้องระบุหมายเหตุ
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-muted mb-1" for="planning_end_close_remark">
                                หมายเหตุการปิดจบงาน
                                <span class="text-danger" id="end_close_remark_required" style="{{ $end_closed ? '' : 'display:none;' }}">*</span>
                            </label>
                            {{-- พิมพ์ได้เสมอ (ไม่ disable) — บังคับกรอกเฉพาะเมื่อติ๊กปิดจบงาน ตรวจทั้งฝั่ง client และ server --}}
                            <textarea class="form-control" id="planning_end_close_remark" rows="2"
                                      placeholder="ระบุหมายเหตุ...">{{ $planning_header->end_close_remark }}</textarea>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-sm btn-warning" id="btn_save_end_close"
                                    data-planning_header_id="{{ $planning_header->id }}">
                                <i class="ti ti-device-floppy me-1"></i>บันทึกการปิดจบงาน
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Planning Items Section -->
        @php $order_closed = ($planning_header->end_order ?? 'N') === 'Y'; @endphp
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0 text-primary">
                <i class="ti ti-list-details me-1"></i>รายการ Planning
            </h6>
            {{-- ปิดออเดอร์แล้ว → ห้ามเพิ่ม Planning ใหม่ --}}
            <button type="button"
                    class="btn btn-sm btn-primary btn_add_planning_item"
                    data-planning_header_id="{{ $planning_header->id }}"
                    {{ $order_closed ? 'disabled' : '' }}
                    @if($order_closed) title="ออเดอร์นี้ถูกปิดแล้ว ไม่สามารถเพิ่ม Planning ได้" @endif>
                <i class="ti ti-plus me-1"></i>เพิ่ม Planning
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:40px">#</th>
                        <th>เลขที่ใบเบิก</th>
                        <th>Item No.</th>
                        {{-- <th class="text-center">Quantity</th> --}}
                        {{-- <th class="text-center">Lot</th> --}}
                        <th class="text-center">Weight</th>
                        <th class="text-center col-inplan">Inplan</th>
                        <th class="text-center">วันที่ส่งสินค้า</th>
                        <th class="text-center">Machine No.</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center" style="width:80px">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planning_header->plannings as $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        {{-- เลขที่ใบเบิก: ดึงจากเลขที่ใบแดง (red_bill_code) ของแต่ละ planning (10/08/2569) --}}
                        <td>{{ $item->red_bill_code ?? '-' }}</td>
                        <td>{{ $item->itemno ?? '-' }}</td>
                        {{-- <td class="text-center">{{ $item->quantity ?? '-' }}</td> --}}
                        {{-- <td class="text-center">{{ $item->lot ?? '-' }}</td> --}}
                        <td class="text-center">{{ number_format($item->weight,2) ?? '-' }}</td>
                        <td class="text-center col-inplan">{{ $item->inplan ? \Carbon\Carbon::parse($item->inplan)->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $item->senddate ? \Carbon\Carbon::parse($item->senddate)->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $item->machine_no ?? '-' }}</td>
                        <td class="text-center">
                            @if($item->planning_status)
                                <span class="badge bg-label-info">{{ $item->planning_status }}</span>
                            @else
                                <span class="badge bg-label-secondary">-</span>
                            @endif
                            {{-- บรรทัดที่ 2: สถานะปิดงานของ item (คอลัมน์ end_job ของ tb_planning) --}}
                            <br>
                            @if(($item->end_job ?? 'N') === 'Y')
                                <span class="badge bg-label-success">ปิดงาน</span>
                            @else
                                <span class="badge bg-label-warning">ยังไม่ปิดงาน</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-sm btn-icon btn-warning btn_edit_planning_item"
                                    data-planning_id="{{ $item->id }}"
                                    data-planning_header_id="{{ $planning_header->id }}"
                                    title="แก้ไข Planning">
                                <i class="ti ti-pencil text-white ti-sm"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="ti ti-inbox ti-lg d-block mb-1 opacity-50"></i>
                            ยังไม่มีรายการ Planning
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="ti ti-x me-1"></i>ปิด
    </button>
</div>
