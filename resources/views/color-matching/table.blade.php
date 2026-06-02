<div class="table-responsive">
    <table class="table table-hover align-middle">

        <thead class="table-light">
            <tr class="align-middle">
                <th class="align-middle text-center" style="width: 50px;">ลำดับ</th>
                <th class="align-middle">
                    เลขที่ใบนำส่ง
                    <br>
                    <small class="text-body-secondary fw-normal">วันที่ส่งเทียบสี</small>
                </th>
                <th class="align-middle">ลูกค้า</th>
                <th class="align-middle">ประเภทงาน</th>
                <th class="align-middle">สี / นำไปทำชิ้นงาน</th>
                <th class="align-middle">Color Matcher</th>
                <th class="align-middle">ปรับแก้ไข</th>
                <th class="align-middle">เลขที่ใบส่ง ต.ย.</th>
                <th class="align-middle">สถานะ</th>
                <th class="align-middle" width="120">จัดการ</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($list_data as $key => $row)
                @php
                    $revBadge = match (trim((string)$row->Adj)) {
                        'New'      => 'bg-label-info',
                        'Revise 1' => 'bg-label-warning',
                        'Revise 2' => 'bg-label-danger',
                        default    => 'bg-label-secondary',
                    };

                    if ($row->cancel == 1) {
                        $statusClass = 'bg-label-danger';
                        $statusIcon  = 'ti-x';
                        $statusText  = 'ยกเลิก';
                    } elseif (!empty(trim((string)$row->RminWating))) {
                        $statusClass = 'bg-label-warning';
                        $statusIcon  = 'ti-hourglass';
                        $statusText  = 'รอวัตถุดิบ';
                    } elseif (empty(trim((string)$row->Testno))) {
                        $statusClass = 'bg-label-info';
                        $statusIcon  = 'ti-palette';
                        $statusText  = 'กำลังเทียบสี';
                    } else {
                        $statusClass = 'bg-label-success';
                        $statusIcon  = 'ti-package-export';
                        $statusText  = 'ส่ง ต.ย. แล้ว';
                    }
                @endphp

                <tr>
                    <td class="text-center text-muted">
                        {{ $list_data->firstItem() + $key }}
                    </td>

                    <td>
                        <strong class="text-primary">{{ $row->SendNo }}</strong>
                        <br>
                        <small class="text-muted">
                            {{ $row->TestDate ? \Carbon\Carbon::parse($row->TestDate)->format('d/m/Y') : '-' }}
                        </small>
                    </td>

                    <td>
                        <span class="badge bg-label-secondary mb-1">{{ $row->custno ?? '-' }}</span>
                        <br>
                        <small>{{ $row->custname ?: '—' }}</small>
                    </td>

                    <td>
                        @if ($row->Type_Work)
                            <span class="badge bg-label-primary">{{ $row->Type_Work }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td>
                        <div>
                            <div class="small fw-semibold">{{ $row->color ?: '—' }}</div>
                            @if ($row->Model)
                                <small class="text-muted">{{ $row->Model }}</small>
                            @endif
                        </div>
                    </td>

                    <td>{{ $row->ColorMatcher ?: '-' }}</td>

                    <td>
                        @if ($row->Adj)
                            <span class="badge {{ $revBadge }}">{{ $row->Adj }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td>
                        @if (!empty(trim((string)$row->Testno)))
                            <strong>{{ $row->Testno }}</strong>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td>
                        <span class="badge {{ $statusClass }}">
                            <i class="ti {{ $statusIcon }} me-1"></i>
                            {{ $statusText }}
                        </span>
                    </td>

                    <td>
                        <button class="btn btn-sm btn-icon btn-label-primary"
                            title="แก้ไขใบนำส่งเทียบสี"
                            onclick="view('{{ $row->SendNo }}')">
                            <i class="ti ti-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-icon btn-label-info"
                            title="แก้ไขใบส่ง ต.ย."
                            onclick="viewSampleDelivery('{{ $row->SendNo }}')">
                            <i class="ti ti-package"></i>
                        </button>
                        <button class="btn btn-sm btn-icon btn-label-danger"
                            title="ลบ"
                            onclick="Delete('{{ $row->SendNo }}')">
                            <i class="ti ti-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                        <i class="ti ti-database-off fs-2 d-block mb-2 opacity-50"></i>
                        ไม่พบข้อมูลที่ตรงกับเงื่อนไข
                    </td>
                </tr>
            @endforelse

        </tbody>

    </table>
</div>

<div class="cm-pagination-wrap mt-4">
    @include('layout/pagination')
</div>

<style>
    /* ─── ปรับ pagination ของหน้าเทียบสีให้ชิดขวา ─── */
    .cm-pagination-wrap .pagination {
        justify-content: flex-end;
        margin-bottom: 0;
    }
    .cm-pagination-wrap .dataTables_paginate {
        text-align: right;
    }
    .cm-pagination-wrap .dataTables_info {
        padding-top: 0.4rem;
    }
</style>
