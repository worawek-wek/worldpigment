{{--
    รายงานผลิตภัณฑ์ที่ต้องผลิต (P) — ใบสั่งซื้อที่อนุมัติวันนี้ เรียงตามเลขที่ใบสั่ง (06/10/2569)
    ปุ่ม "พิมพ์รายการที่อนุมัติวันนี้" บนหน้า /order → OrderApprovalController::approvedTodayPdf()
    ผังตามกระดาษของระบบเดิม (MK01-FM04.04): 1 รายการ = 1 บล็อก 3 บรรทัด
    1 แผนก (morder.Company) = 1 section ขึ้นหน้าใหม่ · ท้าย section: รวมน้ำหนัก P + ช่องเซ็น + รายการสินค้าที่สั่งซ้ำ

    ⚠ page-break ใส่ inline เฉพาะ section ที่ไม่ใช่อันแรก — ห้ามใช้ CSS :first-of-type (mPDF ไม่รองรับ)
    ⚠ เครื่องหมายถูกต้องบังคับฟอนต์ dejavusanscondensed — ฟอนต์ไทยของ mPDF ไม่มี glyph U+2714
    ⚠ กล่องสี่เหลี่ยมหลังรหัสลูกค้า (กระดาษเดิมมีตัว "A" บางราย) ยังไม่ทราบที่เก็บใน DB — วาดกล่องเปล่าไว้ก่อน
--}}
@php
    use Illuminate\Support\Carbon;

    $fmtNum = fn ($v) => ($v === null || $v === '') ? '' : number_format((float) $v, 2, '.', ',');
    $fmtD = function ($d) {
        if (!$d) return '';
        try { return Carbon::parse($d)->format('d/m/y'); } catch (\Exception $e) { return ''; }
    };
    $fmtT = function ($d) {
        if (!$d) return '';
        try { return Carbon::parse($d)->format('H:i'); } catch (\Exception $e) { return ''; }
    };
    // "ซื้อครั้งก่อน" แบบกระดาษเดิม = ปี พ.ศ. 2 หลัก + เดือน + วัน (เช่น 680715) · ไม่เคยสั่ง = NEW
    $fmtLast = function ($d) {
        if (!$d) return 'NEW';
        try {
            $c = Carbon::parse($d);
            return sprintf('%02d%02d%02d', ($c->year + 543) % 100, $c->month, $c->day);
        } catch (\Exception $e) { return ''; }
    };
    $chk = fn ($on) => '<table class="chk"><tr><td>' . ($on ? '<span class="chk-mark">&#10004;</span>' : '&nbsp;') . '</td></tr></table>';
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 11px; color: #000; }

        table.head { width: 100%; border-collapse: collapse; }
        table.head td { padding: 1px 3px; vertical-align: bottom; }
        .title { font-size: 14px; font-weight: bold; }
        .formno { font-size: 10px; text-align: right; }
        .sortby { font-size: 10px; font-weight: bold; color: #b22222; text-align: right; }

        table.list { width: 100%; border-collapse: collapse; }
        table.list th { font-size: 11px; font-weight: bold; text-align: left; padding: 2px 3px;
                        border-bottom: 2px solid #000; vertical-align: bottom; }
        table.list td { padding: 2px 3px; vertical-align: middle; }
        table.list tr.last td { border-bottom: 1px solid #555; padding-bottom: 5px; }

        .orderno { color: #2b4a9b; font-size: 12px; }
        .item { font-size: 12px; font-weight: bold; }
        .wt { font-size: 12px; font-weight: bold; text-align: right; }
        .blue { color: #1a3f9c; font-weight: bold; }
        .sm { font-size: 10px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }

        table.chk { width: 11px; border-collapse: collapse; }
        table.chk td { border: 1px solid #000; padding: 0; height: 11px; text-align: center;
                       font-size: 9px; line-height: 11px; }
        .chk-mark { font-family: dejavusanscondensed, sans-serif; font-size: 8px; }
        table.box { width: 20px; border-collapse: collapse; }
        table.box td { border: 1px solid #000; height: 18px; padding: 0; text-align: center; }

        table.sum { width: 100%; border-collapse: collapse; margin-top: 14px; }
        table.sum td { padding: 4px; vertical-align: bottom; }
        .total { font-size: 14px; font-weight: bold; text-decoration: underline; }
        .sign { font-size: 12px; font-weight: bold; text-align: right; }
        .line { border-bottom: 1px solid #000; }

        .rp-title { font-size: 13px; font-weight: bold; margin-top: 10px; }
        table.rp { border-collapse: collapse; margin-left: 40px; }
        table.rp td { padding: 3px 14px; font-size: 12px; }

        .empty { text-align: center; padding: 20px; font-size: 13px; }
    </style>
</head>
<body>

@forelse ($sections as $sec)
    <div @if (!$loop->first) style="page-break-before: always;" @endif>

        <table class="head">
            <tr>
                <td colspan="2"></td>
                <td class="formno">26-07-50 / MK01-FM04.04</td>
            </tr>
            <tr>
                <td class="title" style="width: 46%;">
                    รายงานผลิตภัณฑ์ที่ต้องผลิต (P) แผนกผลิต &nbsp;{{ $sec->dept !== '' ? $sec->dept : 'ไม่ระบุ' }}
                </td>
                <td>อนุมัติวันที่ {{ $day->format('d/m/y') }}</td>
                <td class="sortby">เรียงตาม Order No.</td>
            </tr>
        </table>

        <table class="list">
            <thead>
                <tr>
                    <th style="width: 3%;"></th>
                    <th style="width: 13%;">เลขที่ใบทบทวนฯ</th>
                    <th style="width: 8%;">เวลารับ</th>
                    <th style="width: 12%;">รหัสสินค้า</th>
                    <th style="width: 9%;">Lot</th>
                    <th style="width: 8%;" class="text-end">นน. P</th>
                    <th style="width: 5%;" class="text-center sm">Sale</th>
                    <th style="width: 6%;">รหัส</th>
                    <th style="width: 4%;"></th>
                    <th>ตามคำสั่งลูกค้าเพื่อ</th>
                    <th style="width: 8%;" class="text-end sm">ซื้อครั้งก่อน</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sec->items as $i => $r)
                    <tr>
                        <td class="text-end">{{ $i + 1 }}</td>
                        <td class="orderno">{{ $r->Orderno }}</td>
                        <td>{{ $fmtT($r->Mdate) }}</td>
                        <td class="item">{{ $r->Itemno }}</td>
                        <td>{{ $r->Lotno }}</td>
                        <td class="wt">{{ $fmtNum($r->Production) }}</td>
                        <td class="text-center sm">{{ $r->supno }}</td>
                        <td class="sm">{{ $r->Custno }}</td>
                        <td><table class="box"><tr><td>&nbsp;</td></tr></table></td>
                        <td class="sm">{{ $r->custname }}</td>
                        <td class="text-end sm">{{ $fmtLast($r->last_order) }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>กำหนดผลิตเสร็จ</td>
                        <td>{{ $fmtD($r->senddate) }}</td>
                        <td colspan="6">
                            <table style="border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 0 3px 0 0;">{!! $chk($r->Send) !!}</td>
                                    <td style="padding: 0 22px 0 0;">ส่งก่อนได้</td>
                                    <td style="padding: 0 3px 0 0;">{!! $chk($r->RP) !!}</td>
                                    <td style="padding: 0 22px 0 0;">RP</td>
                                    <td style="padding: 0 3px 0 0;">{!! $chk($r->Spec) !!}</td>
                                    <td style="padding: 0 22px 0 0;">Spec</td>
                                    <td style="padding: 0 3px 0 0;">{!! $chk($r->Cer) !!}</td>
                                    <td style="padding: 0;">Cer</td>
                                </tr>
                            </table>
                        </td>
                        <td colspan="2" rowspan="2" style="vertical-align: top;">{!! nl2br(e(trim((string) $r->Remark))) !!}</td>
                    </tr>
                    <tr class="last">
                        <td></td>
                        <td colspan="3"></td>
                        <td colspan="3" class="blue">กำหนดลูกค้าต้องการใช้</td>
                        <td colspan="2" class="blue">{{ $fmtD($r->custwant) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="sum">
            <tr>
                <td style="width: 38%;" class="text-end">รวมน้ำหนัก P</td>
                <td style="width: 22%;" class="total text-center">{{ $fmtNum($sec->total) }}</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td class="sign" style="width: 20%;">การตลาด</td>
                <td class="line">&nbsp;</td>
                <td class="sign" style="width: 22%;">วางแผนผลิต</td>
                <td class="line" style="width: 24%;">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td class="sign">เวลา</td>
                <td class="line">&nbsp;</td>
            </tr>
        </table>

        <div class="rp-title">รายการสินค้าที่สั่งซ้ำ</div>
        @if ($sec->repeats->isEmpty())
            <div style="margin-left: 40px;">— ไม่มี —</div>
        @else
            <table class="rp">
                @foreach ($sec->repeats as $rp)
                    <tr>
                        <td>{{ $rp->itemno }}</td>
                        <td>{{ $rp->type }}</td>
                        <td class="text-end">{{ $rp->count }}</td>
                        <td>รายการ</td>
                        <td class="text-end">{{ $fmtNum($rp->weight) }}</td>
                        <td>ก.ก.</td>
                    </tr>
                @endforeach
            </table>
        @endif

    </div>
@empty
    <table class="head">
        <tr><td class="title">รายงานผลิตภัณฑ์ที่ต้องผลิต (P)</td><td>อนุมัติวันที่ {{ $day->format('d/m/y') }}</td></tr>
    </table>
    <div class="empty">ไม่พบใบสั่งซื้อที่อนุมัติในวันนี้</div>
@endforelse

</body>
</html>
