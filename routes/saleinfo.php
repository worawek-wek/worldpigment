<?php

use App\Http\Controllers\SaleinfoController;

Route::prefix('saleinfo')->group(function () {

    // ─── หน้า + datatable ────────────────────────────────────────────
    Route::get('/',          [SaleinfoController::class, 'index'])->name('saleinfo.index');
    Route::get('/datatable', [SaleinfoController::class, 'datatable'])->name('saleinfo.datatable');

    // ─── Lookup / helper ────────────────────────────────────────────
    Route::get('/customer/{code}', [SaleinfoController::class, 'customerLookup'])->name('saleinfo.customer_lookup');
    Route::get('/price-lookup',    [SaleinfoController::class, 'priceLookup'])->name('saleinfo.price_lookup'); // → JSON (ค้นหาราคาสินค้า)
    Route::get('/access-data',     [SaleinfoController::class, 'accessData'])->name('saleinfo.access_data');   // → HTML partial (ข้อมูลจากไฟล์ Access)

    // ─── ตั้งค่าเงื่อนไขราคา (คูณ/หาร/บวก) — modal "ตั้งค่าเงื่อนไขราคา" 10/08/2569 ───
    Route::get('/price-rules',         [SaleinfoController::class, 'priceRules'])->name('saleinfo.price_rules');              // → JSON (รายการเงื่อนไข)
    Route::post('/price-rules/update', [SaleinfoController::class, 'priceRulesUpdate'])->name('saleinfo.price_rules_update'); // บันทึกค่าที่แก้

    // ─── อ่าน ────────────────────────────────────────────────────────
    Route::get('/edit/{id}', [SaleinfoController::class, 'edit'])->name('saleinfo.edit');   // → JSON (เติมฟอร์ม)
    Route::get('/history',   [SaleinfoController::class, 'history'])->name('saleinfo.history'); // → JSON (ประวัติการปรับราคา)

    // ─── เขียน ───────────────────────────────────────────────────────
    Route::post('/insert', [SaleinfoController::class, 'insert'])->name('saleinfo.insert');
    Route::post('/update', [SaleinfoController::class, 'update'])->name('saleinfo.update');
    Route::post('/delete', [SaleinfoController::class, 'destroy'])->name('saleinfo.delete');

    // TODO: ราคา 1/2/3 (DB tier) + ค่าสี/%สี — รอสรุปสูตร/ที่มาข้อมูลกับลูกค้า

});
