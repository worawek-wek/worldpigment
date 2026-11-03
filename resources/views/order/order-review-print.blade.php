{{-- ═══════════════════════════════════════════════════════════════
     ใบทบทวนคำสั่งซื้อ (03/11/2569) — พิมพ์ทับกระดาษแบบฟอร์ม "ใบทบทวนคำสั่งซื้อ" MK01-FM01.00
     หน้า A4 แนวตั้ง — ฟอร์ม (210 × 148 มม.) อยู่ครึ่งบนของแผ่น · ไม่พิมพ์เส้น/หัวฟอร์ม พิมพ์เฉพาะข้อมูลลงตำแหน่งของแต่ละช่อง
     OrderReviewController::print()

     ⚠ ตำแหน่ง (มม. จากมุมซ้ายบนของกระดาษ) ประเมินจากรูปถ่ายแบบฟอร์ม — ต้องลองพิมพ์จริงแล้วปรับ
       ปรับทั้งแผ่น: แก้ $dx / $dy · ปรับทีละช่อง: แก้ค่าใน $F
       สั่งพิมพ์จาก PDF ต้องตั้ง Scale 100% / Actual size (ห้าม Fit to page)
     ═══════════════════════════════════════════════════════════════ --}}
@php
    $dx = 0;   // เลื่อนทั้งแผ่น ซ้าย/ขวา (มม.)
    $dy = 0;   // เลื่อนทั้งแผ่น ขึ้น/ลง (มม.)

    $tick = '✔';
    // [left, top, width, ข้อความ, class เพิ่ม]
    $F = [
        // ── หัวใบ ──
        [168,  25, 38, $d['orderno'], 'b big'],
        [ 22,  36,  9, $d['day'], 'c'],
        [ 33,  36,  9, $d['month'], 'c'],
        [ 46,  36,  9, $d['year'], 'c'],
        [ 58,  36, 70, 'ระบุในบิล : ' . $d['in_bill'], ''],
        [150,  36, 55, $d['itype'], ''],

        // ── ชื่อลูกค้า + ตามคำสั่งลูกค้าเพื่อ ──
        [ 29,  45, 68, $d['seller'], 'u'],
        [ 98,  45, 32, 'ตามคำสั่งลูกค้าเพื่อ', ''],
        [130,  45, 76, trim($d['for_code'] . '  ' . $d['for_name']), 'nw'],

        // ── ตารางรายการ (บรรทัดแรก) ──
        [ 11,  65, 26, $d['itemno'], 'c'],
        [ 37,  65,  6, $d['nold'], 'c'],
        [ 43,  65, 31, $d['prodname'], 'c'],
        [ 74,  65, 16, $d['lotno'], 'c'],
        [ 90,  65, 17, $d['qty'], 'r'],
        [107,  65, 15, $d['stock'], 'c'],
        [122,  65, 15, $d['prod'], 'c'],
        [137,  65, 18, $d['price'], 'r b'],
        [156,  65, 44, $d['po'] !== '' ? 'PO. ' . $d['po'] : '', ''],
        // ── ตารางรายการ (บรรทัดย่อย) ──
        [ 11,  71, 31, $d['term'], 'sm'],
        [ 43,  81, 47, $d['dvpoint'] !== '' ? 'ส่ง : ' . $d['dvpoint'] : '', 'sm'],
        [156,  71, 44, $d['remark'], 'sm wrap'],

        // ── กรณีสั่งทำ STOCK ──
        [ 55,  93, 21, $d['hmstore'], 'c'],
        [112,  93, 24, $d['sendmth'], 'c'],
        [163,  93, 36, $d['sendend'], 'c'],

        // ── ส่งก่อนได้ / RP / SPEC / TECHNICAL CER ──
        [ 17,  99,  6, $d['send'] ? $tick : '', 'tick'],
        [ 69,  99,  6, $d['rp']   ? $tick : '', 'tick'],
        [104,  99,  6, $d['spec'] ? $tick : '', 'tick'],
        [134,  99,  6, $d['cer']  ? $tick : '', 'tick'],

        // ── กำหนดส่ง / ผู้รับคำสั่ง / ผู้อนุมัติ ──
        [ 13, 106, 32, $d['senddate'], 'c'],
        [ 65, 106, 37, $d['taker'], 'c'],
        [109, 106, 19, $d['take_time'], 'c'],
        [147, 106, 34, trim($d['approver'] . ' ตรวจสอบแล้ว'), 'c'],
        [182, 106, 18, $d['appv_time'], 'c'],
        // ทับคำว่า "(ผู้จัดการฝ่ายการตลาด)" ด้วยเครื่องหมาย /
        [140, 112, 62, 'หนง.บริการลูกค้า ' . str_repeat('/', 27), 'nw'],

        // ── ขอรับรองรายการ... ลงชื่อ ──
        [ 11, 122, 60, $d['seller'], 'u'],
        [156, 122, 42, $d['sign'], 'c'],
    ];
@endphp
<html>
<head>
<style>
    body { font-family: sarabun; font-size: 12px; color: #000; margin: 0; }
    .f    { position: absolute; overflow: hidden; line-height: 1.2; }
    .c    { text-align: center; }
    .r    { text-align: right; }
    .b    { font-weight: bold; }
    .u    { text-decoration: underline; }
    .big  { font-size: 14px; }
    .sm   { font-size: 10.5px; }
    .tick { font-family: dejavusanscondensed; font-size: 15px; font-weight: bold; }
</style>
</head>
<body>
@foreach ($F as [$left, $top, $width, $text, $cls])
    @if ((string) $text !== '')
        <div class="f {{ $cls }}" style="left: {{ $left + $dx }}mm; top: {{ $top + $dy }}mm; width: {{ $width }}mm;">{{ $text }}</div>
    @endif
@endforeach
</body>
</html>
