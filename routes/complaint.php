<?php

use App\Http\Controllers\ComplaintController;

// ระบบ Complaint & NCR — 26/11/2569 (ตารางหลัก nc + result + treat)
// ถูก @include_once ภายใน group middleware('auth') ใน web.php แล้ว — ห้ามครอบ auth ซ้ำ
Route::prefix('complaint')->group(function () {
    Route::get('/',                [ComplaintController::class, 'index'])->name('complaint.index');
    Route::get('/datatable',       [ComplaintController::class, 'datatable'])->name('complaint.datatable');
    Route::get('/edit',            [ComplaintController::class, 'edit'])->name('complaint.edit');
    Route::get('/customer/{code}', [ComplaintController::class, 'customerLookup'])->name('complaint.customer_lookup');
    Route::get('/customer-search', [ComplaintController::class, 'customerSearch'])->name('complaint.customer_search');
    Route::get('/product-search',  [ComplaintController::class, 'productSearch'])->name('complaint.product_search');
    Route::get('/product-lookup',  [ComplaintController::class, 'productLookup'])->name('complaint.product_lookup');
    Route::get('/export-excel',    [ComplaintController::class, 'exportExcel'])->name('complaint.export_excel');
    Route::post('/store',          [ComplaintController::class, 'store'])->name('complaint.store');
    Route::post('/delete',         [ComplaintController::class, 'destroy'])->name('complaint.delete');
});
