{{--
    รายงาน "การเบิกตัวอย่าง (ตามรหัสลูกค้า)" + ช่วงวันที่ (29/10/2569) — ผังตามกระดาษระบบเดิม
    ColorMatchingController::sampleWithdrawPdf() · 1 แถว = 1 ใบส่ง ต.ย. (testmain ที่มี Testno) · เรียงตามวันที่เบิก
--}}
@php
    $d     = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/y') : '';
    $phr   = fn ($v) => $v === null || $v === '' ? '' : number_format((float) $v, 4);
    // จำนวน (กรัม) วางลงคอลัมน์ตาม TestType — 1 CP · 2 สีผง · 3 สีเม็ด · 4 Pigment
    $types = ['1', '2', '3', '4'];
    $wage  = fn ($r, $t) => trim((string) $r->TestType) === $t && $r->Wage !== null ? number_format((float) $r->Wage) : '';
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
        .num { text-align: right; }
        .ctr { text-align: center; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
{{-- หัวรายงานพิมพ์ซ้ำทุกหน้า ({PAGENO}/{nbpg} ใช้ได้เฉพาะใน header/footer ของ mPDF) · หัวตาราง (thead) ซ้ำเอง --}}
<htmlpageheader name="sw_head"><div lang="th">
    <table width="100%" class="head">
        <tr>
            <td style="width: 25%; {{ $hf }}"></td>
            <td style="width: 50%; {{ $hf }} font-size: 16px; font-weight: bold; text-align: center;">บริษัท เวิลด์พิกเมนท์อินดัสตรี จำกัด</td>
            <td style="width: 25%; {{ $hf }} font-size: 11px; text-align: right;">หน้าที่ {PAGENO} / {nbpg}&nbsp;&nbsp;{{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>
    <table width="100%" class="head" style="margin-top: 4px;">
        <tr>
            <td style="{{ $hf }} font-size: 13px;">
                <b>รายงานการเบิกตัวอย่าง (ตามรหัสลูกค้า)</b>&nbsp;&nbsp;<b>{{ $custno }}</b>&nbsp;&nbsp;{{ trim((string) optional($customer)->name) }}
            </td>
        </tr>
        <tr>
            <td style="{{ $hf }} font-size: 12px;">
                Sale <b>{{ trim((string) optional($customer)->sale) }}</b>
                &nbsp;&nbsp;&nbsp;&nbsp;วันที่รายงาน&nbsp;&nbsp;{{ $d($from) }}&nbsp;&nbsp;-&nbsp;&nbsp;{{ $d($to) }}
            </td>
        </tr>
    </table>
</div></htmlpageheader>
<sethtmlpageheader name="sw_head" value="on" show-this-page="1" />

{{-- ความกว้างคอลัมน์รวม 100% --}}
<table class="list">
    <thead>
        <tr>
            <th style="width: 7%;">เรียงตาม<br>วันที่เบิก</th>
            <th style="width: 8%;">เลขที่ใบเบิก</th>
            <th style="width: 17%;"></th>
            <th style="width: 10%;">ResinMatch</th>
            <th class="num" style="width: 5%;">PHR</th>
            <th style="width: 13%;">STD</th>
            <th class="ctr" style="width: 5%;">Sale<br>ที่เบิก</th>
            <th style="width: 10%;">Lot No.</th>
            <th class="num" style="width: 5%;">CP</th>
            <th class="num" style="width: 5%;">สีผง</th>
            <th class="num" style="width: 5%;">สีเม็ด</th>
            <th class="num" style="width: 5%;">Pigment</th>
            <th style="width: 5%;">ตั้งเบอร์</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $r)
            <tr>
                <td class="nowrap">{{ $d($r->TestDate) }}</td>
                <td class="nowrap">{{ $r->Testno }}</td>
                <td>{{ $r->TestDesc }}</td>
                <td>{{ $r->ResinMatch }}</td>
                <td class="num">{{ $phr($r->PHR) }}</td>
                <td>{{ $r->STD }}</td>
                <td class="ctr">{{ trim((string) $r->sale) }}</td>
                <td>{{ $r->lotno }}</td>
                @foreach ($types as $t)
                    <td class="num">{{ $wage($r, $t) }}</td>
                @endforeach
                <td>{{ $r->CodeNo }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="13" class="ctr" style="padding: 16px;">ไม่พบการเบิกตัวอย่างของลูกค้ารายนี้ในช่วงวันที่ที่เลือก</td>
            </tr>
        @endforelse
    </tbody>
</table>
</body>
</html>
