{{--
    รายงาน Order ค้างส่งลูกค้า (26/10/2569) — ผังตามกระดาษของระบบเดิม (เรียงตามกำหนดแรก)
    OrderOutstandingController::pdf() · 1 กลุ่ม = 1 ลูกค้า · 1 แถว = 1 รายการ suborder ที่ยังไม่ส่ง (DVDate ว่าง)
--}}
@php
    $d    = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/y') : '';
    $dS   = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('j/n/y') : '';   // ช่องเลื่อน ตามกระดาษ (9/11/26)
    $n    = fn ($v) => abs((float) $v) < 0.000001 ? '' : number_format((float) $v, 2);
    $tick = fn ($v) => $v !== null && (int) $v !== 0;
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 12px; color: #000; }
        .head td { vertical-align: bottom; }
        .title { font-size: 20px; font-weight: bold; }
        .title i { font-family: 'dejavuserifcondensed', serif; }
        .sub { font-size: 12px; padding-left: 14px; }
        /* กรอบ # รหัสผู้ขาย + กล่องยอดรวม — ใช้ตารางซ้อนกำหนดความกว้างเอง (mPDF ไม่ขยาย padding ของ span และ div ในช่องตารางหดเท่าเนื้อหา) */
        table.salebox { border-collapse: collapse; }
        table.salebox td { border: 1px solid #000; width: 40mm; font-size: 16px; font-weight: bold; text-align: center; padding: 2px 6px; }

        table.list { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.list th { font-size: 11px; font-weight: normal; text-align: left; vertical-align: bottom;
                        border-bottom: 2px solid #000; padding: 2px 3px; }
        table.list td { padding: 3px 3px; vertical-align: top; }
        .num { text-align: right; }
        .bold { font-weight: bold; }
        .cust td { padding-top: 10px; }
        .cust span { text-decoration: underline; }
        table.box { border-collapse: collapse; width: 100%; }
        table.box td { border: 1px solid #000; width: 19mm; text-align: right; padding: 3px 4px; }
        .arrow { font-family: 'dejavusanscondensed'; font-size: 9px; }
    </style>
</head>
<body>
    <table width="100%" class="head">
        <tr>
            <td style="width: 58%;">
                <span class="title"><i>Order</i> ค้างส่งลูกค้า</span>
                <span class="sub">( เรียงตามกำหนดแรก )</span>
            </td>
            <td><table class="salebox"><tr><td># {{ $sale }}</td></tr></table></td>
        </tr>
    </table>

    <table class="list">
        <thead>
            <tr>
                <th style="width: 7%;">Orderno</th>
                <th style="width: 6%;"></th>
                <th style="width: 9%;">รหัสสินค้า</th>
                <th style="width: 8%;">Lotno</th>
                <th class="num" style="width: 7%;">Stock</th>
                <th class="num" style="width: 7%;">Production</th>
                <th style="width: 6%;">กำหนด ที่<br>ลูกค้า<br>ต้องการ</th>
                <th style="width: 6%;">กำหนดแรก</th>
                @for ($i = 1; $i <= $shiftCols; $i++)
                    <th style="width: 5%;">เลื่อน{{ $i }}</th>
                @endfor
                <th style="width: 6%;">เข้าคลัง</th>
                <th style="width: 3%; text-align: center;">ส่ง<br>ก่อน<br>ได้</th>
                <th style="width: 11%;">PO. NO.</th>
                <th style="width: 9%;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($groups as $g)
                <tr class="cust">
                    <td></td>
                    <td colspan="{{ 11 + $shiftCols }}">
                        <span>{{ $g['custno'] }}</span> &nbsp;&nbsp; <span>{{ $g['name'] }}</span>
                    </td>
                </tr>
                @foreach ($g['items'] as $r)
                    <tr>
                        <td>{{ $r->Orderno }}</td>
                        <td>{{ $d($r->Mdate) }}</td>
                        <td>{{ $r->Itemno }}</td>
                        <td>{{ $r->Lotno }}</td>
                        <td class="num">{{ $n($r->Stock) }}</td>
                        <td class="num">{{ $n($r->Production) }}</td>
                        <td class="bold">{{ $d($r->custwant) }}</td>
                        <td class="bold">{{ $d($r->first_due) }}</td>
                        @for ($i = 0; $i < $shiftCols; $i++)
                            <td>{{ $dS($r->shifts[$i] ?? null) }}</td>
                        @endfor
                        <td class="bold">{{ $d($r->EndP) }}</td>
                        <td style="text-align: center;">@if ($tick($r->Send))<span class="arrow">&#9654;</span>@endif</td>
                        <td>{{ $r->PO }}</td>
                        <td>{{ $r->overdue ? 'เลย Due ส่งแล้ว' : '' }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4"></td>
                    <td><table class="box"><tr><td>{!! $n($g['stock']) ?: '&nbsp;' !!}</td></tr></table></td>
                    <td><table class="box"><tr><td>{!! $n($g['prod']) ?: '&nbsp;' !!}</td></tr></table></td>
                    <td colspan="{{ 6 + $shiftCols }}"></td>
                </tr>
            @empty
                <tr><td colspan="{{ 12 + $shiftCols }}" style="text-align: center; padding: 20px;">ไม่พบ Order ค้างส่งของรหัสผู้ขาย {{ $sale }}</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
