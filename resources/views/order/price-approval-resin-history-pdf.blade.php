{{--
    ประวัติราคาเม็ด (ปุ่ม "ประวัติ ราคาเม็ด CP" → PDF) — 12/09/2569
    ผังตามรายงานกระดาษของระบบเดิม: 1 ระเบียน = 1 บล็อก 3 คอลัมน์แบบ "ป้าย: ค่า"

    ข้อมูลจาก cp_itemprice ของเบอร์นั้น **ทุกลูกค้า** (ตารางนี้ผูกกับเลขที่ใบสั่ง ไม่มีคอลัมน์ลูกค้า)
    ส่วน Mdate / Custno มาจาก morder ที่ join ด้วยเลขที่ใบสั่ง — ใบเก่าที่ไม่มีใน morder จะเว้นว่าง
    (ดู PriceApprovalController::resinHistoryPdf)
--}}
@php
    use Illuminate\Support\Carbon;

    // ตัวเลขในรายงานเดิมไม่ตรึงทศนิยม (6.7 / 59.5 / 0 / 66.27) — ตัดศูนย์ท้ายทิ้ง
    $fmtNum = function ($v) {
        if ($v === null || $v === '') return '';
        $s = number_format((float) $v, 2, '.', ',');
        return strpos($s, '.') === false ? $s : rtrim(rtrim($s, '0'), '.');
    };
    $fmtDate = function ($d) {
        if (!$d) return '';
        try { return Carbon::parse($d)->format('d/m/y'); } catch (\Exception $e) { return ''; }
    };
    $fmtDateTime = function ($d) {
        if (!$d) return '';
        try { return Carbon::parse($d)->format('d/m/y g:i:s A'); } catch (\Exception $e) { return ''; }
    };
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 12px; color: #000; }

        .head { width: 100%; margin-bottom: 4px; }
        .head td { border: none; padding: 0; vertical-align: bottom; }
        .doc-title { font-size: 20px; font-weight: bold; }
        .head-note { font-size: 12px; text-align: right; }
        .rule { border-bottom: 2px solid #000; margin-bottom: 6px; }

        /* 1 ระเบียน = 1 บล็อก มีเส้นคั่นด้านล่าง */
        table.rec { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        table.rec td { border: none; padding: 2px 4px; font-size: 12px; vertical-align: top; }
        .lbl { color: #000; }
        .val { font-weight: normal; }
        .sep { border-bottom: 1px solid #999; margin-bottom: 8px; }
        .empty { text-align: center; padding: 14px; }
    </style>
</head>
<body>

    <table class="head">
        <tr>
            <td style="width: 40%;"><span class="doc-title">ประวัติราคาเม็ด</span></td>
            <td style="width: 60%;" class="head-note">
                เรียงจากปัจจุบัน ==&gt; อดีต &nbsp;&nbsp; บรรทัดแรก เป็นการขอราคาครั้งนี้
            </td>
        </tr>
    </table>
    <div class="rule"></div>

    @forelse ($rows as $r)
        <table class="rec">
            <tr>
                <td class="lbl" style="width: 9%;">Mdate:</td>
                <td class="val" style="width: 19%;">{{ $fmtDateTime($r->Mdate) }}</td>
                <td class="lbl" style="width: 11%;">Orderno:</td>
                <td class="val" style="width: 16%;">{{ $r->Orderno }}</td>
                <td class="lbl" style="width: 12%;">Custno:</td>
                <td class="val" style="width: 33%;">{{ $r->Custno }}</td>
            </tr>
            <tr>
                <td class="lbl">itemno:</td>
                <td class="val">{{ $r->itemno }}</td>
                <td class="lbl">OrderPrice:</td>
                <td class="val">{{ $fmtNum($r->OrderPrice) }}</td>
                <td class="lbl">Qdate:</td>
                <td class="val">{{ $fmtDate($r->Qdate) }}</td>
            </tr>
            <tr>
                <td class="lbl">wage:</td>
                <td class="val">{{ $fmtNum($r->wage) }}</td>
                <td class="lbl">ResinFrom:</td>
                <td class="val">{{ $r->ResinFrom }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="lbl">Resin1Code:</td>
                <td class="val">{{ $r->Resin1Code }}</td>
                <td class="lbl">Resin1Price:</td>
                <td class="val">{{ $fmtNum($r->Resin1Price) }}</td>
                <td class="lbl">Resin1Per:</td>
                <td class="val">{{ $r->Resin1Per }}</td>
            </tr>
            <tr>
                <td class="lbl">Resin2Code:</td>
                <td class="val">{{ $r->Resin2Code }}</td>
                <td class="lbl">Resin2Price:</td>
                <td class="val">{{ $fmtNum($r->Resin2Price) }}</td>
                <td class="lbl">Resin2Per:</td>
                <td class="val">{{ $r->Resin2Per }}</td>
            </tr>
            <tr>
                <td class="lbl">wageCal:</td>
                <td class="val">{{ $fmtNum($r->wageCal) }}</td>
                <td class="lbl">Diff:</td>
                <td class="val">{{ $fmtNum($r->Diff) }}</td>
                <td class="lbl">status:</td>
                <td class="val">{{ $r->status }}</td>
            </tr>
        </table>
        <div class="sep"></div>
    @empty
        <div class="empty">ไม่พบประวัติราคาเม็ดของเบอร์ {{ $itemno }}</div>
    @endforelse

</body>
</html>
