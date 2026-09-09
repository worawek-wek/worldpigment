<?php

use App\Http\Controllers\QcPlanningController;

/**
 * หน้าพนักงาน QC — ตรวจงานที่ "ส่ง QC รอผล" แล้วบันทึกผล QC (09/09/2569)
 *
 *  - ไม่มีเมนู (แยกจากระบบ admin) — เข้าถึงได้เฉพาะ emp ที่ role = "QC"
 *  - middleware 'qc' กันไม่ให้บัญชีอื่นเข้า (route นี้ไม่ผูกกับ config/menu.php
 *    จึง pass-through CheckAccess โดยปริยาย)
 *  - แสดงทุกงานที่รอ QC (ไม่กรอง empno) — บันทึกผลลง qc_status + qc_datetime
 */
Route::prefix('qc')->middleware('qc')->group(function () {

    Route::get('/', [QcPlanningController::class, 'index'])->name('qc.planning.index');
    Route::get('/datatable', [QcPlanningController::class, 'datatable'])->name('qc.planning.datatable');
    Route::get('/detail', [QcPlanningController::class, 'detail'])->name('qc.planning.detail');
    Route::get('/result-form', [QcPlanningController::class, 'resultForm'])->name('qc.planning.result-form');
    Route::post('/result-update', [QcPlanningController::class, 'resultUpdate'])->name('qc.planning.result-update');

});
