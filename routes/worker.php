<?php

use App\Http\Controllers\WorkerPlanningController;

/**
 * หน้าพนักงานหน้างาน (Worker) — อัพเดทสถานะงานผลิตของตัวเอง (11/08/2569)
 *
 *  - ไม่มีเมนู (แยกจากระบบ admin) — เข้าถึงได้เฉพาะ emp ที่ role = "Worker"
 *  - middleware 'worker' กันไม่ให้บัญชีอื่นเข้า (route นี้ไม่ผูกกับ config/menu.php
 *    จึง pass-through CheckAccess โดยปริยาย)
 *  - ทุก query/update กรอง+ตรวจ empno ของผู้ล็อกอินที่ฝั่ง server เสมอ
 */
Route::prefix('worker')->middleware('worker')->group(function () {

    Route::get('/', [WorkerPlanningController::class, 'index'])->name('worker.planning.index');
    Route::get('/datatable', [WorkerPlanningController::class, 'datatable'])->name('worker.planning.datatable');
    Route::get('/detail', [WorkerPlanningController::class, 'detail'])->name('worker.planning.detail');
    Route::get('/status-form', [WorkerPlanningController::class, 'statusForm'])->name('worker.planning.status-form');
    Route::post('/status-update', [WorkerPlanningController::class, 'statusUpdate'])->name('worker.planning.status-update');

});
