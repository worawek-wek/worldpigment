<div class="table-responsive">
    <table class="table table-hover align-middle">

        @php
            // สถานะการเรียงปัจจุบัน (ส่งมาจาก controller) — เลือกไอคอนหัวตาราง
            $sortCol = $sort_col ?? 'Mdate';
            $sortDir = $sort_dir ?? 'desc';
            $sortIcon = function ($col) use ($sortCol, $sortDir) {
                if ($sortCol === $col) {
                    $dir = $sortDir === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down';
                    return '<i class="ti ' . $dir . ' th-sort-icon active"></i>';
                }
                return '<i class="ti ti-arrows-sort th-sort-icon"></i>';
            };
            $sortActive = fn ($col) => $sortCol === $col ? ' col-sorted' : '';
        @endphp

        <thead class="table-light">
            <tr class="align-middle">
                <th class="align-middle text-center" style="width: 50px;">ลำดับ</th>
                <th class="align-middle th-sort{{ $sortActive('Orderno') }}" data-sort="Orderno">
                    เลขที่ใบสั่ง {!! $sortIcon('Orderno') !!}
                    <br>
                    <small class="text-body-secondary fw-normal">P/O No.</small>
                </th>
                <th class="align-middle th-sort{{ $sortActive('Mdate') }}" data-sort="Mdate">
                    วันที่สั่ง {!! $sortIcon('Mdate') !!}
                </th>
                <th class="align-middle th-sort{{ $sortActive('Custno') }}" data-sort="Custno">ลูกค้า {!! $sortIcon('Custno') !!}</th>
                <th class="align-middle">รหัสสินค้า</th>
                <th class="align-middle text-center th-sort{{ $sortActive('Company') }}" data-sort="Company">ผลิตที่ {!! $sortIcon('Company') !!}</th>
                <th class="align-middle text-end th-sort{{ $sortActive('item_count') }}" data-sort="item_count">รายการ {!! $sortIcon('item_count') !!}</th>
                <th class="align-middle text-end th-sort{{ $sortActive('total_prod') }}" data-sort="total_prod">
                    น้ำหนักผลิต {!! $sortIcon('total_prod') !!}
                    <br>
                    <small class="text-body-secondary fw-normal">ก.ก.</small>
                </th>
                <th class="align-middle text-center" width="110">จัดการ</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($list_data as $key => $row)
                @php
                    // ประเภทใบสั่ง = 2 ตัวอักษรหน้าเลขที่ (CM/CI/HM/… ตาม radio บนฟอร์ม)
                    $type = strtoupper(substr((string) $row->Orderno, 0, 2));
                @endphp
                <tr>
                    <td class="text-center text-muted">{{ $list_data->firstItem() + $key }}</td>

                    <td class="{{ $sortActive('Orderno') }}">
                        <span class="badge bg-label-primary mb-1">{{ $type }}</span>
                        <br>
                        <strong class="text-primary">{{ $row->Orderno }}</strong>
                        @if (trim((string) $row->PO) !== '')
                            <br>
                            <small class="text-muted">P/O {{ $row->PO }}</small>
                        @endif
                    </td>

                    <td class="{{ $sortActive('Mdate') }}">
                        {{ $row->Mdate ? \Carbon\Carbon::parse($row->Mdate)->format('d/m/Y') : '-' }}
                        @if ($row->first_senddate)
                            <br>
                            <small class="text-muted">
                                <i class="ti ti-truck-delivery me-1"></i>ส่ง
                                {{ \Carbon\Carbon::parse($row->first_senddate)->format('d/m/Y') }}
                            </small>
                        @endif
                    </td>

                    <td class="{{ $sortActive('Custno') }}">
                        <span class="badge bg-label-secondary mb-1">{{ $row->Custno ?: '-' }}</span>
                        <br>
                        <small>{{ $row->cust_name ?: ($row->Custname ?: '—') }}</small>
                    </td>

                    <td>
                        <small>{{ $row->itemno_list ?: '—' }}</small>
                    </td>

                    <td class="text-center{{ $sortActive('Company') }}">
                        @if (trim((string) $row->Company) !== '')
                            <span class="badge bg-label-info">{{ $row->Company }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td class="text-end{{ $sortActive('item_count') }}">{{ number_format($row->item_count) }}</td>

                    <td class="text-end{{ $sortActive('total_prod') }}">
                        {{ number_format($row->total_prod, 2) }}
                        @if ($row->total_stock > 0)
                            <br>
                            <small class="text-muted">สต๊อก {{ number_format($row->total_stock, 2) }}</small>
                        @endif
                    </td>

                    <td class="text-center">
                        <button class="btn btn-sm btn-icon btn-label-primary" title="เปิดฟอร์มบันทึกใบสั่งซื้อ"
                            onclick="orderOpen('{{ $row->Orderno }}')">
                            <i class="ti ti-edit"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
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
