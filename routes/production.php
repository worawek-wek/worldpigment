<?php

use App\Http\Controllers\Production as Production;

Route::prefix('production-planning')->group(function () {

    Route::get('/order', [Production\OrderController::class, 'index'])->name('production.order.index');
    Route::get('/order/datatable', [Production\OrderController::class, 'datatable'])->name('production.order.datatable');
    Route::get('/order/detail', [Production\OrderController::class, 'detail'])->name('production.order.detail');
    Route::get('/order/convertplanning', [Production\OrderController::class, 'convertPlanning'])->name('production.order.convertplanning');



    Route::get('/planning', [Production\ProductionPlanController::class, 'index'])->name('production.planning.index');
    Route::get('/planning/datatable', [Production\ProductionPlanController::class, 'datatable'])->name('production.planning.datatable');
    Route::get('/planning/edit', [Production\ProductionPlanController::class, 'edit'])->name('production.planning.edit');
    Route::get('/planning/edit-item', [Production\ProductionPlanController::class, 'editItem'])->name('production.planning.edit-item');
    Route::post('/planning/save-item', [Production\ProductionPlanController::class, 'saveItem'])->name('production.planning.save-item');

});
