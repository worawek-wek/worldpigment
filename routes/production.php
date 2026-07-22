<?php
 
use App\Http\Controllers\Production as Production;
use App\Http\Controllers\DepartmentController as DepartmentController;
use App\Http\Controllers\EmpController as EmpController;
use App\Http\Controllers\RoleController as RoleController;
use App\Http\Controllers\MachineController as MachineController;
use App\Http\Controllers\ProdMethodController as ProdMethodController;

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
    Route::get('/planning/dept-options', [Production\ProductionPlanController::class, 'deptOptions'])->name('production.planning.dept-options');
    Route::post('/planning/save-item', [Production\ProductionPlanController::class, 'saveItem'])->name('production.planning.save-item');
    Route::post('/planning/save-end-order', [Production\ProductionPlanController::class, 'saveEndOrder'])->name('production.planning.save-end-order');

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

    // Pigment — เพิ่ม/แก้ไข/ลบ จาก modal ของหน้า Planning Item (แยกจาก Semi, บันทึกลง tb_pigment)
    Route::post('/pigment/entry/store',  [Production\PigmentController::class, 'entryStore'])->name('production.pigment.entry.store');
    Route::post('/pigment/entry/update', [Production\PigmentController::class, 'entryUpdate'])->name('production.pigment.entry.update');
    Route::post('/pigment/entry/delete', [Production\PigmentController::class, 'entryDestroy'])->name('production.pigment.entry.delete');

    // Pigment (หน้าอนุมัติ) — แยกจากหน้า Semi & Pigment เดิม
    Route::get('/pigment', [Production\PigmentController::class, 'index'])->name('production.pigment.index');
    Route::get('/pigment/datatable', [Production\PigmentController::class, 'datatable'])->name('production.pigment.datatable');
    // Export Excel "ใบขอสั่ง PIGMENT" — ใช้เงื่อนไขค้นหาชุดเดียวกับ datatable (ทุกหน้า ไม่แบ่งหน้า)
    Route::get('/pigment/export-excel', [Production\PigmentController::class, 'exportExcel'])->name('production.pigment.export-excel');
    Route::get('/pigment/edit', [Production\PigmentController::class, 'editForm'])->name('production.pigment.edit');
    Route::get('/pigment/detail', [Production\PigmentController::class, 'detail'])->name('production.pigment.detail');
    Route::post('/pigment/approve', [Production\PigmentController::class, 'approve'])->name('production.pigment.approve');
    Route::post('/pigment/reject', [Production\PigmentController::class, 'reject'])->name('production.pigment.reject');
    Route::post('/pigment/convertplanning', [Production\PigmentController::class, 'convertplanning'])->name('production.pigment.convertplanning');

    // Semi & Pigment (รออนุมัติ)
    Route::get('/semi-pigment', [Production\SemiPigmentController::class, 'index'])->name('production.semipigment.index');
    Route::get('/semi-pigment/datatable', [Production\SemiPigmentController::class, 'datatable'])->name('production.semipigment.datatable');
    // Export Excel "ใบขอสั่งทำ SEMI" — ใช้เงื่อนไขค้นหาชุดเดียวกับ datatable (ทุกหน้า ไม่แบ่งหน้า)
    Route::get('/semi-pigment/export-excel', [Production\SemiPigmentController::class, 'exportExcel'])->name('production.semipigment.export-excel');
    Route::get('/semi-pigment/edit', [Production\SemiPigmentController::class, 'editForm'])->name('production.semipigment.edit');
    Route::post('/semi-pigment/approve', [Production\SemiPigmentController::class, 'approve'])->name('production.semipigment.approve');
    Route::post('/semi-pigment/reject', [Production\SemiPigmentController::class, 'reject'])->name('production.semipigment.reject');

    // Semi & Pigment (อนุมัติแล้ว) — รวมเข้ากับหน้า semi-pigment แล้ว
    Route::get('/semi-pigment/detail', [Production\SemiPigmentController::class, 'detail'])->name('production.semipigment.detail');
    Route::post('/semi-pigment/convertplanning', [Production\SemiPigmentController::class, 'convertplanning'])->name('production.semipigment.convertplanning');
    // ต้นไม้สถานะแผนการผลิตที่สร้างจาก Semi (recursive) — ใช้ใน modal ของหน้า Planning Item
    Route::get('/semi-pigment/plan-tree', [Production\SemiPigmentController::class, 'planTree'])->name('production.semipigment.plan-tree');


    // department
    Route::get('/department', [DepartmentController::class, 'index'])->name('department.index');
    Route::get('/department/datatable', [DepartmentController::class, 'datatable'])->name('department.datatable');
    Route::get('/department/edit', [DepartmentController::class, 'edit'])->name('department.edit');
    Route::post('/department/store', [DepartmentController::class, 'store'])->name('department.store');
    Route::post('/department/toggle-status', [DepartmentController::class, 'toggleStatus'])->name('department.toggle-status');

    // พนักงาน (employee) — ข้อมูลจากตาราง emp
    Route::get('/employee', [EmpController::class, 'index'])->name('employee.index');
    Route::get('/employee/datatable', [EmpController::class, 'datatable'])->name('employee.datatable');
    Route::get('/employee/edit', [EmpController::class, 'edit'])->name('employee.edit');
    Route::post('/employee/store', [EmpController::class, 'store'])->name('employee.store');
    Route::post('/employee/delete', [EmpController::class, 'destroy'])->name('employee.delete');

    // จัดการสิทธิ์การใช้งาน (Role)
    Route::get('/role', [RoleController::class, 'index'])->name('role.index');
    Route::get('/role/datatable', [RoleController::class, 'datatable'])->name('role.datatable');
    Route::get('/role/edit', [RoleController::class, 'edit'])->name('role.edit');
    Route::post('/role/store', [RoleController::class, 'store'])->name('role.store');
    Route::post('/role/delete', [RoleController::class, 'destroy'])->name('role.delete');

    // เครื่องจักร (Machine)
    Route::get('/machine', [MachineController::class, 'index'])->name('machine.index');
    Route::get('/machine/datatable', [MachineController::class, 'datatable'])->name('machine.datatable');
    Route::get('/machine/edit', [MachineController::class, 'edit'])->name('machine.edit');
    Route::post('/machine/store', [MachineController::class, 'store'])->name('machine.store');
    Route::post('/machine/delete', [MachineController::class, 'destroy'])->name('machine.delete');

    // วิธีการผลิต (Production Method — master)
    Route::get('/prod-method', [ProdMethodController::class, 'index'])->name('prodmethod.index');
    Route::get('/prod-method/datatable', [ProdMethodController::class, 'datatable'])->name('prodmethod.datatable');
    Route::get('/prod-method/edit', [ProdMethodController::class, 'edit'])->name('prodmethod.edit');
    Route::post('/prod-method/store', [ProdMethodController::class, 'store'])->name('prodmethod.store');
    Route::post('/prod-method/toggle-status', [ProdMethodController::class, 'toggleStatus'])->name('prodmethod.toggle-status');

});
