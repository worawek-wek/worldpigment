{{--
    รายงาน "บันทึกติดตามผลการเทียบสี แผนก MK" ตามช่วงวันที่เบิกตัวอย่าง (30/10/2569) — ผังตามกระดาษระบบเดิม MK01-LB03.00 (A4 แนวนอน)
    ColorMatchingController::followUpPdf() · 1 section = 1 เซลส์ (ขึ้นหน้าใหม่) → กลุ่มลูกค้า → 1 แถว = 1 ใบส่ง ต.ย.
    ⚠ "ส่ง Match" กับ "ส่ง ต.ย." นับเท่ากัน (= จำนวนแถว) ตามกระดาษ · "ตั้งเบอร์ใหม่" = แถวที่มี CodeNo — ทั้งคู่เดาจากกระดาษ
    สาเหตุ = TyResp A–H (+ Resp) · ยังไม่ระบุ = เส้นว่างให้เขียนมือ
--}}
@php
    $d      = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/y') : '';
    $phr    = fn ($v) => $v === null || $v === '' ? '' : number_format((float) $v, 4);
    $wage   = fn ($v) => $v === null || $v === '' ? '' : number_format((float) $v);
    $cut    = fn ($v, $n) => \Illuminate\Support\Str::limit(trim((string) $v), $n, '');
    $reason = function ($r) use ($reasons) {
        $code = strtoupper(trim((string) $r->TyResp));
        if (!isset($reasons[$code])) return '';
        return trim($code . ' ' . trim((string) $r->Resp));
    };
    // ข้อความไทยใน htmlpageheader/footer ต้องตั้ง garuda ตรง ๆ ทุกช่อง (sarabun ไม่ได้ลงทะเบียนใน mPDF — ดู sample-report-pdf)
    $hf = 'font-family: garuda;';
    $legend = $reasons->map(fn ($o, $k) => $k . ' = ' . $o['label'] . (!empty($o['specify']) ? ' ระบุ ...' : ''))->values();
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 11px; color: #000; }

        table.list { width: 100%; border-collapse: collapse; }
        table.list th { font-size: 10px; font-weight: normal; text-align: left; vertical-align: bottom;
                        border-bottom: 1px solid #000; padding: 1px 2px; line-height: 1.1; }
        table.list td { padding: 2px 2px; vertical-align: top; font-size: 10.5px; white-space: nowrap; }
        table.list tr.first td { padding-top: 6px; }
        table.list tr.endgrp td { padding: 0; border-bottom: 1px solid #000; height: 2px; }
        table.list td.reason { border-bottom: 1px solid #000; }
        .num { text-align: right; }
        .ctr { text-align: center; }

        .sum td { padding: 10px 2px 2px; font-size: 13px; }
        .sum .ttl { color: #1d3fa0; text-decoration: underline; }
        .legend td { font-size: 10.5px; color: #b0302a; padding: 1px 2px; }
        .box { border: 2px solid #b0302a; padding: 6px 10px; margin-top: 14px; }
        .box .head { font-size: 18px; font-weight: bold; color: #b0302a; }
    </style>
</head>
<body>

<htmlpagefooter name="fu_foot"><div lang="th">
    <table width="100%">
        <tr>
            <td style="{{ $hf }} font-size: 10px;">P.{PAGENO} / {nbpg} วันที่พิมพ์:{{ now()->format('d/m/y') }}</td>
            <td style="{{ $hf }} font-size: 10px; text-align: right;">12-02-51 / MK01-LB03.00</td>
        </tr>
    </table>
</div></htmlpagefooter>
<sethtmlpagefooter name="fu_foot" value="on" />

{{-- หัวรายงาน 1 ชุดต่อเซลส์ (เลข Sale อยู่บนหัว) — สลับด้วย sethtmlpageheader หลังขึ้นหน้าใหม่ --}}
@foreach ($sections->isEmpty() ? [(object) ['sale' => '']] : $sections as $i => $sec)
    <htmlpageheader name="fu_head{{ $i }}"><div lang="th">
        <table width="100%" style="border-bottom: 1px solid #000;">
            <tr>
                <td style="{{ $hf }} font-size: 14px;">
                    <b>บันทึกติดตามผลการเทียบสี แผนก MK</b>&nbsp; จากวันที่เบิกตัวอย่าง {{ $d($from) }} ถึงวันที่ {{ $d($to) }}
                </td>
                <td style="{{ $hf }} font-size: 14px; text-align: right;">
                    Sale&nbsp; <b>{{ $sec->sale !== '' ? $sec->sale : 'ไม่ระบุ' }}</b>&nbsp;&nbsp;
                    <span style="{{ $hf }} font-size: 10px; color: #b0302a;">(เรียงตามรหัสเซลส์, ลูกค้า)</span>
                </td>
            </tr>
        </table>
    </div></htmlpageheader>
@endforeach

@php $colspan = 14; @endphp
@forelse ($sections as $i => $sec)
    @if (!$loop->first)
        <pagebreak />
    @endif
    <sethtmlpageheader name="fu_head{{ $i }}" value="on" show-this-page="1" />

    {{-- ความกว้างคอลัมน์รวม 100% --}}
    <table class="list">
        <thead>
            <tr>
                <th style="width: 2%;"></th>
                <th style="width: 13%;">ลูกค้า</th>
                <th style="width: 5%;">วันที่<br>เบิกตัวอย่าง</th>
                <th style="width: 6%;">เลขที่<br>ใบเบิกตัวอย่าง</th>
                <th style="width: 15%;">สี / วัตถุดิบ</th>
                <th style="width: 10%;"></th>
                <th class="num" style="width: 5%;">PHR</th>
                <th style="width: 7%;">เลขที่ใบนำส่ง</th>
                <th style="width: 8%;">Lot Test</th>
                <th class="num" style="width: 5%;">น.น.เบิก<br>(กรัม)</th>
                <th style="width: 6%;">ประเภทงาน /</th>
                <th style="width: 9%;">นำไปทำชิ้นงาน</th>
                <th style="width: 1%;"></th>
                <th style="width: 8%;">สาเหตุ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sec->customers as $cust)
                @foreach ($cust->items as $n => $r)
                    <tr class="{{ $n === 0 ? 'first' : '' }}">
                        <td class="num">{{ $n + 1 }}</td>
                        <td>{{ $n === 0 ? $cut($cust->name, 24) : '' }}</td>
                        <td>{{ $d($r->TestDate) }}</td>
                        <td>{{ $r->Testno }}</td>
                        <td>{{ $cut($r->TestDesc, 28) }}</td>
                        <td>{{ $cut($r->ResinMatch, 18) }}</td>
                        <td class="num">{{ $phr($r->PHR) }}</td>
                        <td>{{ $cut($r->SendNo, 14) }}</td>
                        <td>{{ $cut($r->lotno, 14) }}</td>
                        <td class="num">{{ $wage($r->Wage) }}</td>
                        <td>{{ $cut($r->Type_Work, 10) }}</td>
                        <td>{{ $cut($r->Model, 16) }}</td>
                        <td></td>
                        <td class="reason">{{ $reason($r) }}</td>
                    </tr>
                @endforeach
                <tr class="endgrp"><td colspan="12"></td><td colspan="2" style="border-bottom: 0;"></td></tr>
            @endforeach
        </tbody>
    </table>

    {{-- สรุปท้ายเซลส์ --}}
    <table width="100%" class="sum">
        <tr>
            <td style="width: 35%;"><span class="ttl">รวม Sale #{{ $sec->sale !== '' ? $sec->sale : 'ไม่ระบุ' }} ส่ง Match (รายการ)</span>&nbsp;&nbsp;{{ number_format($sec->count) }}</td>
            <td style="width: 35%;">รวม ส่ง ต.ย. (รายการ) =&nbsp;&nbsp;{{ number_format($sec->count) }}</td>
            <td style="width: 30%; text-align: right;">ตั้งเบอร์ใหม่ =&nbsp;&nbsp;{{ number_format($sec->new_codes) }}</td>
        </tr>
    </table>
    @include('color-matching.partials.follow-up-legend', ['legend' => $legend])

    @if ($loop->last)
        {{-- สรุปทุกเซลส์ --}}
        <div class="box">
            <div class="head">สรุป ( ทุกเซลส์ )</div>
            <table width="100%" class="sum">
                <tr>
                    <td style="width: 35%;">รวม ส่ง Match (รายการ) = <u>{{ number_format($total) }}</u></td>
                    <td style="width: 35%;">รวม ส่ง ต.ย. (รายการ) = <u>{{ number_format($total) }}</u></td>
                    <td style="width: 30%; text-align: right;">ตั้งเบอร์ใหม่ =&nbsp;&nbsp;{{ number_format($newCodes) }}</td>
                </tr>
            </table>
            @include('color-matching.partials.follow-up-legend', ['legend' => $legend])
        </div>
    @endif
@empty
    <sethtmlpageheader name="fu_head0" value="on" show-this-page="1" />
    <div class="ctr" style="padding: 24px; font-size: 13px;">ไม่พบการเบิกตัวอย่างในช่วงวันที่ที่เลือก</div>
@endforelse

</body>
</html>
