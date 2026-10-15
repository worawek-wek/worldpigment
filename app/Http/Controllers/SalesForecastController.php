<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

/**
 * ประมาณการยอดขาย ตามรหัสพนักงานขาย (15/10/2569) — แปลงจากฟอร์ม Access "SaleOrder"
 * ปุ่มบนหัวหน้า /order → หน้าต่างเลือกเงื่อนไข (#salesForecastModal) → PDF
 *
 * ตอนนี้ทำแค่ปุ่ม "ยอดรับ Order" (มีกระดาษตัวอย่างให้เทียบ) — อีก 3 ปุ่มของฟอร์มเดิม
 * (กำหนดส่ง (แรก) / เลื่อนล่าสุด / ยอดขายที่จัดส่งลูกค้า เฉพาะราคาผ่านช่อง 2) ยังไม่รู้เงื่อนไข
 */
class SalesForecastController extends Controller
{
    /** คอลัมน์ยอดตามแผนกที่ผลิต (morder.Company) — เรียงตามรายงานกระดาษเดิม */
    public const COMPANIES = ['MB', 'DB', 'CP', 'SPP'];

    /**
     * รายงานยอดรับ Order ตามรหัสพนักงานขาย
     * ยอด = morder.netqty × morder.price · กรองด้วย morder.Mdate · จัดกลุ่มตาม morder.supno → Custno
     * (ตรวจกับกระดาษตัวอย่างแล้ว เช่น 00531 MB 100 × 295 = 29,500)
     */
    public function orderPdf(Request $request)
    {
        $from = self::parseDateTime((string) $request->query('from', ''), now()->startOfMonth());
        $to   = self::parseDateTime((string) $request->query('to', ''), now()->endOfMonth()->startOfMinute());
        $sale = trim((string) $request->query('sale', ''));   // ว่าง = รวม (ทุกพนักงานขาย)

        $rows = DB::table('morder')
            ->whereBetween('Mdate', [$from, $to->copy()->second(59)])   // รวมทั้งนาทีสุดท้าย
            ->when($sale !== '', fn ($q) => $q->where('supno', $sale))
            ->groupBy('supno', 'Custno', 'Company')
            ->orderBy('supno')
            ->orderBy('Custno')
            ->get([
                'supno', 'Custno', 'Company',
                DB::raw('SUM(COALESCE(netqty, 0) * COALESCE(price, 0)) as amount'),
            ]);

        // ประกอบเป็น กลุ่มพนักงานขาย → แถวลูกค้า → ยอดตามแผนก
        // แผนกที่ไม่อยู่ใน COMPANIES (ว่าง/อื่น ๆ) นับเข้าเฉพาะช่อง "รวม"
        $blank  = array_fill_keys(self::COMPANIES, 0.0);
        $groups = [];
        $grand  = $blank + ['total' => 0.0];

        foreach ($rows as $r) {
            $sup  = trim((string) $r->supno);
            $cust = trim((string) $r->Custno);
            $comp = strtoupper(trim((string) $r->Company));
            $amt  = (float) $r->amount;

            if (!isset($groups[$sup])) {
                $groups[$sup] = ['sale' => $sup, 'rows' => [], 'sum' => $blank + ['total' => 0.0]];
            }
            if (!isset($groups[$sup]['rows'][$cust])) {
                $groups[$sup]['rows'][$cust] = ['custno' => $cust, 'amt' => $blank, 'total' => 0.0];
            }

            if (isset($blank[$comp])) {
                $groups[$sup]['rows'][$cust]['amt'][$comp] += $amt;
                $groups[$sup]['sum'][$comp] += $amt;
                $grand[$comp] += $amt;
            }
            $groups[$sup]['rows'][$cust]['total'] += $amt;
            $groups[$sup]['sum']['total'] += $amt;
            $grand['total'] += $amt;
        }

        // ตัดลูกค้าที่ยอดเป็น 0 ทั้งแถว (ใบที่ยังไม่กรอกน้ำหนัก/ราคา)
        foreach ($groups as $k => $g) {
            $groups[$k]['rows'] = array_values(array_filter($g['rows'], fn ($row) => abs($row['total']) > 0.000001));
            if (!$groups[$k]['rows']) {
                unset($groups[$k]);
            }
        }

        $html = view('order.sales-forecast-pdf', [
            'groups'    => array_values($groups),
            'grand'     => $grand,
            'companies' => self::COMPANIES,
            'from'      => $from,
            'to'        => $to,
            'sale'      => $sale,
        ])->render();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => 12,
            'margin_right'  => 12,
            'margin_top'    => 12,
            'margin_bottom' => 14,
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->SetHTMLFooter(
            '<table width="100%" style="font-family: sarabun; font-size: 10px; color: #1a3f9c;"><tr>'
            . '<td>' . now()->format('d/m/y') . '</td>'
            . '<td style="text-align: right;">Page {PAGENO} of {nbpg}</td>'
            . '</tr></table>'
        );
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="sales-order-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.pdf"',
        ]);
    }

    /** รหัสพนักงานขายที่เคยใช้จริงใน morder.supno — ตัวเลือกของ dropdown (เรียงแบบข้อความ ตามรายงานเดิม) */
    public static function saleCodes()
    {
        return DB::table('morder')
            ->whereRaw("TRIM(COALESCE(supno, '')) <> ''")
            ->distinct()
            ->orderBy('supno')
            ->pluck('supno')
            ->map(fn ($v) => trim((string) $v));
    }

    /** วันที่-เวลาของรายงาน — รับ d/m/Y H:i / d/m/Y / Y-m-d H:i / Y-m-d · อ่านไม่ได้ = ค่าเริ่มต้น */
    private static function parseDateTime(string $value, Carbon $default): Carbon
    {
        $value = trim($value);
        foreach (['d/m/Y H:i', 'Y-m-d H:i', 'd/m/Y', 'Y-m-d'] as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $value);
                if ($d && $d->format($fmt) === $value) {
                    return str_contains($fmt, 'H') ? $d->second(0) : $d->startOfDay();
                }
            } catch (\Exception $e) {
                // ลองรูปแบบถัดไป
            }
        }

        return $default;
    }
}
