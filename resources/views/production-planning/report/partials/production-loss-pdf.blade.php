<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 10px; color: #000; }
        .title { font-size: 15px; font-weight: bold; color: #0b7285; }
        .summary { font-size: 9px; margin: 2px 0 6px; color: #333; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { border-bottom: 1.5px solid #0b7285; padding: 3px 2px; font-size: 9px; text-align: center; }
        table.data td { border-bottom: 0.5px solid #ccc; padding: 2px; font-size: 9px; }
        .r { text-align: right; } .c { text-align: center; }
        .neg { color: #198754; } .high { color: #dc3545; font-weight: bold; }
        tr.total td { border-top: 1.5px solid #000; border-bottom: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="title">รายงานการผลิตและสูญเสีย</div>
    <div class="summary">{{ $summary }} · สูญเสีย % = (น้ำหนักผลิต − นน.บรรจุได้) ÷ น้ำหนักผลิต × 100</div>

    @php
        $n2  = fn ($v) => $v !== null ? number_format((float) $v, 2) : '-';
        $dmy = fn ($v, $f = 'd/m/y') => $v ? \Carbon\Carbon::parse($v)->format($f) : '';
        $cls = fn ($p) => $p === null ? '' : ($p < 0 ? 'neg' : ($p > 5 ? 'high' : ''));
    @endphp

    <table class="data">
        <thead>
            <tr>
                <th style="width:3%">#</th>
                <th style="width:5%">หน่วย</th>
                <th style="width:9%">เลขที่ใบสั่ง</th>
                <th style="width:10%">เลขที่ใบเบิก</th>
                <th style="width:12%">รหัสสินค้า</th>
                <th style="width:8%">Lot No.</th>
                <th style="width:7%">น้ำหนัก PO</th>
                <th style="width:8%">น้ำหนักผลิต</th>
                <th style="width:8%">น.น.บรรจุได้</th>
                <th style="width:7%">สูญเสีย (กก.)</th>
                <th style="width:7%">สูญเสีย (%)</th>
                <th style="width:6%">Inplan</th>
                <th style="width:10%">วันที่ผลิตเสร็จ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $it)
                <tr>
                    <td class="c">{{ $loop->iteration }}</td>
                    <td class="c">{{ $it->dept }}</td>
                    <td>{{ $it->orderno }}</td>
                    <td>{{ $it->red_bill_code }}</td>
                    <td>{{ $it->itemno }}</td>
                    <td>{{ $it->lot }}</td>
                    <td class="r">{{ $n2($it->quantity) }}</td>
                    <td class="r">{{ $n2($it->weight) }}</td>
                    <td class="r">{{ $n2($it->weight_produced) }}</td>
                    <td class="r {{ $cls($it->loss_pct) }}">{{ $n2($it->loss_kg) }}</td>
                    <td class="r {{ $cls($it->loss_pct) }}">{{ $it->loss_pct !== null ? number_format($it->loss_pct, 2).'%' : '-' }}</td>
                    <td class="c">{{ $dmy($it->inplan) }}</td>
                    <td class="c">{{ $dmy($it->end_date) }}</td>
                </tr>
            @empty
                <tr><td colspan="13" class="c">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td></tr>
            @endforelse
            @if($total > 0)
                <tr class="total">
                    <td colspan="6" class="r">รวม {{ number_format($total) }} รายการ</td>
                    <td class="r">{{ number_format($sum['po'], 2) }}</td>
                    <td class="r">{{ number_format($sum['tp'], 2) }}</td>
                    <td class="r">{{ number_format($sum['produced'], 2) }}</td>
                    <td class="r">{{ number_format($sum['loss_kg'], 2) }}</td>
                    <td class="r">{{ $sum['loss_pct'] !== null ? number_format($sum['loss_pct'], 2).'%' : '-' }}</td>
                    <td colspan="2"></td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
