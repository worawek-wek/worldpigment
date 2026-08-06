@php
    use Illuminate\Support\Carbon;
    $fmtNum = function ($v) {
        if ($v === null || $v === '') return '-';
        return rtrim(rtrim(number_format((float) $v, 6, '.', ','), '0'), '.');
    };
    $fmtDate = function ($d) {
        if (!$d) return '-';
        try { return Carbon::parse($d)->format('d/m/Y'); } catch (\Exception $e) { return '-'; }
    };
    $rangeText = '';
    if ($date_from || $date_to) {
        $rangeText = 'ช่วงวันที่ (ปิดจบงาน / เปลี่ยน senddate): ' . $fmtDate($date_from) . ' - ' . $fmtDate($date_to);
    }
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 12px; color: #000; }
        .title-wrap { text-align: center; margin-bottom: 6px; }
        .company { font-size: 14px; font-weight: bold; }
        .doc-title { font-size: 16px; font-weight: bold; }
        .range { font-size: 11px; margin-top: 2px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.data th, table.data td { border: 1px solid #000; padding: 4px 6px; font-size: 11px; }
        table.data th { background-color: #eee; text-align: center; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .footer { margin-top: 24px; width: 100%; }
        .footer td { padding: 4px 8px; font-size: 11px; border: none; }
    </style>
</head>
<body>
    <div class="title-wrap">
        <div class="company">บริษัท เวิลด์ปิกเม้นท์อินดัสตรี จำกัด</div>
        <div class="doc-title">ใบขอเปลี่ยนแปลงคำสั่งซื้อจากภายใน</div>
        @if($rangeText)
            <div class="range">{{ $rangeText }}</div>
        @endif
    </div>

    <table class="data">
        <thead>
            <tr>
                <th rowspan="2" style="width: 4%;">ลำดับ</th>
                <th rowspan="2" style="width: 13%;">รหัสสินค้า</th>
                <th rowspan="2" style="width: 15%;">ตามคำสั่งลูกค้าเพื่อ</th>
                <th rowspan="2" style="width: 13%;">เลขที่<br>ใบทบทวนคำสั่งซื้อ</th>
                <th rowspan="2" style="width: 10%;">กำหนดเสร็จ<br>เดิม</th>
                <th rowspan="2" style="width: 10%;">ขอเลื่อน<br>เป็นวันที่</th>
                <th colspan="2" style="width: 16%;">เปลี่ยนน้ำหนัก</th>
                <th rowspan="2" style="width: 19%;">สาเหตุ</th>
            </tr>
            <tr>
                <th style="width: 8%;">จาก</th>
                <th style="width: 8%;">เป็น</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $row['itemno'] ?: '-' }}</td>
                    <td>{{ $row['custname'] ?: '-' }}</td>
                    <td class="text-center">{{ $row['red_bill_code'] ?: '-' }}</td>
                    <td class="text-center">{{ $fmtDate($row['due_original']) }}</td>
                    <td class="text-center">{{ $fmtDate($row['due_postpone']) }}</td>
                    <td class="text-end">{{ $fmtNum($row['weight_from']) }}</td>
                    <td class="text-end">{{ $fmtNum($row['weight_to']) }}</td>
                    <td>{{ $row['reason'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 16px;">ไม่พบข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer">
        <tr>
            <td style="width: 50%;">ผู้รายงาน ......................................... วันที่ ........./........./......... เวลา ................</td>
            <td style="width: 50%;">ผู้รับรายงาน ......................................... วันที่ ........./........./......... เวลา ................</td>
        </tr>
        <tr>
            <td>หัวหน้างานวางแผนผลิต</td>
            <td>เจ้าหน้าที่ฝ่ายการตลาด</td>
        </tr>
    </table>
</body>
</html>
