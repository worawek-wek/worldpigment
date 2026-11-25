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
        ? 'บริษัทฯ ขอเรียนแจ้งการปรับเปลี่ยนราคา ของผลิตภัณฑ์ตามรายละเอียดดังต่อไปนี้'
        : 'บริษัทฯ มีความยินดีขอเสนอราคาผลิตภัณฑ์ตามรายละเอียดดังต่อไปนี้';

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
        /* 16/11/2569 ตามที่ผู้ใช้สั่ง: ฟอนต์ TH Sarabun New ทั้งเอกสาร (ฟอนต์แห่งชาติของ SIPA — แจกฟรี)
           ไฟล์เก็บในเครื่อง public/assets/vendor/fonts/thsarabunnew/ — ไม่โหลดจาก CDN
           ใช้ชื่อ family 'WP TH Sarabun New' เพื่อไม่ให้ไปจับฟอนต์ที่ลงไว้ในเครื่องผู้ใช้ (ถ้ามี) แทนไฟล์ของระบบ
           (ระหว่างทางเคยลอง Liberation Sans Narrow และ Sarabun ของ Google — ไฟล์ยังอยู่ใน fonts/ แต่ไม่ได้ใช้แล้ว) */
        @font-face {
            font-family: 'WP TH Sarabun New';
            src: url('{{ asset('assets/vendor/fonts/thsarabunnew/THSarabunNew-Regular.ttf') }}') format('truetype');
            font-weight: normal; font-style: normal;
        }
        @font-face {
            font-family: 'WP TH Sarabun New';
            src: url('{{ asset('assets/vendor/fonts/thsarabunnew/THSarabunNew-Bold.ttf') }}') format('truetype');
            font-weight: bold; font-style: normal;
        }
        @font-face {
            font-family: 'WP TH Sarabun New';
            src: url('{{ asset('assets/vendor/fonts/thsarabunnew/THSarabunNew-Italic.ttf') }}') format('truetype');
            font-weight: normal; font-style: italic;
        }
        @font-face {
            font-family: 'WP TH Sarabun New';
            src: url('{{ asset('assets/vendor/fonts/thsarabunnew/THSarabunNew-BoldItalic.ttf') }}') format('truetype');
            font-weight: bold; font-style: italic;
        }
        body {
            /* ฟอนต์เดียวทั้งเอกสาร (10/11/2569 ตามที่ผู้ใช้สั่ง "ใช้ font เดียวกัน อย่างเป็นทางการ")
               16/11/2569: เปลี่ยนเป็น TH Sarabun New (ดู @font-face ด้านบน) · ตัวดูเล็กกว่าฟอนต์อื่นที่ขนาดเท่ากัน จึงตั้ง 18px */
            font-family: 'WP TH Sarabun New', 'Tahoma', sans-serif;
            font-size: 18px; color: #000; margin: 0; padding: 28px 40px; line-height: 1.2; /* 18/11/2569 ตามที่ผู้ใช้สั่ง: ลดระยะบรรทัด (เดิม 1.5 — TH Sarabun New เผื่อพื้นที่บน/ล่างในตัวฟอนต์อยู่แล้ว) */
        }
        .toolbar { text-align: center; margin-bottom: 18px; }
        .toolbar button { padding: 8px 22px; font-size: 15px; cursor: pointer; }

        /* หัวกระดาษ — รูปกว้างเต็มหน้า */
        .letterhead { margin-bottom: 18px; }
        /* ขนาด 90% ของความกว้างหน้า (ย่อลง 10% ตามที่ผู้ใช้สั่ง 16/10/2569) จัดกึ่งกลาง */
        /* 26/10/2569: ย่อหน้าเนื้อหาด้านล่างให้เท่าขอบรูป — .page เว้นซ้าย/ขวา 5% แล้วรูปกว้าง 100% ของ .page
           (= 90% ของหน้าเท่าเดิม) เนื้อหาทุกส่วนจึงตรงแนวขอบรูป */
        /* 13/11/2569 ตามที่ผู้ใช้สั่ง "พิมพ์ออกมาแล้วระยะขอบเป็น 0.5 นิ้ว" — เอาระยะเว้นซ้าย/ขวา 5% ออก
           ให้ขอบที่เห็นจริง = ขอบกระดาษ @page 0.5in ทั้ง 4 ด้าน (หัวกระดาษกว้างเต็มพื้นที่พิมพ์) */
        .page { padding: 0; }
        .letterhead img { display: block; width: 100%; height: auto; margin: 0 auto; }
        /* ไม่พิมพ์หัวกระดาษ — ซ่อนรูปแต่คงพื้นที่ไว้ (visibility ไม่ใช่ display) */
        .letterhead.blank img { visibility: hidden; }
        .doc-head { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 16px; }
        /* 17/11/2569 ตามที่ผู้ใช้สั่ง: หัวเอกสาร (เลขที่/วันที่) + บรรทัดลูกค้า/เรียน ใหญ่ขึ้น 1 ขนาด (body 18px → 20px) */
        .doc-head, .subject { font-size: 20px; }
        /* 18/11/2569 ตามที่ผู้ใช้สั่ง: หมายเหตุใต้ตาราง (จำนวนส่งมอบขั้นต่ำ/เทอมการชำระเงิน ฯลฯ) + ชื่อ/ตำแหน่งใต้ลายเซ็น ใหญ่ขึ้น 1 ขนาดด้วย */
        .footer-info, .sign { font-size: 20px; }
        /* 19/11/2569 ตามที่ผู้ใช้สั่ง: ย่อหน้าเนื้อความ (บริษัทฯ มีความยินดี…) ขนาดเท่าหัวเอกสาร/ลูกค้า/หมายเหตุ/ลายเซ็น */
        .body-text { font-size: 20px; }
        /* ใบที่มีตรา REVISED — เว้นที่ใต้หัวเอกสารให้ตรา (ลอยใต้วันที่ สูง ~60px) ไม่ทับบรรทัดลูกค้า (19/10/2569) */
        /* 20/11/2569 ตามที่ผู้ใช้สั่ง: ตรา REVISED ขยับลงมาอยู่แถวเดียวกับบรรทัดลูกค้า → ไม่ต้องเว้นที่ 60px แล้ว (เดิม .doc-head.has-stamp { margin-bottom: 60px; }) */
        .doc-head .qno { font-weight: bold; }

        /* ── ตราประทับ REVISED (19/10/2569) — ลอยใต้วันที่มุมขวาบน ไม่ดันเนื้อหาส่วนอื่น ── */
        .doc-head .date-col { position: relative; }
        .revised-stamp {
            position: absolute; right: 0; top: 100%; margin-top: 14px; /* 20/11/2569: เดิม 10px — 14px ≈ ระยะใต้หัวเอกสาร (16px) ให้กรอบ REVISED ตรงแถวลูกค้า */
            text-align: center;
            white-space: nowrap; pointer-events: none;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .revised-stamp .word {
            display: inline-block; padding: 0 8px;
            border: 2px solid #d32f2f; border-radius: 5px;
            color: #d32f2f; font-family: inherit; /* เดิม Arial Black — ใช้ฟอนต์เดียวกับเอกสาร (10/11/2569) */
            font-size: 20px; font-weight: 900; letter-spacing: 1.5px; line-height: 1.15; /* ย่อจาก 30px (19/10/2569) */
            opacity: .85;
        }
        .revised-stamp .date {
            margin-top: 2px; color: #1f3fa8; font-size: 15px; font-weight: bold; /* ย่อจาก 22px */
            letter-spacing: 2px; opacity: .85;
        }
        .field-label { display: inline-block; }
        /* 16/11/2569 ตามที่ผู้ใช้สั่ง: ป้าย "เรียน" กว้างเท่าป้าย "ลูกค้า:" พอดี (ซ้อนคำ "ลูกค้า:" แบบมองไม่เห็นไว้ใต้คำ "เรียน")
           ข้อความหลังป้ายจึงเริ่มตรงแนวเดียวกัน โดยระยะห่างเท่าเดิม (&nbsp;&nbsp;) */
        .to-label { display: inline-grid; }
        .to-label > span { grid-area: 1 / 1; }
        .to-label .to-ghost { visibility: hidden; }
        .subject { margin: 2px 0; }
        .body-text { text-indent: 40px; margin: 14px 0 10px; }

        /* ── ตารางรายการ (ไม่มีเส้นตาราง; หัวคอลัมน์ขีดเส้นใต้อย่างเดียว) ── */
        /* table-layout: fixed = ใช้ความกว้างจาก <colgroup> ตรง ๆ ไม่ยืดตามข้อความ (10/11/2569) */
        table.items { width: 100%; border-collapse: collapse; margin-top: 6px; table-layout: fixed; }
        /* ข้อความยาว (รายละเอียด 30 ตัวขึ้นไป / รหัสยาว) ตัดขึ้นบรรทัดใหม่ในช่อง ไม่ล้นไปช่องข้าง ๆ */
        table.items td { overflow-wrap: anywhere; word-break: break-word; }
        table.items th, table.items td { padding: 4px 7px; vertical-align: top; }
        table.items thead th { text-align: center; /* 16/11/2569 ตามที่ผู้ใช้สั่ง: หัวคอลัมน์จัดกึ่งกลาง (เดิม left) */ font-weight: bold; line-height: 1.1; /* 25/11/2569 ตามที่ผู้ใช้สั่ง: ลดระยะบรรทัด (เดิม 1.25) */ }
        table.items thead th { border-top: 1px solid #000; border-bottom: 1px solid #000; } /* 19/11/2569 ตามที่ผู้ใช้สั่ง: หัวตารางมีเส้นบน-ล่าง */
        /* 20/11/2569 หัวตาราง 2 ชั้น: ไม่มีเส้นคั่นระหว่างหัวกลุ่มกับแถว ปัจจุบัน|ใหม่ (เส้นใต้หัวกลุ่มเอาออกตามที่ผู้ใช้สั่ง)
           · หัวที่กินสูง 2 ชั้นจัดกึ่งกลางแนวตั้ง */
        table.items thead th.th-group { border-bottom: none; padding-bottom: 0; }
        table.items thead th.th-sub { border-top: none; padding-top: 0; }
        table.items thead th[rowspan] { vertical-align: middle; }
        table.items thead th small { font-weight: normal; color: #333; font-size: .78em; }
        table.items tbody td { font-weight: bold; } /* 18/11/2569 ตามที่ผู้ใช้สั่ง: ข้อมูลในตารางเป็นตัวหนา */
        table.items td { text-align: center; } /* 16/11/2569 ตามที่ผู้ใช้สั่ง: ข้อมูลในตารางจัดกึ่งกลาง (เดิม left) */
        .text-end { text-align: right; }
        /* ต้อง qualify ด้วย table.items ไม่งั้นกฎ text-align:left ของ th/td ข้างบนชนะ (specificity สูงกว่า)
           — ช่องยอดเงิน/น้ำหนักจึงเคยชิดซ้าย (แก้ 19/10/2569) */
        table.items td.text-end { text-align: center; } /* 16/11/2569 ตามที่ผู้ใช้สั่ง: ทั้ง th และ td กึ่งกลางทุกช่อง (เดิมช่องตัวเลขชิดขวา) */
        .text-center { text-align: center; }
        /* 18/11/2569 ตามที่ผู้ใช้สั่ง: คอลัมน์ สินค้า (code) + รายละเอียด (name) ชิดซ้ายทั้ง th และ td */
        table.items th.col-left, table.items td.col-left { text-align: left; }
        /* รหัสสินค้าใช้ฟอนต์เดียวกับทั้งเอกสาร (เดิม monospace — 10/11/2569) */
        .code { font-family: inherit; }
        tfoot th { border-top: 1px solid #000; }

        /* ส่วนท้าย (หมายเหตุ + ลายเซ็น) — ระยะห่างจากตารางอย่างน้อย 20px (เดิมเป็น margin-top ของ .footer-info) */
        .page-bottom { padding-top: 20px; }
        .footer-info { margin-top: 1.5em; } /* 16/11/2569 ตามที่ผู้ใช้สั่ง: เว้น 1 บรรทัดใต้ตาราง (เดิม 0 อยู่ใน .page-bottom ที่ชิดล่าง) */
        .footer-info .row { margin: 2px 0; }
        /* หมายเหตุอื่น — ป้ายซ้าย + รายการเรียงลงมาในคอลัมน์ขวา */
        .footer-info table.other-notes { border-collapse: collapse; font: inherit; }
        .footer-info table.other-notes td { padding: 0; vertical-align: top; font: inherit; }
        .footer-info table.other-notes td.on-label { white-space: nowrap; padding-right: 4px; }
        .footer-info table.other-notes td div { margin-bottom: 2px; }
        .sign { margin-top: 44px; display: flex; justify-content: flex-end; }
        .sign { margin-bottom: 1.2em; } /* 19/11/2569 ตามที่ผู้ใช้สั่ง: ลายเซ็นขยับขึ้นจากขอบล่าง 1 บรรทัด (1.2em = ระยะบรรทัด) */
        /* กล่องเซ็นชื่อกว้างขึ้นให้รับกับรูปลายเซ็นขนาดใหญ่ (เส้นเซ็นชื่อยาวขึ้นตาม) */
        /* 13/11/2569 ตามที่ผู้ใช้สั่ง "ขยับขวา" — กล่อง 420 → 300px (ชิดขวาอยู่แล้ว กล่องแคบลง = กึ่งกลางเลื่อนไปขวา 60px) */
        .sign .box { text-align: center; width: 300px; }
        /* ช่องว่างเหนือเส้นเซ็นชื่อ — ใส่รูปลายเซ็นของพนักงานคนที่เสนอราคา (ถ้ามี)
           สูง 84px = 2 เท่าของระยะเว้นเดิม (42px) เพื่อให้ลายเซ็นเห็นชัด */
        .sign .sign-space { height: 84px; display: flex; align-items: flex-end; justify-content: center; }
        .sign .sign-space img { max-height: 84px; max-width: 300px; object-fit: contain; }
        /* เส้นประเซ็นชื่อ — สั้นครึ่งหนึ่งของกล่อง (210px) จัดกึ่งกลางใต้รูปลายเซ็น
           แยกความกว้างออกจากกล่อง เพื่อให้รูปลายเซ็นยังใหญ่เต็ม 420px ได้ */
        .sign .line { border-top: 1px dotted #000; padding-top: 4px; width: 210px; margin: 0 auto; white-space: nowrap; }
        .sign .emp-name { font-weight: bold; }
        .sign .emp-dept { white-space: nowrap; }
        .sign .closing { white-space: nowrap; }
        .fmt-tag { font-size: .75em; color: #888; }

        /* 17/11/2569 ตามที่ผู้ใช้สั่ง "ระยะขอบซ้าย ขวา บน ล่าง ต้อง 0.5 นิ้ว" — ตอนพิมพ์ body ไม่มี padding (เดิม 10px 24px บวกเพิ่มจากขอบ @page)
           ขอบที่เห็นจริงจึง = @page margin 0.5in ทั้ง 4 ด้าน · รูปหัวกระดาษกว้าง 100% = ชนขอบ 0.5in ซ้าย/ขวาพอดี */
        @media print { .toolbar { display: none; } body { padding: 0; } }

        /* ── ส่วนท้ายชิดล่างของหน้ากระดาษ (19/10/2569 ตามที่ผู้ใช้สั่ง) ──
           .page สูงเท่าพื้นที่พิมพ์ของ A4 (297mm − ขอบ 10mm×2 − padding ของ body) แล้วดัน .page-bottom ลงล่างด้วย margin-top:auto
           ตั้งต่ำกว่าพื้นที่จริงเล็กน้อย กันปัดเศษแล้วล้นไปหน้า 2 · ใบที่ยาวเกิน 1 หน้า ส่วนท้ายจะต่อท้ายตารางตามปกติ */
        /* 13/11/2569 ตามที่ผู้ใช้สั่ง — ขอบ 0.5 นิ้วทั้ง 4 ด้าน */
        @page { size: A4; margin: 0.5in 0.5in 0.8in 0.8in; } /* 17/11/2569 ขอบซ้าย 0.8 นิ้ว · 19/11/2569 ตามที่ผู้ใช้สั่ง: ขอบล่าง 0.8 นิ้ว (บน/ขวา 0.5) */
        @media print {
            .page { display: flex; flex-direction: column; min-height: 262mm; }/* 19/11/2569: ขอบล่าง 0.8in → พื้นที่พิมพ์สูง 297 − 33.02 = 264mm (เผื่อ 2mm กันล้นหน้า 2) · เดิม: พื้นที่พิมพ์สูง 297 − 25.4 = 271.6mm · 17/11/2569: body ไม่มี padding แล้ว 264 → 270 (เผื่อ 1.6mm กันล้นหน้า 2) ลายเซ็นจึงชิดขอบล่าง 0.5in */
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
        <div class="subject"><span class="field-label to-label"><span>เรียน</span><span class="to-ghost" aria-hidden="true">ลูกค้า:</span></span> &nbsp;&nbsp; ผู้จัดการฝ่ายจัดซื้อ</div>

        <div class="body-text">{{ $bodyText }}</div>
    @endif

    {{-- ตารางรายการ — คอลัมน์ตาม col_config (ผู้ใช้กำหนดเอง) --}}
    {{-- ความกว้างคอลัมน์ — ดู $colFixed ด้านล่าง (25/11/2569: รายละเอียดยืดตามจำนวนคอลัมน์) --}}
    @php
        // 20/11/2569 ตามที่ผู้ใช้สั่ง: หัวตาราง 2 ชั้น — คอลัมน์คู่ "X ปัจจุบัน" + "X ใหม่" ที่อยู่ติดกัน
        // (EN: "Current X" + "New X") → แถวบน X คลุม 2 ช่อง · แถวล่าง ปัจจุบัน | ใหม่ (Current | New)
        // คอลัมน์อื่นในแถวบน rowspan=2 · ไม่มีคู่ = หัวแถวเดียวแบบเดิม
        $splitPair = function ($c) use ($headEn) {
            $t = trim($headEn ? ($c['label_en'] ?? $c['label']) : $c['label']);
            if ($headEn) {
                return preg_match('/^(Current|New)\s+(.+)$/u', $t, $m) ? [$m[2], $m[1]] : null;
            }
            return preg_match('/^(.*\S)\s+\(?(ปัจจุบัน|ใหม่)\)?$/u', $t, $m) ? [$m[1], $m[2]] : null;
        };
        // $grpStart: index → [ชื่อกลุ่ม (escape แล้ว), จำนวนช่องที่คลุม, มีเส้นใต้?] · $grpSub: index → ป้ายแถวล่าง (HTML)
        $grpStart = []; $grpSub = [];
        $brkUnitHead = fn ($s) => $headEn ? e($s) : preg_replace('/\s*(\()/u', '<br>$1', e($s), 1);
        for ($i = 0; $i < count($colConfig) - 1; $i++) {
            $a = $splitPair($colConfig[$i]); $b = $splitPair($colConfig[$i + 1]);
            if ($a && $b && $a[0] === $b[0] && in_array($a[1], ['ปัจจุบัน', 'Current'], true) && in_array($b[1], ['ใหม่', 'New'], true)) {
                // 25/11/2569 ตามที่ผู้ใช้สั่ง (Form 2.1): ราคา ปัจจุบัน → ราคา ใหม่ → รวม Vat ติดกัน
                // ⇒ แถวบน "ปัจจุบัน" (1 ช่อง) | "ใหม่" (คลุม ราคา + รวม Vat) มีเส้นใต้ · แถวล่าง ราคา | ราคา | รวม Vat (บาท/กก)
                $v = $colConfig[$i + 2] ?? null;
                if ($colConfig[$i]['key'] === 'price_kg' && $colConfig[$i + 1]['key'] === 'new_price' && ($v['key'] ?? null) === 'price_vat') {
                    $vl = trim($headEn ? ($v['label_en'] ?? $v['label']) : $v['label']);
                    $vl = $headEn ? preg_replace('/^New\s+/u', '', $vl) : preg_replace('/\s+\(?ใหม่\)?$/u', '', $vl);
                    if (!str_contains($vl, '(')) {
                        $vl .= $headEn ? ' (THB/kg)' : ' (บาท/กก)';
                    }
                    $grpStart[$i]     = [e($a[1]), 1, true];
                    $grpStart[$i + 1] = [e($b[1]), 2, true];
                    $grpSub[$i] = $brkUnitHead($a[0]); $grpSub[$i + 1] = $brkUnitHead($b[0]); $grpSub[$i + 2] = $brkUnitHead($vl);
                    $i += 2;
                    continue;
                }
                $grpStart[$i] = [$brkUnitHead($a[0]), 2, false];
                $grpSub[$i] = e($a[1]); $grpSub[$i + 1] = e($b[1]);
                $i++;
            }
        }
        $hasGrp = !empty($grpStart);

        // 25/11/2569 ตามที่ผู้ใช้สั่ง: ช่องอื่นกว้างคงที่ (% ของหน้า) · ช่องรายละเอียด (name) ยืดรับพื้นที่ที่เหลือ
        // ⇒ คอลัมน์น้อย รายละเอียดกว้างขึ้นเอง · ช่องในหัวกลุ่ม ปัจจุบัน|ใหม่ แคบกว่าปกติ (10) ให้ดูเป็นกลุ่มเดียวกัน
        $colFixed = ['code' => 11, 'delivery_qty' => 12, 'remark' => 14];
        $colW = array_map(fn ($c) => $colFixed[$c['key']] ?? 11, $colConfig);
        foreach (array_keys($grpSub) as $gi) {
            $colW[$gi] = 10;
        }
        $nameIdx = array_search('name', array_column($colConfig, 'key'), true);
        if ($nameIdx !== false) {
            $colW[$nameIdx] = 0;
            // เหลือไม่ถึง 20% (คอลัมน์เยอะมาก) → รายละเอียดได้ 20 แล้วย่อทุกช่องตามสัดส่วนให้รวมเป็น 100%
            $colW[$nameIdx] = max(20, 100 - array_sum($colW));
        }
        $colWSum = array_sum($colW) ?: 1;
    @endphp
    <table class="items">
        <colgroup>
            @foreach ($colW as $w)
                <col style="width: {{ round($w * 100 / $colWSum, 2) }}%">
            @endforeach
        </colgroup>
        <thead>
            <tr>
                @foreach ($colConfig as $ci => $c)
                    @if (isset($grpStart[$ci]))
                        {{-- หัวกลุ่ม — ภาษาไทยหน่วยในวงเล็บขึ้นบรรทัดใหม่เหมือนหัวอื่น · ไม่มีเส้นใต้ (25/11/2569 ตามที่ผู้ใช้สั่ง) --}}
                        <th @if($grpStart[$ci][1] > 1) colspan="{{ $grpStart[$ci][1] }}" @endif class="th-group">{!! $grpStart[$ci][0] !!}</th>
                        @continue
                    @endif
                    @if (isset($grpSub[$ci]))
                        @continue
                    @endif
                    @php
                        $head = $headEn ? ($c['label_en'] ?? $c['label']) : $c['label'];
                        // การขึ้นบรรทัดของหัวคอลัมน์ภาษาไทย — 16/11/2569 ตามแบบที่ผู้ใช้ส่งมา (แทนกติกาเดิม 26/10/2569)
                        //  - คำว่า ปัจจุบัน/ใหม่ ที่ต่อท้ายแบบมีเว้นวรรค → ขึ้นเป็นบรรทัดแรก (เช่น ปัจจุบัน / ราคา / (บาท/กก))
                        //  - หน่วยในวงเล็บ (บาท/กก) → บรรทัดใหม่
                        //  - บางชื่อกำหนดจุดตัดเอง ($thBreaks) · ภาษาอังกฤษไม่แตะ · escape ก่อนแล้วค่อยแทรก <br>
                        $headHtml = e($head);
                        if (!$headEn) {
                            $thBreaks = [
                                // "ต่อครั้ง (กก)" อยู่บรรทัดเดียวกันเสมอ — nowrap (19/11/2569 ตามที่ผู้ใช้สั่ง)
                                'น้ำหนักต่อครั้ง (กก)' => 'น้ำหนัก<br><span style="white-space: nowrap">ต่อครั้ง (กก)</span>',
                                'ราคาเม็ดพลาสติก'     => 'ราคาเม็ด<br>พลาสติก',
                                'ค่าผลิตและค่าแม่สี'    => 'ค่าผลิต<br>และค่าแม่สี',
                            ];
                            $t = trim($head);
                            $brkUnit = fn ($s) => preg_replace('/\s*(\()/u', '<br>$1', e($s), 1);
                            if (isset($thBreaks[$t])) {
                                $headHtml = $thBreaks[$t];
                            } elseif (preg_match('/^(.*\S)\s+\(?(ปัจจุบัน|ใหม่)\)?$/u', $t, $m)) {
                                $headHtml = e($m[2]) . '<br>' . $brkUnit($m[1]);
                            } else {
                                $headHtml = $brkUnit($t);
                            }
                        }
                    @endphp
                    {{-- น้ำหนักส่งครั้งละ (delivery_qty) เก็บเป็นข้อความ แต่ให้ชิดขวาเหมือนช่องตัวเลข (19/10/2569) --}}
                    <th @if($hasGrp) rowspan="2" @endif class="{{ (($colRegistry[$c['key']]['num'] ?? false) || $c['key'] === 'delivery_qty') ? 'text-end' : '' }} {{ in_array($c['key'], ['code', 'name'], true) ? 'col-left' : '' }}">{!! $headHtml !!}</th>{{-- เอาขีดเส้นใต้ (<u>) ออก 13/11/2569 ตามที่ผู้ใช้สั่ง --}}
                @endforeach
            </tr>
            @if ($hasGrp)
                {{-- แถวล่างของหัวกลุ่ม: ปัจจุบัน | ใหม่ (20/11/2569) --}}
                <tr>
                    @foreach ($grpSub as $sub)
                        <th class="th-sub">{!! $sub !!}</th>
                    @endforeach
                </tr>
            @endif
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
                        <td class="{{ ($num || $k === 'delivery_qty') ? 'text-end' : '' }} {{ $k === 'code' ? 'code' : '' }} {{ in_array($k, ['code', 'name'], true) ? 'col-left' : '' }}">{{ $disp }}</td>
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

    {{-- 16/11/2569 ตามที่ผู้ใช้สั่ง: หมายเหตุ (เทอมการชำระเงิน ฯลฯ) อยู่ใต้ตารางเว้น 1 บรรทัด — เหลือเฉพาะลายเซ็นที่ชิดล่างของหน้า --}}
    <div class="page-bottom">
    <div class="sign">
        <div class="box">
            {{-- คำลงท้ายเหนือลายเซ็น (29/10/2569 ตามที่ผู้ใช้สั่ง) --}}
            {{-- 13/11/2569 ตามที่ผู้ใช้สั่ง: เอาคำลงท้ายภาษาไทย "ขอแสดงความนับถืออย่างสูง" ออก (ฉบับอังกฤษยังมี Sincerely Yours,) --}}
            {{-- 20/11/2569 ตามที่ผู้ใช้สั่ง: ตัด "Sincerely Yours," ของฉบับอังกฤษออกด้วย (เปิดคืน: @if ($isEn)) --}}
            @if (false)
                <div class="closing">Sincerely Yours,</div>
            @endif
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
