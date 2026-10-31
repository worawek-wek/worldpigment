{{--
    รายงาน "บันทึกใบนำส่งตัวอย่างเทียบสี" ตามช่วงวันที่ส่งเทียบสี (29/10/2569) — ผังตามกระดาษระบบเดิม (A4 แนวนอน)
    ColorMatchingController::sendRecordPdf() · 1 แถว = 1 ใบนำส่ง (SendNo) · Mems แสดงเป็นบรรทัดย่อยใต้แถว
--}}
@php
    $d  = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/y') : '';
    // ข้อความไทยใน htmlpageheader ต้องตั้ง garuda ตรง ๆ ทุกช่อง (sarabun ไม่ได้ลงทะเบียนใน mPDF — ดู sample-report-pdf)
    $hf = 'font-family: garuda;';
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
        table.list th { font-size: 10px; font-weight: bold; text-align: center; vertical-align: middle;
                        border: 1px solid #000; padding: 2px 3px; }
        table.list td { padding: 3px; vertical-align: top; font-size: 10px; border: 1px solid #000; }
        /* แถวหัวกลุ่ม — เว้นช่องไฟบน-ล่างให้ตัวหนังสือไม่ชิดเส้น */
        table.list th.grp { padding: 6px 3px; font-size: 11px; }
        /* แถวหมายเหตุ (Mems) ใต้แถวหลัก — ไม่มีเส้นคั่นบนให้ดูเป็นแถวเดียวกัน */
        table.list tr.has-mems td { border-bottom: none; }
        table.list tr.mems td { border-top: none; padding-top: 0; }
        .ctr { text-align: center; }
        table.list td.num { text-align: right; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
{{-- หัวรายงานพิมพ์ซ้ำทุกหน้า ({PAGENO}/{nbpg} ใช้ได้เฉพาะใน header/footer ของ mPDF) · หัวตาราง (thead) ซ้ำเอง --}}
<htmlpageheader name="sn_head"><div lang="th">
    <table width="100%" class="head">
        <tr>
            <td style="width: 25%; {{ $hf }}"></td>
            <td style="width: 50%; {{ $hf }} font-size: 16px; font-weight: bold; text-align: center;">บันทึกใบนำส่งตัวอย่างเทียบสี</td>
            <td style="width: 25%; {{ $hf }} font-size: 11px; text-align: right;">หน้าที่ {PAGENO} / {nbpg}&nbsp;&nbsp;{{ now()->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td colspan="3" style="{{ $hf }} font-size: 12px; text-align: center;">
                วันที่ส่งเทียบสี&nbsp;&nbsp;{{ $d($from) }}&nbsp;&nbsp;-&nbsp;&nbsp;{{ $d($to) }}
            </td>
        </tr>
    </table>
</div></htmlpageheader>
<sethtmlpageheader name="sn_head" value="on" show-this-page="1" />

{{-- ความกว้างคอลัมน์รวม 100% --}}
<table class="list">
    <thead>
        <tr>
            <th colspan="9" class="grp">ส่งตัวอย่างจากลูกค้า ให้แผนกเทคนิค</th>
            <th colspan="6" class="grp">รับตัวอย่างผลิตภัณฑ์ จากแผนกเทคนิค</th>
        </tr>
        <tr>
            <th style="width: 4.5%;">วันที่ส่ง<br>เทียบสี</th>
            <th style="width: 4.5%;">เลขที่<br>ใบนำส่ง</th>
            <th style="width: 11%;">ตามคำสั่ง<br>ลูกค้าเพื่อ</th>
            <th style="width: 10%;">สี / วัตถุดิบ</th>
            <th style="width: 7%;">ลักษณะตัวอย่าง</th>
            <th style="width: 5%;">ประเภท<br>งาน</th>
            <th style="width: 6%;">นำไปทำ<br>ชิ้นงาน</th>
            <th style="width: 9%;">หมายเหตุ<br>กำหนดเทียบสีเสร็จ</th>
            <th style="width: 5%;">ผู้รับเอกสาร<br>(เทคนิค)</th>
            <th style="width: 6%;">วันที่รับ<br>ตัวอย่าง<br>ผลิตภัณฑ์</th>
            <th style="width: 6%;">รหัสสินค้า</th>
            <th style="width: 5%;">LOT NO.</th>
            <th style="width: 7%;">เบอร์เทียบ /<br>การใช้วัตถุดิบ</th>
            <th style="width: 6%;">น้ำหนัก<br>ตัวอย่าง<br>(กรัม)</th>
            <th style="width: 8%;">เลขที่ใบ<br>รายงานผล</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $r)
            @php
                $mems = trim((string) $r->Mems);
                $note = collect([trim((string) $r->pop), $d($r->TNDate)])->filter()->implode(' , ');
            @endphp
            <tr class="{{ $mems !== '' ? 'has-mems' : '' }}">
                <td class="nowrap">{{ $d($r->DsendT) }}</td>
                <td class="nowrap">{{ $r->SendNo }}</td>
                <td>{{ trim((string) ($r->cust_name ?: $r->custname)) }}</td>
                <td>{{ $r->color }}</td>
                <td>{{ $r->STD }}</td>
                <td>{{ $r->Type_Work }}</td>
                <td>{{ $r->Model }}</td>
                <td>{{ $note }}</td>
                <td>{{ $r->TNname }}</td>
                <td class="nowrap">{{ $d($r->SampleDate) }}</td>
                <td>{{ $r->CodeNo }}</td>
                <td>{{ $r->lotno }}</td>
                <td>{{ $r->TestDesc }}<br>{{ $r->ResinMatch }}</td>
                <td class="num">{{ $r->Wage !== null && $r->Wage !== '' ? number_format((float) $r->Wage) : '' }}</td>
                <td>{{ $r->rptno }}</td>
            </tr>
            @if ($mems !== '')
                <tr class="mems">
                    <td colspan="2"></td>
                    <td colspan="13">{{ $mems }}</td>
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="15" class="ctr" style="padding: 16px;">ไม่พบใบนำส่งตัวอย่างเทียบสีในช่วงวันที่ที่เลือก</td>
            </tr>
        @endforelse
    </tbody>
</table>
</body>
</html>
