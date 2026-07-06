@php
    $qno   = trim($header->Qno);
    $tname = ($cust && $cust->name) ? $cust->name : $header->CustName;
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

    $subject  = $isRevision ? 'ขอแจ้งปรับราคา' : 'ขอเสนอราคา';
    $bodyText = $isRevision
        ? 'เนื่องจากขณะนี้ราคาของวัตถุดิบมีการปรับตัวสูงขึ้น บริษัทฯ มีความจำเป็นอย่างยิ่งที่จะต้องขอปรับราคา ' . $ptname . ' ดังต่อไปนี้'
        : 'บริษัทฯ ขอเสนอราคา ' . $ptname . ' ดังรายละเอียดต่อไปนี้';
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

        /* ── ตารางรายการ (ใช้ร่วมทุกรูปแบบ) ── */
        table.items { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.items th, table.items td { border: 1px solid #999; padding: 4px 7px; vertical-align: top; }
        table.items thead th { background: #eef4f4; text-align: center; font-weight: bold; line-height: 1.25; }
        table.items thead th small { font-weight: normal; color: #333; font-size: .78em; }
        table.items td { text-align: left; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .code { font-family: ui-monospace, Consolas, monospace; }
        tfoot th { background: #eef4f4; }

        .footer-info { margin-top: 20px; }
        .footer-info .row { margin: 2px 0; }
        .sign { margin-top: 44px; display: flex; justify-content: flex-end; }
        .sign .box { text-align: center; width: 260px; }
        .sign .line { border-top: 1px dotted #000; margin-top: 42px; padding-top: 4px; }
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

    {{-- หัวเอกสาร --}}
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

    {{-- ตารางรายการ — คอลัมน์ตาม col_config (ผู้ใช้กำหนดเอง) --}}
    <table class="items">
        <thead>
            <tr>
                @foreach ($colConfig as $c)
                    <th class="{{ ($colRegistry[$c['key']]['num'] ?? false) ? 'text-end' : '' }}">{{ $c['label'] }}</th>
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

    {{-- ข้อมูลท้ายเอกสาร --}}
    <div class="footer-info">
        @if ($header->Term)     <div class="row">เงื่อนไขการชำระเงิน : {{ $header->Term }}</div> @endif
        @if ($header->Validto)  <div class="row">ยืนราคาถึงวันที่ : {{ $thaiDate($header->Validto) }}</div> @endif
        @if ($header->LeadTime) <div class="row">ส่งสินค้าได้ภายใน : {{ $header->LeadTime }} วัน</div> @endif
        @if ($header->Qremark && $header->Qremark !== '-') <div class="row">ควรกำหนดยอดซื้อขั้นต่ำ : {{ $header->Qremark }} ก.ก.</div> @endif
    </div>

    <div class="sign">
        <div class="box"><div class="line">ผู้เสนอราคา</div></div>
    </div>

</body>
</html>
