{{--
    รายงาน Order ค้างส่งลูกค้า (26/10/2569) — ผังตามกระดาษของระบบเดิม (เรียงตามกำหนดแรก)
    OrderOutstandingController::pdf() · 1 กลุ่ม = 1 ลูกค้า · 1 แถว = 1 รายการ suborder ที่ยังไม่ส่ง (DVDate ว่าง)
--}}
@php
    $d    = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/y') : '';
    $dS   = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('j/n/y') : '';   // ช่องเลื่อน ตามกระดาษ (9/11/26)
    $n    = fn ($v) => abs((float) $v) < 0.000001 ? '' : number_format((float) $v, 2);
    $tick = fn ($v) => $v !== null && (int) $v !== 0;
    // Bath = (Stock + Production) × @ (ราคาขายต่อหน่วย) — 27/10/2569 ตามที่ผู้ใช้สั่ง
    $bath = fn ($r) => ((float) $r->Stock + (float) $r->Production) * (float) $r->price;
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
        table.list td { padding: 3px 3px; vertical-align: top; font-size: 11px; white-space: nowrap; }
        /* ไม่ตัดบรรทัดในช่องข้อมูล (27/10/2569) — ถ้ายังเกินหน้า mPDF ย่อทั้งตารางให้พอดีเอง · แถวชื่อลูกค้า/ข้อความไม่พบ ยอมตัดบรรทัดได้ */
        table.list tr.cust td, table.list td.wrap { white-space: normal; }
        .num { text-align: right; }
        .bold { font-weight: bold; }
        .cust td { padding-top: 10px; }
        .cust span { text-decoration: underline; }
        table.box { border-collapse: collapse; width: 100%; }
        table.box td { border: 1px solid #000; text-align: right; padding: 3px 4px; }
        .arrow { font-family: 'dejavusanscondensed'; font-size: 9px; }
        tr.total td { padding-top: 10px; font-weight: bold; }
        tr.total table.box td { border-width: 2px; }
        table.list td.sep { border-top: 1.5px solid #000; padding-top: 8px; }
        .crnote { font-size: 20px; font-weight: bold; font-style: italic; text-align: right; padding-right: 30mm; }
    </style>
</head>
<body>
@foreach ($sections as $sec)
    @php $sale = $sec['sale']; $groups = $sec['groups']; @endphp
    {{-- 1 ผู้ขาย = หัวรายงาน + กรอบ # รหัส + ตารางของตัวเอง · ผู้ขายคนถัดไปขึ้นหน้าใหม่
         ⚠ ใช้แท็ก <pagebreak /> ของ mPDF — page-break-before บน <table> mPDF ไม่ทำตาม (ทดสอบแล้ว) --}}
    @if (!$loop->first)
        <pagebreak />
    @endif
    <table width="100%" class="head">
        <tr>
            <td style="width: 58%;">
                <span class="title"><i>Order</i> ค้างส่งลูกค้า {{ $subtitle }}</span>
                <span class="sub">( เรียงตามกำหนดแรก )</span>
            </td>
            <td><table class="salebox"><tr><td># {{ $sale !== '' ? $sale : '-' }}</td></tr></table></td>
            {{-- "รวม CR" มุมขวา (เหนือคอลัมน์ PO. NO.) — เฉพาะรายงานเฉพาะผลิตไม่เสร็จ ตามกระดาษเดิม (26/10/2569) --}}
            @if ($type === 'undone')
                <td class="crnote">รวม CR</td>
            @endif
        </tr>
    </table>

    {{-- แบบแรก (ตามรหัสผู้ขาย) ไม่แสดงคอลัมน์ Baht / @ (27/10/2569 ตามที่ผู้ใช้สั่ง) — ความกว้างที่เหลือแบ่งให้ช่องข้อความ (รวม 100% ทั้ง 2 แบบ) --}}
    @php
        $showPrice = $type !== 'sale';
        $pc = $showPrice ? 2 : 0;                       // จำนวนคอลัมน์ราคา (ใช้ใน colspan)
        $w  = fn ($withPrice, $noPrice) => $showPrice ? $withPrice : $noPrice;
    @endphp
    <table class="list">
        <thead>
            <tr>
                <th style="width: 5.5%;">Orderno</th>
                <th style="width: 5%;"></th>
                <th style="width: {{ $w(10, 12.5) }}%;">รหัสสินค้า</th>
                <th style="width: {{ $w(6, 8) }}%;">Lotno</th>
                <th class="num" style="width: {{ $w(6, 7) }}%;">Stock</th>
                <th class="num" style="width: {{ $w(6.5, 7.5) }}%;">Production</th>
                <th style="width: 5%;">กำหนด ที่<br>ลูกค้า<br>ต้องการ</th>
                <th style="width: 5%;">กำหนดแรก</th>
                @for ($i = 1; $i <= $shiftCols; $i++)
                    <th style="width: 4.5%;">เลื่อน{{ $i }}</th>
                @endfor
                <th style="width: 5%;">เข้าคลัง</th>
                <th style="width: 2.5%; text-align: center;">ส่ง<br>ก่อน<br>ได้</th>
                <th style="width: {{ $w(11, 15) }}%;">PO. NO.</th>
                <th style="width: {{ $w(6.5, 8.5) }}%;"></th>
                @if ($showPrice)
                {{-- ต้อง text-align inline — กฎ table.list th (ชิดซ้าย) ชนะ class .num --}}
                <th style="width: 7.5%; text-align: right;">Baht</th>
                {{-- ราคาขายต่อหน่วย = morder.price (26/10/2569 ตามที่ผู้ใช้สั่ง) --}}
                <th style="width: 5%; text-align: center;">@</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($groups as $g)
                <tr class="cust">
                    <td></td>
                    <td colspan="{{ 11 + $pc + $shiftCols }}">
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
                        @if ($showPrice)
                            <td class="num">{{ $n($bath($r)) }}</td>
                            <td class="num">{{ $n($r->price) }}</td>
                        @endif
                    </tr>
                @endforeach
                @php $bathTotal = collect($g['items'])->sum($bath); @endphp
                <tr>
                    <td colspan="4"></td>
                    <td><table class="box"><tr><td>{!! $n($g['stock']) ?: '&nbsp;' !!}</td></tr></table></td>
                    <td><table class="box"><tr><td>{!! $n($g['prod']) ?: '&nbsp;' !!}</td></tr></table></td>
                    <td colspan="{{ 6 + $shiftCols }}"></td>
                    @if ($showPrice)
                        <td><table class="box"><tr><td>{!! $n($bathTotal) ?: '&nbsp;' !!}</td></tr></table></td>
                        <td></td>
                    @endif
                </tr>
            @empty
                <tr><td class="wrap" colspan="{{ 12 + $pc + $shiftCols }}" style="text-align: center; padding: 20px;">ไม่พบ Order ค้างส่ง{{ $sale !== '' ? 'ของรหัสผู้ขาย ' . $sale : '' }}</td></tr>
            @endforelse
            {{-- Sale Total = ยอดรวมของผู้ขายคนนี้ · แถวสุดท้ายของรายงาน = ยอดรวมทุก Sale (27/10/2569 ตามที่ผู้ใช้สั่ง) --}}
            @php
                $totalRows = [];
                if ($groups->isNotEmpty()) {
                    $totalRows[] = ['Sale Total # ' . ($sale !== '' ? $sale : '-'), $sec['stock'], $sec['prod'], $sec['bath']];
                }
                if ($loop->last && $grand['count'] > 0) {
                    $totalRows[] = ['Grand Total (ทุก Sale)', $grand['stock'], $grand['prod'], $grand['bath']];
                }
            @endphp
            @foreach ($totalRows as [$label, $tStock, $tProd, $tBath])
                {{-- คั่นแถวยอดรวมด้วยเส้นเต็มความกว้าง (class sep ที่ td ชั้นนอก — ไม่ให้โดนกล่องตัวเลขข้างใน) --}}
                <tr class="total">
                    <td colspan="4" class="bold sep" style="text-align: right;">{{ $label }}</td>
                    <td class="sep"><table class="box"><tr><td>{!! $n($tStock) ?: '&nbsp;' !!}</td></tr></table></td>
                    <td class="sep"><table class="box"><tr><td>{!! $n($tProd) ?: '&nbsp;' !!}</td></tr></table></td>
                    <td class="sep" colspan="{{ 6 + $shiftCols }}"></td>
                    @if ($showPrice)
                        <td class="sep"><table class="box"><tr><td>{!! $n($tBath) ?: '&nbsp;' !!}</td></tr></table></td>
                        <td class="sep"></td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach
</body>
</html>
