{{--
    รายงาน "เทียบสีค้างสะสม จนถึง ส่งตัวอย่างวันที่ …" (29/10/2569) — ผังตามกระดาษระบบเดิม (A4 แนวนอน)
    ColorMatchingController::pendingPdf() · 1 แถว = 1 ใบนำส่งเทียบสีที่ยังไม่มีใบส่ง ต.ย. · เรียงตามเลขที่ใบนำส่ง
    Sale + ลูกค้า แสดงเฉพาะแถวแรกของลูกค้ารายเดิมที่อยู่ติดกัน (แบบ hide duplicates ของรายงาน Access)
    คอลัมน์ "วันที่เบิกตัวอย่าง" เว้นว่างไว้ตามกระดาษ (ใบที่ค้างยังไม่มีการเบิก)
--}}
@php
    $d    = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/y') : '';
    // ข้อความไทยใน htmlpageheader ต้องตั้ง garuda ตรง ๆ ทุกช่อง (sarabun ไม่ได้ลงทะเบียนใน mPDF — ดู sample-report-pdf)
    $hf   = 'font-family: garuda;';
    $prev = null;
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
<htmlpageheader name="pd_head"><div lang="th">
    <table width="100%" class="head">
        <tr>
            <td style="width: 75%; {{ $hf }} font-size: 17px; font-weight: bold;">
                รายงานเทียบสีค้างสะสม&nbsp;&nbsp;จนถึง&nbsp;&nbsp;ส่งตัวอย่างวันที่&nbsp;&nbsp;{{ $d($to) }}
                <span style="{{ $hf }} font-size: 11px; font-weight: normal;">&nbsp;&nbsp;( เรียงตาม เลขที่ใบนำส่งเทียบสี )</span>
            </td>
            <td style="width: 25%; {{ $hf }} font-size: 11px; text-align: right;">หน้าที่ {PAGENO} / {nbpg}&nbsp;&nbsp;{{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>
</div></htmlpageheader>
<sethtmlpageheader name="pd_head" value="on" show-this-page="1" />

{{-- ความกว้างคอลัมน์รวม 100% --}}
<table class="list">
    <thead>
        <tr>
            <th style="width: 7%;">เลขที่ใบนำส่ง</th>
            <th class="ctr" style="width: 4%;">Sale</th>
            <th style="width: 14%;">ลูกค้า</th>
            <th style="width: 6%;">วันที่<br>ส่งเทียบ</th>
            <th style="width: 15%;">สี / วัตถุดิบ</th>
            <th style="width: 12%;">ลักษณะตัวอย่าง</th>
            <th colspan="2" style="width: 17%;">ประเภทงาน / นำไปทำชิ้นงาน</th>
            <th style="width: 13%;">หมายเหตุ</th>
            <th style="width: 6%;">กำหนด<br>เทียบสีเสร็จ</th>
            <th style="width: 6%;">วันที่เบิก<br>ตัวอย่าง</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $r)
            @php
                $custno = trim((string) $r->custno);
                $same   = $prev !== null && $custno === $prev;
                $prev   = $custno;
            @endphp
            <tr>
                <td class="nowrap">{{ $r->SendNo }}</td>
                <td class="ctr">{{ $same ? '' : trim((string) $r->sale) }}</td>
                <td>{{ $same ? '' : trim((string) ($r->cust_name ?: $r->custname ?: $custno)) }}</td>
                <td class="nowrap">{{ $d($r->DsendT) }}</td>
                <td>{{ $r->color }}</td>
                <td>{{ $r->STD }}</td>
                <td style="width: 7%;">{{ $r->Type_Work }}</td>
                <td style="width: 10%;">{{ $r->Model }}</td>
                <td>{{ $r->Mems }}</td>
                <td class="nowrap">{{ $d($r->TNDate) }}</td>
                <td></td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="ctr" style="padding: 16px;">ไม่พบใบนำส่งเทียบสีที่ค้างอยู่ จนถึงวันที่ที่เลือก</td>
            </tr>
        @endforelse
    </tbody>
</table>
</body>
</html>
