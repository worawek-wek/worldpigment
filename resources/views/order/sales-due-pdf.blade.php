{{--
    ยอดขายสะสม ตามรหัสพนักงานขาย เทียบกับ Sale Target — ใช้ร่วม 2 รายงาน (12/11/2569)
      $mode = 'first'  → ยอดขายสะสมที่มีกำหนดส่ง (แรก)      (SalesForecastController::duePdf)
      $mode = 'latest' → ยอดขายสะสมตามวันที่เลื่อนล่าสุด     (SalesForecastController::latestPdf) — มีคอลัมน์ชื่อลูกค้า target.custname
    ผังตามกระดาษระบบเดิม · 1 เซลส์ = 1 หน้า
    กลุ่ม A = ลูกค้าที่มีใน target · กลุ่ม OTHER = ลูกค้าที่มียอดแต่ไม่มีใน target + แถว OTHER (target ของกลุ่มอื่น ๆ)
--}}
@php
    $fmt  = fn ($v) => abs((float) $v) < 0.000001 ? '' : number_format((float) $v, 0, '.', ',');
    $fmt0 = fn ($v) => number_format((float) $v, 0, '.', ',');          // แสดง 0 ด้วย (คอลัมน์ Target)
    $diff = function ($v) {
        if ($v === null) return '';
        $cls = $v < 0 ? 'neg' : ($v > 0 ? 'pos' : '');
        $txt = $v < 0 ? '- ' . number_format(abs($v), 0, '.', ',') : number_format($v, 0, '.', ',');
        return '<span class="' . $cls . '">' . $txt . '</span>';
    };
    $latest  = ($mode ?? 'first') === 'latest';
    $nameCol = $latest ? 1 : 0;                        // คอลัมน์ชื่อลูกค้า (เฉพาะเลื่อนล่าสุด)
    $cols    = count($companies) + 6 + $nameCol;
    $heading = $latest
        ? 'ยอดขายสะสมตามวันที่เลื่อนล่าสุด ของรหัสพนักงานขาย'
        : 'ยอดขายสะสมที่มีกำหนดส่ง (แรก) ตามรหัสพนักงานขาย';
    $rangeLabel = $latest ? 'กำหนดส่ง (เลื่อนล่าสุด)' : 'กำหนดส่งแรก';
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 12px; color: #c0504d; }
        .title { border: 1px solid #888; padding: 4px 10px; font-size: 17px; font-weight: bold; color: #c0504d; text-align: center; }
        .title .sale { color: #c00000; }
        .meta td { font-size: 12px; padding: 4px 2px; color: #000; }

        table.list { width: 100%; border-collapse: collapse; }
        table.list th { font-size: 12px; color: #c0504d; padding: 3px 4px; border-bottom: 2px solid #1a3f9c; font-weight: normal; white-space: nowrap; }
        table.list td { white-space: nowrap; }
        table.list th.noline { border-bottom: none; }
        table.list td { padding: 2px 4px; }
        .num { text-align: right; }
        .total { color: #2e8b57; }
        .tg { color: #000; font-weight: bold; }
        .cname { color: #1a3f9c; white-space: nowrap; }
        .neg { color: #c00000; }
        .pos { color: #2e8b57; }
        tr.sum td { border-top: 1px solid #1a3f9c; font-weight: bold; padding-top: 4px; padding-bottom: 4px; }
        tr.gap td { height: 22px; }
    </style>
</head>
<body>
@forelse ($sections as $sec)
    @if (!$loop->first)<pagebreak />@endif

    <div class="title">
        {{ $heading }} : <span class="sale">{{ $sec['sale'] }}</span>
        &nbsp; เทียบกับ Sale Target
    </div>
    <table class="meta" width="100%"><tr>
        <td>{{ $rangeLabel }} จาก : {{ $from->format('d/m/y H:i') }} &nbsp; ถึงวันที่ {{ $to->format('d/m/y H:i') }}</td>
        <td style="text-align: right;">Month : <b style="color:#c0504d;">{{ $month }}</b></td>
    </tr></table>

    <table class="list">
        <thead>
            <tr>
                {{-- ช่องหน้าคอลัมน์ยอดไม่มีเส้นใต้หัวตาราง (ตามกระดาษ) --}}
                <th class="noline" style="width: {{ $latest ? 5 : 6 }}%; text-align: left;">เซลส์</th>
                <th class="noline" style="width: {{ $latest ? 7 : 8 }}%;"></th>
                <th class="{{ $latest ? 'noline' : '' }}" style="width: {{ $latest ? 7 : 9 }}%; text-align: left;">รหัสลูกค้า</th>
                @if ($latest)
                    <th class="noline" style="width: 18%;"></th>
                @endif
                @foreach ($companies as $c)
                    <th class="num" style="width: {{ $latest ? 8 : 10 }}%;">{{ $c }} (บาท)</th>
                @endforeach
                <th class="num" style="width: {{ $latest ? 10 : 12 }}%;">รวม (บาท)</th>
                <th class="num" style="width: {{ $latest ? 12 : 13 }}%; color:#000;">Month Target<br>ของปีปัจจุบัน</th>
                <th class="num" style="width: {{ $latest ? 11 : 12 }}%;">Diff</th>
            </tr>
        </thead>
        <tbody>
            {{-- กลุ่ม A — ลูกค้าที่มี Sale Target --}}
            @foreach ($sec['a']['rows'] as $row)
                <tr>
                    <td>{{ $loop->first ? $sec['sale'] : '' }}</td>
                    <td>{{ $loop->first ? 'A' : '' }}</td>
                    <td>{{ $row['custno'] }}</td>
                    @if ($latest)
                        <td class="cname">{{ $row['name'] }}</td>
                    @endif
                    @foreach ($companies as $c)
                        <td class="num">{{ $fmt($row['amt'][$c]) }}</td>
                    @endforeach
                    <td class="num total">{{ $fmt($row['total']) }}</td>
                    <td class="num tg">{{ $fmt0($row['target']) }}</td>
                    <td class="num">{!! $diff($row['diff']) !!}</td>
                </tr>
            @endforeach
            @if ($sec['a']['rows'])
                <tr class="sum">
                    <td colspan="{{ 3 + $nameCol }}"></td>
                    @foreach ($companies as $c)
                        <td class="num">{{ $fmt($sec['a']['sum'][$c]) }}</td>
                    @endforeach
                    <td class="num total">{{ $fmt0($sec['a']['sum']['total']) }}</td>
                    <td class="num tg">{{ $fmt0($sec['a']['sum']['target']) }}</td>
                    <td class="num">{!! $diff($sec['a']['sum']['diff']) !!}</td>
                </tr>
                <tr class="gap"><td colspan="{{ $cols }}"></td></tr>
            @endif

            {{-- กลุ่ม OTHER — ลูกค้าที่ไม่มี Sale Target (ไม่มีชื่อใน target) --}}
            @foreach ($sec['other']['rows'] as $row)
                <tr>
                    <td>{{ $loop->first ? $sec['sale'] : '' }}</td>
                    <td>{{ $loop->first ? 'OTHER' : '' }}</td>
                    <td>{{ $row['custno'] }}</td>
                    @if ($latest)
                        <td></td>
                    @endif
                    @foreach ($companies as $c)
                        <td class="num">{{ $fmt($row['amt'][$c]) }}</td>
                    @endforeach
                    <td class="num total">{{ $fmt($row['total']) }}</td>
                    <td class="num tg">0</td>
                    <td class="num tg">0</td>
                </tr>
            @endforeach
            <tr>
                <td>{{ $sec['other']['rows'] ? '' : $sec['sale'] }}</td>
                <td></td>
                <td>OTHER</td>
                <td colspan="{{ count($companies) + 1 + $nameCol }}"></td>
                <td class="num tg">{{ $fmt0($sec['other']['other_target']) }}</td>
                <td class="num tg">0</td>
            </tr>
            <tr class="sum">
                <td colspan="{{ 3 + $nameCol }}"></td>
                @foreach ($companies as $c)
                    <td class="num">{{ $fmt($sec['other']['sum'][$c]) }}</td>
                @endforeach
                <td class="num total">{{ $fmt0($sec['other']['sum']['total']) }}</td>
                <td class="num tg">{{ $fmt0($sec['other']['sum']['target']) }}</td>
                <td class="num">{!! $diff($sec['other']['sum']['diff']) !!}</td>
            </tr>
        </tbody>
    </table>
@empty
    <div class="title">{{ $heading }} เทียบกับ Sale Target</div>
    <p style="text-align:center; padding:20px; color:#000;">ไม่พบข้อมูลในช่วงวันที่ที่เลือก</p>
@endforelse
</body>
</html>
