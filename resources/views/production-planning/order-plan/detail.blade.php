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
                        <div class="fw-semibold">{{ $planning_header->mdate ? \Carbon\Carbon::parse($planning_header->mdate)->format('d/m/Y') : '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">วันที่ลูกค้าต้องการ</div>
                        <div class="fw-semibold">{{ $planning_header->custwant ? \Carbon\Carbon::parse($planning_header->custwant)->format('d/m/Y') : '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">วันที่ส่ง</div>
                        <div class="fw-semibold">{{ $planning_header->senddate ? \Carbon\Carbon::parse($planning_header->senddate)->format('d/m/Y') : '-' }}</div>
                    </div>
                    {{-- สถานะปิดงานของ Order (อ้างอิงคอลัมน์ end_order ของ tb_planning_header) --}}
                    <div class="col-md-3 mb-2">
                        <div class="text-muted small">สถานะปิดorder</div>
                        <div class="fw-semibold">
                            @if(($planning_header->end_order ?? 'N') === 'Y')
                                <span class="badge bg-label-success">ปิดงาน</span>
                            @else
                                <span class="badge bg-label-warning">ยังไม่ปิดงาน</span>
                            @endif
                        </div>
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
                        <th class="text-left col-2">Item No.</th>
                        {{-- <th class="text-center">Quantity</th> --}}
                        <th class="text-center col-1">Lot</th>
                        <th class="text-center col-1">Weight</th>
                        <th class="text-center col-2"">Machine No.</th>
                        <th class="text-center col-1">Inplan</th>
                        <th class="text-center col-2">Custwant</th>
                        <th class="text-center col-1">สถานะ</th>
                        <th class="text-center col-3" >หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planning_header->plannings as $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $item->itemno ?? '-' }}</td>
                        {{-- <td class="text-center">{{ $item->quantity ?? '-' }}</td> --}}
                        <td class="text-center">{{ $item->lot ?? '-' }}</td>
                        <td class="text-center">{{ $item->weight ? number_format($item->weight, 2) : '-' }}</td>
                        <td class="text-center">{{ $item->machine_no ?? '-' }}</td>
                        <td class="text-center">{{ $item->inplan ? \Carbon\Carbon::parse($item->inplan)->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $item->custwant ? \Carbon\Carbon::parse($item->custwant)->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">
                            @if($item->planning_status)
                                <span class="badge bg-label-info">{{ $item->planning_status }}</span>
                            @else
                                <span class="badge bg-label-secondary">-</span>
                            @endif
                            {{-- บรรทัดที่ 2: สถานะปิดงานของ item (อ้างอิงคอลัมน์ end_job ของ tb_planning) --}}
                            <br>
                            @if(($item->end_job ?? 'N') === 'Y')
                                <span class="badge bg-label-success">ปิดงาน</span>
                            @else
                                <span class="badge bg-label-warning">ยังไม่ปิดงาน</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->remark ?? '-' }}</td>
                    </tr>

                    {{-- รายละเอียดที่เกี่ยวข้อง: Semi / Pigment sub-plans (แสดงย่อยลงไปแบบ recursive) --}}
                    @if($item->subHeadersRecursive->count())
                    <tr>
                        <td></td>
                        <td colspan="8" class="bg-light">
                            @include('production-planning.order-plan.partials.related-plans', ['planning' => $item])
                        </td>
                    </tr>
                    @endif
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
