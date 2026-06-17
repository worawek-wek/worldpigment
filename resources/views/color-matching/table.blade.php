<div class="table-responsive">
    <table class="table table-hover align-middle">

        <thead class="cm-thead-dark">
            <tr class="align-middle">
                <th class="align-middle text-center" style="width: 50px;">ลำดับ</th>
                <th class="align-middle">
                    เอกสาร / เลขที่
                    <br>
                    <small class="text-body-secondary fw-normal">วันที่ส่งเทียบสี</small>
                </th>
                <th class="align-middle">ลูกค้า</th>
                <th class="align-middle">ประเภทงาน</th>
                <th class="align-middle">สี / นำไปทำชิ้นงาน</th>
                <th class="align-middle">Color Matcher</th>
                <th class="align-middle">สถานะ</th>
                <th class="align-middle text-center" width="120">จัดการ</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($list_data as $key => $row)
                @php
                    // มี Testno = ใบส่ง ต.ย. (SD), ไม่มี = ใบนำส่งเทียบสี (CM)
                    $isSD = !empty(trim((string)$row->Testno));

                    if ($row->cancel == 1) {
                        $statusClass = 'bg-label-danger';
                        $statusIcon  = 'ti-x';
                        $statusText  = 'ยกเลิก';
                    } elseif (!empty(trim((string)$row->RminWating))) {
                        $statusClass = 'bg-label-warning';
                        $statusIcon  = 'ti-hourglass';
                        $statusText  = 'รอวัตถุดิบ';
                    } elseif (empty(trim((string)$row->Testno))) {
                        $statusClass = 'badge-status-cm';
                        $statusIcon  = 'ti-palette';
                        $statusText  = 'กำลังเทียบสี';
                    } else {
                        $statusClass = 'bg-label-success';
                        $statusIcon  = 'ti-package-export';
                        $statusText  = 'ส่ง ต.ย. แล้ว';
                    }
                @endphp

                <tr class="{{ $isSD ? 'tr-sd' : 'tr-cm' }}">
                    <td class="text-center text-muted">
                        {{ $list_data->firstItem() + $key }}
                    </td>

                    <td>
                        @if ($isSD)
                            <span class="badge badge-doc-sd mb-1">
                                <i class="ti ti-package me-1"></i>ใบส่ง ต.ย.
                            </span>
                            <br>
                            <strong class="text-primary">{{ $row->Testno ?: '—' }}</strong>
                            <br>
                            <small class="text-muted">อ้างอิงใบเทียบสี: {{ $row->SendNo ?: '—' }}</small>
                        @else
                            <span class="badge badge-doc-cm mb-1">
                                <i class="ti ti-file-text me-1"></i>ใบนำส่งเทียบสี
                            </span>
                            <br>
                            <strong class="text-primary">{{ $row->SendNo }}</strong>
                        @endif
                        <br>
                        <small class="text-muted">
                            {{ $row->DsendT ? \Carbon\Carbon::parse($row->DsendT)->format('d/m/Y') : '-' }}
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

                    <td class="td-status">
                        <span class="badge {{ $statusClass }}">
                            <i class="ti {{ $statusIcon }} me-1"></i>
                            {{ $statusText }}
                        </span>
                    </td>

                    <td class="text-center td-action">
                        <div class="d-inline-flex gap-1">
                            {{-- ดูรายละเอียด (อ่านอย่างเดียว) — แสดงคนละหน้าตาตามชนิดเอกสาร --}}
                            <button class="btn btn-sm btn-icon {{ $isSD ? 'btn-theme-sd' : 'btn-theme-cm' }}"
                                title="ดูรายละเอียด"
                                onclick="viewDetail('{{ $row->id }}')">
                                <i class="ti ti-eye"></i>
                            </button>

                            @if ($isSD)
                                <button class="btn btn-sm btn-icon btn-theme-sd"
                                    title="แก้ไขใบส่ง ต.ย."
                                    onclick="viewSampleDelivery('{{ $row->id }}')">
                                    <i class="ti ti-edit"></i>
                                </button>
                            @else
                                <button class="btn btn-sm btn-icon btn-theme-cm"
                                    title="แก้ไขใบนำส่งเทียบสี"
                                    onclick="view('{{ $row->id }}')">
                                    <i class="ti ti-edit"></i>
                                </button>
                            @endif
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

<div class="cm-pagination-wrap mt-4 mb-3">
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
