<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

// ข้อมูลสินค้า (tb_products) — 07/08/2569
// ถูก @include_once ภายใน group middleware('auth') ใน web.php แล้ว — ห้ามครอบ auth ซ้ำ
Route::controller(ProductController::class)->group(function () {
    Route::get('product', 'index')->name('product.index');
    Route::get('product/datatable', 'datatable')->name('product.datatable');
    Route::get('product/edit', 'edit')->name('product.edit');
    Route::post('product/store', 'store')->name('product.store');
    Route::post('product/delete', 'destroy')->name('product.delete');
});
