<?php

use App\Http\Controllers\Production as Production;

Route::prefix('production-planning')->group(function () {

    Route::get('/order', [Production\OrderController::class, 'inde'])->name('production.order.index');
    Route::get('/datatable', [Production\OrderController::class, 'datatable'])->name('production.order.datatable');

    Route::get('/planning', [Production\ProductionPlanController::class, 'planning'])->name('production.planning');

});
