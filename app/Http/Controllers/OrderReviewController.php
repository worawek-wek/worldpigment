<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * ฟอร์ม "ใบทบทวนคำสั่งซื้อ" (03/11/2569) — แปลงจากฟอร์ม Access "OrderPRT"
 * เลือกวันที่ Order + แผนกที่ผลิต → เดินดูทีละรายการ แล้วพิมพ์ใบทบทวนฯ
 *
 * เงื่อนไข: เฉพาะใบที่อนุมัติแล้ว (morder.appv ไม่ใช่ NULL/0 — ตามข้อความบนฟอร์มเดิม
 *           "เพื่อเลือกพิมพ์ใบทบทวนฯ เฉพาะที่อนุมัติแล้ว") และ morder.Mdate ตรงกับวันที่เลือก
 * 1 ระเบียน = 1 รายการ (suborder) เรียงเลขที่ใบ → Runno
 */
class OrderReviewController extends Controller
{
    public function data(Request $request)
    {
        $date = self::parseDate((string) $request->query('date', '')) ?? now()->startOfDay();
        $dept = trim((string) $request->query('dept', ''));   // ว่าง = ทุกแผนก

        $rows = DB::table('suborder as s')
            ->join('morder as m', 'm.Orderno', '=', 's.Orderno')
            ->leftJoin('customer as c', 'c.code', '=', 'm.Custno')
            ->whereBetween('m.Mdate', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->whereNotNull('m.appv')
            ->where('m.appv', '<>', 0)
            ->when($dept !== '', fn ($q) => $q->where('m.Company', $dept))
            ->orderBy('m.Orderno')
            ->orderBy('s.Runno')
            ->get([
                'm.Orderno', 'm.Mdate', 'm.Company', 'm.Custno', 'm.Custname', 'm.Emp', 'm.PO', 'm.supno',
                'm.Send', 'm.RP', 'm.Spec', 'm.Cer', 'm.price',
                's.Runno', 's.Itemno', 's.Lotno', 's.Stock', 's.Production', 's.senddate', 's.Remark',
                'c.name as cust_name',
            ])
            ->map(fn ($r) => [
                'orderno'    => trim((string) $r->Orderno),
                'runno'      => $r->Runno,
                'mdate'      => $r->Mdate ? Carbon::parse($r->Mdate)->format('d/m/y H:i') : '',
                'company'    => trim((string) $r->Company),
                'custno'     => trim((string) $r->Custno),
                'custname'   => trim((string) ($r->cust_name ?: $r->Custname)),
                'emp'        => trim((string) $r->Emp),
                'po'         => trim((string) $r->PO),
                'sale'       => trim((string) $r->supno),
                'send'       => self::checked($r->Send),
                'rp'         => self::checked($r->RP),
                'spec'       => self::checked($r->Spec),
                'cer'        => self::checked($r->Cer),
                'itemno'     => trim((string) $r->Itemno),
                'lotno'      => trim((string) $r->Lotno),
                'stock'      => (float) $r->Stock,
                'production' => (float) $r->Production,
                'price'      => $r->price === null ? null : (float) $r->price,
                'senddate'   => $r->senddate ? Carbon::parse($r->senddate)->format('d/m/y') : '',
                'remark'     => (string) $r->Remark,
            ])
            ->values();

        return response()->json(['date' => $date->format('d/m/Y'), 'dept' => $dept, 'rows' => $rows]);
    }

    /** checkbox แบบ Access: -1 = ติ๊ก, 0/NULL = ไม่ติ๊ก */
    private static function checked($v): bool
    {
        return $v !== null && (int) $v !== 0;
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
