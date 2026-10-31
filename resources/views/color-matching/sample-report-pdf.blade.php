{{--
    รายงาน "ใบส่งตัวอย่างเทียบสี" ตามรหัสลูกค้า + ช่วงวันที่ (29/10/2569) — ผังตามกระดาษระบบเดิม
    ColorMatchingController::sampleReportPdf() · 1 แถว = 1 แถว testmain (ใบส่ง ต.ย. หรือ CM ที่ยังไม่มีใบส่ง ต.ย.)
--}}
@php
    $d     = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/y') : '';
    $phr   = fn ($v) => $v === null || $v === '' ? '' : number_format((float) $v, 4);
    // จำนวน (กรัม) วางลงคอลัมน์ตาม TestType — 1 CP · 2 สีผง · 3 สีเม็ด · 4 Pigment
    $types = ['1', '2', '3', '4'];
    $wage  = fn ($r, $t) => trim((string) $r->TestType) === $t && $r->Wage !== null ? number_format((float) $r->Wage) : '';
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 12px; color: #000; }
        .company { font-size: 16px; font-weight: bold; text-align: center; }
        .title { font-size: 14px; font-weight: bold; text-align: center; }
        .head td { padding: 1px 2px; vertical-align: bottom; }

        table.list { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.list th { font-size: 11px; font-weight: normal; text-align: left; vertical-align: bottom;
                        border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 2px 3px; }
        table.list th.grp { border-bottom: 1px solid #000; text-align: center; }
        table.list td { padding: 4px 3px; vertical-align: top; font-size: 11px; }
        .num { text-align: right; }
        .ctr { text-align: center; }
        .nowrap { white-space: nowrap; }
        /* กล่อง Cancel — ตารางซ้อน 1 ช่อง (mPDF คุมขนาด inline-block ไม่แน่นอน) · เครื่องหมายถูกต้องใช้ dejavu (Garuda ไม่มี glyph) */
        table.chk { border-collapse: collapse; margin: 0 auto; }
        table.chk td { border: 1px solid #000; width: 11px; height: 11px; padding: 0; text-align: center;
                       font-family: 'dejavusanscondensed'; font-size: 9px; line-height: 11px; }
    </style>
</head>
<body>
{{-- หัวรายงานพิมพ์ซ้ำทุกหน้าผ่าน htmlpageheader ({PAGENO}/{nbpg} ใช้ได้เฉพาะใน header/footer ของ mPDF) · หัวตาราง (thead) ซ้ำเอง --}}
<htmlpageheader name="sr_head"><div lang="th">
    <table width="100%" class="head" style="font-family: garuda;">
        <tr>
            <td style="width: 25%; font-family: garuda;"></td>
            <td style="width: 50%; font-family: garuda; font-size: 16px; font-weight: bold; text-align: center;">บริษัท เวิลด์พิกเมนท์อินดัสตรี จำกัด</td>
            <td style="width: 25%; text-align: right; font-size: 11px; font-family: garuda;">หน้าที่ {PAGENO} / {nbpg}&nbsp;&nbsp;{{ now()->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td></td>
            <td style="font-family: garuda; font-size: 14px; font-weight: bold; text-align: center;">ใบส่งตัวอย่างเทียบสี จากวันที่ {{ $d($from) }} ถึงวันที่ {{ $d($to) }}</td>
            <td></td>
        </tr>
    </table>
    <table width="100%" class="head" style="margin-top: 4px; font-family: garuda;">
        <tr>
            <td style="width: 75%; font-family: garuda; font-size: 12px;">ลูกค้า <b>{{ $custno }}</b>&nbsp;&nbsp;{{ trim((string) optional($customer)->name) }}</td>
            <td style="text-align: right; font-family: garuda; font-size: 12px;">Sale <b>{{ trim((string) optional($customer)->sale) }}</b></td>
        </tr>
    </table>
</div></htmlpageheader>
<sethtmlpageheader name="sr_head" value="on" show-this-page="1" />

{{-- ความกว้างคอลัมน์รวม 100% --}}
<table class="list">
    <thead>
        <tr>
            <th rowspan="2" style="width: 8%;">เลขที่ใบนำส่ง<br>เทียบสี</th>
            <th rowspan="2" style="width: 7%;">TN กำหนด<br>ส่ง ต.ย. เสร็จ</th>
            <th rowspan="2" style="width: 7%;">เลขที่ใบส่ง<br>ตัวอย่างสินค้า</th>
            <th rowspan="2" style="width: 14%;">ตัวอย่างสินค้า</th>
            <th rowspan="2" style="width: 9%;">ResinMatch</th>
            <th rowspan="2" class="num" style="width: 5%;">PHR</th>
            <th rowspan="2" style="width: 9%;">Lot No.</th>
            <th rowspan="2" style="width: 12%;">ตัวอย่างชิ้นงาน</th>
            <th colspan="4" class="grp" style="width: 17%;">จำนวน ( กรัม )</th>
            <th rowspan="2" style="width: 8%;">ตั้งเบอร์เป็น</th>
            <th rowspan="2" class="ctr" style="width: 4%;">Cancel</th>
        </tr>
        <tr>
            <th class="num" style="width: 4%;">CP</th>
            <th class="num" style="width: 4%;">สีผง</th>
            <th class="num" style="width: 4%;">สีเม็ด</th>
            <th class="num" style="width: 5%;">Pigment</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $r)
            <tr>
                <td class="nowrap">{{ $r->SendNo }}</td>
                <td class="nowrap">{{ $d($r->TNDate) }}</td>
                <td class="nowrap">{{ $r->Testno }}</td>
                <td>{{ $r->TestDesc }}</td>
                <td>{{ $r->ResinMatch }}</td>
                <td class="num">{{ $phr($r->PHR) }}</td>
                <td>{{ $r->lotno }}</td>
                <td>{{ $r->STD }}</td>
                @foreach ($types as $t)
                    <td class="num">{{ $wage($r, $t) }}</td>
                @endforeach
                <td>{{ $r->CodeNo }}</td>
                <td>
                    <table class="chk"><tr><td>{!! (int) $r->cancel !== 0 ? '&#10004;' : '' !!}</td></tr></table>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="14" class="ctr" style="padding: 16px;">ไม่พบใบส่งตัวอย่างของลูกค้ารายนี้ในช่วงวันที่ที่เลือก</td>
            </tr>
        @endforelse
    </tbody>
</table>
</body>
</html>
