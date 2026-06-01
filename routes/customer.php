<?php

use App\Http\Controllers\CustomerController;

Route::prefix('customer')->group(function () {

    Route::get('/', [CustomerController::class, 'index'])->name('customer.index');

});
