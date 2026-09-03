{{-- ตารางรายงานการขาดวัตถุดิบ — งานที่ยังไม่ปิดงาน (แบนราบ ไม่จัดกลุ่ม)
     ผังคอลัมน์ให้ตรงกับ PDF (material-shortage-pdf) + เพิ่ม "สถานะปัจจุบัน" ไว้ท้ายสุด (03/09/2569) --}}
<div class="mb-2 text-muted small">
    พบ <span class="fw-bold">{{ number_format($total) }}</span> รายการ (ยังไม่ปิดงาน + ขาด semi/วัตถุดิบ)
</div>

<table id="materialShortageTable" class="table table-bordered table-hover table-sm align-middle text-nowrap">
    <thead class="table-light">
        <tr class="text-center">
            <th style="width: 3%;">#</th>
            <th>แผนก</th>
            <th>เลขที่ใบแดง</th>
            <th>Revise</th>
            <th class="col-lack">ขาดวัตถุดิบ</th>
            <th class="col-lack">ขาด semi</th>
            <th class="col-custdue">Cust Due</th>
            <th>Cust no</th>
            <th>Cust Name</th>
            <th>Order Date</th>
            <th>PRODUCT NO</th>
            <th>น้ำหนัก</th>
            <th>สถานะปัจจุบัน</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $it)
            @php
                $custDue = $it->item_custwant ?: $it->header_custwant;
                $dept    = $it->item_company ?: $it->header_company;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $dept ?: '-' }}</td>
                <td class="text-center">{{ $it->red_bill_code ?: '-' }}</td>
                <td class="text-center">{{ $it->senddate ? \Carbon\Carbon::parse($it->senddate)->format('d/m/Y') : '-' }}</td>
                <td class="col-lack">{{ $it->lack_pigment ?: '' }}</td>
                <td class="col-lack">{{ $it->lack_semi ?: '' }}</td>
                <td class="text-center col-custdue">{{ $custDue ? \Carbon\Carbon::parse($custDue)->format('d/m/Y') : '-' }}</td>
                <td class="text-center">{{ $it->custno ?: '-' }}</td>
                <td>{{ $it->cust_name ?: '-' }}</td>
                <td class="text-center">{{ $it->order_date ? \Carbon\Carbon::parse($it->order_date)->format('d/m/Y') : '-' }}</td>
                <td>{{ $it->itemno ?: '-' }}</td>
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
                <td colspan="13" class="text-center text-muted py-4">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
            </tr>
        @endforelse
    </tbody>
</table>
