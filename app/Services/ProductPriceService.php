<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * คำนวณราคาขายจากราคาทุน (ตาราง `access_pdprice` = สำเนาของ PdPrice ในไฟล์ Access)
 *
 *   ราคาขาย 1 = PdPrice.Price × mul ÷ div + add
 *   ราคาขาย 2 = ราคาขาย 1 × 1.14
 *   ราคาขาย 3 = ราคาขาย 2 × 1.30
 *
 * ตัวคูณ/หาร/บวก มาจากตารางเงื่อนไขของลูกค้าใน `config/product_price.php`
 * ซึ่งจับคู่ด้วย "ตัวขึ้นต้น" (และบางแถวมี "ตัวลงท้าย") ของ PdCode
 * ส่วนตัวคูณระหว่างขั้นราคาอยู่ที่ `product_price.tier` ในไฟล์เดียวกัน
 */
class ProductPriceService
{
    /** ตารางราคาทุนบน MySQL (สำเนาของ PdPrice ในไฟล์ Access) */
    private const TABLE_PDPRICE = 'access_pdprice';

    /**
     * ค้นราคาทุน + คำนวณราคาขายจากรหัสสินค้า
     *
     * คืน array เสมอ ให้ controller ตัดสินใจต่อได้ว่าจะโชว์อะไร:
     *   found  = เจอรหัสใน PdPrice ไหม
     *   rule   = เงื่อนไขที่จับคู่ได้ (null = ไม่มีเงื่อนไขรองรับรหัสนี้)
     *   prices = ราคาขาย 1/2/3 ที่คำนวณได้ (null = คำนวณไม่ได้ ดู reason)
     */
    public function lookup(string $code): array
    {
        $code = trim($code);

        if ($code === '') {
            return $this->fail($code, null, 'ยังไม่ได้กรอกรหัสสินค้า');
        }

        $row = $this->findPdPrice($code);

        if (!$row) {
            return $this->fail($code, null, 'ไม่พบรหัสสินค้านี้ในตารางราคาทุน');
        }

        $base = (float) $row->Price;
        $rule = $this->match($row->PdCode);

        if (!$rule) {
            return $this->fail($row->PdCode, $base, 'ไม่มีเงื่อนไขราคารองรับรหัสที่ขึ้นต้นด้วยนี้');
        }

        // แถวที่ลูกค้าตั้งไว้ 0/0/0 (เช่นกลุ่ม "1") = ยังไม่ได้กำหนดสูตร — หารด้วย 0 ไม่ได้
        if ((float) $rule['div'] == 0.0) {
            return $this->fail($row->PdCode, $base, 'เงื่อนไข "' . $rule['label'] . '" ยังไม่ได้กำหนดสูตรราคา (ตั้งไว้ 0)', $rule);
        }

        // ไล่ขั้นจากค่าที่ยังไม่ปัดเศษ แล้วค่อยปัดตอนแสดงผล — ปัดทีละขั้นจะเพี้ยนสะสม
        $price1 = $base * $rule['mul'] / $rule['div'] + $rule['add'];
        $price2 = $price1 * (float) config('product_price.tier.price_2_from_price_1', 1);
        $price3 = $price2 * (float) config('product_price.tier.price_3_from_price_2', 1);

        return [
            'found'      => true,
            'code'       => $row->PdCode,
            'base_price' => round($base, 2),
            'rule'       => $rule,
            'prices'     => [
                'price_1' => round($price1, 2),
                'price_2' => round($price2, 2),
                'price_3' => round($price3, 2),
            ],
            'reason'     => null,
        ];
    }

    /**
     * หาแถวราคาทุน — เทียบแบบตรงตัวก่อน ไม่เจอค่อยเทียบแบบตัดช่องว่างหัวท้าย
     * (ข้อมูลเก่าบางรหัสมีช่องว่างต่อท้าย)
     *
     * อ่านจาก MySQL `access_pdprice` (สำเนาของ PdPrice ในไฟล์ Access) — 05/08/2569
     * เพราะเครื่อง server ของลูกค้าไม่มีไฟล์ .mdb และไม่มี ODBC driver
     */
    private function findPdPrice(string $code)
    {
        $row = DB::table(self::TABLE_PDPRICE)
            ->select('PdCode', 'Price')
            ->where('PdCode', $code)
            ->first();

        if ($row) {
            return $row;
        }

        return DB::table(self::TABLE_PDPRICE)
            ->select('PdCode', 'Price')
            ->whereRaw('TRIM(PdCode) = ?', [$code])
            ->first();

        // ── เวอร์ชันอ่านจากไฟล์ Access โดยตรง (ปิดไว้ตอนขึ้น server ให้ลูกค้าทดสอบ) ──
        // ใช้ได้เฉพาะเครื่องที่มี formula_2000.mdb + ODBC driver (ดู ACCESS_DB_PATH ใน .env)
        // $rows = DB::connection('access')
        //     ->select('SELECT PdCode, Price FROM PdPrice WHERE PdCode = ?', [$code]);
        //
        // if ($rows) {
        //     return $rows[0];
        // }
        //
        // $rows = DB::connection('access')
        //     ->select('SELECT PdCode, Price FROM PdPrice WHERE TRIM(PdCode) = ?', [$code]);
        //
        // return $rows[0] ?? null;
    }

