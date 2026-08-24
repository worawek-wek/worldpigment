<?php

use App\Http\Controllers\CustomerController;

// ฐานข้อมูลลูกค้า (customer / contact / naddress / engname) — 24/08/2569
// ถูก @include_once ภายใน group middleware('auth') ใน web.php แล้ว — ห้ามครอบ auth ซ้ำ
Route::prefix('customer')->group(function () {

    Route::get('/',          [CustomerController::class, 'index'])->name('customer.index');
    Route::get('/datatable', [CustomerController::class, 'datatable'])->name('customer.datatable');

    // ฟอร์มข้อมูลลูกค้า (modal) — ไม่ส่ง code = เพิ่มลูกค้าใหม่
    Route::get('/form', [CustomerController::class, 'form'])->name('customer.form');

    Route::post('/save',   [CustomerController::class, 'save'])->name('customer.save');
    Route::post('/delete', [CustomerController::class, 'destroy'])->name('customer.delete');

});
