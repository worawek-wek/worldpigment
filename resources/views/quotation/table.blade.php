<div class="table-responsive">
    <table class="table table-hover align-middle">

        <thead class="table-light">
            <tr class="align-middle">
                <th class="align-middle text-center" style="width: 50px;">ลำดับ</th>
                <th class="align-middle">
                    เลขที่ใบเสนอราคา
                    <br>
                    <small class="text-body-secondary fw-normal">วันที่เสนอราคา</small>
                </th>
                <th class="align-middle">ลูกค้า</th>
                <th class="align-middle text-center">ชนิดสินค้า</th>
                <th class="align-middle text-end">จำนวนรายการ</th>
                <th class="align-middle text-end">มูลค่ารวม</th>
                <th class="align-middle">หมายเหตุ</th>
                <th class="align-middle text-center" width="160">จัดการ</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($list_data as $key => $row)
                <tr>
                    <td class="text-center text-muted">{{ $list_data->firstItem() + $key }}</td>

                    <td>
                        <span class="badge bg-label-primary mb-1">
                            <i class="ti ti-file-invoice me-1"></i>ใบเสนอราคา
                        </span>
                        <br>
                        <strong class="text-primary">{{ $row->Qno }}</strong>
                        <br>
                        <small class="text-muted">
                            {{ $row->Qdate ? \Carbon\Carbon::parse($row->Qdate)->format('d/m/Y') : '-' }}
                        </small>
                    </td>

                    <td>
                        <span class="badge bg-label-secondary mb-1">{{ $row->Custid ?: '-' }}</span>
                        <br>
                        <small>{{ $row->cust_name ?: '—' }}</small>
                    </td>

                    <td class="text-center">
                        @if ($row->PDtype)
                            <span class="badge bg-label-info">{{ $row->PDtype }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td class="text-end">{{ number_format($row->item_count) }}</td>

                    <td class="text-end">{{ number_format($row->total_net, 2) }}</td>

                    <td>
                        @if ($row->exam == 1)
                            <span class="badge bg-label-warning"><i class="ti ti-flask me-1"></i>พร้อมตัวอย่าง</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>

                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            @if ($row->Custid)
                                <button class="btn btn-sm btn-icon btn-label-success" title="ประวัติใบเสนอราคาของลูกค้ารายนี้"
                                    onclick="quotationHistory('{{ $row->Custid }}')">
                                    <i class="ti ti-history"></i>
                                </button>
                            @endif
                            <button class="btn btn-sm btn-icon btn-label-secondary" title="ดูรายละเอียด"
                                onclick="quotationView('{{ $row->Qno }}')">
                                <i class="ti ti-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-label-primary" title="แก้ไข"
                                onclick="quotationEdit('{{ $row->Qno }}')">
                                <i class="ti ti-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-label-info" title="พิมพ์"
                                onclick="quotationPrint('{{ $row->Qno }}')">
                                <i class="ti ti-printer"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="ti ti-database-off fs-2 d-block mb-2 opacity-50"></i>
                        ไม่พบข้อมูลที่ตรงกับเงื่อนไข
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>
</div>

<div class="cm-pagination-wrap mt-4 mb-3 px-3">
    @include('layout/pagination')
</div>

<style>
    .cm-pagination-wrap .pagination { justify-content: flex-end; margin-bottom: 0; }
</style>
