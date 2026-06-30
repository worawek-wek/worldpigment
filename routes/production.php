<?php

use App\Http\Controllers\Production as Production;

Route::prefix('production-planning')->group(function () {

    Route::get('/order', [Production\OrderController::class, 'index'])->name('production.order.index');
    Route::get('/order/datatable', [Production\OrderController::class, 'datatable'])->name('production.order.datatable');
    Route::get('/order/detail', [Production\OrderController::class, 'detail'])->name('production.order.detail');
    Route::get('/order/convertplanning', [Production\OrderController::class, 'convertPlanning'])->name('production.order.convertplanning');


    // แผนการผลิต Order (อ่านอย่างเดียว) — header ที่ parent_planning_id ว่าง
    Route::get('/order-plan', [Production\OrderPlanController::class, 'index'])->name('production.orderplan.index');
    Route::get('/order-plan/datatable', [Production\OrderPlanController::class, 'datatable'])->name('production.orderplan.datatable');
    Route::get('/order-plan/detail', [Production\OrderPlanController::class, 'detail'])->name('production.orderplan.detail');


    Route::get('/planning', [Production\ProductionPlanController::class, 'index'])->name('production.planning.index');
    Route::get('/planning/datatable', [Production\ProductionPlanController::class, 'datatable'])->name('production.planning.datatable');
    Route::get('/planning/edit', [Production\ProductionPlanController::class, 'edit'])->name('production.planning.edit');
    Route::get('/planning/edit-item', [Production\ProductionPlanController::class, 'editItem'])->name('production.planning.edit-item');
    Route::post('/planning/save-item', [Production\ProductionPlanController::class, 'saveItem'])->name('production.planning.save-item');

    // สถานะ Planning (master data)
    Route::get('/planning-status', [Production\PlanningStatusController::class, 'index'])->name('production.planningstatus.index');
    Route::get('/planning-status/datatable', [Production\PlanningStatusController::class, 'datatable'])->name('production.planningstatus.datatable');
    Route::get('/planning-status/edit', [Production\PlanningStatusController::class, 'edit'])->name('production.planningstatus.edit');
    Route::post('/planning-status/save', [Production\PlanningStatusController::class, 'save'])->name('production.planningstatus.save');

    // Semi & Pigment — เพิ่ม/แก้ไข/ลบ จาก modal ของหน้า Planning Item (บันทึกลงฐานข้อมูลทันที)
    Route::post('/semi-pigment/entry/store',  [Production\SemiPigmentController::class, 'entryStore'])->name('production.semipigment.entry.store');
    // สร้าง Semi กรอกเอง (ไม่ผูกแผนการผลิต) จากหน้า Planning → เข้ารายการรออนุมัติ
    Route::post('/semi-pigment/standalone/store', [Production\SemiPigmentController::class, 'standaloneStore'])->name('production.semipigment.standalone.store');
    Route::post('/semi-pigment/entry/update', [Production\SemiPigmentController::class, 'entryUpdate'])->name('production.semipigment.entry.update');
    Route::post('/semi-pigment/entry/delete', [Production\SemiPigmentController::class, 'entryDestroy'])->name('production.semipigment.entry.delete');

    // Semi & Pigment (รออนุมัติ)
    Route::get('/semi-pigment', [Production\SemiPigmentController::class, 'index'])->name('production.semipigment.index');
    Route::get('/semi-pigment/datatable', [Production\SemiPigmentController::class, 'datatable'])->name('production.semipigment.datatable');
    Route::get('/semi-pigment/edit', [Production\SemiPigmentController::class, 'editForm'])->name('production.semipigment.edit');
    Route::post('/semi-pigment/approve', [Production\SemiPigmentController::class, 'approve'])->name('production.semipigment.approve');
    Route::post('/semi-pigment/reject', [Production\SemiPigmentController::class, 'reject'])->name('production.semipigment.reject');

    // Semi & Pigment (อนุมัติแล้ว) — รวมเข้ากับหน้า semi-pigment แล้ว
    Route::get('/semi-pigment/detail', [Production\SemiPigmentController::class, 'detail'])->name('production.semipigment.detail');
    Route::post('/semi-pigment/convertplanning', [Production\SemiPigmentController::class, 'convertplanning'])->name('production.semipigment.convertplanning');

});
