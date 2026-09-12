<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderApprovalController;
use App\Http\Controllers\PriceApprovalController;

Route::prefix('order')->group(function () {

    // ─── หน้า + ตารางรายการ ──────────────────────────────────────────
    Route::get('/',          [OrderController::class, 'index'])->name('order.index');
    Route::get('/datatable', [OrderController::class, 'datatable'])->name('order.datatable');

    // ─── ฟอร์มบันทึกใบสั่งซื้อ (อ่านข้อมูลมาเติมฟอร์ม) ──────────────────
    // ใช้ query string ?orderno= เพราะเลขที่ใบสั่งเก่าบางตัวมีช่องว่าง/อักขระแปลก
    Route::get('/form', [OrderController::class, 'form'])->name('order.form');

    // ─── Lookup ที่ฟอร์มเรียกระหว่างกรอก ─────────────────────────────
    Route::get('/customer/{code}', [OrderController::class, 'customerLookup'])->name('order.customer_lookup');
    Route::get('/price-info',      [OrderController::class, 'priceInfo'])->name('order.price_info');
    Route::get('/next-orderno',    [OrderController::class, 'nextOrderno'])->name('order.next_orderno');
    Route::get('/item-lookup',     [OrderController::class, 'itemLookup'])->name('order.item_lookup');

    // ─── บันทึก (สร้างใหม่ / แก้ไข) ───────────────────────────────────
    Route::post('/save', [OrderController::class, 'save'])->name('order.save');

    // ปุ่ม "เพิ่มใบสั่งซื้อใหม่" ในฟอร์ม — สร้างใบสั่งซื้อทันที (12/09/2569)
    Route::post('/create', [OrderController::class, 'create'])->name('order.create');

    // ─── ฟอร์มขออนุมัติราคาพิเศษ (MD) — ฟอร์มลูกของเมนูนี้ ─────────────
    Route::prefix('price-approval')->group(function () {
        Route::get('/items',           [PriceApprovalController::class, 'items'])->name('order.approval.items');
        // ใบที่ยังไม่อนุมัติทั้งหมด — ตัวเดินระเบียนบนหัวฟอร์ม (12/09/2569)
        Route::get('/pending',         [PriceApprovalController::class, 'pending'])->name('order.approval.pending');
        Route::get('/data',            [PriceApprovalController::class, 'data'])->name('order.approval.data');
        Route::get('/other-items',     [PriceApprovalController::class, 'otherItems'])->name('order.approval.other_items');
        // พิมพ์ประวัติการขออนุมัติราคาของเบอร์ที่กรอก — ทุกลูกค้า ทั้งอนุมัติและไม่อนุมัติ (12/09/2569)
        Route::get('/other-items-pdf', [PriceApprovalController::class, 'otherItemsPdf'])->name('order.approval.other_items_pdf');
        Route::get('/other-customers', [PriceApprovalController::class, 'otherCustomers'])->name('order.approval.other_customers');
        Route::get('/history',         [PriceApprovalController::class, 'history'])->name('order.approval.history');
        // พิมพ์ประวัติของเบอร์นี้เป็น PDF ตามผังรายงานกระดาษเดิม (12/09/2569)
        Route::get('/history-pdf',     [PriceApprovalController::class, 'historyPdf'])->name('order.approval.history_pdf');
        Route::get('/resin-history',   [PriceApprovalController::class, 'resinHistory'])->name('order.approval.resin_history');
        // พิมพ์ประวัติราคาเม็ด CP ของเบอร์นี้ (ทุกลูกค้า) เป็น PDF (12/09/2569)
        Route::get('/resin-history-pdf', [PriceApprovalController::class, 'resinHistoryPdf'])->name('order.approval.resin_history_pdf');

        // โหมดอนุมัติ (MD) — ปลดล็อกด้วยรหัสผ่านก่อนจึงจะติ๊ก "อนุมัติ" ได้
        Route::get('/md-state', [PriceApprovalController::class, 'mdState'])->name('order.approval.md_state');
        Route::post('/unlock',  [PriceApprovalController::class, 'unlock'])->name('order.approval.unlock');
        Route::post('/lock',    [PriceApprovalController::class, 'lock'])->name('order.approval.lock');

        Route::post('/save',   [PriceApprovalController::class, 'save'])->name('order.approval.save');
        Route::post('/delete', [PriceApprovalController::class, 'destroy'])->name('order.approval.delete');
    });

    // ─── ฟอร์มอนุมัติใบสั่งซื้อ (morderAPPV) ────────────────────────
    Route::prefix('order-approval')->group(function () {
        Route::get('/queue',  [OrderApprovalController::class, 'queue'])->name('order.orderappv.queue');
        Route::get('/record', [OrderApprovalController::class, 'record'])->name('order.orderappv.record');

        // กดอนุมัติ / ยกเลิกอนุมัติ — เขียน morder.appv + morder.appvDT
        Route::post('/approve', [OrderApprovalController::class, 'approve'])->name('order.orderappv.approve');
    });

});
