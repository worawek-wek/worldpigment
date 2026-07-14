<?php

use App\Http\Controllers\SaleinfoController;

Route::prefix('saleinfo')->group(function () {

    // ─── หน้า + ตาราง (ตอนนี้เป็น UI + ข้อมูลจำลอง ยังไม่ต่อ DB) ───
    Route::get('/',          [SaleinfoController::class, 'index'])->name('saleinfo.index');
    Route::get('/datatable', [SaleinfoController::class, 'datatable'])->name('saleinfo.datatable');

    // TODO: CRUD (insert/update/delete) + customer lookup + item lookup — รอต่อกับตาราง uprice

});
