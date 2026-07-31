{{-- ตารางรายงานผลิตตามเครื่องจักร (จัดกลุ่มตามเครื่องจักร) — โหลดผ่าน AJAX --}}
<div class="d-flex justify-content-between align-items-center mb-2">
    <span class="text-muted small">พบทั้งหมด {{ number_format($total) }} รายการ</span>
</div>

<table class="table table-striped table-hover nowrap align-middle" style="width:100%">
    <thead class="table-light">
        <tr>
            <th class="text-center" style="width:60px;">#</th>
            <th>รหัสเครื่องจักร</th>
            <th class="text-center">วัน Inplan</th>
            <th>Cust Name</th>
            <th class="text-center">เลขที่ใบแดง</th>
            <th>Item No</th>
            <th class="text-center">Lot</th>
            <th class="text-end">น้ำหนักผลิต</th>
        </tr>
    </thead>
    <tbody>
        @php $rownum = 0; @endphp
        @forelse($groups as $group)
            {{-- หัวกลุ่มเครื่องจักร --}}
            <tr class="table-secondary">
                <td colspan="8">
                    <i class="ti ti-tools me-1"></i>
                    <strong>เครื่องจักร: {{ $group['machine'] !== '' ? $group['machine'] : 'ไม่ระบุเครื่องจักร' }}</strong>
                    <span class="text-muted small ms-1">({{ number_format($group['items']->count()) }} รายการ)</span>
                </td>
            </tr>
            @foreach($group['items'] as $it)
                <tr>
                    <td class="text-center">{{ ++$rownum }}</td>
                    <td>{{ $it->machine_no ?: '-' }}</td>
                    <td class="text-center">{{ $it->inplan ? \Carbon\Carbon::parse($it->inplan)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $it->cust_name ?: '-' }}</td>
                    <td class="text-center">{{ $it->red_bill_code ?: '-' }}</td>
                    <td>{{ $it->itemno ?: '-' }}</td>
                    <td class="text-center">{{ $it->lot ?: '-' }}</td>
                    <td class="text-end">{{ $it->quantity !== null ? number_format($it->quantity, 2) : '-' }}</td>
                </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-4">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
            </tr>
        @endforelse
    </tbody>
</table>
