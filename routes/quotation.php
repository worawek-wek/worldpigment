<?php

use App\Http\Controllers\QuotationController;

Route::prefix('quotation')->group(function () {

    Route::get('/', [QuotationController::class, 'index'])->name('quotation.index');

});
