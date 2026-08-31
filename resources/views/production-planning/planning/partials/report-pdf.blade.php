<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 9px; color: #000; }
        .title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 6px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #000; padding: 2px 3px; font-size: 8px; }
        table.data th { background-color: #e9ecef; text-align: center; }
        /* Inplan พื้นน้ำเงิน, Custwant พื้นแดง — ให้เหมือนฝั่งเว็บ */
        table.data th.col-inplan, table.data td.col-inplan { background-color: #cfe2ff; color: #084298; }
        table.data th.col-custwant, table.data td.col-custwant { background-color: #f8d7da; color: #842029; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <div class="title">รายงานวางแผนการผลิต</div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 3%;">#</th>
                <th style="width: 9%;">Orderno</th>
                <th style="width: 9%;">เลขที่ใบเบิก</th>
                <th style="width: 7%;">Company</th>
                <th class="col-inplan" style="width: 9%;">Inplan</th>
                <th class="col-custwant" style="width: 9%;">Custwant</th>
                <th style="width: 11%;">วันเวลาบรรจุเสร็จ</th>
                <th style="width: 11%;">Itemno</th>
                <th style="width: 8%;">Quantity</th>
                <th style="width: 9%;">MachineNo</th>
                <th style="width: 15%;">สถานะภายใน</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ $row->orderno ?: '-' }}</td>
                    <td class="text-center">{{ $row->red_bill_code ?: '-' }}</td>
                    <td class="text-center">{{ $row->company_display ?: '-' }}</td>
                    <td class="col-inplan text-center">
                        {{ $row->inplan ? \Carbon\Carbon::parse($row->inplan)->format('d/m/Y') : '-' }}
                        @if(!empty($row->work_shift)) (กะ {{ $row->work_shift }})@endif
                    </td>
                    <td class="col-custwant text-center">
                        {{ $row->custwant ? \Carbon\Carbon::parse($row->custwant)->format('d/m/Y') : '-' }}
                    </td>
                    <td class="text-center">
                        {{ $row->packing_datetie ? \Carbon\Carbon::parse($row->packing_datetie)->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td>{{ $row->itemno ?: '-' }}</td>
                    <td class="text-end">{{ $row->quantity !== null ? number_format($row->quantity, 2) : '-' }}</td>
                    <td class="text-center">{{ $row->machine_no ?: '-' }}</td>
                    <td>{{ $row->inner_status_text }} ({{ $row->end_job_label }})</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
