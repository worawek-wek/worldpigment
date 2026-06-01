<?php

use App\Http\Controllers\OrderController;

Route::prefix('order')->group(function () {

    Route::get('/', [OrderController::class, 'index'])->name('order.index');

});
