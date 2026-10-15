{{--
    รายงานยอดรับ Order ตามรหัสพนักงานขาย (15/10/2569) — ผังตามกระดาษของฟอร์ม Access "SaleOrder"
    SalesForecastController::orderPdf() · 1 กลุ่ม = 1 พนักงานขาย (morder.supno) · 1 แถว = 1 ลูกค้า
    ยอด = netqty × price แยกคอลัมน์ตามแผนกที่ผลิต (MB / DB / CP / SPP) + รวม
--}}
@php
    $fmt = fn ($v) => abs((float) $v) < 0.000001 ? '' : number_format((float) $v, 0, '.', ',');
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 12px; color: #000; }
        .title { font-size: 18px; font-weight: bold; color: #1a3f9c; text-decoration: underline; text-align: center; }
        .title .sale { color: #c00000; text-decoration: none; padding-left: 20px; }
        .range { font-size: 11px; text-align: center; margin: 6px 0 10px; }

        table.list { width: 100%; border-collapse: collapse; }
        table.list th { font-size: 12px; font-weight: bold; color: #1a3f9c; padding: 3px 4px;
                        border-bottom: 2px solid #1a3f9c; }
        table.list th.total { color: #2e8b57; }
        table.list td { padding: 2px 4px; }
        .num { text-align: right; }
        .total { color: #2e8b57; font-weight: bold; }

        tr.sum td { color: #1a3f9c; border-top: 1px solid #1a3f9c; border-bottom: 1px solid #555; padding-top: 3px; padding-bottom: 3px; }
        tr.sum td.total { color: #2e8b57; }
        tr.gap td { height: 12px; }
        tr.grand td { font-weight: bold; border-top: 1px solid #000; border-bottom: 3px double #000; padding-top: 5px; padding-bottom: 5px; }
    </style>
</head>
<body>
    <div class="title">
        รายงานยอดรับ Order ตามรหัสพนักงานขาย
        <span class="sale">{{ $sale === '' ? 'รวม' : $sale }}</span>
    </div>
    <div class="range">
        วันที่ Order จาก : {{ $from->format('d/m/y H:i') }} &nbsp; ถึงวันที่ {{ $to->format('d/m/y H:i') }}
    </div>

    <table class="list">
        <thead>
            <tr>
                <th style="width: 8%; text-align: left;">เซลส์</th>
                <th style="width: 12%; text-align: left;">รหัสลูกค้า</th>
                @foreach ($companies as $c)
                    <th class="num" style="width: 16%;">{{ $c }} (บาท)</th>
                @endforeach
                <th class="num total" style="width: 16%;">รวม (บาท)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($groups as $g)
                @foreach ($g['rows'] as $row)
                    <tr>
                        <td>{{ $g['sale'] }}</td>
                        <td>{{ $row['custno'] }}</td>
                        @foreach ($companies as $c)
                            <td class="num">{{ $fmt($row['amt'][$c]) }}</td>
                        @endforeach
                        <td class="num total">{{ $fmt($row['total']) }}</td>
                    </tr>
                @endforeach
                <tr class="sum">
                    <td></td>
                    <td style="text-align: center;">รวม</td>
                    @foreach ($companies as $c)
                        <td class="num">{{ $fmt($g['sum'][$c]) }}</td>
                    @endforeach
                    <td class="num total">{{ $fmt($g['sum']['total']) }}</td>
                </tr>
                <tr class="gap"><td colspan="{{ count($companies) + 3 }}"></td></tr>
            @empty
                <tr><td colspan="{{ count($companies) + 3 }}" style="text-align: center; padding: 20px;">ไม่พบใบสั่งซื้อในช่วงวันที่ที่เลือก</td></tr>
            @endforelse

            @if ($groups)
                <tr class="grand">
                    <td colspan="2" style="text-align: center;">รวมทุกราย</td>
                    @foreach ($companies as $c)
                        <td class="num">{{ $fmt($grand[$c]) }}</td>
                    @endforeach
                    <td class="num">{{ $fmt($grand['total']) }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
