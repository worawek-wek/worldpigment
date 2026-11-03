<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

/**
 * "รายงานสินค้าที่มีกำหนดส่ง วันที่ ..." (02/11/2569) — ผังตามกระดาษของระบบเดิม · A4 แนวนอน
 * หน้าต่าง "รายงาน" ของหน้า /order → เลือกวันที่ (+ แผนก) → PDF
 *
 * แถว = รายการ (suborder) ที่กำหนดส่งปัจจุบัน (suborder.senddate) ตรงกับวันที่เลือก
 * ไม่ตัดรายการที่ส่งแล้ว / ยังผลิตไม่เสร็จ (กระดาษมีทั้งสองแบบ)
 * แบ่ง section ตามแผนกผลิต (morder.Company) "สำหรับวางแผนจัดส่ง XX"
 * ในแผนก: จัดกลุ่มตามชื่อลูกค้า (รหัสต่างกันแต่ชื่อเดียวกันอยู่กลุ่มเดียวกัน) เรียงกลุ่มตามรหัสลูกค้าน้อยสุด
 *          → ในกลุ่มเรียงรหัสสินค้า → เลขที่ใบ → Runno
 *
 * หมายเหตุ = สถานที่ส่ง (morder.DVpoint) — ตรงกับกระดาษ เช่น "พระราม 2สง่8-16.00น."
 * สายรถจัดส่ง = customer.DV ตาม SQL ของโปรแกรมเดิม (ตอนนี้ว่างทุกแถว)
 * กำหนดส่งแรก / เลื่อน 1-3 = ประวัติใน tb_order_change_line (OrderOutstandingController::dueHistory)
 */
class OrderDueReportController extends Controller
{
    /** ลำดับแผนกบนกระดาษ — แผนกอื่นต่อท้าย, ไม่ระบุแผนกท้ายสุด */
    private const COMPANY_ORDER = ['CP', 'DB', 'MB', 'SPP'];

    public function pdf(Request $request)
    {
        $date = self::parseDate((string) $request->query('date', '')) ?? now()->startOfDay();
        $dept = trim((string) $request->query('dept', ''));   // ว่าง = ทุกแผนก

        $rows = DB::table('suborder as s')
            ->join('morder as m', 'm.Orderno', '=', 's.Orderno')
            ->leftJoin('customer as c', 'c.code', '=', 'm.Custno')
            ->whereBetween('s.senddate', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->when($dept !== '', fn ($q) => $q->where('m.Company', $dept))
            ->orderBy('s.Itemno')
            ->orderBy('m.Orderno')
            ->orderBy('s.Runno')
            ->get([
                'm.Company', 'm.Orderno', 'm.Custno', 'm.Custname', 'm.DVpoint',
                'm.Send', 'm.RP', 'm.Spec', 'm.Cer', 'm.MSDS',
                's.Runno', 's.Itemno', 's.Lotno', 's.Stock', 's.Production',
                's.EndP', 's.senddate', 's.DVDate',
                'c.name as cust_name',
                'c.DV',   // สายรถจัดส่ง — ตาม SQL ของโปรแกรมเดิม (customer.DV)
            ]);

        $history = app(OrderOutstandingController::class)->dueHistory($rows->pluck('Orderno')->unique()->values()->all());

        foreach ($rows as $r) {
            $h = $history($r);
            $r->name      = trim((string) ($r->cust_name ?: $r->Custname));
            $r->first_due = $h['first'] ?? $r->senddate;
            // มีเลื่อนเกิน 3 ครั้ง → แสดง 3 ครั้งล่าสุด
            $r->shifts    = array_slice($h['shifts'], -OrderOutstandingController::SHIFT_COLS);
        }

        $html = view('order.due-report-pdf', [
            'sections'  => $this->sections($rows),
            'date'      => $date,
            'shiftCols' => OrderOutstandingController::SHIFT_COLS,
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
            '<table width="100%" style="font-family: sarabun; font-size: 10px;"><tr>'
            . '<td>' . now()->format('d/m/y H:i') . '</td>'
            . '<td style="text-align: right;">Page {PAGENO} of {nbpg}</td>'
            . '</tr></table>'
        );
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="order-due-' . $date->format('Ymd') . '.pdf"',
        ]);
    }

    /** แผนก → กลุ่มลูกค้า (ตามชื่อ) → รายการ */
    private function sections($rows)
    {
        $rank = fn ($company) => $company === ''
            ? 999
            : (($i = array_search($company, self::COMPANY_ORDER, true)) === false ? 100 : $i);

        return $rows
            ->groupBy(fn ($r) => strtoupper(trim((string) $r->Company)))
            ->sortBy(fn ($items, $company) => sprintf('%03d|%s', $rank((string) $company), $company))
            ->map(fn ($items, $company) => [
                'company' => (string) $company === '' ? 'ไม่ระบุ' : (string) $company,
                'groups'  => $items
                    ->groupBy(fn ($r) => $r->name)
                    ->sortBy(fn ($g) => $g->min(fn ($r) => trim((string) $r->Custno)), SORT_NATURAL)
                    ->map(fn ($g, $name) => ['name' => (string) $name, 'items' => $g->values()])
                    ->values(),
            ])
            ->values();
    }

    /** 'd/m/Y' (flatpickr) หรือ 'Y-m-d' → Carbon | null */
    private static function parseDate(string $v): ?Carbon
    {
        $v = trim($v);
        foreach (['d/m/Y', 'Y-m-d'] as $format) {
            $d = \DateTime::createFromFormat('!' . $format, $v);
            if ($d && $d->format($format) === $v) {
                return Carbon::instance($d)->startOfDay();
            }
        }

        return null;
    }
}
