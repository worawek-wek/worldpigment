<?php

use App\Http\Controllers\Production as Production;

Route::prefix('production-planning')->group(function () {

    Route::get('/', [Production\ProductionPlanController::class, 'index'])->name('production.index');

});
