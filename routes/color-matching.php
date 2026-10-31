<?php

use App\Http\Controllers\ColorMatchingController;

Route::prefix('color-matching')->group(function () {

    // ─── หน้า + datatable + summary ──────────────────────────────────
    Route::get('/',          [ColorMatchingController::class, 'index'])->name('color_matching.index');
    Route::get('/datatable', [ColorMatchingController::class, 'datatable'])->name('color_matching.datatable');
    Route::get('/summary',   [ColorMatchingController::class, 'getSummary'])->name('color_matching.summary');

    // ─── Lookup ลูกค้า (ต้องอยู่ก่อน /{sendno} เพราะ .+ จับ slash ได้) ───
    Route::get('/customer/{code}',     [ColorMatchingController::class, 'customerLookup'])->name('color_matching.customer_lookup');

    // ─── Lookup record CM จาก SendNo (ฟอร์มใบส่ง ต.ย. → เลขอ้างอิง) ───
    // ใช้ query string (?sendno=) เพราะ SendNo มี "/" (เช่น 69/0143) — Apache บล็อก %2F ใน path (404)
    Route::get('/ref',                 [ColorMatchingController::class, 'lookupBySendNo'])->name('color_matching.ref');

    // ─── ตรวจ Lot No. ซ้ำ (ฟอร์มใบส่ง ต.ย., 29/10/2569) — query string เพราะ Lot มี "/" ───
    Route::get('/check-lot',           [ColorMatchingController::class, 'checkLot'])->name('color_matching.check_lot');

    // ─── รายงาน PDF "ใบส่งตัวอย่างเทียบสี" ตามรหัสลูกค้า + ช่วงวันที่ (29/10/2569) ───
    Route::get('/sample-report-pdf',   [ColorMatchingController::class, 'sampleReportPdf'])->name('color_matching.sample_report_pdf');
    // รายงานการเบิกตัวอย่าง (ตามรหัสลูกค้า) — ช่องกรอกชุดเดียวกัน (29/10/2569)
    Route::get('/sample-withdraw-pdf', [ColorMatchingController::class, 'sampleWithdrawPdf'])->name('color_matching.sample_withdraw_pdf');
    // รายงานการเบิกตัวอย่าง (ตามพนักงานขาย) — รหัส Sale + ช่วงวันที่ (29/10/2569)
    Route::get('/sale-withdraw-pdf',   [ColorMatchingController::class, 'saleWithdrawPdf'])->name('color_matching.sale_withdraw_pdf');
    // บันทึกใบนำส่งตัวอย่างเทียบสี — ช่วงวันที่ส่งเทียบสี (29/10/2569)
    Route::get('/send-record-pdf',     [ColorMatchingController::class, 'sendRecordPdf'])->name('color_matching.send_record_pdf');
    // เทียบสีค้างสะสม จนถึงวันที่ (29/10/2569)
    Route::get('/pending-pdf',         [ColorMatchingController::class, 'pendingPdf'])->name('color_matching.pending_pdf');
    // รายงานเทียบสี ส่งตัวอย่าง ระหว่างวันที่ (เรียงตามรหัสเซลล์, ลูกค้า) (29/10/2569)
    Route::get('/match-report-pdf',    [ColorMatchingController::class, 'matchReportPdf'])->name('color_matching.match_report_pdf');
    // บันทึกติดตามผลการเทียบสี แผนก MK ตามช่วงวันที่เบิกตัวอย่าง (30/10/2569)
    Route::get('/follow-up-pdf',       [ColorMatchingController::class, 'followUpPdf'])->name('color_matching.follow_up_pdf');

    // ─── รายละเอียด (อ่านอย่างเดียว) — อ้างอิงด้วย id ───
    Route::get('/detail/{id}',         [ColorMatchingController::class, 'detail'])->whereNumber('id')->name('color_matching.detail');

    // ─── CRUD (อ้างอิงด้วย id auto-increment) ────────────────────────
    Route::post('/insert',             [ColorMatchingController::class, 'insert'])->name('color_matching.insert');
    Route::post('/update/{id}',        [ColorMatchingController::class, 'update'])->whereNumber('id')->name('color_matching.update');
    Route::post('/delete/{id}',        [ColorMatchingController::class, 'destroy'])->whereNumber('id')->name('color_matching.delete');
    // ผลการทดสอบตัวอย่างสี ของใบส่ง ต.ย. (10/08/2569) — แตะเฉพาะ TyResp/Resp/Respdate
    Route::post('/result/{id}',        [ColorMatchingController::class, 'saveResult'])->whereNumber('id')->name('color_matching.result');
    Route::get('/{id}',                [ColorMatchingController::class, 'edit'])->whereNumber('id')->name('color_matching.edit');

});
