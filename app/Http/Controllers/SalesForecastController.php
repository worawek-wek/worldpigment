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
 * ทำแล้ว: "ยอดรับ Order" · "ยอดขายที่จัดส่งลูกค้า เฉพาะราคาผ่านช่อง 2" (27/10/2569)
 * ยังไม่ทำ: กำหนดส่ง (แรก) / เลื่อนล่าสุด — ยังไม่รู้เงื่อนไข
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

    /**
     * รายงานยอดขายที่ส่งลูกค้า เฉพาะเบอร์ที่ราคาผ่านช่อง 2 (27/10/2569 — ผังตามกระดาษของระบบเดิม)
     *
     * 1 แถว = 1 รายการ suborder ที่ส่งแล้ว (suborder.DVDate อยู่ในช่วงวันที่) · กรองพนักงานขายด้วย morder.supno
     *   นน.       = suborder.Stock + Production
     *   ราคาขาย   = morder.price · รวมเงิน = นน. × ราคาขาย
     *   ราคาช่อง 2 = ราคาขาย 2 ของ ProductPriceService (คิดจากราคาทุนปัจจุบัน) ปัดเป็นจำนวนเต็ม — เหมือนช่อง "ราคาช่อง 2" ในฟอร์มใบสั่งซื้อ
     *   กำไร      = ราคาขาย − ราคาช่อง 2
     * "ผ่านช่อง 2" = ราคาขาย ≥ ราคาช่อง 2 · คำนวณราคาช่อง 2 ไม่ได้ = ไม่แสดงในรายงาน
     * จัดกลุ่มตามเซลส์ (ยอดรวม นน./รวมเงิน ท้ายกลุ่ม) + ยอดรวมทุกเซลส์ท้ายรายงาน · เรียง เซลส์ → ลูกค้า → รหัสสินค้า
     */
    public function deliveredPdf(Request $request)
    {
        $from = self::parseDateTime((string) $request->query('from', ''), now()->startOfMonth());
        $to   = self::parseDateTime((string) $request->query('to', ''), now()->endOfMonth()->startOfMinute());
        $sale = trim((string) $request->query('sale', ''));   // ว่าง = รวม (ทุกพนักงานขาย)

        $rows = DB::table('suborder as s')
            ->join('morder as m', 'm.Orderno', '=', 's.Orderno')
            ->leftJoin('customer as c', 'c.code', '=', 'm.Custno')
            ->whereBetween('s.DVDate', [$from, $to->copy()->second(59)])
            ->when($sale !== '', fn ($q) => $q->where('m.supno', $sale))
            ->orderBy('m.supno')
            ->orderBy('m.Custno')
            ->orderBy('s.Itemno')
            ->orderBy('s.Orderno')
            ->orderBy('s.Runno')
            ->get([
                'm.supno', 'm.Company', 'm.Custno', 'm.Custname', 'm.price',
                's.Orderno', 's.Itemno', 's.Stock', 's.Production', 's.DVDate',
                'c.name as cust_name', 'c.nickname',
            ]);

        $svc    = app(\App\Services\ProductPriceService::class);
        $price2 = [];   // cache ราคาช่อง 2 ต่อรหัสสินค้า
        $groups = [];
        $grand  = ['weight' => 0.0, 'amount' => 0.0];

        foreach ($rows as $r) {
            $item = trim((string) $r->Itemno);
            if (!array_key_exists($item, $price2)) {
                $p2 = $svc->lookup($item)['prices']['price_2'] ?? null;
                $price2[$item] = $p2 === null ? null : round((float) $p2);
            }
            $p2    = $price2[$item];
            $price = (float) $r->price;
            if ($p2 === null || $price < $p2) {
                continue;   // คำนวณราคาช่อง 2 ไม่ได้ / ราคาไม่ผ่านช่อง 2
            }

            $r->weight = (float) $r->Stock + (float) $r->Production;
            $r->amount = $r->weight * $price;
            $r->price2 = $p2;
            $r->profit = $price - $p2;
            // ชื่อย่อ (customer.nickname) ตามกระดาษเดิม — ไม่มีค่อยใช้ชื่อเต็ม
            $r->name   = trim((string) ($r->nickname ?: ($r->cust_name ?: $r->Custname)));

            $sup = trim((string) $r->supno);
            if (!isset($groups[$sup])) {
                $groups[$sup] = ['sale' => $sup, 'rows' => [], 'weight' => 0.0, 'amount' => 0.0];
            }
            $groups[$sup]['rows'][]  = $r;
            $groups[$sup]['weight'] += $r->weight;
            $groups[$sup]['amount'] += $r->amount;
            $grand['weight'] += $r->weight;
            $grand['amount'] += $r->amount;
        }

        $html = view('order.sales-delivered-pdf', [
            'groups' => array_values($groups),
            'grand'  => $grand,
            'from'   => $from,
            'to'     => $to,
        ])->render();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L',
            'margin_left'   => 12,
            'margin_right'  => 12,
            'margin_top'    => 12,
            'margin_bottom' => 14,
        ]);
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->SetHTMLFooter(
            '<table width="100%" style="font-family: sarabun; font-size: 10px;"><tr>'
            . '<td>' . now()->format('d/m/y') . '</td>'
            . '<td style="text-align: right;">หน้า {PAGENO} จาก {nbpg}</td>'
            . '</tr></table>'
        );
        // ช่วงวันที่ยาว (หลายเดือน) HTML เกิน default 1,000,000 → mPDF throw (แบบเดียวกับ PriceApprovalController::customerHistoryPdf)
        ini_set('pcre.backtrack_limit', '10000000');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="sales-delivered-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.pdf"',
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
