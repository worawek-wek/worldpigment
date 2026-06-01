<?php

use App\Http\Controllers\ColorMatchingController;

Route::prefix('color-matching')->group(function () {

    Route::get('/', [ColorMatchingController::class, 'index'])->name('color_matching.index');
    Route::get('/datatable', [ColorMatchingController::class, 'datatable'])->name('color_matching.datatable');
    Route::get('/summary', [ColorMatchingController::class, 'getSummary'])->name('color_matching.summary');

});
