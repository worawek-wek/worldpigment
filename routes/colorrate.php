<?php

use App\Http\Controllers\ColorRateController;

// จัดการกลุ่มราคา (zcolorrate) — ราคาขั้นต่ำกลุ่ม A/B/C แยกตามรหัสสินค้า 19/09/2569
// ⚠ ไฟล์นี้ถูก include ใน group middleware('auth') ของ web.php แล้ว — ห้ามครอบ auth ซ้ำ
Route::prefix('color-rate')->group(function () {

    Route::get('/',          [ColorRateController::class, 'index'])->name('colorrate.index');
    Route::get('/datatable', [ColorRateController::class, 'datatable'])->name('colorrate.datatable');
    Route::get('/edit',      [ColorRateController::class, 'edit'])->name('colorrate.edit');
    Route::post('/store',    [ColorRateController::class, 'store'])->name('colorrate.store');
    Route::post('/delete',   [ColorRateController::class, 'destroy'])->name('colorrate.delete');

    // อ่านอย่างเดียว — ปุ่ม "ดูกลุ่มราคา" ในหน้า /saleinfo และ /order
    // ⚠ ตั้งใจใช้ namespace `colorratelookup` (ไม่ผูกเมนู) เพื่อให้ pass-through CheckAccess
    //   ไม่งั้นคนที่ไม่ได้ติ๊กเมนู "จัดการกลุ่มราคา" จะกดปุ่มดูไม่ได้ (403)
    Route::get('/lookup',    [ColorRateController::class, 'lookup'])->name('colorratelookup.data');

});