    /**
     * จับคู่ PdCode กับเงื่อนไขใน config — เข้าได้หลายแถวก็เอาแถวที่เจาะจงที่สุด
     *
     * แถวที่มี suffix_pos = ตัวลงท้ายต้องเริ่มที่ตัวที่ N พอดี (ดู matchesSuffixAt)
     *
     * ความเจาะจง: มี suffix (+100) > prefix ยาว (+ความยาว)
     * เช่น 219E649TR เข้าทั้ง "21" และ "2 ลงท้าย TR" → ใช้แบบหลัง
     *      1500102   เข้าทั้ง "1" และ "15"          → ใช้ "15"
     */
    public function match(string $code): ?array
    {
        $code = strtoupper(trim($code));
        $best = null;
        $bestScore = -1;

        foreach (config('product_price.rules', []) as $rule) {
            $prefix = $this->matchedPrefix($code, $rule['prefix'] ?? []);
            if ($prefix === null) {
                continue;
            }

            $suffixes = $rule['suffix'] ?? [];
            $suffixPos = $rule['suffix_pos'] ?? null;

            if ($suffixes) {
                $ok = $suffixPos
                    ? $this->matchesSuffixAt($code, $suffixes, (int) $suffixPos)
                    : $this->matchesSuffix($code, $suffixes);

                if (!$ok) {
                    continue;
                }
            }

            $score = strlen($prefix) + ($suffixes ? 100 : 0);
            if ($score > $bestScore) {
                $best = $rule;
                $bestScore = $score;
            }
        }

        return $best;
    }

    /** คืน prefix ที่ยาวที่สุดในกลุ่มนี้ที่ตรงกับรหัส (ไม่ตรงเลย = null) */
    private function matchedPrefix(string $code, array $prefixes): ?string
    {
        $found = null;
        foreach ($prefixes as $p) {
            $p = strtoupper($p);
            if (str_starts_with($code, $p) && ($found === null || strlen($p) > strlen($found))) {
                $found = $p;
            }
        }

        return $found;
    }

    /**
     * รหัสลงท้ายด้วยตัวใดตัวหนึ่งในรายการไหม
     *
     * รหัสที่มีขีด (-) ให้เทียบกับ "ท่อนหลังขีดสุดท้าย" ทั้งก้อนเท่านั้น
     * ไม่งั้นรหัสอย่าง XX09113A-BLK จะไปเข้าเงื่อนไข "ลงท้าย K" เพราะบังเอิญจบด้วยตัว K
     */
    private function matchesSuffix(string $code, array $suffixes): bool
    {
        $hasDash = str_contains($code, '-');
        $tail    = $hasDash ? substr($code, strrpos($code, '-') + 1) : $code;

        foreach ($suffixes as $s) {
            $s = strtoupper($s);
            if ($hasDash ? $tail === $s : str_ends_with($tail, $s)) {
                return true;
            }
        }

        return false;
    }

    /**
     * ตัวลงท้ายต้องเริ่มที่ "ตัวที่ N" ของรหัสพอดี (นับจาก 1) — ใช้กับ suffix_pos ใน config
     *
     * เงื่อนไข "MB ตัวที่ 8 ลงท้ายด้วย P, PC, K, J" (10/08/2569)
     *   MB1B040K   ตัวที่ 8 เป็นต้นไป = "K"   → เข้า
     *   MB1B190AP  ตัวที่ 8 เป็นต้นไป = "AP"  → ไม่เข้า (P อยู่หลักที่ 9)
     *   MB1B192EYP ตัวที่ 8 เป็นต้นไป = "EYP" → ไม่เข้า
     */
    private function matchesSuffixAt(string $code, array $suffixes, int $pos): bool
    {
        $tail = substr($code, $pos - 1);

        if ($tail === '' || $tail === false) {
            return false;
        }

        foreach ($suffixes as $s) {
            if ($tail === strtoupper($s)) {
                return true;
            }
        }

        return false;
    }

    private function fail(string $code, ?float $base, string $reason, ?array $rule = null): array
    {
        return [
            'found'      => $base !== null,
            'code'       => $code,
            'base_price' => $base !== null ? round($base, 2) : null,
            'rule'       => $rule,
            'prices'     => null,
            'reason'     => $reason,
        ];
    }
}
