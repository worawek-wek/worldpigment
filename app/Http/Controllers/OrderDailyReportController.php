<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

/**
 * รายงานคำสั่งซื้อรายวัน (20/10/2569) — ผังตามกระดาษของระบบเดิม
 * ปุ่มบนหัวหน้า /order → หน้าต่างเลือก แผนก + ช่วงวันที่ (#dailyReportModal) → PDF A4 แนวนอน
 *
 * 1 แถว = 1 รายการ (suborder) · กรองด้วย morder.Mdate (วันที่สั่ง) + morder.Company (แผนกผลิต)
 * เรียงตามเลขที่ใบสั่ง → Runno
 */
class OrderDailyReportController extends Controller
{
    public function pdf(Request $request)
    {
        $from = self::parseDate((string) $request->query('date_from', '')) ?? now()->startOfDay();
        $to   = self::parseDate((string) $request->query('date_to', '')) ?? $from->copy();
        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }
        $dept = trim((string) $request->query('dept', ''));   // ว่าง = ทุกแผนก

        $rows = DB::table('morder as m')
            ->join('suborder as s', 's.Orderno', '=', 'm.Orderno')
            ->leftJoin('customer as c', 'c.code', '=', 'm.Custno')
            ->whereBetween('m.Mdate', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->when($dept !== '', fn ($q) => $q->where('m.Company', $dept))
            ->orderBy('m.Orderno')
            ->orderBy('s.Runno')
            ->get([
                'm.Orderno', 'm.Mdate', 'm.Custno', 'm.supno', 'm.PO', 'm.HMStore', 'm.SendCust',
                's.Itemno', 's.Stock', 's.Production', 's.custwant', 's.senddate', 's.Remark',
                'c.name as cust_name',
            ]);

        // Lead Time = จำนวนวันจากวันที่สั่งถึงกำหนดส่ง นับรวมวันแรก (ตามกระดาษ: สั่ง 20/10 ส่ง 02/11 = 14)
        foreach ($rows as $r) {
            $r->lead_time = null;
            if ($r->Mdate && $r->senddate) {
                $r->lead_time = Carbon::parse($r->Mdate)->startOfDay()
                    ->diffInDays(Carbon::parse($r->senddate)->startOfDay(), false) + 1;
            }
        }

        $html = view('order.daily-report-pdf', [
            'rows' => $rows,
            'from' => $from,
            'to'   => $to,
            'dept' => $dept,
        ])->render();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L',
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 10,
            'margin_bottom' => 12,
        ]);
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->SetHTMLFooter(
            '<table width="100%" style="font-family: sarabun; font-size: 10px;"><tr>'
            . '<td>' . now()->format('d/m/y H:i') . '</td>'
            . '<td style="text-align: right;">Page {PAGENO} of {nbpg}</td>'
            . '</tr></table>'
        );
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="daily-order-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.pdf"',
        ]);
    }

    /** รับ d/m/Y หรือ Y-m-d · อ่านไม่ได้ = null */
    private static function parseDate(string $value): ?Carbon
    {
        $value = trim($value);
        foreach (['d/m/Y', 'Y-m-d'] as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $value);
                if ($d && $d->format($fmt) === $value) {
                    return $d->startOfDay();
                }
            } catch (\Exception $e) {
                // ลองรูปแบบถัดไป
            }
        }

        return null;
    }
}
