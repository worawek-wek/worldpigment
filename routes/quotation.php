<?php

use App\Http\Controllers\QuotationController;

Route::prefix('quotation')->group(function () {

    // ─── หน้า + datatable ────────────────────────────────────────────
    Route::get('/',          [QuotationController::class, 'index'])->name('quotation.index');
    Route::get('/datatable', [QuotationController::class, 'datatable'])->name('quotation.datatable');

    // ─── Lookup / helper ────────────────────────────────────────────
    Route::get('/customer/{code}', [QuotationController::class, 'customerLookup'])->name('quotation.customer_lookup');
    Route::get('/next-qno',        [QuotationController::class, 'nextQno'])->name('quotation.next_qno');
    Route::get('/item-lookup',     [QuotationController::class, 'itemLookup'])->name('quotation.item_lookup');

    // ─── ประวัติตามลูกค้า: รายชื่อลูกค้า → ใบเสนอราคาของลูกค้า → HTML partial (modal) ───
    Route::get('/customers', [QuotationController::class, 'customers'])->name('quotation.customers');
    Route::get('/history',   [QuotationController::class, 'history'])->name('quotation.history');

    // ─── อ่าน (ใช้ query string ?qno= เพราะ Qno เก่าบางตัวมีช่องว่าง/อักขระแปลก) ───
    Route::get('/show',  [QuotationController::class, 'show'])->name('quotation.show');   // → HTML partial (modal)
    Route::get('/edit',  [QuotationController::class, 'edit'])->name('quotation.edit');   // → JSON (เติมฟอร์ม)
    Route::get('/print', [QuotationController::class, 'print'])->name('quotation.print'); // → หน้าพิมพ์

    // ─── เขียน ───────────────────────────────────────────────────────
    Route::post('/insert',   [QuotationController::class, 'insert'])->name('quotation.insert');
    Route::post('/update',   [QuotationController::class, 'update'])->name('quotation.update');
    Route::post('/delete',   [QuotationController::class, 'destroy'])->name('quotation.delete');

});
