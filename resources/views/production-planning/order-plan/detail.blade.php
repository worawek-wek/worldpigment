<div class="modal-header" style="background-color: #54BAB9; padding: 1rem 1.5rem;">
    <h5 class="modal-title text-white mb-0">
        <i class="ti ti-calendar-stats me-1"></i>
        แผนการผลิต Order
        @if($planning_header)
            <span class="ms-1 opacity-75">— {{ $planning_header->planning_code }}</span>
        @endif
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    @if(!$planning_header)
        <div class="text-muted text-center py-4">
            <i class="ti ti-info-circle ti-lg mb-2 d-block"></i>
            ไม่พบข้อมูลแผนการผลิต
        </div>
    @else
        <!-- ข้อมูลแผนการผลิต -->
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
                        <div class="text-muted small">Plan Type</div>
                        <div class="fw-semibold">{{ $planning_header->plan_type ?? '-' }}</div>
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
                        <div class="fw-semibold">{{ $planning_header->netqty ?? '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">วันที่ (Mdate)</div>
                        <div class="fw-semibold">{{ $planning_header->mdate ?? '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">วันที่ลูกค้าต้องการ</div>
                        <div class="fw-semibold">{{ $planning_header->custwant ?? '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">วันที่ส่ง</div>
                        <div class="fw-semibold">{{ $planning_header->senddate ?? '-' }}</div>
                    </div>
                    @if($planning_header->remark)
                    <div class="col-md-12 mb-2">
                        <div class="text-muted small">หมายเหตุ</div>
                        <div class="fw-semibold">{{ $planning_header->remark }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- รายการ Planning -->
        <h6 class="mb-2 text-primary">
            <i class="ti ti-list-details me-1"></i>รายการ Planning ({{ $planning_header->plannings->count() }})
        </h6>

        <div class="table-responsive mb-4">
            <table class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:40px">#</th>
                        <th>Item No.</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Lot</th>
                        <th class="text-center">Weight</th>
                        <th class="text-center">Machine No.</th>
                        <th class="text-center">Custwant</th>
                        <th class="text-center">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planning_header->plannings as $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $item->itemno ?? '-' }}</td>
                        <td class="text-center">{{ $item->quantity ?? '-' }}</td>
                        <td class="text-center">{{ $item->lot ?? '-' }}</td>
                        <td class="text-center">{{ $item->weight ? number_format($item->weight, 2) : '-' }}</td>
                        <td class="text-center">{{ $item->machine_no ?? '-' }}</td>
                        <td class="text-center">{{ $item->custwant ?? '-' }}</td>
                        <td class="text-center">
                            @if($item->planning_status)
                                <span class="badge bg-label-info">{{ $item->planning_status }}</span>
                            @else
                                <span class="badge bg-label-secondary">-</span>
                            @endif
                        </td>
                    </tr>

                    {{-- รายละเอียดที่เกี่ยวข้อง: Semi / Pigment sub-plans ที่ระบบสร้างภายใน --}}
                    @php
                        $sub_headers = $item->semi_headers->concat($item->pigment_headers);
                    @endphp
                    @if($sub_headers->count())
                    <tr>
                        <td></td>
                        <td colspan="7" class="bg-light">
                            <div class="small text-muted mb-1">
                                <i class="ti ti-corner-down-right me-1"></i>รายการที่เกี่ยวข้อง (Semi / Pigment)
                            </div>
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:40px">#</th>
                                        <th class="text-center">ประเภท</th>
                                        <th>Planning Code</th>
                                        <th>Item No.</th>
                                        <th class="text-center">จำนวนรายการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sub_headers as $sub)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-label-{{ $sub->plan_type === 'semi' ? 'warning' : 'success' }}">
                                                {{ strtoupper($sub->plan_type) }}
                                            </span>
                                        </td>
                                        <td>{{ $sub->planning_code ?? '-' }}</td>
                                        <td>{{ $sub->plannings->pluck('itemno')->filter()->implode(', ') ?: '-' }}</td>
                                        <td class="text-center">{{ $sub->plannings->count() }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
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
