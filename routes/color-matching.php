<?php

use App\Http\Controllers\ColorMatchingController;

Route::prefix('color-matching')->group(function () {

    Route::get('/', [ColorMatchingController::class, 'index'])->name('color_matching.index');

});
