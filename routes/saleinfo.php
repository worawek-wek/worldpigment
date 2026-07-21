<?php

use App\Http\Controllers\SaleinfoController;

Route::prefix('saleinfo')->group(function () {

    // ─── หน้า + datatable ────────────────────────────────────────────
    Route::get('/',          [SaleinfoController::class, 'index'])->name('saleinfo.index');
    Route::get('/datatable', [SaleinfoController::class, 'datatable'])->name('saleinfo.datatable');

    // ─── Lookup / helper ────────────────────────────────────────────
    Route::get('/customer/{code}', [SaleinfoController::class, 'customerLookup'])->name('saleinfo.customer_lookup');

    // ─── อ่าน ────────────────────────────────────────────────────────
    Route::get('/edit/{id}', [SaleinfoController::class, 'edit'])->name('saleinfo.edit');   // → JSON (เติมฟอร์ม)
    Route::get('/history',   [SaleinfoController::class, 'history'])->name('saleinfo.history'); // → JSON (ประวัติการปรับราคา)

    // ─── เขียน ───────────────────────────────────────────────────────
    Route::post('/insert', [SaleinfoController::class, 'insert'])->name('saleinfo.insert');
    Route::post('/update', [SaleinfoController::class, 'update'])->name('saleinfo.update');
    Route::post('/delete', [SaleinfoController::class, 'destroy'])->name('saleinfo.delete');

    // TODO: ราคา 1/2/3 (DB tier) + ค่าสี/%สี — รอสรุปสูตร/ที่มาข้อมูลกับลูกค้า

});
