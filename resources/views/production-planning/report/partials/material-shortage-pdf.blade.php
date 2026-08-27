<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 8px; color: #000; }
        .title { text-align: center; font-size: 13px; font-weight: bold; margin-bottom: 2px; }
        .summary { text-align: center; font-size: 8px; margin-bottom: 5px; color: #333; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #000; padding: 1px 2px; font-size: 6.5px; }
        table.data th { background-color: #e9ecef; text-align: center; }
        .status { background-color: #fff8b3; } /* สถานะปัจจุบัน — เน้นเหลืองตามฟอร์มต้นฉบับ */
        .text-center { text-align: center; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <div class="title">รายงานการขาดวัตถุดิบ</div>
    <div class="summary">{{ $summary }}</div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 3%;">#</th>
                <th style="width: 6%;">แผนก</th>
                <th style="width: 6%;">เลขที่ใบแดง</th>
                <th style="width: 7%;">MACHINE No.</th>
                <th style="width: 5%; background-color: #cfe2ff;">IN PLAN</th>
                <th style="width: 5%;">Revise</th>
                <th style="width: 6%;">สถานะปัจจุบัน</th>
                <th style="width: 12%;">ขาด semi</th>
                <th style="width: 10%;">ขาดวัตถุดิบ</th>
                <th style="width: 5%; background-color: #f8d7da; color: #842029;">Cust Due</th>
                <th style="width: 4%;">Cust no</th>
                <th style="width: 12%;">Cust Name</th>
                <th style="width: 4%;">SaleNo</th>
                <th style="width: 5%;">Order Date</th>
                <th style="width: 7%;">Order No</th>
                <th style="width: 8%;">PRODUCT NO</th>
                <th style="width: 5%;">LOT</th>
                <th style="width: 5%;">น้ำหนัก</th>
                <th style="width: 4%;">ส่งชั่งสี</th>
                <th style="width: 5%;">เริ่มผลิต</th>
                <th style="width: 5%;">วันที่ส่ง QC</th>
                <th style="width: 5%;">เวลาที่ส่ง QC</th>
                <th style="width: 5%;">สถานะ QC</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $it)
                @php $custDue = $it->item_custwant ?: $it->header_custwant; $dept = $it->item_company ?: $it->header_company; @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $dept ?: '-' }}</td>
                    <td class="text-center">{{ $it->red_bill_code ?: '-' }}</td>
                    <td>{{ $it->machine_no ?: '-' }}</td>
                    <td class="text-center" style="background-color: #cfe2ff;">{{ $it->inplan ? \Carbon\Carbon::parse($it->inplan)->format('d/m/y') : '-' }}</td>
                    <td class="text-center">{{ $it->senddate ? \Carbon\Carbon::parse($it->senddate)->format('d/m/y') : '' }}</td> {{-- Revise = senddate (กำหนดส่งทบทวน) --}}
                    <td class="status">{{ $it->planning_status ?: '' }}</td>
                    <td class="status">{{ $it->lack_semi ?: '' }}</td>
                    <td class="status">{{ $it->lack_pigment ?: '' }}</td>
                    <td class="text-center" style="background-color: #f8d7da; color: #842029;">{{ $custDue ? \Carbon\Carbon::parse($custDue)->format('d/m/y') : '-' }}</td>
                    <td class="text-center">{{ $it->custno ?: '-' }}</td>
                    <td>{{ $it->cust_name ?: '-' }}</td>
                    <td class="text-center">{{ $it->saleno ?: '' }}</td>
                    <td class="text-center">{{ $it->order_date ? \Carbon\Carbon::parse($it->order_date)->format('d/m/y') : '-' }}</td>
                    <td class="text-center">{{ $it->orderno ?: '-' }}</td>
                    <td>{{ $it->itemno ?: '-' }}</td>
                    <td class="text-center">{{ $it->lot ?: '-' }}</td>
                    <td class="text-end">{{ $it->quantity !== null ? number_format($it->quantity, 2) : '' }}</td> {{-- น้ำหนัก (quantity) --}}
                    <td></td> {{-- ส่งชั่งสี (ยังไม่มีฟิลด์) --}}
                    <td class="text-center">{{ $it->start_date ? \Carbon\Carbon::parse($it->start_date)->format('d/m/y') : '' }}</td>
                    <td class="text-center">{{ $it->qc_date ? \Carbon\Carbon::parse($it->qc_date)->format('d/m/y') : '' }}</td>
                    <td class="text-center">{{ $it->qc_time ? substr($it->qc_time, 0, 5) : '' }}</td>
                    <td class="text-center">{{ $it->qc_status ?: '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="23" class="text-center" style="padding: 14px;">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
