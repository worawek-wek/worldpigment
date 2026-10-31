{{--
    รายงาน "เทียบสี ส่งตัวอย่าง ระหว่างวันที่ … ถึงวันที่ …" (29/10/2569) — ผังตามกระดาษระบบเดิม (A4 แนวนอน)
    ColorMatchingController::matchReportPdf() · 1 แถว = 1 ใบนำส่ง (SendNo) · เรียงตาม รหัสเซลล์ → ลูกค้า → วันที่ส่ง
    Sale แสดงเฉพาะแถวแรกของเซลล์ · ลูกค้าแสดงเฉพาะแถวแรกของลูกค้า (แบบ hide duplicates ของรายงาน Access)
--}}
@php
    $d        = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/y') : '';
    $dHead    = fn ($v) => \Illuminate\Support\Carbon::parse($v)->format('j/n/y');   // หัวรายงานตามกระดาษ (1/1/26)
    // ข้อความไทยใน htmlpageheader ต้องตั้ง garuda ตรง ๆ ทุกช่อง (sarabun ไม่ได้ลงทะเบียนใน mPDF — ดู sample-report-pdf)
    $hf       = 'font-family: garuda;';
    $prevSale = null;
    $prevCust = null;
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 11px; color: #000; }
        .head td { padding: 1px 2px; vertical-align: bottom; }

        table.list { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.list th { font-size: 11px; font-weight: normal; text-align: left; vertical-align: bottom;
                        border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 3px; }
        table.list td { padding: 5px 3px; vertical-align: top; font-size: 11px; }
        .ctr { text-align: center; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
{{-- หัวรายงานพิมพ์ซ้ำทุกหน้า ({PAGENO}/{nbpg} ใช้ได้เฉพาะใน header/footer ของ mPDF) · หัวตาราง (thead) ซ้ำเอง --}}
<htmlpageheader name="mr_head"><div lang="th">
    <table width="100%" class="head">
        <tr>
            <td style="width: 75%; {{ $hf }} font-size: 17px; font-weight: bold;">
                รายงานเทียบสี&nbsp;&nbsp;ส่งตัวอย่าง&nbsp;&nbsp;ระหว่างวันที่&nbsp;&nbsp;{{ $dHead($from) }}&nbsp;&nbsp;&nbsp;&nbsp;ถึงวันที่&nbsp;&nbsp;{{ $dHead($to) }}
                <span style="{{ $hf }} font-size: 11px; font-weight: normal;">&nbsp;&nbsp;( เรียงตามรหัสเซลล์ , ลูกค้า )</span>
            </td>
            <td style="width: 25%; {{ $hf }} font-size: 11px; text-align: right;">หน้าที่ {PAGENO} / {nbpg}&nbsp;&nbsp;{{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>
</div></htmlpageheader>
<sethtmlpageheader name="mr_head" value="on" show-this-page="1" />

{{-- ความกว้างคอลัมน์รวม 100% --}}
<table class="list">
    <thead>
        <tr>
            <th class="ctr" style="width: 4%;">Sale</th>
            <th style="width: 14%;">ลูกค้า</th>
            <th style="width: 6%;">วันที่ส่ง<br>เทียบสี</th>
            <th style="width: 7%;">เลขที่ใบนำส่ง</th>
            <th style="width: 16%;">สี / วัตถุดิบ</th>
            <th style="width: 12%;">ลักษณะตัวอย่าง</th>
            <th colspan="2" style="width: 16%;">ประเภทงาน / นำไปทำชิ้นงาน</th>
            <th style="width: 13%;">หมายเหตุ</th>
            <th style="width: 6%;">กำหนด<br>เทียบสีเสร็จ</th>
            <th style="width: 6%;">วันที่เบิก<br>ตัวอย่าง</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $r)
            @php
                $sale     = trim((string) $r->sale);
                $custno   = trim((string) $r->custno);
                $newSale  = $prevSale === null || $sale !== $prevSale;
                $newCust  = $newSale || $custno !== $prevCust;
                $prevSale = $sale;
                $prevCust = $custno;
            @endphp
            <tr>
                <td class="ctr">{{ $newSale ? $sale : '' }}</td>
                <td>{{ $newCust ? trim((string) ($r->cust_name ?: $r->custname ?: $custno)) : '' }}</td>
                <td class="nowrap">{{ $d($r->DsendT) }}</td>
                <td class="nowrap">{{ $r->SendNo }}</td>
                <td>{{ $r->color }}</td>
                <td>{{ $r->STD }}</td>
                <td style="width: 7%;">{{ $r->Type_Work }}</td>
                <td style="width: 9%;">{{ $r->Model }}</td>
                <td>{{ $r->Mems }}</td>
                <td class="nowrap">{{ $d($r->TNDate) }}</td>
                <td class="nowrap">{{ $d($r->TestDate) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="ctr" style="padding: 16px;">ไม่พบใบนำส่งเทียบสีในช่วงวันที่ที่เลือก</td>
            </tr>
        @endforelse
    </tbody>
</table>
</body>
</html>
