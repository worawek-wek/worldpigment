<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 9px; color: #000; }
        .title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 2px; }
        .summary { text-align: center; font-size: 9px; margin-bottom: 6px; color: #333; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #000; padding: 2px 3px; font-size: 8px; }
        table.data th { background-color: #e9ecef; text-align: center; }
        .group-row td { background-color: #f1f3f5; font-weight: bold; }
        .step-row td { background-color: #f8f9fa; font-style: italic; }
        .sum-row td { font-weight: bold; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <div class="title">รายงานผลิตตามเครื่องจักร</div>
    <div class="summary">{{ $summary }}</div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 3%;">#</th>
                <th style="width: 7%;">วันที่ลงแผน</th>
                <th style="width: 7%;">Revise</th>
                <th style="width: 13%;">Cust Name</th>
                <th style="width: 7%;">เลขที่ใบเบิก</th>
                <th style="width: 9%;">PRODUCT NO</th>
                <th style="width: 6%;">LOT</th>
                <th style="width: 7%;">น้ำหนักออเดอร์</th>
                <th style="width: 4%;">TP</th>
                <th style="width: 6%;">Resin</th>
                <th style="width: 5%;">CODE</th>
                <th style="width: 6%;">Speed (RPM)</th>
                <th style="width: 4%;">Pack</th>
                <th style="width: 4%;">Batch</th>
                <th style="width: 6%;">สูตรตัวอย่าง</th>
                <th style="width: 9%;">Remark</th>
            </tr>
        </thead>
        <tbody>
            @php $rownum = 0; @endphp
            @forelse($groups as $group)
                @php $machineLabel = $group['machine'] !== '' ? $group['machine'] : 'ไม่ระบุเครื่องจักร'; $groupSum = 0; @endphp
                <tr class="group-row">
                    <td colspan="16">เครื่องจักร: {{ $machineLabel }}</td>
                </tr>
                @foreach($group['items'] as $it)
                    @php $groupSum += (float) ($it->quantity ?? 0); @endphp
                    {{-- แถวผลิตสินค้า --}}
                    <tr>
                        <td class="text-center">{{ ++$rownum }}</td>
                        <td class="text-center">{{ $it->inplan ? \Carbon\Carbon::parse($it->inplan)->format('d/m/Y') : '-' }}</td>
                        <td></td> {{-- Revise --}}
                        <td>{{ $it->cust_name ?: '-' }}</td>
                        <td class="text-center">{{ $it->red_bill_code ?: '-' }}</td>
                        <td>{{ $it->itemno ?: '-' }}</td>
                        <td class="text-center">{{ $it->lot ?: '-' }}</td>
                        <td class="text-end">{{ $it->quantity !== null ? number_format($it->quantity, 2) : '-' }}</td>
                        <td class="text-end">{{ $it->weight !== null ? number_format($it->weight, 2) : '' }}</td> {{-- TP = น้ำหนัก TP (Weight) --}}
                        <td></td> {{-- Resin --}}
                        <td></td> {{-- CODE --}}
                        <td class="text-center">{{ $it->speed_rpm ?: '' }}</td>
                        <td></td> {{-- Pack --}}
                        <td></td> {{-- Batch --}}
                        <td></td> {{-- สูตรตัวอย่าง --}}
                        <td>{{ $it->remark ?: '' }}</td>
                    </tr>
                    {{-- แถวขั้นตอน "สถานะวิธีการผลิต" (แสดงใต้แถวผลิต) --}}
                    @foreach($it->steps as $s)
                        <tr class="step-row">
                            <td class="text-center">↳</td>
                            <td class="text-center">{{ $s->work_date ? \Carbon\Carbon::parse($s->work_date)->format('d/m/Y') : '-' }}</td>
                            <td colspan="14">
                                ขั้นตอน: {{ $s->method_name ?: '-' }}
                                ({{ $s->start_time ? substr($s->start_time, 0, 5) : '--' }}–{{ $s->end_time ? substr($s->end_time, 0, 5) : '--' }})
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                <tr class="sum-row">
                    <td colspan="5" class="text-end">รวม {{ $machineLabel }}</td>
                    <td class="text-center">{{ number_format($group['items']->count()) }} รายการ</td>
                    <td></td>
                    <td class="text-end">{{ number_format($groupSum, 2) }}</td>
                    <td colspan="8"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="16" class="text-center" style="padding: 14px;">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
