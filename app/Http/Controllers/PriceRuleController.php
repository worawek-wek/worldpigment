<?php

namespace App\Http\Controllers;

use App\Models\PriceRule;
use App\Services\ProductPriceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ตั้งค่าเงื่อนไขราคา (คูณ / หาร / บวก) — 21/08/2569
 *
 * เดิมเป็น modal "ตั้งค่าเงื่อนไขราคา" ในหน้ากำหนดราคา (/saleinfo)
 * แยกออกมาเป็นเมนูของตัวเองที่ /price-rule (ตัวโค้ดย้ายมาจาก SaleinfoController)
 *
 * โครงเงื่อนไข (ชื่อ / ตัวขึ้นต้น / ตัวลงท้าย) มาจาก `config/product_price.php` อ่านอย่างเดียว
 * ส่วนตัวเลข คูณ/หาร/บวก ผู้ใช้แก้ได้จากหน้านี้ → เก็บลง `tb_price_rule` ทับค่าใน config
 * แถวที่ค่าตรงกับค่าตั้งต้น จะถูกลบทิ้ง = กลับไปใช้ค่าจาก config
 */
class PriceRuleController extends Controller
{
    public function index()
    {
        $data['page_url'] = 'price-rule';

        return view('pricerule.index', $data);
    }

    /**
     * GET — ตารางเงื่อนไขราคา (JSON)
     *
     * ต้องอ่านผ่าน ProductPriceService::rules() เท่านั้น (config + override ใน tb_price_rule)
     * ถ้าไปอ่าน config('product_price.rules') ตรง ๆ จะได้ค่าตั้งต้นเสมอ
     */
    public function data(ProductPriceService $prices)
    {
        $rows = array_map(function ($rule) {
            return [
                'key'        => $rule['key'] ?? null,
                'label'      => $rule['label'] ?? '',
                'prefix'     => implode(', ', $rule['prefix'] ?? []),
                'suffix'     => implode(', ', $rule['suffix'] ?? []),
                'suffix_pos' => $rule['suffix_pos'] ?? null,
                'mul'        => (float) $rule['mul'],
                'div'        => (float) $rule['div'],
                'add'        => (float) $rule['add'],
            ];
        }, $prices->rules());

        // แถวที่ไม่มี key ตั้งค่าจากหน้าจอไม่ได้ (ผูกกับ tb_price_rule ไม่ได้) — กันไว้ให้เห็นชัด
        $rows = array_values(array_filter($rows, fn ($r) => $r['key'] !== null));

        return response()->json([
            'rows' => $rows,
            'tier' => [
                'price_2_from_price_1' => (float) config('product_price.tier.price_2_from_price_1', 0),
                'price_3_from_price_2' => (float) config('product_price.tier.price_3_from_price_2', 0),
            ],
        ]);
    }

    /**
     * POST — บันทึกค่า คูณ/หาร/บวก ที่ผู้ใช้แก้
     *
     * รับมาทีละหลายแถว: rules[<key>][mul|div|add]
     * แถวไหนค่าตรงกับค่าตั้งต้นใน config → ลบ override ทิ้ง ให้กลับไปใช้ค่าตั้งต้น
     */
    public function update(Request $request)
    {
        $input = $request->input('rules', []);

        if (!is_array($input) || $input === []) {
            return response()->json(['error' => 'ไม่มีข้อมูลที่จะบันทึก'], 422);
        }

        // เทียบกับ config เสมอ — กันไม่ให้ยัด key แปลกปลอมเข้าตาราง
        $defaults = [];
        foreach (config('product_price.rules', []) as $rule) {
            if (isset($rule['key'])) {
                $defaults[$rule['key']] = $rule;
            }
        }

        $errors = [];
        $clean  = [];

        foreach ($input as $key => $values) {
            if (!isset($defaults[$key])) {
                continue;   // key ที่ไม่มีใน config — ข้ามไปเงียบ ๆ
            }

            $label = $defaults[$key]['label'];
            $mul   = $this->numberOrNull($values['mul'] ?? null);
            $div   = $this->numberOrNull($values['div'] ?? null);
            $add   = $this->numberOrNull($values['add'] ?? null);

            if ($mul === null || $div === null || $add === null) {
                $errors[] = "\"{$label}\" — ต้องกรอกตัวเลขให้ครบทั้ง คูณ / หาร / บวก";
                continue;
            }

            // แถว 0/0/0 คือแถวที่ตั้งใจปิดไว้ (Pigment/เคมี) — ยอมให้เป็น 0 ทั้งชุดได้
            // แต่ห้ามหารด้วย 0 ทั้งที่ตัวคูณไม่เป็น 0 เพราะจะพังตอนคำนวณ
            if ((float) $div == 0.0 && ((float) $mul != 0.0 || (float) $add != 0.0)) {
                $errors[] = "\"{$label}\" — ช่องหารเป็น 0 ไม่ได้";
                continue;
            }

            $clean[$key] = ['mul' => $mul, 'div' => $div, 'add' => $add];
        }

        if ($errors) {
            return response()->json(['error' => implode("\n", $errors)], 422);
        }

        $user    = auth()->user()?->name ?: auth()->user()?->username;
        $changed = 0;

        DB::transaction(function () use ($clean, $defaults, $user, &$changed) {
            foreach ($clean as $key => $values) {
                $default = $defaults[$key];

                $isDefault = (float) $values['mul'] == (float) $default['mul']
                    && (float) $values['div'] == (float) $default['div']
                    && (float) $values['add'] == (float) $default['add'];

                if ($isDefault) {
                    $changed += PriceRule::where('rule_key', $key)->delete();
                    continue;
                }

                $row = PriceRule::firstOrNew(['rule_key' => $key]);

                if ((float) $row->mul == (float) $values['mul']
                    && (float) $row->div == (float) $values['div']
                    && (float) $row->add == (float) $values['add']
                    && $row->exists) {
                    continue;   // ไม่มีอะไรเปลี่ยน
                }

                $row->fill($values);
                $row->updated_by = $user ?: null;
                $row->save();
                $changed++;
            }
        });

        return response()->json(['saved' => true, 'changed' => $changed]);
    }

    /** แปลงค่าที่กรอกเป็นตัวเลข — ว่าง/ไม่ใช่ตัวเลข = null ให้ผู้เรียกไปแจ้ง error เอง */
    private function numberOrNull($value): ?float
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
