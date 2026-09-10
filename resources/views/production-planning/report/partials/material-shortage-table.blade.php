{{-- ตารางรายงานการขาดวัตถุดิบ — งานที่ยังไม่ปิดงาน (แบนราบ ไม่จัดกลุ่ม)
     ผังคอลัมน์ให้ตรงกับ PDF (material-shortage-pdf)
     Cust no / Order Date / สถานะปัจจุบัน ถูกซ่อน (10/09/2569)
     ตารางนี้ init เป็น DataTables ฝั่ง client (paging/search/sort) หลังโหลดผ่าน AJAX
       - render ทุกแถวจาก server แล้วให้ DataTables จัดหน้า/ค้นหา/เรียง (ไม่ใช่ serverSide)
       - คอลัมน์วันที่/ตัวเลขใส่ data-order (Y-m-d / ตัวเลขดิบ) เพื่อเรียงตามค่าจริง ไม่ใช่ข้อความบนจอ
       - คอลัมน์ # ปิด sort แล้ว renumber ตามผลลัพธ์ที่เรียง/ค้นหา (order.dt/search.dt ใน index) --}}
<div class="mb-2 text-muted small">
    พบ <span class="fw-bold">{{ number_format($total) }}</span> รายการ (ยังไม่ปิดงาน + ขาด semi/วัตถุดิบ)
</div>

<table id="materialShortageTable" class="table table-bordered table-hover table-sm align-middle text-nowrap w-100">
    <thead class="table-light">
        <tr class="text-center">
            <th style="width: 3%;">#</th>
            <th>แผนก</th>
            <th>เลขที่ใบแดง</th>
            <th>Revise</th>
            <th class="col-lack">ขาดวัตถุดิบ</th>
            <th class="col-lack">ขาด semi</th>
            <th class="col-custdue">Cust Due</th>
            {{-- <th>Cust no</th> --}}
            <th style="max-width: 200px;">Cust Name</th>
            {{-- <th>Order Date</th> --}}
            <th>PRODUCT NO</th>
            <th>น้ำหนัก</th>
            {{-- <th>สถานะปัจจุบัน</th> --}}
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $it)
            @php
                $custDue = $it->item_custwant ?: $it->header_custwant;
                $dept    = $it->item_company ?: $it->header_company;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $dept ?: '-' }}</td>
                <td class="text-center">{{ $it->red_bill_code ?: '-' }}</td>
                <td class="text-center" data-order="{{ $it->senddate ? \Carbon\Carbon::parse($it->senddate)->format('Y-m-d') : '' }}">{{ $it->senddate ? \Carbon\Carbon::parse($it->senddate)->format('d/m/Y') : '-' }}</td>
                <td class="col-lack">{{ $it->lack_pigment ?: '' }}</td>
                <td class="col-lack">{{ $it->lack_semi ?: '' }}</td>
                <td class="text-center col-custdue" data-order="{{ $custDue ? \Carbon\Carbon::parse($custDue)->format('Y-m-d') : '' }}">{{ $custDue ? \Carbon\Carbon::parse($custDue)->format('d/m/Y') : '-' }}</td>
                {{-- <td class="text-center">{{ $it->custno ?: '-' }}</td> --}}
                <td style="max-width: 200px;" data-order="{{ $it->cust_name }}">
                    <span class="d-inline-block text-truncate" style="max-width: 200px; vertical-align: middle;" title="{{ $it->cust_name }}">{{ $it->cust_name ?: '-' }}</span>
                </td>
                {{-- <td class="text-center">{{ $it->order_date ? \Carbon\Carbon::parse($it->order_date)->format('d/m/Y') : '-' }}</td> --}}
                <td>{{ $it->itemno ?: '-' }}</td>
                <td class="text-end" data-order="{{ $it->quantity !== null ? $it->quantity : '' }}">{{ $it->quantity !== null ? number_format($it->quantity, 2) : '-' }}</td>
                {{-- <td>
                    @if($it->planning_status)
                        <span class="badge bg-label-warning">{{ $it->planning_status }}</span>
                    @else
                        -
                    @endif
                </td> --}}
            </tr>
        @endforeach
    </tbody>
</table>
