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
    /** ลำดับหน่วยผลิตในรายงาน Order Daily (price) — ตามที่เขียนกำกับบนกระดาษ (02/11/2569) */
    private const DAILY_PRICE_COMPANIES = ['DB', 'MB', 'CP'];

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

    /**
     * รายงาน Order Daily (price) (02/11/2569) — ผังตามกระดาษของระบบเดิม · เลือกวันที่วันเดียว · A4 แนวนอน
     *
     * 1 รายการ (suborder) = 2 บรรทัด: ข้อมูลใบ + หมายเหตุราคา (uprice.REM1 / REM2 ของคู่ ลูกค้า+เบอร์)
     * เฉพาะใบที่อนุมัติแล้ว (morder.appv = -1) · จัดกลุ่มตามหน่วยผลิต (morder.Company) เรียง DB → MB → CP → อื่น ๆ
     * ในกลุ่มเรียง ตัวที่ 2 ของเลขที่ใบ → เลขที่ใบ → Runno
     *
     * สูตรตาม query ของระบบเดิม (ผู้ใช้ส่งมา 02/11/2569):
     * - น้ำหนัก = S ถ้า S > 0 ไม่งั้น P (ไม่ใช่ S + P)
     * - Price-2 / Price-2 (Baht) / BAHTSALE คิดเฉพาะใบที่ตัวที่ 2 ของเลขที่ใบ = "I" (CI/HI/WI) — ใบอื่น = 0
     * - ราคา 2 เป็น 0 / คำนวณไม่ได้ → Price-2 และ Price-2 (Baht) = 0
     * ราคา 1/2/3 = ProductPriceService ปัดเป็นจำนวนเต็ม · Price-2 = morder.price − ราคา 2 (ปัดแล้ว)
     */
    public function pricePdf(Request $request)
    {
        $date = self::parseDate((string) $request->query('date', '')) ?? now()->startOfDay();

        $rows = DB::table('morder as m')
            ->join('suborder as s', 's.Orderno', '=', 'm.Orderno')
            ->leftJoin('customer as c', 'c.code', '=', 'm.Custno')
            ->whereBetween('m.Mdate', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->where('m.appv', -1)
            ->orderByRaw('SUBSTRING(m.Orderno, 2, 1)')
            ->orderBy('m.Orderno')
            ->orderBy('s.Runno')
            ->get([
                'm.Company', 'm.Orderno', 'm.Mdate', 'm.Custno', 'm.Custname', 'm.supno', 'm.price',
                's.Runno', 's.Itemno', 's.Lotno', 's.Stock', 's.Production', 's.custwant', 's.senddate',
                'c.nickname', 'c.name as cust_name', 'c.term',
            ]);

        $history = app(OrderOutstandingController::class)->dueHistory($rows->pluck('Orderno')->unique()->values()->all());
        $uprices = $this->latestUprices($rows);
        $svc     = app(\App\Services\ProductPriceService::class);
        $prices  = [];   // cache ราคา 1/2/3 ต่อรหัสสินค้า

        foreach ($rows as $r) {
            $item = trim((string) $r->Itemno);
            if (!array_key_exists($item, $prices)) {
                $prices[$item] = array_map(
                    fn ($p) => $p === null ? null : round((float) $p),
                    array_intersect_key($svc->lookup($item)['prices'] ?? [], array_flip(['price_1', 'price_2', 'price_3']))
                );
            }

            $r->name      = trim((string) ($r->nickname ?: ($r->cust_name ?: $r->Custname)));
            $r->first_due = $history($r)['first'] ?? $r->senddate;
            $r->price_1   = $prices[$item]['price_1'] ?? null;
            $r->price_2   = $prices[$item]['price_2'] ?? null;
            $r->price_3   = $prices[$item]['price_3'] ?? null;

            $weight      = (float) $r->Stock > 0 ? (float) $r->Stock : (float) $r->Production;
            $isI         = strtoupper(substr(trim((string) $r->Orderno), 1, 1)) === 'I';
            $r->bahtsale = $isI ? $weight * (float) $r->price : 0;
            $r->diff     = $isI && $r->price_2 ? (float) $r->price - $r->price_2 : 0;
            $r->diff_baht = $r->diff * $weight;

            $u = $uprices[trim((string) $r->Custno) . '|' . $item] ?? null;
            $r->rem1 = $u->REM1 ?? null;
            $r->rem2 = $u->REM2 ?? null;
        }

        $html = view('order.daily-price-pdf', [
            'groups' => $this->groupByCompany($rows),
            'date'   => $date,
        ])->render();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L',
            'margin_left'   => 8,
            'margin_right'  => 8,
            'margin_top'    => 10,
            'margin_bottom' => 12,
        ]);
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->SetFont('sarabun');
        $mpdf->SetHTMLFooter(
            '<table width="100%" style="font-family: sarabun; font-size: 10px; color: #1a3a8f;"><tr>'
            . '<td>' . now()->format('d/m/Y H:i') . '</td>'
            . '<td style="text-align: right;">Page {PAGENO} of {nbpg}</td>'
            . '</tr></table>'
        );
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="order-daily-price-' . $date->format('Ymd') . '.pdf"',
        ]);
    }

    /** แถว uprice ล่าสุด (DATE) ของคู่ ลูกค้า+เบอร์ ในรายงาน — key "custno|itemno" */
    private function latestUprices($rows): array
    {
        $custnos = $rows->map(fn ($r) => trim((string) $r->Custno))->filter()->unique()->values()->all();
        if (!$custnos) {
            return [];
        }

        $map = [];
        $list = DB::table('uprice')
            ->whereIn('CustNo', $custnos)
            ->orderByDesc('DATE')
            ->orderByDesc('id')
            ->get(['CustNo', 'ITEMNO', 'REM1', 'REM2']);
        foreach ($list as $u) {
            $map[trim((string) $u->CustNo) . '|' . trim((string) $u->ITEMNO)] ??= $u;
        }

        return $map;
    }

    /** จัดกลุ่มตามหน่วยผลิต เรียงตาม DAILY_PRICE_COMPANIES แล้วตามด้วยหน่วยอื่น · ไม่ระบุแผนกไว้ท้ายสุด */
    private function groupByCompany($rows): array
    {
        $groups = $rows
            ->groupBy(fn ($r) => trim((string) $r->Company))
            ->map(fn ($items, $company) => [
                'company'   => (string) $company === '' ? 'ไม่ระบุ' : (string) $company,
                'rows'      => $items->values(),
                'stock'     => $items->sum(fn ($r) => (float) $r->Stock),
                'prod'      => $items->sum(fn ($r) => (float) $r->Production),
                'diff_baht' => $items->sum(fn ($r) => (float) $r->diff_baht),
                'bahtsale'  => $items->sum(fn ($r) => (float) $r->bahtsale),
            ])
            ->all();

        uksort($groups, function ($a, $b) {
            $rank = function ($company) {
                $pos = array_search($company, self::DAILY_PRICE_COMPANIES, true);

                return $pos !== false ? $pos : ($company === '' ? 99 : 50);
            };

            return [$rank((string) $a), (string) $a] <=> [$rank((string) $b), (string) $b];
        });

        return array_values($groups);
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
