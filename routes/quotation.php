<?php

use App\Http\Controllers\QuotationController;

Route::prefix('quotation')->group(function () {

    Route::get('/',          [QuotationController::class, 'index'])->name('quotation.index');
    Route::get('/datatable', [QuotationController::class, 'datatable'])->name('quotation.datatable');

});
