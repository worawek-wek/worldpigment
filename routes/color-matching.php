<?php

use App\Http\Controllers\ColorMatchingController;

Route::prefix('color-matching')->group(function () {

    // ─── หน้า + datatable + summary ──────────────────────────────────
    Route::get('/',          [ColorMatchingController::class, 'index'])->name('color_matching.index');
    Route::get('/datatable', [ColorMatchingController::class, 'datatable'])->name('color_matching.datatable');
    Route::get('/summary',   [ColorMatchingController::class, 'getSummary'])->name('color_matching.summary');

    // ─── Lookup ลูกค้า (ต้องอยู่ก่อน /{sendno} เพราะ .+ จับ slash ได้) ───
    Route::get('/customer/{code}',     [ColorMatchingController::class, 'customerLookup'])->name('color_matching.customer_lookup');

    // ─── Lookup record CM จาก SendNo (ฟอร์มใบส่ง ต.ย. → เลขอ้างอิง) ───
    Route::get('/ref/{sendno}',        [ColorMatchingController::class, 'lookupBySendNo'])->where('sendno', '.+')->name('color_matching.ref');

    // ─── รายละเอียด (อ่านอย่างเดียว) — อ้างอิงด้วย id ───
    Route::get('/detail/{id}',         [ColorMatchingController::class, 'detail'])->whereNumber('id')->name('color_matching.detail');

    // ─── CRUD (อ้างอิงด้วย id auto-increment) ────────────────────────
    Route::post('/insert',             [ColorMatchingController::class, 'insert'])->name('color_matching.insert');
    Route::post('/update/{id}',        [ColorMatchingController::class, 'update'])->whereNumber('id')->name('color_matching.update');
    Route::get('/{id}',                [ColorMatchingController::class, 'edit'])->whereNumber('id')->name('color_matching.edit');

});
