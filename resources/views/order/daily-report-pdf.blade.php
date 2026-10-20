{{--
    รายงานคำสั่งซื้อรายวัน (20/10/2569) — ผังตามกระดาษของระบบเดิม · A4 แนวนอน
    OrderDailyReportController::pdf() · 1 แถว = 1 รายการ (suborder)
--}}
@php
    $w    = fn ($v) => abs((float) $v) < 0.000001 ? '' : number_format((float) $v, 2, '.', ',');
    $d    = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/y') : '';
    $int  = fn ($v) => $v === null || $v === '' ? '' : number_format((float) $v, 0, '.', ',');
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 11px; color: #000; }
        .title { font-size: 17px; margin-bottom: 6px; }
        .title b { padding: 0 8px; }

        table.list { width: 100%; border-collapse: collapse; }
        table.list th { font-size: 11px; font-weight: bold; padding: 3px 3px; border-bottom: 2px solid #000;
                        vertical-align: bottom; text-align: left; }
        table.list td { padding: 4px 3px; vertical-align: top; }
        /* ช่องไฟระหว่างแต่ละ Order (20/10/2569) — แถวแรกของใบใหม่ */
        table.list tr.new-order td { padding-top: 12px; }
        .num { text-align: right; }
        .center { text-align: center; }
        /* mPDF ไม่สน padding ซ้ายขวาของ span — ช่องไฟในกรอบใช้ &nbsp; แทน (20/10/2569) */
        .box { border: 1px solid #555; padding: 1px 4px; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="title">
        รายงานคำสั่งซื้อรายวัน ประจำวันที่
        <b>{{ $from->format('d/m/Y') }}</b> ถึง <b>{{ $to->format('d/m/Y') }}</b>
        แผนกผลิต <b>{{ $dept === '' ? 'ทุกแผนก' : $dept }}</b>
    </div>

    <table class="list">
        <thead>
            <tr>
                <th style="width:7%;">เลขที่<br>ใบทบทวนฯ</th>
                <th style="width:8%;">รหัสสินค้า</th>
                <th class="num" style="width:5%;">นน. S</th>
                <th class="num" style="width:5%;">นน. P</th>
                <th style="width:4%;">รหัส</th>
                <th style="width:14%;">ชื่อลูกค้า</th>
                <th class="center" style="width:3%;">Sale</th>
                <th class="center" style="width:8%;">กำหนดที่<br>ลูกค้าต้องการ</th>
                <th class="center" style="width:7%;">กำหนดส่ง</th>
                <th class="center" style="width:4%;">Lead<br>Time</th>
                <th style="width:8%;">เลขที่ PO</th>
                <th style="width:18%;">หมายเหตุ</th>
                <th class="num" style="width:4%;">Stock<br>HM</th>
                <th class="num nowrap" style="width:5%;">ส่งมอบ<br>ภายใน</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                <tr class="{{ $loop->first || $rows[$loop->index - 1]->Orderno !== $r->Orderno ? 'new-order' : '' }}">
                    <td class="nowrap">{{ $r->Orderno }}</td>
                    <td>{{ $r->Itemno }}</td>
                    <td class="num">{{ $w($r->Stock) }}</td>
                    <td class="num">{{ $w($r->Production) }}</td>
                    <td>{{ $r->Custno }}</td>
                    <td>{{ $r->cust_name }}</td>
                    <td class="center">{{ $r->supno }}</td>
                    <td class="center">@if ($r->custwant)<span class="box">&nbsp;&nbsp;{{ $d($r->custwant) }}&nbsp;&nbsp;</span>@endif</td>
                    <td class="center"><b>{{ $d($r->senddate) }}</b></td>
                    <td class="center">{{ $r->lead_time }}</td>
                    <td>{{ $r->PO }}</td>
                    <td>{{ $r->Remark }}</td>
                    <td class="num">{{ $int($r->HMStore) }}</td>
                    <td class="num">{{ $int($r->SendCust) }}</td>
                </tr>
            @empty
                <tr><td colspan="14" class="center" style="padding:20px;">ไม่พบคำสั่งซื้อในช่วงวันที่ที่เลือก</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
