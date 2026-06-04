<?php

use App\Http\Controllers\ColorMatchingController;

Route::prefix('color-matching')->group(function () {

    // ─── หน้า + datatable + summary ──────────────────────────────────
    Route::get('/',          [ColorMatchingController::class, 'index'])->name('color_matching.index');
    Route::get('/datatable', [ColorMatchingController::class, 'datatable'])->name('color_matching.datatable');
    Route::get('/summary',   [ColorMatchingController::class, 'getSummary'])->name('color_matching.summary');

    // ─── Lookup ลูกค้า (ต้องอยู่ก่อน /{sendno} เพราะ .+ จับ slash ได้) ───
    Route::get('/customer/{code}',     [ColorMatchingController::class, 'customerLookup'])->name('color_matching.customer_lookup');

    // ─── CRUD ────────────────────────────────────────────────────────
    Route::post('/insert',             [ColorMatchingController::class, 'insert'])->name('color_matching.insert');
    Route::post('/update/{sendno}',    [ColorMatchingController::class, 'update'])->where('sendno', '.+')->name('color_matching.update');
    Route::get('/{sendno}',            [ColorMatchingController::class, 'edit'])->where('sendno', '.+')->name('color_matching.edit');

});
