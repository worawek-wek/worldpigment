{{--
    "รายงานสินค้าที่มีกำหนดส่ง วันที่ ..." (02/11/2569) — ผังตามกระดาษของระบบเดิม · A4 แนวนอน
    OrderDueReportController::pdf() · 1 แผนก = 1 section "สำหรับวางแผนจัดส่ง XX"
    ชื่อลูกค้าแสดงเฉพาะแถวแรกของกลุ่ม · เส้นคั่นท้ายกลุ่ม
    "สายรถจัดส่ง" = customer.DV ตาม SQL ของโปรแกรมเดิม (ตอนนี้ว่างทุกแถว)
--}}
@php
    $d = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/y') : '';
    // น้ำหนัก: 0 หรือว่าง = ช่องว่าง (กระดาษเดิมพิมพ์ "Null")
    $w = fn ($v) => $v === null || abs((float) $v) < 0.000001 ? '' : number_format((float) $v, 2);
    // checkbox แบบ Access (-1 = ติ๊ก) · เครื่องหมายต้องใช้ dejavusanscondensed (Garuda ไม่มี glyph)
    $on   = fn ($v) => $v !== null && (int) $v !== 0;
    $chk  = fn ($v) => '<table class="chk"><tr><td>' . ($on($v) ? '<span class="mark">&#10004;</span>' : '&nbsp;') . '</td></tr></table>';
    $send = fn ($v) => '<span class="mark">' . ($on($v) ? '&#9673;' : '&#9675;') . '</span>';
    $cols = 20;
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 10px; color: #222; }
        .title { font-size: 17px; font-weight: bold; margin-bottom: 4px; }
        .title span { padding-left: 14px; }
        .section { font-size: 14px; font-weight: bold; text-align: center; margin: 12px 0 4px; color: #d32f2f; }
        table.list th.route { color: #1f35d6; }

        table.list { width: 100%; border-collapse: collapse; }
        table.list th { font-size: 9.5px; font-weight: bold; padding: 3px 2px; vertical-align: bottom;
                        border-top: 1px solid #333; border-bottom: 1px solid #333; }
        table.list td { padding: 4px 2px; vertical-align: middle; white-space: nowrap; }
        table.list td.wrap { white-space: normal; }
        table.list tr.endgroup td { border-bottom: 1px solid #333; padding: 0; height: 1px; }
        table.list tr.endsection td { border-bottom: 1px solid #333; padding: 0; height: 1px; }
        .num { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: bold; }

        table.chk { width: 10px; border-collapse: collapse; margin: 0 auto; }
        table.chk td { border: 1px solid #555; padding: 0; height: 10px; text-align: center;
                       font-size: 8px; line-height: 10px; }
        .mark { font-family: dejavusanscondensed, sans-serif; font-size: 9px; }
        .empty { text-align: center; padding: 14px; }
    </style>
</head>
<body>
    <div class="title">รายงานสินค้าที่มีกำหนดส่ง <span>วันที่</span> <span>{{ $date->format('d/m/y') }}</span></div>

    @forelse ($sections as $sec)
        <div class="section">สำหรับวางแผนจัดส่ง &nbsp; {{ $sec['company'] }}</div>
        <table class="list">
            <thead>
                <tr>
                    <th style="width:12%;">ตามคำสั่งลูกค้าเพื่อ</th>
                    <th style="width:4.5%;">รหัสลูกค้า</th>
                    <th style="width:8%;">รหัสสินค้า</th>
                    <th style="width:5%;">Lot No.</th>
                    <th class="num" style="width:5.5%;">นน. S</th>
                    <th class="num" style="width:5.5%;">นน. P</th>
                    <th style="width:6%;">เลขที่<br>ใบทบทวน</th>
                    <th class="center" style="width:5%;">วันที่ผลิต<br>เสร็จ</th>
                    <th class="center" style="width:3%;">ส่ง<br>ก่อนได้</th>
                    <th class="center" style="width:2.5%;">RP</th>
                    <th class="center" style="width:2.5%;">Spec</th>
                    <th class="center" style="width:2.5%;">Cer</th>
                    <th class="center" style="width:3%;">MSDS</th>
                    @for ($i = $shiftCols; $i >= 1; $i--)
                        <th class="center" style="width:4%;">เลื่อน {{ $i }}</th>
                    @endfor
                    <th class="center" style="width:4.5%;">กำหนด<br>ส่งแรก</th>
                    <th class="center" style="width:4.5%;">วันที่ลูกค้า<br>ได้รับ</th>
                    <th class="center route" style="width:4%;">สายรถ<br>จัดส่ง</th>
                    <th style="width:10%;">หมายเหตุ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sec['groups'] as $g)
                    @foreach ($g['items'] as $r)
                        <tr>
                            <td class="wrap">{{ $loop->first ? $g['name'] : '' }}</td>
                            <td>{{ $r->Custno }}</td>
                            <td>{{ $r->Itemno }}</td>
                            <td>{{ $r->Lotno }}</td>
                            <td class="num">{{ $w($r->Stock) }}</td>
                            <td class="num bold">{{ $w($r->Production) }}</td>
                            <td>{{ $r->Orderno }}</td>
                            <td class="center">{{ $d($r->EndP) }}</td>
                            <td class="center">{!! $send($r->Send) !!}</td>
                            <td class="center">{!! $chk($r->RP) !!}</td>
                            <td class="center">{!! $chk($r->Spec) !!}</td>
                            <td class="center">{!! $chk($r->Cer) !!}</td>
                            <td class="center">{!! $chk($r->MSDS) !!}</td>
                            @for ($i = $shiftCols; $i >= 1; $i--)
                                <td class="center">{{ $d($r->shifts[$i - 1] ?? null) }}</td>
                            @endfor
                            <td class="center">{{ $d($r->first_due) }}</td>
                            <td class="center">{{ $d($r->DVDate) }}</td>
                            <td class="center">{{ $r->DV }}</td>
                            <td class="wrap">{{ $r->DVpoint }}</td>
                        </tr>
                    @endforeach
                    @if (!$loop->last)
                        <tr class="endgroup"><td colspan="{{ $cols }}"></td></tr>
                    @endif
                @endforeach
                <tr class="endsection"><td colspan="{{ $cols }}"></td></tr>
            </tbody>
        </table>
    @empty
        <div class="empty">ไม่พบรายการที่มีกำหนดส่งวันที่ {{ $date->format('d/m/Y') }}</div>
    @endforelse
</body>
</html>
