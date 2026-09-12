{{--
    ประวัติการขออนุมัติราคาของเบอร์หนึ่ง (ปุ่ม "ประวัติของเบอร์นี้" → พิมพ์ PDF) — 12/09/2569
    ผังตามรายงานกระดาษของระบบเดิม: เรียงจากใบล่าสุด → เก่าสุด
    ข้อมูลจาก appvreq ของคู่ (ลูกค้า, เบอร์) ที่เลือกอยู่ในฟอร์ม (ดู PriceApprovalController::historyPdf)
    ปุ่ม "ตรวจสอบ เบอร์อื่น ..." ใช้ blade ตัวเดียวกันนี้ แต่ดึงทุกลูกค้าของเบอร์ที่กรอก (otherItemsPdf)
--}}
@php
    use Illuminate\Support\Carbon;

    $fmtNum = function ($v, $dec = 2) {
        if ($v === null || $v === '') return '';
        return number_format((float) $v, $dec, '.', ',');
    };
    // รายงานเดิมใช้ปีแบบ 2 หลัก (เช่น 07/09/26)
    $fmtDate = function ($d) {
        if (!$d) return '';
        try { return Carbon::parse($d)->format('d/m/y'); } catch (\Exception $e) { return ''; }
    };
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        /* ขนาดฟอนต์ลดลงทั้งชุด 12/09/2569 (ตามที่ผู้ใช้สั่ง) — มีผลกับทั้ง 2 ปุ่มที่ใช้ blade นี้ */
        body { font-size: 10px; color: #000; }

        .head { width: 100%; margin-bottom: 6px; }
        .head td { border: none; padding: 0; vertical-align: bottom; }
        .doc-title { font-size: 14px; font-weight: bold; }
        .doc-itemno { font-size: 14px; font-weight: bold; }
        .order-note { font-size: 10px; text-align: right; }

        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { padding: 4px 5px; font-size: 9px; vertical-align: top; }
        /* รายงานเดิมมีเส้นคาดใต้หัวตารางเส้นเดียว ไม่ได้ตีกรอบทุกช่อง */
        table.data thead th { border-bottom: 2px solid #000; text-align: center; font-weight: bold; }
        table.data tbody td { border-bottom: 1px solid #ddd; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .price-req { font-size: 11px; font-weight: bold; }
        .empty { text-align: center; padding: 14px; }
    </style>
</head>
<body>

    <table class="head">
        <tr>
            <td style="width: 42%;">
                <span class="doc-title">ประวัติการขออนุมัติราคาของเบอร์ ...</span>
            </td>
            <td style="width: 28%;" class="text-center">
                <span class="doc-itemno">{{ $itemno }}</span>
            </td>
            <td style="width: 30%;" class="order-note">เรียงจากปัจจุบัน ==&gt; อดีต</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 8%;">วันที่ขอ</th>
                <th style="width: 8%;">รหัสลูกค้า</th>
                <th style="width: 11%;">รหัสสินค้า</th>
                <th style="width: 10%;">จน.สั่งซื้อ (Kg.)</th>
                <th style="width: 10%;">ราคาที่ขอ</th>
                <th style="width: 7%;">price1</th>
                <th style="width: 7%;">price2</th>
                <th style="width: 7%;">price3</th>
                <th style="width: 26%;">หมายเหตุการปรับราคา</th>
                <th style="width: 6%;">อนุมัติ</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td class="text-center">{{ $fmtDate($r->ReqDate) }}</td>
                    <td class="text-center">{{ $r->custno }}</td>
                    <td>{{ $r->itemno }}</td>
                    <td class="text-center">{{ $fmtNum($r->weight, 0) }}</td>
                    <td class="text-center price-req">{{ $fmtNum($r->price) }}</td>
                    <td class="text-center">{{ $fmtNum($r->price1, 0) }}</td>
                    <td class="text-center">{{ $fmtNum($r->price2, 0) }}</td>
                    <td class="text-center">{{ $fmtNum($r->price3, 0) }}</td>
                    <td>{{ $r->remark }}</td>
                    {{-- Access เก็บ -1 = ติ๊ก (แปลงมาเป็น bool แล้วฝั่ง controller) --}}
                    <td class="text-center">{!! $r->Appv ? '&#10004;' : '' !!}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="empty">ไม่พบประวัติการขออนุมัติราคาของเบอร์นี้</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
