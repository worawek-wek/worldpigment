@php
    $qno   = trim($header->Qno);
    $tname = ($cust && $cust->name) ? $cust->name : $header->CustName;
    // ชื่อชนิดสินค้าภาษาไทย (pdtype.PDHead1 เป็น UTF-8 ปกติ) → fallback code
    $ptname = $pdtype->PDHead1 ?? $header->PDtype;

    // วันที่แบบไทย: "22 เมษายน 2569"
    $thaiMonths = [1=>'มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                   'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
    $thaiDate = function ($d) use ($thaiMonths) {
        if (!$d) return '-';
        $c = \Carbon\Carbon::parse($d);
        return $c->day . ' ' . $thaiMonths[$c->month] . ' ' . ($c->year + 543);
    };
    $price = fn ($v) => $v !== null ? number_format($v, 2) : '';
@endphp
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>ใบเสนอราคา {{ $qno }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'TH Sarabun New', 'Sarabun', 'Angsana New', 'Cordia New', 'Tahoma', sans-serif;
            font-size: 17px; color: #000; margin: 0; padding: 28px 40px; line-height: 1.5;
        }
        .toolbar { text-align: center; margin-bottom: 18px; }
        .toolbar button { padding: 8px 22px; font-size: 15px; cursor: pointer; }

        .doc-head { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 16px; }
        .doc-head .qno { font-weight: bold; }
        .field-label { display: inline-block; }
        .subject { margin: 2px 0; }
        .body-text { text-indent: 40px; margin: 14px 0 10px; }

        table.items { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.items thead th {
            border-bottom: 1px solid #000; padding: 4px 6px; text-align: left; font-weight: bold;
        }
        table.items td { padding: 3px 6px; vertical-align: top; }
        .text-end { text-align: right; }
        .col-price { width: 110px; }
        .col-code  { width: 130px; }

        .footer-info { margin-top: 22px; }
        .footer-info .row { margin: 2px 0; }
        .sign { margin-top: 46px; display: flex; justify-content: flex-end; }
        .sign .box { text-align: center; width: 260px; }
        .sign .line { border-top: 1px dotted #000; margin-top: 42px; padding-top: 4px; }

        @media print { .toolbar { display: none; } body { padding: 10px 24px; } }
    </style>
</head>
<body>

    <div class="toolbar">
        <button onclick="window.print()">🖨 พิมพ์</button>
        <button onclick="window.close()">ปิด</button>
    </div>

    {{-- หัวเอกสาร: เลขที่ (ซ้าย) + วันที่ (ขวา) --}}
    <div class="doc-head">
        <div><span class="field-label">ใบเสนอราคาเลขที่</span> &nbsp;&nbsp; <span class="qno">{{ $qno }}</span></div>
        <div>วันที่ {{ $thaiDate($header->Qdate) }}</div>
    </div>

    {{-- เรื่อง / เรียน --}}
    <div class="subject"><span class="field-label">เรื่อง</span> &nbsp;&nbsp; ขอแจ้งปรับราคา &nbsp; {{ $ptname }}</div>
    <div class="subject">
        <span class="field-label">เรียน</span> &nbsp;&nbsp; ท่านผู้จัดการฝ่ายจัดซื้อ &nbsp;
        {{ $tname ?: '—' }} @if($header->Custid) ({{ $header->Custid }}) @endif
    </div>

    {{-- ย่อหน้าเนื้อความ --}}
    <div class="body-text">
        เนื่องจากขณะนี้ราคาของวัตถุดิบมีการปรับตัวสูงขึ้น บริษัทฯ มีความจำเป็นอย่างยิ่งที่จะต้อง
        ขอปรับราคา {{ $ptname }} ดังต่อไปนี้
    </div>

    {{-- ตารางรายการ --}}
    <table class="items">
        <thead>
            <tr>
                <th class="col-code">รหัสสินค้า</th>
                <th>ชื่อสินค้า</th>
                <th class="col-price text-end">ราคาเก่า</th>
                <th class="col-price text-end">ราคาใหม่</th>
                <th class="col-price text-end">ราคาใหม่รวมภาษี</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $it)
                <tr>
                    <td>{{ $it->Qitemno }}</td>
                    <td>{{ $it->Qdesc }}</td>
                    <td class="text-end">{{ $price($it->oldprice) }}</td>
                    <td class="text-end">{{ $price($it->QPrice) }}</td>
                    <td class="text-end">{{ $price($it->QNet) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">ไม่มีรายการ</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ข้อมูลท้ายเอกสาร --}}
    <div class="footer-info">
        @if ($header->Term)
            <div class="row">เงื่อนไขการชำระเงิน : {{ $header->Term }}</div>
        @endif
        @if ($header->Validto)
            <div class="row">ยืนราคาถึงวันที่ : {{ $thaiDate($header->Validto) }}</div>
        @endif
        @if ($header->LeadTime)
            <div class="row">ส่งสินค้าได้ภายใน : {{ $header->LeadTime }} วัน</div>
        @endif
        @if ($header->Qremark && $header->Qremark !== '-')
            <div class="row">ควรกำหนดยอดซื้อขั้นต่ำ : {{ $header->Qremark }} ก.ก.</div>
        @endif
    </div>

    <div class="sign">
        <div class="box">
            <div class="line">ผู้เสนอราคา</div>
        </div>
    </div>

    {{-- การพิมพ์สั่งจากหน้าหลักผ่าน iframe (quotationPrint) — ไม่ auto-print กันพิมพ์ซ้ำ --}}
</body>
</html>
