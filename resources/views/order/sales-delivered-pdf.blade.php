{{--
    รายงานยอดขายที่ส่งลูกค้า เฉพาะเบอร์ที่ราคาผ่านช่อง 2 (27/10/2569) — ผังตามกระดาษของระบบเดิม
    SalesForecastController::deliveredPdf() · 1 กลุ่ม = 1 เซลส์ (morder.supno) · 1 แถว = 1 รายการ suborder ที่ส่งแล้ว
--}}
@php
    $n = fn ($v) => number_format((float) $v, 2);
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 12px; color: #000; }
        .title { font-size: 15px; font-weight: bold; margin-bottom: 6px; }
        .title u { padding-left: 10px; }

        table.list { width: 100%; border-collapse: collapse; }
        table.list th { font-size: 13px; font-weight: bold; text-align: left; padding: 3px 4px; border-bottom: 2px solid #000; }
        table.list td { padding: 4px 4px; white-space: nowrap; }
        table.list th.num, table.list td.num { text-align: right; }
        tr.sum td { font-weight: bold; padding-bottom: 14px; }
        table.box { border-collapse: collapse; width: 100%; }
        table.box td { border: 2px solid #000; text-align: right; font-weight: bold; padding: 4px 6px; }
        tr.grand td { padding-top: 12px; }
    </style>
</head>
<body>
    <div class="title">
        รายงาน ยอดขายที่ส่งลูกค้า ช่วงวันที่ {{ $from->format('d/m/y H:i') }} ถึง วันที่ {{ $to->format('d/m/y H:i') }}
        <u>เฉพาะเบอร์ที่ราคาผ่านช่อง 2</u>
    </div>

    <table class="list">
        <thead>
            <tr>
                <th style="width: 5%;">เซลส์</th>
                <th style="width: 5%;">ผลิตที่</th>
                <th style="width: 6%;">รหัสลูกค้า</th>
                <th style="width: 15%;"></th>
                <th style="width: 7%;">วันที่ส่ง</th>
                <th style="width: 7%;"></th>
                <th style="width: 12%;">รหัสสินค้า</th>
                <th class="num" style="width: 8%;">นน.(กก.)</th>
                <th class="num" style="width: 9%;">ราคาขาย</th>
                <th class="num" style="width: 10%;">รวมเงิน</th>
                <th class="num" style="width: 8%;">ราคาช่อง 2</th>
                <th class="num" style="width: 8%;">กำไร</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($groups as $g)
                @foreach ($g['rows'] as $r)
                    <tr>
                        <td>{{ $g['sale'] }}</td>
                        <td>{{ $r->Company }}</td>
                        <td>{{ $r->Custno }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($r->name, 30, '…') }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($r->DVDate)->format('d/m/y') }}</td>
                        <td>{{ $r->Orderno }}</td>
                        <td>{{ $r->Itemno }}</td>
                        <td class="num">{{ $n($r->weight) }}</td>
                        <td class="num">{{ $n($r->price) }}</td>
                        <td class="num">{{ $n($r->amount) }}</td>
                        <td class="num">{{ $n($r->price2) }}</td>
                        <td class="num">{{ $n($r->profit) }}</td>
                    </tr>
                @endforeach
                {{-- ยอดรวมของเซลส์คนนี้ --}}
                <tr class="sum">
                    <td colspan="7"></td>
                    <td class="num">{{ $n($g['weight']) }}</td>
                    <td></td>
                    <td class="num">{{ $n($g['amount']) }}</td>
                    <td colspan="2"></td>
                </tr>
            @empty
                <tr><td colspan="12" style="text-align: center; padding: 20px;">ไม่พบรายการที่ส่งลูกค้าและราคาผ่านช่อง 2 ในช่วงวันที่ที่เลือก</td></tr>
            @endforelse

            @if ($groups)
                {{-- รวมทุกเซลส์ --}}
                <tr class="grand">
                    <td colspan="4" style="font-weight: bold; font-size: 14px;">รวมทุกเซลส์</td>
                    <td colspan="3"></td>
                    <td><table class="box"><tr><td>{{ $n($grand['weight']) }}</td></tr></table></td>
                    <td></td>
                    <td><table class="box"><tr><td>{{ $n($grand['amount']) }}</td></tr></table></td>
                    <td colspan="2"></td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
