<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\EquiptrackController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DarkModeController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExportExcelController;
use App\Http\Controllers\ColorSchemeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/clc', function() {

	Artisan::call('cache:clear');
	Artisan::call('config:clear');
	Artisan::call('config:cache');
	Artisan::call('view:clear');
    Artisan::call('route:clear');
  
	return "Cleared!";
  
  });

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('branch.manage'); // หรือหน้า dashboard ที่ต้องการ
    }
    return redirect()->route('login.index');
});

Route::get('dark-mode-switcher', [DarkModeController::class, 'switch'])->name('dark-mode-switcher');
Route::get('color-scheme-switcher/{color_scheme}', [ColorSchemeController::class, 'switch'])->name('color-scheme-switcher');
Route::get('user/check-have-email', [UserController::class, 'check_have_email'])->name('user.check-have-email');

Route::controller(AuthController::class)->middleware('loggedin')->group(function() {
    Route::get('login', 'loginView')->name('login.index');
    Route::post('login', 'login')->name('login.check');
});
Route::controller(UserController::class)->middleware('loggedin')->group(function() {
    Route::get('register', 'register')->name('register.index');
    // Route::post('register', 'store')->name('register.store');
    Route::post('user', 'store')->name('user.insert');    //////////////////////////
});
Route::middleware('auth')->group(function() {
    Route::controller(Controller::class)->group(function() {                    //////////////////////////
        Route::get('get-summary-menu', 'get_summary_menu')->name('get-summary-menu');    //////////////////////////
    });

    Route::controller(ReportController::class)->group(function() {                    //////////////////////////
        Route::get('report/view-overview', 'view_overview')->name('report.view_overview');    //////////////////////////
        Route::get('report/borrow', 'borrow')->name('report.borrow');    //////////////////////////
        Route::get('report/borrow/datatable', 'borrow_datatable')->name('report.borrow-datatable');    //////////////////////////
        Route::get('report/borrow/summary', 'borrow_summary')->name('report.borrow-summary');    //////////////////////////
        Route::get('report/return', 'return')->name('report.return');    //////////////////////////
        Route::get('report/return/datatable', 'return_datatable')->name('report.return-datatable');    //////////////////////////
        Route::get('report/return/summary', 'return_summary')->name('report.return-summary');    //////////////////////////
        Route::get('report/move-in', 'move_in')->name('report.move_in');    //////////////////////////
        Route::get('report/move-in/datatable', 'move_in_datatable')->name('report.move-in-datatable');    //////////////////////////
        Route::get('report/income', 'income')->name('report.income');    //////////////////////////
        Route::get('report/income/datatable', 'income_datatable')->name('report.income-datatable');    //////////////////////////
        Route::get('report/tax-invoice', 'tax_invoice')->name('report.tax-invoice');    //////////////////////////
        Route::get('report/tax-invoice/datatable', 'tax_invoice_datatable')->name('report.tax-invoice-datatable');    //////////////////////////
        Route::get('report/tax-invoice/get-invoice-by-room/{roomId}', 'get_invoice_by_room')->name('report.tax-invoice-get-invoice-by-room');    //////////////////////////
        Route::get('report/move-out/datatable', 'move_out_datatable')->name('report.move-out-datatable');    //////////////////////////
        Route::get('report/move-out', 'move_out')->name('report.move_out');    //////////////////////////
        Route::get('report/bad-debt', 'badDebt')->name('report.bad_debt');    //////////////////////////
        Route::get('report/bad-debt/datatable', 'badDebt_datatable')->name('report.bad-debt-datatable');    //////////////////////////
        Route::get('report/bad-debt/export/excel', 'badDebt_export_excel')->name('room.export_excel');    //////////////////////////
        Route::get('report/monthly-booking', 'monthly_booking')->name('report.monthly_booking');    //////////////////////////
        Route::get('report/monthly-booking/datatable', 'monthly_booking_datatable')->name('report.monthly_booking-datatable');    //////////////////////////
        Route::get('report/monthly-booking/excel', 'monthly_booking_excel')->name('report.monthly_booking-excel');    //////////////////////////
        Route::get('report/view-overview/datatable', 'view_overview_datatable')->name('report.view-overview-datatable');    //////////////////////////
        Route::get('report/view-overview/excel', 'view_overview_excel')->name('report.view-overview-excel');    //////////////////////////
        Route::get('report/rent-bill/excel', 'rent_bill_excel')->name('report.rent-bill-excel');    //////////////////////////
        Route::get('report/move-in/excel', 'move_in_excel')->name('report.move-in-excel');    //////////////////////////
    });
    Route::controller(CategoryController::class)->group(function() {                    //////////////////////////
        ////////////////
        Route::get('category', 'index');    //////////////////////////
        Route::get('category/color-matching', 'color_matching');    //////////////////////////
        Route::get('category/order', 'order');    //////////////////////////
        Route::get('category/production-planning', 'production_planning');    //////////////////////////
        Route::get('category/customer', 'customer');    //////////////////////////
        Route::get('category/report', 'report');    //////////////////////////
        Route::get('category/permission', 'permission');    //////////////////////////
        





        Route::get('category/datatable', 'datatable')->name('category-datatable');    //////////////////////////
        Route::post('category/insert', 'insert')->name('category-insert');    //////////////////////////
        Route::get('category/import/{id}', 'get_form_import')->name('category-edit');    //////////////////////////
        Route::get('category/history/{id}', 'get_history')->name('category-history');    //////////////////////////
        Route::get('category/{id}', 'edit')->name('category-edit');    //////////////////////////
        Route::post('category/update/{id}', 'update')->name('category-update');    //////////////////////////
        Route::post('category/update_stock/{id}', 'update_stock')->name('category-update_stock');    //////////////////////////
        Route::delete('category/{id}', 'delete')->name('category-delete');    //////////////////////////
        ////////////////
    });
    Route::controller(EquipmentController::class)->group(function() {                    ////////////////////////// 
        ////////////////
        Route::get('equipments', 'index')->name('equipments');    //////////////////////////
        Route::get('equipments/datatable', 'datatable')->name('equipments-datatable');    //////////////////////////
        Route::post('equipments/insert', 'insert')->name('equipments-insert');    //////////////////////////
        Route::get('equipments/import/{id}', 'get_form_import')->name('equipments-edit');    //////////////////////////
        Route::get('equipments/history/{id}', 'get_history')->name('equipments-history');    //////////////////////////
        Route::get('equipments/{id}', 'edit')->name('equipments-edit');    //////////////////////////
        Route::post('equipments/update/{id}', 'update')->name('equipments-update');    //////////////////////////
        Route::post('equipments/update_stock/{id}', 'update_stock')->name('equipments-update_stock');    //////////////////////////
        Route::delete('equipments/{id}', 'delete')->name('equipments-delete');    //////////////////////////
        ////////////////
    });
    Route::controller(EquiptrackController::class)->group(function() {                    ////////////////////////// 
        ////////////////
        Route::get('equiptrack', 'index')->name('equiptrack');    //////////////////////////
        Route::get('equiptrack/datatable', 'datatable')->name('equiptrack-datatable');    //////////////////////////
        Route::post('equiptrack/insert', 'insert')->name('equiptrack-insert');    //////////////////////////
        Route::get('equiptrack/import/{id}', 'get_form_import')->name('equiptrack-edit');    //////////////////////////
        Route::get('equiptrack/history/{id}', 'get_history')->name('equiptrack-history');    //////////////////////////
        Route::get('equiptrack/{id}', 'edit')->name('equiptrack-edit');    //////////////////////////
        Route::post('equiptrack/update/{id}', 'update')->name('equiptrack-update');    //////////////////////////
        Route::post('equiptrack/update_stock/{id}', 'update_stock')->name('equiptrack-update_stock');    //////////////////////////
        Route::delete('equiptrack/{id}', 'delete')->name('equiptrack-delete');    //////////////////////////
        ////////////////
    });
    Route::controller(PDFController::class)->group(function() {                    //////////////////////////
        // Route::get('pdf', 'index')->name('dashboard');    ////////////////////////// 
        Route::get('pdf/receipt_all/{receipt_id}', 'receipt_all')->name('pdf.receipt');    //////////////////////////
        Route::get('pdf/receipt_all_download/{receipt_id}', 'receipt_all_download')->name('pdf.receipt');    //////////////////////////
        Route::get('pdf/invoice_all/{invoice_id}', 'invoice_all')->name('pdf.invoice');    //////////////////////////
        Route::get('pdf/receipt/{receipt_id}', 'receipt')->name('pdf.receipt');    //////////////////////////
        Route::get('pdf/invoice/{invoice_id}', 'invoice')->name('pdf.invoice');    //////////////////////////
        Route::get('pdf/invoice/move-out/not-yet-recorded/{invoice_id}', 'move_out_not_yet_recorded')->name('pdf.invoice');    //////////////////////////
        Route::get('pdf/overdue/invoice/{room_id}', 'overdue_invoice')->name('pdf.overdue_invoice');    //////////////////////////
        Route::get('pdf/overdue/all', 'overdue_all')->name('pdf.overdue-all');    //////////////////////////
        Route::get('pdf/invoice-many/{invoice_id}', 'invoice_many')->name('pdf.invoice_many');    //////////////////////////
        Route::get('pdf/invoice-bill-all/{invoice_id}', 'invoice_bill')->name('pdf.invoice_bill');    //////////////////////////
        Route::get('pdf/income-expenses-all/{invoice_id}', 'income_expenses_all')->name('pdf.income_expenses_all');    //////////////////////////
        Route::get('pdf/checkCarPDF/{status}', 'checkCarPDF')->name('pdf.checkCarPDF');
        Route::get('pdf/report/monthly-booking', 'report_monthly_booking')->name('pdf.report-monthly-booking');
        Route::get('pdf/report/view-overview', 'report_view_overview')->name('pdf.report-view-overview');
        Route::get('pdf/report/rent-bill', 'report_rent_bill')->name('pdf.report-rent-bill');
        Route::get('pdf/report/move-in', 'report_move_in')->name('pdf.report-move-in');
        Route::get('pdf/report/bad-debt', 'report_bad_debt')->name('pdf.report-bad-debt');
        Route::get('pdf/report/income', 'report_income')->name('pdf.report-income');
        Route::get('pdf/report/tax-invoice', 'report_tax_invoice')->name('pdf.report-tax-invoice');
    });
    Route::controller(UserController::class)->group(function() {                   //////////////////////////
        Route::get('user', 'index')->name('user');    //////////////////////////
        Route::get('user/datatable', 'datatable')->name('user.datatable');    //////////////////////////
        Route::get('user/check-user/{email}', 'check_user')->name('user.check-user');    //////////////////////////
        Route::get('user/{id}', 'edit')->name('user');    //////////////////////////
        // Route::post('user/{id}', 'update')->name('user.update');    //////////////////////////
        Route::get('user/export/excel', 'exportExcel')->name('pdf.userPdf'); 
    });
    
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');

    Route::controller(ExportExcelController::class)->group(function() {                    //////////////////////////
        Route::get('export-excel/import-excel-user', 'user_detail')->name('export-excel.import_excel_user');    //////////////////////////
        Route::get('export-excel/import-excel-user-leave', 'user_leave')->name('export-excel.import_excel_user_leave');    //////////////////////////
        Route::get('export-excel-page', 'index')->name('export-excel');    //////////////////////////
    });


/////////////// Ajax ////////////////
Route::get('change_date_format/{date}', [UserController::class, 'ChangeDateFormat'])->name('change_date_format');    //////////////////////////

});
