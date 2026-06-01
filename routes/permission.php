<?php

use App\Http\Controllers\PermissionController;

Route::prefix('permission')->group(function () {

    Route::get('/', [PermissionController::class, 'index'])->name('permission.index');

});
