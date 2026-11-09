<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerStatusController;

// ฐานข้อมูลลูกค้า (customer / contact / naddress / engname) — 24/08/2569
// ถูก @include_once ภายใน group middleware('auth') ใน web.php แล้ว — ห้ามครอบ auth ซ้ำ
Route::prefix('customer')->group(function () {

    Route::get('/',          [CustomerController::class, 'index'])->name('customer.index');
    Route::get('/datatable', [CustomerController::class, 'datatable'])->name('customer.datatable');

    // ฟอร์มข้อมูลลูกค้า (modal) — ไม่ส่ง code = เพิ่มลูกค้าใหม่
    Route::get('/form', [CustomerController::class, 'form'])->name('customer.form');

    Route::post('/save',   [CustomerController::class, 'save'])->name('customer.save');
    Route::post('/delete', [CustomerController::class, 'destroy'])->name('customer.delete');

    // แปลงเครดิตแบบ "N วัน" → "เครดิต N วัน นับจากวันส่งสินค้า" — 07/11/2569
    Route::get('/term-days/preview',  [CustomerController::class, 'termDaysPreview'])->name('customer.term_days.preview');
    Route::post('/term-days/convert', [CustomerController::class, 'termDaysConvert'])->name('customer.term_days.convert');

    // ขอเปลี่ยนสถานะลูกค้า (Black List) + อนุมัติด้วยรหัส — 02/11/2569
    Route::prefix('status')->group(function () {
        Route::get('/data',     [CustomerStatusController::class, 'data'])->name('customer.status.data');
        Route::get('/pending',  [CustomerStatusController::class, 'pending'])->name('customer.status.pending');
        Route::get('/md-state', [CustomerStatusController::class, 'mdState'])->name('customer.status.md_state');
        Route::post('/unlock',  [CustomerStatusController::class, 'unlock'])->name('customer.status.unlock');
        Route::post('/lock',    [CustomerStatusController::class, 'lock'])->name('customer.status.lock');
        Route::post('/save',    [CustomerStatusController::class, 'save'])->name('customer.status.save');
        Route::post('/delete',  [CustomerStatusController::class, 'destroy'])->name('customer.status.delete');
    });

});
