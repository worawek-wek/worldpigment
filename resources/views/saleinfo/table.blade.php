<div class="table-responsive">
    <table class="table table-hover align-middle">

        <thead class="pr-thead-dark">
            <tr class="align-middle">
                <th class="align-middle text-center" style="width: 50px;">ลำดับ</th>
                <th class="align-middle">
                    ลูกค้า
                    <br>
                    <small class="text-body-secondary fw-normal">รหัส / ชื่อบริษัท</small>
                </th>
                <th class="align-middle">
                    สินค้า
                    <br>
                    <small class="text-body-secondary fw-normal">ชื่อสินค้า / รหัสสินค้า</small>
                </th>
                <th class="align-middle text-center" style="width: 120px;">วันที่เริ่มซื้อ</th>
                <th class="align-middle text-end" style="width: 120px;">ราคา</th>
                <th class="align-middle">เงื่อนไข / หมายเหตุ</th>
                <th class="align-middle text-center" style="width: 90px;">จัดการ</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($list_data as $key => $row)
                <tr>
                    <td class="text-center text-muted">
                        {{ $list_data->firstItem() + $key }}
                    </td>

                    <td>
                        <span class="badge bg-label-secondary mb-1">{{ $row->CustNo ?: '-' }}</span>
                        <br>
                        <small>{{ $row->custname ?: '—' }}</small>
                    </td>

                    <td>
                        <strong class="text-primary">{{ $row->st_code ?: '—' }}</strong>
                        <br>
                        <small class="text-muted">รหัสสินค้า: {{ $row->ITEMNO ?: '—' }}</small>
                    </td>

                    <td class="text-center">
                        {{ $row->DATE ? \Carbon\Carbon::parse($row->DATE)->format('d/m/Y') : '-' }}
                    </td>

                    <td class="text-end fw-semibold">
                        {{ $row->PRICE !== null ? number_format($row->PRICE, 2) : '-' }}
                    </td>

                    <td>
                        @if ($row->term)
                            <span class="badge bg-label-primary mb-1">{{ $row->term }}</span>
                            <br>
                        @endif
                        <small class="text-muted">{{ $row->REM1 ?: '—' }}</small>
                    </td>

                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            <button class="btn btn-sm btn-icon btn-theme-saleinfo"
                                title="แก้ไขราคา"
                                onclick="viewSaleinfo('{{ $row->id }}')">
                                <i class="ti ti-edit"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="ti ti-database-off fs-2 d-block mb-2 opacity-50"></i>
                        ไม่พบข้อมูลที่ตรงกับเงื่อนไข
                    </td>
                </tr>
            @endforelse

        </tbody>

    </table>
</div>

<div class="pr-pagination-wrap mt-4 mb-3">
    @include('layout/pagination')
</div>

<style>
    .pr-pagination-wrap .pagination {
        justify-content: flex-end;
        margin-bottom: 0;
    }
    .pr-pagination-wrap .dataTables_info {
        padding-top: 0.4rem;
    }
</style>
