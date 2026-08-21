<?php

use App\Http\Controllers\PriceRuleController;

// ตั้งค่าเงื่อนไขราคา (คูณ/หาร/บวก) — แยกออกจาก modal ในหน้ากำหนดราคา มาเป็นเมนูของตัวเอง 21/08/2569
// ⚠ ไฟล์นี้ถูก include ใน group middleware('auth') ของ web.php แล้ว — ห้ามครอบ auth ซ้ำ
Route::prefix('price-rule')->group(function () {

    Route::get('/',       [PriceRuleController::class, 'index'])->name('pricerule.index');
    Route::get('/data',   [PriceRuleController::class, 'data'])->name('pricerule.data');     // → JSON (รายการเงื่อนไข)
    Route::post('/update', [PriceRuleController::class, 'update'])->name('pricerule.update'); // บันทึกค่าที่แก้

});
