{{--
    รายงาน "การเบิกตัวอย่าง (ตามพนักงานขาย)" + ช่วงวันที่ (29/10/2569) — ผังตามกระดาษระบบเดิม (A4 แนวตั้ง)
    ColorMatchingController::saleWithdrawPdf() · 1 กลุ่ม = 1 ลูกค้า · 1 แถว = 1 ใบส่ง ต.ย. · เรียงวันที่เบิก
    กระดาษเดิมมีคอลัมน์จำนวนแค่ CP / สีผง / สีเม็ด (ไม่มี Pigment)
--}}
@php
    $d     = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/y') : '';
    $phr   = fn ($v) => $v === null || $v === '' ? '' : number_format((float) $v, 4);
    // จำนวน (กรัม) วางลงคอลัมน์ตาม TestType — 1 CP · 2 สีผง · 3 สีเม็ด
    $types = ['1', '2', '3'];
    $wage  = fn ($r, $t) => trim((string) $r->TestType) === $t && $r->Wage !== null ? number_format((float) $r->Wage, 2) : '';
    // ข้อความไทยใน htmlpageheader ต้องตั้ง garuda ตรง ๆ ทุกช่อง (sarabun ไม่ได้ลงทะเบียนใน mPDF — ดู sample-report-pdf)
    $hf    = 'font-family: garuda;';
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 12px; color: #000; }
        .head td { padding: 1px 2px; vertical-align: bottom; }

        table.list { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.list th { font-size: 11px; font-weight: normal; text-align: left; vertical-align: bottom;
                        border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 2px 3px; }
        table.list td { padding: 4px 3px; vertical-align: top; font-size: 11px; }
        table.list tr.cust td { padding-top: 10px; }
        /* เส้นสั้นใต้แต่ละกลุ่มลูกค้า ตามกระดาษ */
        table.list tr.endgrp td { padding: 0 0 2px; }
        table.list tr.endgrp td.line { border-bottom: 1px solid #000; }
        .num { text-align: right; }
        .ctr { text-align: center; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
{{-- หัวรายงานพิมพ์ซ้ำทุกหน้า ({PAGENO}/{nbpg} ใช้ได้เฉพาะใน header/footer ของ mPDF) · หัวตาราง (thead) ซ้ำเอง --}}
<htmlpageheader name="sl_head"><div lang="th">
    <table width="100%" class="head">
        <tr>
            <td style="width: 25%; {{ $hf }}"></td>
            <td style="width: 50%; {{ $hf }} font-size: 16px; font-weight: bold; text-align: center;">บริษัท เวิลด์พิกเมนท์อินดัสตรี จำกัด</td>
            <td style="width: 25%; {{ $hf }} font-size: 11px; text-align: right;">หน้าที่ {PAGENO} / {nbpg}&nbsp;&nbsp;{{ now()->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td colspan="3" style="{{ $hf }} font-size: 13px; text-align: center;">
                <b>รายงานการเบิกตัวอย่าง ( พนักงานขายเลขที่ {{ $sale }} )</b>&nbsp;&nbsp;&nbsp;{{ $d($from) }}&nbsp;&nbsp;-&nbsp;&nbsp;{{ $d($to) }}
            </td>
        </tr>
    </table>
</div></htmlpageheader>
<sethtmlpageheader name="sl_head" value="on" show-this-page="1" />

{{-- ความกว้างคอลัมน์รวม 100% --}}
<table class="list">
    <thead>
        <tr>
            <th style="width: 10%;">เลขที่ใบเบิก</th>
            <th style="width: 8%;">วันที่เบิก</th>
            <th style="width: 20%;">สี</th>
            <th style="width: 12%;">ResinMatch</th>
            <th class="num" style="width: 7%;">PHR</th>
            <th style="width: 11%;">Lot No.</th>
            <th class="num" style="width: 7%;">CP</th>
            <th class="num" style="width: 7%;">สีผง</th>
            <th class="num" style="width: 8%;">สีเม็ด</th>
            <th style="width: 10%;">ตั้งเบอร์</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($groups as $g)
            <tr class="cust">
                <td colspan="10">{{ $g['custno'] }}&nbsp;&nbsp;{{ $g['name'] }}</td>
            </tr>
            @foreach ($g['items'] as $r)
                <tr>
                    <td class="nowrap">{{ $r->Testno }}</td>
                    <td class="nowrap">{{ $d($r->TestDate) }}</td>
                    <td>{{ $r->TestDesc }}</td>
                    <td>{{ $r->ResinMatch }}</td>
                    <td class="num">{{ $phr($r->PHR) }}</td>
                    <td>{{ $r->lotno }}</td>
                    @foreach ($types as $t)
                        <td class="num">{{ $wage($r, $t) }}</td>
                    @endforeach
                    <td>{{ $r->CodeNo }}</td>
                </tr>
            @endforeach
            <tr class="endgrp">
                <td colspan="3" class="line"></td>
                <td colspan="7"></td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="ctr" style="padding: 16px;">ไม่พบการเบิกตัวอย่างของพนักงานขายรายนี้ในช่วงวันที่ที่เลือก</td>
            </tr>
        @endforelse
    </tbody>
</table>
</body>
</html>
