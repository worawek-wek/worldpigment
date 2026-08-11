<?php

use App\Http\Controllers\OrderController;

Route::prefix('order')->group(function () {

    // ─── หน้า + ตารางรายการ ──────────────────────────────────────────
    Route::get('/',          [OrderController::class, 'index'])->name('order.index');
    Route::get('/datatable', [OrderController::class, 'datatable'])->name('order.datatable');

    // ─── ฟอร์มบันทึกใบสั่งซื้อ (อ่านข้อมูลมาเติมฟอร์ม) ──────────────────
    // ใช้ query string ?orderno= เพราะเลขที่ใบสั่งเก่าบางตัวมีช่องว่าง/อักขระแปลก
    Route::get('/form', [OrderController::class, 'form'])->name('order.form');

    // ─── Lookup ที่ฟอร์มเรียกระหว่างกรอก ─────────────────────────────
    Route::get('/customer/{code}', [OrderController::class, 'customerLookup'])->name('order.customer_lookup');
    Route::get('/price-info',      [OrderController::class, 'priceInfo'])->name('order.price_info');
    Route::get('/next-orderno',    [OrderController::class, 'nextOrderno'])->name('order.next_orderno');

});
