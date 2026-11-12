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

    /**
     * ยอดขายสะสมที่มีกำหนดส่ง (แรก) ตามรหัสพนักงานขาย เทียบกับ Sale Target (12/11/2569 — ผังตามกระดาษของระบบเดิม)
     * (query Access เดิม: Sale_Send_GrpT — grp / supno / Custno / MB / DB / CP / SPP / TOTAL / TARGET / DIFF)
     *
     * แถว       = suborder ที่ "กำหนดส่งแรก" อยู่ในช่วงวันที่ · เฉพาะใบ HI / CI / WI (ตามที่ผู้ใช้เขียนกำกับบนกระดาษ)
     * กำหนดส่งแรก = OrderOutstandingController::dueHistory() (ไม่เคยเลื่อน = suborder.senddate)
     * ยอด       = (Stock + Production) × morder.price แยกคอลัมน์ตาม morder.Company
     * Target    = ตาราง target คอลัมน์เดือนของ "จากวันที่" (`1`..`12`) ของคู่ saleNo + custid
     * กลุ่ม A    = ลูกค้าที่มีแถวใน target ของเซลส์นั้น (แสดงทุกราย แม้ไม่มียอด) · Diff = รวม − Target เฉพาะรายที่มียอด
     * กลุ่ม OTHER = ลูกค้าที่มียอดแต่ไม่มีใน target (Target/Diff = 0) + แถว "OTHER" = target.custid 'OTHER'
     * ไม่เลือกเซลส์ = ทุกเซลส์ที่มียอดหรือมี target · เซลส์ละหน้า
     */
    public function duePdf(Request $request)
    {
        $from  = self::parseDateTime((string) $request->query('from', ''), now()->startOfMonth());
        $to    = self::parseDateTime((string) $request->query('to', ''), now()->endOfMonth()->startOfMinute());
        $sale  = trim((string) $request->query('sale', ''));
        $month = (int) $from->format('n');
        $fromYmd = $from->format('Y-m-d H:i:s');
        $toYmd   = $to->copy()->second(59)->format('Y-m-d H:i:s');

        // ใบที่เคยเลื่อน/แยกส่ง — กำหนดส่งแรกอาจไม่ใช่ senddate ปัจจุบัน จึงต้องดึงมาคิดด้วย
        $changed = DB::table('tb_order_change_line')->distinct()->pluck('orderno')->all();

        $rows = DB::table('suborder as s')
            ->join('morder as m', 'm.Orderno', '=', 's.Orderno')
            ->whereRaw("LEFT(m.Orderno, 1) IN ('H', 'C', 'W') AND SUBSTRING(m.Orderno, 2, 1) = 'I'")
            ->when($sale !== '', fn ($q) => $q->whereRaw('TRIM(m.supno) = ?', [$sale]))
            ->where(function ($q) use ($fromYmd, $toYmd, $changed) {
                $q->whereBetween('s.senddate', [$fromYmd, $toYmd]);
                if ($changed) {
                    $q->orWhereIn('s.Orderno', $changed);
                }
            })
            ->get(['s.Orderno', 's.Runno', 's.senddate', 's.Stock', 's.Production', 'm.supno', 'm.Custno', 'm.Company', 'm.price']);

        $history = app(OrderOutstandingController::class)
            ->dueHistory($rows->pluck('Orderno')->unique()->values()->all());

        $blank = array_fill_keys(self::COMPANIES, 0.0);
        $sales = [];   // supno → custno → ['amt' => …, 'total' => …]
        foreach ($rows as $r) {
            $first = $history($r)['first'] ?? null;
            $first = $first ?: ($r->senddate ? Carbon::parse($r->senddate)->format('Y-m-d') : null);
            if (!$first || $first < $from->format('Y-m-d') || $first > $to->format('Y-m-d')) {
                continue;
            }
            $amt = ((float) $r->Stock + (float) $r->Production) * (float) $r->price;
            if (abs($amt) < 0.000001) {
                continue;
            }
            $sup  = trim((string) $r->supno);
            $cust = trim((string) $r->Custno);
            $comp = strtoupper(trim((string) $r->Company));
            $sales[$sup][$cust] ??= ['amt' => $blank, 'total' => 0.0];
            if (isset($blank[$comp])) {
                $sales[$sup][$cust]['amt'][$comp] += $amt;
            }
            $sales[$sup][$cust]['total'] += $amt;
        }

        // Sale Target ของเดือน
        $targets = DB::table('target')
            ->when($sale !== '', fn ($q) => $q->whereRaw('TRIM(saleNo) = ?', [$sale]))
            ->get(['saleNo', 'custid', DB::raw('`' . $month . '` as tg')]);
        $tgBySale = [];
        foreach ($targets as $t) {
            $tgBySale[trim((string) $t->saleNo)][trim((string) $t->custid)] = (float) $t->tg;
        }

        $codes = array_unique(array_merge(array_keys($sales), array_keys($tgBySale)));
        usort($codes, 'strnatcasecmp');

        $sections = [];
        foreach ($codes as $sup) {
            $sold = $sales[$sup] ?? [];
            $tg   = $tgBySale[$sup] ?? [];
            $otherTarget = $tg['OTHER'] ?? 0.0;
            unset($tg['OTHER']);

            $groupA = ['rows' => [], 'sum' => $blank + ['total' => 0.0, 'target' => 0.0, 'diff' => 0.0]];
            $custA  = array_keys($tg);
            usort($custA, 'strnatcasecmp');
            foreach ($custA as $cust) {
                $s = $sold[$cust] ?? ['amt' => $blank, 'total' => 0.0];
                $hasSale = abs($s['total']) > 0.000001;
                $groupA['rows'][] = [
                    'custno' => $cust, 'amt' => $s['amt'], 'total' => $s['total'],
                    'target' => $tg[$cust], 'diff' => $hasSale ? $s['total'] - $tg[$cust] : null,
                ];
                foreach (self::COMPANIES as $c) {
                    $groupA['sum'][$c] += $s['amt'][$c];
                }
                $groupA['sum']['total']  += $s['total'];
                $groupA['sum']['target'] += $tg[$cust];
            }
            $groupA['sum']['diff'] = $groupA['sum']['total'] - $groupA['sum']['target'];

            $groupO = ['rows' => [], 'sum' => $blank + ['total' => 0.0, 'target' => $otherTarget, 'diff' => 0.0],
                       'other_target' => $otherTarget];
            $custO = array_values(array_diff(array_keys($sold), $custA));
            usort($custO, 'strnatcasecmp');
            foreach ($custO as $cust) {
                $s = $sold[$cust];
                $groupO['rows'][] = ['custno' => $cust, 'amt' => $s['amt'], 'total' => $s['total'], 'target' => 0.0, 'diff' => 0.0];
                foreach (self::COMPANIES as $c) {
                    $groupO['sum'][$c] += $s['amt'][$c];
                }
                $groupO['sum']['total'] += $s['total'];
            }
            $groupO['sum']['diff'] = $groupO['sum']['total'] - $otherTarget;

            $sections[] = ['sale' => $sup, 'a' => $groupA, 'other' => $groupO];
        }

        $html = view('order.sales-due-pdf', [
            'sections'  => $sections,
            'companies' => self::COMPANIES,
            'from'      => $from,
            'to'        => $to,
            'month'     => $month,
            'sale'      => $sale,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8', 'format' => 'A4',
            'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 10, 'margin_bottom' => 14,
        ]);
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->SetHTMLFooter(
            '<table width="100%" style="font-family: sarabun; font-size: 10px; color: #c0504d;"><tr>'
            . '<td>' . now()->format('d/m/Y') . '</td>'
            . '<td style="text-align: center;">Page {PAGENO} of {nbpg}</td><td></td>'
            . '</tr></table>'
        );
        ini_set('pcre.backtrack_limit', '10000000');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="sales-due-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.pdf"',
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
