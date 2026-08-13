{{-- ตารางรายงานการขาดวัตถุดิบ — งานที่ยังไม่ปิดงาน (แบนราบ ไม่จัดกลุ่ม) --}}
<div class="mb-2 text-muted small">
    พบ <span class="fw-bold">{{ number_format($total) }}</span> รายการ (ยังไม่ปิดงาน)
</div>

<table id="materialShortageTable" class="table table-bordered table-hover table-sm align-middle">
    <thead class="table-light">
        <tr class="text-center">
            <th style="width: 3%;">#</th>
            <th>แผนก</th>
            <th>เลขที่ใบแดง</th>
            <th>เครื่องจักร</th>
            <th>IN PLAN</th>
            <th>รหัสลูกค้า</th>
            <th>ชื่อลูกค้า</th>
            <th>Order No</th>
            <th>รหัสสินค้า</th>
            <th>LOT</th>
            <th>น้ำหนัก</th>
            <th>สถานะปัจจุบัน</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $it)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ ($it->item_company ?: $it->header_company) ?: '-' }}</td>
                <td class="text-center">{{ $it->red_bill_code ?: '-' }}</td>
                <td>{{ $it->machine_no ?: '-' }}</td>
                <td class="text-center">{{ $it->inplan ? \Carbon\Carbon::parse($it->inplan)->format('d/m/Y') : '-' }}</td>
                <td class="text-center">{{ $it->custno ?: '-' }}</td>
                <td>{{ $it->cust_name ?: '-' }}</td>
                <td class="text-center">{{ $it->orderno ?: '-' }}</td>
                <td>{{ $it->itemno ?: '-' }}</td>
                <td class="text-center">{{ $it->lot ?: '-' }}</td>
                <td class="text-end">{{ $it->quantity !== null ? number_format($it->quantity, 2) : '-' }}</td>
                <td>
                    @if($it->planning_status)
                        <span class="badge bg-label-warning">{{ $it->planning_status }}</span>
                    @else
                        -
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="text-center text-muted py-4">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
            </tr>
        @endforelse
    </tbody>
</table>
