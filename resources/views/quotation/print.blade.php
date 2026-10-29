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

    // ── รูปหัวกระดาษ (16/10/2569) — ตามช่อง "หัวกระดาษ" ของใบ (qmast.letterhead) ไฟล์อยู่ที่ public/letter-head ──
    $letterheadFiles = [
        'WH'  => 'WH QUOTATION.png',
        'WPC' => 'WPC QUOTATION.png',
        'WPI' => 'WPI QUOTATION.png',
    ];
    $lhFile = $letterheadFiles[strtoupper(trim($header->letterhead ?? ''))] ?? null;
    $letterheadUrl = $lhFile ? asset('letter-head/' . rawurlencode($lhFile)) : null;
    // ผู้ใช้เลือก "ไม่พิมพ์หัวกระดาษ" ตอนกดพิมพ์ → ส่ง lh=0 มา (query ของ /print หรือ POST ของ /print-preview)
    //  → ไม่แสดงรูป แต่ "เว้นที่ว่าง" สูงเท่าหัวกระดาษไว้ ให้เอากระดาษที่มีหัวพิมพ์แล้วมาใส่เอง
    $letterheadBlank = request('lh') === '0';

    // ── ตราประทับ "REVISED" + วันที่ (19/10/2569) — ขึ้นเองเมื่อใบมี Revise Date (qmast.Revisedate) ──
    //    ไทย: 21 ก.ย. 2569 · อังกฤษ: 21 Sep 2026 · วันที่ของใบ (Qdate) ยังเป็นวันเสนอราคาเดิม
    $revisedStamp = null;
    if (!empty($header->Revisedate)) {
        $rv = \Carbon\Carbon::parse($header->Revisedate);
        $thShort = [1=>'ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        $revisedStamp = $headEn
            ? $rv->day . ' ' . $rv->format('M') . ' ' . $rv->year
            : $rv->day . ' ' . $thShort[$rv->month] . ' ' . ($rv->year + 543);
    }

    $subject  = $isRevision ? 'ขอแจ้งปรับราคา' : 'ขอเสนอราคา';
    // ข้อความเนื้อจดหมาย — ตามแบบที่ลูกค้ากำหนด (12/09/2569)
    $bodyText = $isRevision
        ? 'บริษัทฯ ขอเรียนแจ้งการปรับเปลี่ยนราคา ของผลิตภัณฑ์ตามรายละเอียดด้านล่างนี้'
        : 'บริษัทฯ มีความยินดีขอเสนอราคาผลิตภัณฑ์ตามรายละเอียดด้านล่างนี้';

    // ── ข้อความหัวบิลภาษาอังกฤษ ──
    $subjectEn  = $isRevision ? 'Price Revision' : 'Quotation';
    $bodyTextEn = $isRevision
        ? 'Please find below our revised offers for the following products.'
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

        /* หัวกระดาษ — รูปกว้างเต็มหน้า */
        .letterhead { margin-bottom: 18px; }
        /* ขนาด 90% ของความกว้างหน้า (ย่อลง 10% ตามที่ผู้ใช้สั่ง 16/10/2569) จัดกึ่งกลาง */
        /* 26/10/2569: ย่อหน้าเนื้อหาด้านล่างให้เท่าขอบรูป — .page เว้นซ้าย/ขวา 5% แล้วรูปกว้าง 100% ของ .page
           (= 90% ของหน้าเท่าเดิม) เนื้อหาทุกส่วนจึงตรงแนวขอบรูป */
        .page { padding: 0 5%; }
        .letterhead img { display: block; width: 100%; height: auto; margin: 0 auto; }
        /* ไม่พิมพ์หัวกระดาษ — ซ่อนรูปแต่คงพื้นที่ไว้ (visibility ไม่ใช่ display) */
        .letterhead.blank img { visibility: hidden; }
        .doc-head { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 16px; }
        /* ใบที่มีตรา REVISED — เว้นที่ใต้หัวเอกสารให้ตรา (ลอยใต้วันที่ สูง ~60px) ไม่ทับบรรทัดลูกค้า (19/10/2569) */
        .doc-head.has-stamp { margin-bottom: 60px; }
        .doc-head .qno { font-weight: bold; }

        /* ── ตราประทับ REVISED (19/10/2569) — ลอยใต้วันที่มุมขวาบน ไม่ดันเนื้อหาส่วนอื่น ── */
        .doc-head .date-col { position: relative; }
        .revised-stamp {
            position: absolute; right: 0; top: 100%; margin-top: 10px;
            text-align: center;
            white-space: nowrap; pointer-events: none;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .revised-stamp .word {
            display: inline-block; padding: 0 8px;
            border: 2px solid #d32f2f; border-radius: 5px;
            color: #d32f2f; font-family: 'Arial Black', 'Arial', sans-serif;
            font-size: 20px; font-weight: 900; letter-spacing: 1.5px; line-height: 1.15; /* ย่อจาก 30px (19/10/2569) */
            opacity: .85;
        }
        .revised-stamp .date {
            margin-top: 2px; color: #1f3fa8; font-size: 15px; font-weight: bold; /* ย่อจาก 22px */
            letter-spacing: 2px; opacity: .85;
        }
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
        /* ต้อง qualify ด้วย table.items ไม่งั้นกฎ text-align:left ของ th/td ข้างบนชนะ (specificity สูงกว่า)
           — ช่องยอดเงิน/น้ำหนักจึงเคยชิดซ้าย (แก้ 19/10/2569) */
        table.items th.text-end, table.items td.text-end { text-align: right; }
        .text-center { text-align: center; }
        .code { font-family: ui-monospace, Consolas, monospace; }
        tfoot th { border-top: 1px solid #000; }

        /* ส่วนท้าย (หมายเหตุ + ลายเซ็น) — ระยะห่างจากตารางอย่างน้อย 20px (เดิมเป็น margin-top ของ .footer-info) */
        .page-bottom { padding-top: 20px; }
        .footer-info { margin-top: 0; }
        .footer-info .row { margin: 2px 0; }
        /* หมายเหตุอื่น — ป้ายซ้าย + รายการเรียงลงมาในคอลัมน์ขวา */
        .footer-info table.other-notes { border-collapse: collapse; font: inherit; }
        .footer-info table.other-notes td { padding: 0; vertical-align: top; font: inherit; }
        .footer-info table.other-notes td.on-label { white-space: nowrap; padding-right: 4px; }
        .footer-info table.other-notes td div { margin-bottom: 2px; }
        .sign { margin-top: 44px; display: flex; justify-content: flex-end; }
        /* กล่องเซ็นชื่อกว้างขึ้นให้รับกับรูปลายเซ็นขนาดใหญ่ (เส้นเซ็นชื่อยาวขึ้นตาม) */
        .sign .box { text-align: center; width: 420px; }
        /* ช่องว่างเหนือเส้นเซ็นชื่อ — ใส่รูปลายเซ็นของพนักงานคนที่เสนอราคา (ถ้ามี)
           สูง 84px = 2 เท่าของระยะเว้นเดิม (42px) เพื่อให้ลายเซ็นเห็นชัด */
        .sign .sign-space { height: 84px; display: flex; align-items: flex-end; justify-content: center; }
        .sign .sign-space img { max-height: 84px; max-width: 420px; object-fit: contain; }
        /* เส้นประเซ็นชื่อ — สั้นครึ่งหนึ่งของกล่อง (210px) จัดกึ่งกลางใต้รูปลายเซ็น
           แยกความกว้างออกจากกล่อง เพื่อให้รูปลายเซ็นยังใหญ่เต็ม 420px ได้ */
        .sign .line { border-top: 1px dotted #000; padding-top: 4px; width: 210px; margin: 0 auto; white-space: nowrap; }
        .sign .emp-name { font-weight: bold; }
        .sign .emp-dept { white-space: nowrap; }
        .sign .closing { white-space: nowrap; }
        .fmt-tag { font-size: .75em; color: #888; }

        @media print { .toolbar { display: none; } body { padding: 10px 24px; } }

        /* ── ส่วนท้ายชิดล่างของหน้ากระดาษ (19/10/2569 ตามที่ผู้ใช้สั่ง) ──
           .page สูงเท่าพื้นที่พิมพ์ของ A4 (297mm − ขอบ 10mm×2 − padding ของ body) แล้วดัน .page-bottom ลงล่างด้วย margin-top:auto
           ตั้งต่ำกว่าพื้นที่จริงเล็กน้อย กันปัดเศษแล้วล้นไปหน้า 2 · ใบที่ยาวเกิน 1 หน้า ส่วนท้ายจะต่อท้ายตารางตามปกติ */
        @page { size: A4; margin: 10mm; }
        @media print {
            .page { display: flex; flex-direction: column; min-height: 270mm; }
            .page-bottom { margin-top: auto; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <button onclick="window.print()">🖨 พิมพ์</button>
        <button onclick="window.close()">ปิด</button>
        <span class="fmt-tag">{{ $isRevision ? 'แจ้งปรับราคา' : 'เสนอราคา' }}</span>
    </div>

    {{-- .page = ตัวคุมให้ส่วนท้ายชิดล่างของหน้า (ดู CSS @media print) --}}
    <div class="page">
    {{-- หัวกระดาษ (16/10/2569) --}}
    @if ($letterheadUrl)
        <div class="letterhead{{ $letterheadBlank ? ' blank' : '' }}" id="letterhead"><img src="{{ $letterheadUrl }}" alt="หัวกระดาษ {{ $header->letterhead }}"></div>
    @endif

    {{-- หัวเอกสาร (สลับ TH/EN ตามภาษาที่เลือก) --}}
    @if ($headEn)
        <div class="doc-head{{ $revisedStamp ? ' has-stamp' : '' }}">
            <div><span class="field-label">QUOTATION NO.</span> &nbsp;&nbsp; <span class="qno">{{ $qno }}</span></div>
            <div class="date-col">
                {{ $enDate($header->Qdate) }}
                @include('quotation.partials.revised-stamp')
            </div>
        </div>

        {{-- บรรทัด "Re: Quotation / Price Revision" — เอาออกทั้งบรรทัด 16/10/2569 ตามที่ผู้ใช้สั่ง
        <div class="subject"><span class="field-label">Re:</span> &nbsp;&nbsp; {{ $subjectEn }}</div>
        --}}
        <div class="subject">
            <span class="field-label">Customer:</span> &nbsp;&nbsp;
            {{ $tnameEn ?: '—' }} @if($header->Custid) ({{ $header->Custid }}) @endif
        </div>
        <div class="subject">Dear Purchasing Manager,</div>

        <div class="body-text">{{ $bodyTextEn }}</div>
    @else
        <div class="doc-head{{ $revisedStamp ? ' has-stamp' : '' }}">
            <div><span class="field-label">ใบเสนอราคาเลขที่:</span> &nbsp;&nbsp; <span class="qno">{{ $qno }}</span></div>
            <div class="date-col">
                วันที่ {{ $thaiDate($header->Qdate) }}
                @include('quotation.partials.revised-stamp')
            </div>
        </div>

        {{-- บรรทัด "เรื่อง" — เอาออก 19/10/2569 ตามที่ผู้ใช้สั่ง
        <div class="subject"><span class="field-label">เรื่อง</span> &nbsp;&nbsp; {{ $subject }} &nbsp; {{ $ptname }}</div>
        --}}
        {{-- ชื่อลูกค้า + รหัสลูกค้า แยกบรรทัดจาก "เรียน" (12/09/2569) · ป้ายเดิม "ATTN:" → "ลูกค้า:" (19/10/2569) --}}
        <div class="subject">
            <span class="field-label">ลูกค้า:</span> &nbsp;&nbsp;
            {{ $tname ?: '—' }} @if($header->Custid) ({{ $header->Custid }}) @endif
        </div>
        <div class="subject"><span class="field-label">เรียน</span> &nbsp;&nbsp; ผู้จัดการฝ่ายจัดซื้อ</div>

        <div class="body-text">{{ $bodyText }}</div>
    @endif

    {{-- ตารางรายการ — คอลัมน์ตาม col_config (ผู้ใช้กำหนดเอง) --}}
    <table class="items">
        <thead>
            <tr>
                @foreach ($colConfig as $c)
                    @php
                        $head = $headEn ? ($c['label_en'] ?? $c['label']) : $c['label'];
                        // คำว่า "ปัจจุบัน" / "ใหม่" (รวมวงเล็บ) ขึ้นบรรทัดใหม่ — 26/10/2569 · escape ก่อนแล้วค่อยแทรก <br>
                        $headHtml = preg_replace('/\s*(\(?(?:ปัจจุบัน|ใหม่)\)?)/u', '<br>$1', e($head), 1);
                    @endphp
                    {{-- น้ำหนักส่งครั้งละ (delivery_qty) เก็บเป็นข้อความ แต่ให้ชิดขวาเหมือนช่องตัวเลข (19/10/2569) --}}
                    <th class="{{ (($colRegistry[$c['key']]['num'] ?? false) || $c['key'] === 'delivery_qty') ? 'text-end' : '' }}"><u>{!! $headHtml !!}</u></th>
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
                            // น้ำหนักส่งครั้งละ เก็บเป็นข้อความ (อาจเป็นช่วง เช่น 1000-2000) → ใส่คอมมาให้ตัวเลข 4 หลักขึ้นไป
                            // (ข้ามตัวเลขที่ใส่คอมมา/จุดทศนิยมมาแล้ว) — 16/10/2569
                            if ($k === 'delivery_qty' && $disp !== null && $disp !== '') {
                                $disp = preg_replace_callback('/(?<![\d,.])\d{4,}/', fn ($m) => number_format((int) $m[0]), (string) $disp);
                            }
                        @endphp
                        <td class="{{ ($num || $k === 'delivery_qty') ? 'text-end' : '' }} {{ $k === 'code' ? 'code' : '' }}">{{ $disp }}</td>
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
        // วันที่ในหมายเหตุ: ภาษาอังกฤษ = UK English Long Date (21 July 2026) · ภาษาไทย = d/m/Y ตามเดิม
        $shortDate = $isEn
            ? $enDate
            : fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '-';
    @endphp
    <div class="page-bottom">
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
        {{-- หมายเหตุอื่น: ป้าย "หมายเหตุ :" ครั้งเดียว แล้วต่อด้วยรายการที่พิมพ์ — รายการถัดไปเรียงลงมาตรงแนวเดียวกัน (27/10/2569 ตามที่ผู้ใช้สั่ง) --}}
        @if (count($otherNotes))
            <table class="row other-notes">
                <tr>
                    <td class="on-label">{{ $isEn ? 'Remark' : 'หมายเหตุ' }} :</td>
                    <td>
                        @foreach ($otherNotes as $note)
                            <div>{{ $note }}</div>
                        @endforeach
                    </td>
                </tr>
            </table>
        @endif
    </div>

    <div class="sign">
        <div class="box">
            {{-- คำลงท้ายเหนือลายเซ็น (29/10/2569 ตามที่ผู้ใช้สั่ง) --}}
            <div class="closing">{{ $isEn ? 'Sincerely Yours,' : 'ขอแสดงความนับถืออย่างสูง' }}</div>
            {{-- รูปลายเซ็นของพนักงานผู้เสนอราคา (emp.signature) — ไม่มีก็เว้นช่องไว้ให้เซ็นมือ --}}
            <div class="sign-space">
                @if (!empty($empSign))
                    <img src="{{ $empSign }}" alt="ลายเซ็น {{ $empName }}">
                @endif
            </div>
            {{-- ฉบับภาษาอังกฤษ: ชื่ออังกฤษ (emp.sname_e) + ตำแหน่ง (emp.spost) — 29/10/2569
                 ไม่ได้กรอกไว้ = ใช้ชื่อไทย / คำอธิบายแผนกแทน --}}
            @php
                $signName = $isEn ? (($empNameEn ?? null) ?: $empName) : $empName;
                // ใต้ชื่อ = ตำแหน่ง (emp.spost) ทั้งไทย/อังกฤษ (29/10/2569 ตามที่ผู้ใช้สั่ง) · ไม่ได้กรอก = คำอธิบายแผนก
                $signDept = ($empPost ?? null) ?: $empDept;
            @endphp
            <div class="line"><span class="emp-name">{{ $signName }}</span></div>
            {{-- คำอธิบายแผนกของผู้เสนอราคา (tb_departments.description) — 19/10/2569 --}}
            @if (!empty($signDept))
                <div class="emp-dept">{{ $signDept }}</div>
            @endif
        </div>
    </div>
    </div>{{-- /.page-bottom --}}
    </div>{{-- /.page --}}

</body>
</html>
