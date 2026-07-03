<?php

use App\Http\Controllers\QuotationController;

Route::prefix('quotation')->group(function () {

    // ─── หน้า + datatable ────────────────────────────────────────────
    Route::get('/',          [QuotationController::class, 'index'])->name('quotation.index');
    Route::get('/datatable', [QuotationController::class, 'datatable'])->name('quotation.datatable');

    // ─── Lookup / helper ────────────────────────────────────────────
    Route::get('/customer/{code}', [QuotationController::class, 'customerLookup'])->name('quotation.customer_lookup');
    Route::get('/next-qno',        [QuotationController::class, 'nextQno'])->name('quotation.next_qno');

    // ─── อ่าน (ใช้ query string ?qno= เพราะ Qno เก่าบางตัวมีช่องว่าง/อักขระแปลก) ───
    Route::get('/show',  [QuotationController::class, 'show'])->name('quotation.show');   // → HTML partial (modal)
    Route::get('/edit',  [QuotationController::class, 'edit'])->name('quotation.edit');   // → JSON (เติมฟอร์ม)
    Route::get('/print', [QuotationController::class, 'print'])->name('quotation.print'); // → หน้าพิมพ์

    // ─── เขียน ───────────────────────────────────────────────────────
    Route::post('/insert',   [QuotationController::class, 'insert'])->name('quotation.insert');
    Route::post('/update',   [QuotationController::class, 'update'])->name('quotation.update');
    Route::post('/delete',   [QuotationController::class, 'destroy'])->name('quotation.delete');

});
