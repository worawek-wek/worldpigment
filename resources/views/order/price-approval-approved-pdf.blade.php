{{--
    รายการที่อนุมัติราคาแล้วใน 3 วันล่าสุด (ปุ่ม "พิมพ์" ท้ายฟอร์ม MK ขออนุมัติราคาพิเศษ) — 12/09/2569
    ผังตามกระดาษของระบบเดิม: 1 ระเบียน = 1 บล็อก (ไม่ใช่ตาราง) เรียงใหม่ → เก่า

    ⚠ "เวลาอนุมัติ" ใช้ค่าเดียวกับ "วันที่ขอ" (`appvreq.ReqDate`) เพราะตาราง appvreq
      **ไม่มีคอลัมน์เก็บเวลาที่กดอนุมัติ** (ผู้ใช้เลือกแนวทางนี้ 12/09/2569)
      ถ้าภายหลังเพิ่มคอลัมน์ appvDT ให้เปลี่ยนแค่ค่าที่ส่งเข้า $r->appv_at
--}}
@php
    use Illuminate\Support\Carbon;

    $fmtNum = function ($v, $dec = 2) {
        if ($v === null || $v === '') return '';
        return number_format((float) $v, $dec, '.', ',');
    };
    // กระดาษเดิมใช้ปี 2 หลัก + เวลา 24 ชม. (เช่น 08/09/26 14:21)
    $fmtDT = function ($d) {
        if (!$d) return '';
        try { return Carbon::parse($d)->format('d/m/y H:i'); } catch (\Exception $e) { return ''; }
    };
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 11px; color: #000; }

        table.rec { width: 100%; border-collapse: collapse; }
        table.rec td { padding: 2px 4px; vertical-align: bottom; }

        .lb   { font-size: 11px; }
        .big  { font-size: 15px; font-weight: bold; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }

        /* บรรทัดหมายเหตุ + เส้นคั่นระเบียน (เส้นไม่ลากถึงขอบซ้าย เหมือนกระดาษเดิม) */
        .rem { padding-top: 6px; padding-bottom: 8px; border-bottom: 1px solid #000; }
        .spacer { height: 10px; }

        .empty { text-align: center; padding: 20px; font-size: 13px; }
    </style>
</head>
<body>

    @forelse ($rows as $r)
        <table class="rec">
            <tr>
                <td class="lb" style="width: 13%;">เวลาอนุมัติ</td>
                <td class="lb" colspan="5">{{ $fmtDT($r->appv_at) }}</td>
            </tr>
            <tr>
                <td class="lb">วันที่ขอ</td>
                <td class="lb" style="width: 17%;">{{ $fmtDT($r->ReqDate) }}</td>
                <td class="lb text-center" style="width: 10%;">{{ $r->custno }}</td>
                <td class="lb" colspan="3">{{ $r->custname }}</td>
            </tr>
            <tr>
                <td class="big" colspan="2">{{ $r->itemno }}</td>
                <td class="lb text-end" style="width: 10%;">ราคาขายครั้งนี้</td>
                <td class="big text-end" style="width: 14%;">{{ $fmtNum($r->price) }}</td>
                <td class="lb" style="width: 18%;">บาท&nbsp;&nbsp;&nbsp;จำนวนสั่งซื้อ</td>
                <td class="big text-end" style="width: 18%;">
                    {{ $fmtNum($r->weight) }} <span class="lb">ก.ก.</span>
                </td>
            </tr>
            <tr>
                <td></td>
                <td class="lb rem" colspan="5">{{ $r->remark }}</td>
            </tr>
        </table>
        <div class="spacer"></div>
    @empty
        <div class="empty">ไม่พบรายการที่อนุมัติราคาใน 3 วันล่าสุด</div>
    @endforelse

</body>
</html>
