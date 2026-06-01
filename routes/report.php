<?php

use App\Http\Controllers\ReportPageController;

Route::prefix('report')->group(function () {

    Route::get('/', [ReportPageController::class, 'index'])->name('report.index');

});
