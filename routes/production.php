<?php

use App\Http\Controllers\Production as Production;

Route::prefix('production-planning')->group(function () {

    Route::get('/order', [Production\ProductionPlanController::class, 'order'])->name('production.order');
    Route::get('/planning', [Production\ProductionPlanController::class, 'planning'])->name('production.planning');

});
