@php
    // ── ภาษาเอกสารทั้งใบ (หัวบิล + หัวตาราง) ตามสวิตช์ TH/EN เดิม ──
    $headEn = ($header->remark_lang ?? 'th') === 'en';

    $qno    = trim($header->Qno);
    $tname  = ($cust && $cust->name) ? $cust->name : $header->CustName;
    // ชื่อลูกค้าอังกฤษ (nameEN) — ถ้าไม่มีใช้ชื่อไทยแทน
    $tnameEn = ($cust && ($cust->nameEN ?? null)) ? $cust->nameEN : $tname;
    $ptname = $pdtype->PDHead1 ?? $header->PDtype;

    // $isRevision มาจาก controller (derive จากคอลัมน์ที่ใช้จริง)
    // วันที่แบบไทย
    $thaiMonths = [1=>'มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                   'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
    $thaiDate = function ($d) use ($thaiMonths) {
        if (!$d) return '-';
        $c = \Carbon\Carbon::parse($d);
        return $c->day . ' ' . $thaiMonths[$c->month] . ' ' . ($c->year + 543);
    };
    // วันที่แบบอังกฤษ (UK English Long Date) — เช่น 21 July 2026
    $enMonths = [1=>'January','February','March','April','May','June',
                 'July','August','September','October','November','December'];
    $enDate = function ($d) use ($enMonths) {
        if (!$d) return '-';
        $c = \Carbon\Carbon::parse($d);
        return $c->day . ' ' . $enMonths[$c->month] . ' ' . $c->year;
    };

    $subject  = $isRevision ? 'ขอแจ้งปรับราคา' : 'ขอเสนอราคา';
    $bodyText = $isRevision
        ? 'เนื่องจากขณะนี้ราคาของวัตถุดิบมีการปรับตัวสูงขึ้น บริษัทฯ มีความจำเป็นอย่างยิ่งที่จะต้องขอปรับราคา ' . $ptname . ' ดังต่อไปนี้'
        : 'บริษัทฯ ขอเสนอราคา ' . $ptname . ' ดังรายละเอียดต่อไปนี้';

    // ── ข้อความหัวบิลภาษาอังกฤษ ──
    $subjectEn  = $isRevision ? 'Price Revision' : 'Quotation';
    $bodyTextEn = $isRevision
        ? 'Due to the current rise in raw material prices, we regret to inform you that it is necessary to adjust the price of ' . $ptname . ' as follows:'
        : 'We are pleased to submit our offer for the following products.';
@endphp
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>ใบเสนอราคา {{ $qno }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            /* Arial Narrow ไม่มี glyph ภาษาไทย — ตัวไทยจะ fallback ไป Tahoma ให้อัตโนมัติ */
            font-family: 'Arial Narrow', 'Tahoma', sans-serif;
            font-size: 17px; color: #000; margin: 0; padding: 28px 40px; line-height: 1.5;
        }
        .toolbar { text-align: center; margin-bottom: 18px; }
        .toolbar button { padding: 8px 22px; font-size: 15px; cursor: pointer; }

        .doc-head { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 16px; }
        .doc-head .qno { font-weight: bold; }
        .field-label { display: inline-block; }
        .subject { margin: 2px 0; }
        .body-text { text-indent: 40px; margin: 14px 0 10px; }

        /* ── ตารางรายการ (ไม่มีเส้นตาราง; หัวคอลัมน์ขีดเส้นใต้อย่างเดียว) ── */
        table.items { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.items th, table.items td { padding: 4px 7px; vertical-align: top; }
        table.items thead th { text-align: left; font-weight: bold; line-height: 1.25; }
        table.items thead th small { font-weight: normal; color: #333; font-size: .78em; }
        table.items td { text-align: left; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .code { font-family: ui-monospace, Consolas, monospace; }
        tfoot th { border-top: 1px solid #000; }

        .footer-info { margin-top: 20px; }
        .footer-info .row { margin: 2px 0; }
        .sign { margin-top: 44px; display: flex; justify-content: flex-end; }
        .sign .box { text-align: center; width: 260px; }
        .sign .line { border-top: 1px dotted #000; margin-top: 42px; padding-top: 4px; }
        .sign .emp-name { font-weight: bold; }
        .fmt-tag { font-size: .75em; color: #888; }

        @media print { .toolbar { display: none; } body { padding: 10px 24px; } }
    </style>
</head>
<body>

    <div class="toolbar">
        <button onclick="window.print()">🖨 พิมพ์</button>
        <button onclick="window.close()">ปิด</button>
        <span class="fmt-tag">{{ $isRevision ? 'แจ้งปรับราคา' : 'เสนอราคา' }}</span>
    </div>

    {{-- หัวเอกสาร (สลับ TH/EN ตามภาษาที่เลือก) --}}
    @if ($headEn)
        <div class="doc-head">
            <div><span class="field-label">Quotation No.</span> &nbsp;&nbsp; <span class="qno">{{ $qno }}</span></div>
            <div>Date {{ $enDate($header->Qdate) }}</div>
        </div>

        <div class="subject"><span class="field-label">Re:</span> &nbsp;&nbsp; {{ $subjectEn }} &nbsp; {{ $ptname }}</div>
        <div class="subject">
            <span class="field-label">ATTN:</span> &nbsp;&nbsp;
            {{ $tnameEn ?: '—' }} @if($header->Custid) ({{ $header->Custid }}) @endif
        </div>
        <div class="subject">Dear Purchasing Manager,</div>

        <div class="body-text">{{ $bodyTextEn }}</div>
    @else
        <div class="doc-head">
            <div><span class="field-label">ใบเสนอราคาเลขที่</span> &nbsp;&nbsp; <span class="qno">{{ $qno }}</span></div>
            <div>วันที่ {{ $thaiDate($header->Qdate) }}</div>
        </div>

        <div class="subject"><span class="field-label">เรื่อง</span> &nbsp;&nbsp; {{ $subject }} &nbsp; {{ $ptname }}</div>
        <div class="subject">
            <span class="field-label">เรียน</span> &nbsp;&nbsp; ท่านผู้จัดการฝ่ายจัดซื้อ &nbsp;
            {{ $tname ?: '—' }} @if($header->Custid) ({{ $header->Custid }}) @endif
        </div>

        <div class="body-text">{{ $bodyText }}</div>
    @endif

    {{-- ตารางรายการ — คอลัมน์ตาม col_config (ผู้ใช้กำหนดเอง) --}}
    <table class="items">
        <thead>
            <tr>
                @foreach ($colConfig as $c)
                    @php $head = $headEn ? ($c['label_en'] ?? $c['label']) : $c['label']; @endphp
                    <th class="{{ ($colRegistry[$c['key']]['num'] ?? false) ? 'text-end' : '' }}"><u>{{ $head }}</u></th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $it)
                <tr>
                    @foreach ($colConfig as $c)
                        @php
                            $k = $c['key']; $reg = $colRegistry[$k] ?? [];
                            $num = $reg['num'] ?? false;
                            $v = $it->cells[$k] ?? null;
                            $disp = $num
                                ? (($v !== null && $v !== '') ? number_format((float) $v, 2) . ($reg['suffix'] ?? '') : '')
                                : $v;
                        @endphp
                        <td class="{{ $num ? 'text-end' : '' }} {{ $k === 'code' ? 'code' : '' }}">{{ $disp }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($colConfig) }}" class="text-center">ไม่มีรายการ</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── หมายเหตุ (2 ภาษา — label ตาม remark_lang; ช่องว่างไม่โชว์หัวข้อ) ── --}}
    @php
        $isEn = ($header->remark_lang ?? 'th') === 'en';
        $L = $isEn
            ? ['resin'=>'Resin Price', 'valid'=>'Price validity from', 'to'=>'to',
               'minqty'=>'Minimum Quantity', 'unit'=>'kg', 'place'=>'Delivery Location',
               'dterm'=>'Price Term', 'pay'=>'Term of Payment']
            : ['resin'=>'ราคาเม็ดพลาสติก', 'valid'=>'ราคานี้มีผลวันที่', 'to'=>'ถึง',
               'minqty'=>'จำนวนส่งมอบขั้นต่ำ', 'unit'=>'กก.', 'place'=>'สถานที่ส่งสินค้า',
               'dterm'=>'เทอมการส่งมอบสินค้า', 'pay'=>'เทอมการชำระเงิน'];
        $shortDate = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '-';
    @endphp
    <div class="footer-info">
        @if ($header->resin_price_note)
            <div class="row">{{ $L['resin'] }} : {{ $header->resin_price_note }}</div>
        @endif
        @if ($header->ValidFrom || $header->Validto)
            <div class="row">{{ $L['valid'] }} : {{ $shortDate($header->ValidFrom) }} {{ $L['to'] }} {{ $shortDate($header->Validto) }}</div>
        @endif
        @if ($header->Qremark && $header->Qremark !== '-')
            <div class="row">{{ $L['minqty'] }} : {{ $header->Qremark }} {{ $L['unit'] }}</div>
        @endif
        @if ($header->delivery_place)
            <div class="row">{{ $L['place'] }} : {{ $header->delivery_place }}</div>
        @endif
        @if ($header->delivery_term)
            <div class="row">{{ $L['dterm'] }} : {{ $header->delivery_term }}</div>
        @endif
        @if ($header->Term)
            <div class="row">{{ $L['pay'] }} : {{ $header->Term }}</div>
        @endif
        @foreach ($otherNotes as $note)
            <div class="row">{{ $note }}</div>
        @endforeach
    </div>

    <div class="sign">
        <div class="box">
            <div class="line"><span class="emp-name">{{ $empName }}</span></div>
        </div>
    </div>

</body>
</html>
