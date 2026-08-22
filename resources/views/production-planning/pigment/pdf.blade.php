<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 8px; color: #000; }
        .title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 2px; }
        .summary { text-align: center; font-size: 8px; margin-bottom: 5px; color: #333; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #000; padding: 1px 3px; font-size: 7px; }
        table.data th { background-color: #d9e1f2; text-align: center; vertical-align: middle; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <div class="title">ใบขอสั่ง PIGMENT</div>
    <div class="summary">{{ $summary }}</div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 7%;">วันที่ขอ</th>
                <th style="width: 10%;">Item No.</th>
                <th style="width: 7%;">วันที่สั่ง</th>
                <th style="width: 7%;">วันที่ต้องการ</th>
                <th style="width: 6%;">แผนกที่สั่ง</th>
                <th style="width: 9%;">ใช้กับ Order</th>
                <th style="width: 7%;">ยอดคงเหลือ</th>
                <th style="width: 8%;">ยอดใช้ย้อนหลัง 2 เดือน</th>
                <th style="width: 7%;">น้ำหนักที่ใช้</th>
                <th style="width: 7%;">น้ำหนักที่จะสั่ง</th>
                <th style="width: 6%;">ผลการอนุมัติ</th>
                <th style="width: 8%;">เลขที่ใบเบิก Red Bill (งาน)</th>
                <th style="width: 10%;">รหัสสินค้า Item No. (งาน)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d/m/Y') : '' }}</td>
                    <td>{{ $row->itemno ?: '' }}</td>
                    <td class="text-center">{{ $row->order_date ? \Carbon\Carbon::parse($row->order_date)->format('d/m/Y') : '' }}</td>
                    <td class="text-center">{{ $row->want_date ? \Carbon\Carbon::parse($row->want_date)->format('d/m/Y') : '' }}</td>
                    <td class="text-center">{{ $row->custno ?: '' }}</td>
                    <td class="text-center">{{ $row->orderno ?: '' }}</td>
                    <td class="text-end">{{ $row->balance !== null ? number_format($row->balance, 2) : '' }}</td>
                    <td class="text-end">{{ $row->retrospective !== null ? number_format($row->retrospective, 2) : '' }}</td>
                    <td class="text-end">{{ $row->weight_request !== null ? number_format($row->weight_request, 2) : '' }}</td>
                    <td class="text-end">{{ $row->weight_production !== null ? number_format($row->weight_production, 2) : '' }}</td>
                    <td class="text-center">{{ $row->statusLabel() }}</td>
                    <td class="text-center">{{ optional($row->planning)->red_bill_code ?: '' }}</td>
                    <td>{{ optional($row->planning)->itemno ?: '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" class="text-center" style="padding: 14px;">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
