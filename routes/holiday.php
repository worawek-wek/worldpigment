<?php

use App\Http\Controllers\HolidayController;

/*
| วันหยุดนักขัตฤกษ์ (master data) — 01/09/2569
| ไฟล์นี้ถูก @include_once ใน routes/web.php ภายใน group middleware('auth')
| จึงไม่ต้องครอบ auth ซ้ำ
*/
Route::prefix('holiday')->group(function () {
    Route::get('/', [HolidayController::class, 'index'])->name('holiday.index');
    Route::get('/datatable', [HolidayController::class, 'datatable'])->name('holiday.datatable');
    Route::get('/calendar', [HolidayController::class, 'calendar'])->name('holiday.calendar');
    Route::get('/edit', [HolidayController::class, 'edit'])->name('holiday.edit');
    Route::post('/store', [HolidayController::class, 'store'])->name('holiday.store');
    Route::post('/delete', [HolidayController::class, 'destroy'])->name('holiday.delete');
    Route::post('/toggle-status', [HolidayController::class, 'toggleStatus'])->name('holiday.toggle-status');
});
