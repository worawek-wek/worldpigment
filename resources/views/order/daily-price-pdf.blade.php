{{--
    รายงาน Order Daily (price) (02/11/2569) — ผังตามกระดาษของระบบเดิม · A4 แนวนอน
    OrderDailyReportController::pricePdf() · 1 รายการ (suborder) = 2 บรรทัด
    สีตามกระดาษ: น้ำเงิน = customer · ม่วง = uprice (REM1 / REM2) · ดำ = morder / suborder
--}}
@php
    // ตัวเลขทั่วไป: ตัดศูนย์ท้าย (1,000 / 3.5 / -75.2) · 0 หรือว่าง = ช่องว่าง
    $n = function ($v) {
        if ($v === null || abs((float) $v) < 0.000001) {
            return '';
        }

        return rtrim(rtrim(number_format((float) $v, 2, '.', ','), '0'), '.');
    };
    $d    = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/y') : '';
    $sign = fn ($v) => $v !== null && $v < 0 ? 'neg' : '';
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 11px; color: #222; }
        table.head { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
        table.head td { vertical-align: bottom; padding: 0; }
        .title { font-size: 17px; font-weight: bold; color: #1a3a8f; }
        .datelbl { font-size: 11px; text-align: right; padding-right: 8px !important; }
        .datebox { border: 1px solid #333; padding: 2px 4px !important; text-align: center; font-size: 12px; }

        table.list { width: 100%; border-collapse: collapse; }
        table.list th { font-size: 10px; font-weight: normal; padding: 3px 2px; border-bottom: 2px solid #1a3a8f;
                        vertical-align: bottom; text-align: left; color: #333; }
        table.list th.num { text-align: right; }
        table.list th.center { text-align: center; }
        table.list th.want { color: #b03030; }
        table.list th.blue { color: #1a3a8f; font-weight: bold; }
        table.list td { padding: 7px 2px 0; vertical-align: top; }
        table.list tr.rem td { padding: 3px 2px 6px; }
        table.list td.company { font-size: 13px; padding-top: 8px; }
        table.list td.total { border: 1px solid #333; font-weight: bold; text-align: right; padding: 2px 4px; }
        table.list td.endgroup { border-bottom: 1px solid #1a3a8f; padding: 0 0 8px; }
        .small { font-size: 9px; }
        .num { text-align: right; }
        .center { text-align: center; }
        .cust { color: #1a56c4; }
        .want { color: #c0392b; }
        .rem { color: #6a1b9a; }
        .price { color: #1a56c4; }
        .neg { color: #c0392b; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td class="title" style="width:22%;">Order_Daily ( price )</td>
            <td class="datelbl" style="width:12%;">ประจำวันที่</td>
            <td class="datebox" style="width:10%;">{{ $date->format('d/m/y') }}</td>
            <td></td>
        </tr>
    </table>

    <table class="list">
        <thead>
            <tr>
                <th style="width:2%;">Company</th>
                <th style="width:6%;">Orderno</th>
                <th style="width:3%;"></th>
                <th style="width:4%;">Custno</th>
                <th style="width:9%;"></th>
                <th style="width:5%;"></th>
                <th class="center" style="width:2.5%;">supno</th>
                <th style="width:7.5%;"></th>
                <th style="width:5%;"></th>
                <th class="num" style="width:5%;">S</th>
                <th class="num" style="width:5.5%;">P</th>
                <th class="center want" style="width:5%;">กำหนดที่<br>ลูกค้าต้องการ</th>
                <th class="center" style="width:5%;">กำหนดส่ง<br>แรก</th>
                <th class="num" style="width:6%;">price</th>
                <th class="num" style="width:4%;">1</th>
                <th class="num" style="width:4%;">2</th>
                <th class="num" style="width:4%;">3</th>
                <th class="num blue" style="width:4.5%;">Price -2</th>
                <th class="num blue" style="width:6%;">Price -2<br>(Baht)</th>
                <th class="num blue" style="width:7%;">BAHTSALE</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($groups as $g)
                <tr><td></td><td colspan="19" class="company">{{ $g['company'] }}</td></tr>
                @foreach ($g['rows'] as $r)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $r->Orderno }}</td>
                        <td class="small">{{ \Illuminate\Support\Carbon::parse($r->Mdate)->format('H:i') }}</td>
                        <td class="small">{{ $r->Custno }}</td>
                        <td class="cust">{{ $r->name }}</td>
                        <td class="cust">{{ $r->term }}</td>
                        <td class="center">{{ $r->supno }}</td>
                        <td>{{ $r->Itemno }}</td>
                        <td>{{ $r->Lotno }}</td>
                        <td class="num">{{ $n($r->Stock) }}</td>
                        <td class="num">{{ $n($r->Production) }}</td>
                        <td class="center want">{{ $d($r->custwant) }}</td>
                        <td class="center">{{ $d($r->first_due) }}</td>
                        <td class="num price">{{ number_format((float) $r->price, 2, '.', ',') }}</td>
                        <td class="num">{{ $n($r->price_1) }}</td>
                        <td class="num">{{ $n($r->price_2) }}</td>
                        <td class="num">{{ $n($r->price_3) }}</td>
                        <td class="num bold {{ $sign($r->diff) }}">{{ $n($r->diff) }}</td>
                        <td class="num bold {{ $sign($r->diff_baht) }}">{{ $n($r->diff_baht) }}</td>
                        <td class="num price">{{ $n($r->bahtsale) }}</td>
                    </tr>
                    <tr class="rem">
                        <td colspan="3"></td>
                        <td colspan="6" class="rem">{{ $r->rem1 }}</td>
                        <td colspan="11" class="rem">{{ $r->rem2 }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="9"></td>
                    <td class="total">{{ $n($g['stock']) }}</td>
                    <td class="total">{{ $n($g['prod']) }}</td>
                    <td colspan="6"></td>
                    <td colspan="2" class="total {{ $sign($g['diff_baht']) }}">{{ $n($g['diff_baht']) }}</td>
                    <td class="total price">{{ $n($g['bahtsale']) }}</td>
                </tr>
                <tr><td colspan="20" class="endgroup"></td></tr>
            @empty
                <tr><td colspan="20" class="center" style="padding:20px;">ไม่พบคำสั่งซื้อในวันที่ที่เลือก</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
