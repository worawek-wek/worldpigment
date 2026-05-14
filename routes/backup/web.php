<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserTimeController;
use App\Http\Controllers\DarkModeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\MeterController;
use App\Http\Controllers\RenterController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\IncomeExpensesController;
use App\Http\Controllers\UserSettingController;
use App\Http\Controllers\WorkShiftController;
use App\Http\Controllers\WelfareController;
use App\Http\Controllers\ExportExcelController;
use App\Http\Controllers\AnnualHolidayController;
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

Route::controller(AuthController::class)->middleware('loggedin')->group(function() {
    Route::get('login', 'loginView')->name('login.index');
    Route::post('login', 'login')->name('login.check');
});
Route::controller(UserController::class)->middleware('loggedin')->group(function() {
    Route::get('register', 'register')->name('register.index');
    Route::post('register', 'store')->name('register.store');
});
Route::middleware('auth')->group(function() {
    Route::controller(AnalysisController::class)->group(function() {                    //////////////////////////
        Route::get('analysis/monthly-rent', 'monthly_rent')->name('analysis.monthly-rent');    //////////////////////////
        Route::get('analysis/income-expense', 'income_expense')->name('analysis.income-expense');    //////////////////////////
        Route::get('analysis/water', 'water')->name('analysis.water');    //////////////////////////
        Route::get('analysis/elect', 'elect')->name('analysis.elect');    //////////////////////////
        Route::get('analysis/meter', 'meter')->name('analysis.meter');    //////////////////////////
        Route::get('analysis/tenants', 'tenants')->name('analysis.tenants');    //////////////////////////
    });

    Route::controller(ReportController::class)->group(function() {                    //////////////////////////
        Route::get('report/view-overview', 'view_overview')->name('report.view_overview');    //////////////////////////
        Route::get('report/rent-bill', 'rent_bill')->name('report.rent_bill');    //////////////////////////
        Route::get('report/move-in', 'move_in')->name('report.move_in');    //////////////////////////
        Route::get('report/move-out', 'move_out')->name('report.move_out');    //////////////////////////
        Route::get('report/bad-debt', 'badDebt')->name('report.bad_debt');    //////////////////////////
        Route::get('report/monthly-booking', 'monthly_booking')->name('report.monthly_booking');    //////////////////////////
    });
    Route::controller(SettingController::class)->group(function() {                    //////////////////////////

        Route::get('setting/fine', 'fine')->name('setting.fine');    //////////////////////////
        Route::get('setting/fine/datatable', 'fine_datatable')->name('setting.fine-datatable');    //////////////////////////
        Route::get('setting/fine/{id}', 'fine_edit')->name('setting.fine-edit');    //////////////////////////
        Route::post('user/insert_user_has_branch', 'insert_user_has_branch')->name('user.insert-user-has-branch');    //////////////////////////
        Route::post('user/change-position/{user_has_branch_id}', 'change_position')->name('user.insert-user-has-branch');    //////////////////////////
        Route::delete('setting/user/{id}', 'delete_user_in_branch')->name('registration.delete-user-in-branch');    //////////////////////////
        Route::post('setting/fine/update/{id}', 'fine_update')->name('setting.fine-update');    //////////////////////////

        Route::get('setting/manage-bill', 'manage_bill')->name('setting.manage_bill');    //////////////////////////
        Route::get('setting/rental-contract', 'rental_contract')->name('setting.rental_contract');    //////////////////////////
        Route::get('setting/dorm-info', 'dorm_info')->name('setting.dorm_info');    //////////////////////////
        Route::post('setting/dorm-info/update_branch', 'update_branch')->name('setting.update_branch');    //////////////////////////
        Route::get('setting/room-rent', 'room_rent')->name('setting.room_rent');    //////////////////////////
        Route::get('setting/room-rent/datatable', 'room_rent_datatable')->name('setting.room-rent-datatable');    //////////////////////////
        Route::get('setting/room-rent/{id}', 'room_rent_edit')->name('setting.room-rent-edit');    //////////////////////////
        Route::post('setting/room-rent/update/{id}', 'room_rent_update')->name('setting.room-rent-update');    //////////////////////////

        Route::get('setting/room-layout', 'room_layout')->name('setting.room_layout');    //////////////////////////
        Route::post('insert_all', 'insert_all')->name('setting.insert_all');    //////////////////////////
        ////////////////
        Route::get('setting/room-layout/building', 'room_layout_building')->name('setting.room_layout_building');    //////////////////////////
        Route::post('setting/room-layout/building', 'room_layout_building_insert')->name('setting.room_layout_building_insert');    //////////////////////////
        Route::delete('setting/room-layout/building/{id}', 'room_layout_building_delete')->name('setting.room_layout_building_delete');    //////////////////////////
        Route::get('setting/room-layout/floor/{building_id}', 'room_layout_floor')->name('setting.room_layout_floor');    //////////////////////////
        Route::post('setting/room-layout/floor', 'room_layout_floor_insert')->name('setting.room_layout_floor_insert');    //////////////////////////
        Route::delete('setting/room-layout/floor/{id}', 'room_layout_floor_delete')->name('setting.room_layout_floor_delete');    //////////////////////////
        Route::get('setting/room-layout/room/{floor_id}', 'room_layout_room')->name('setting.room_layout_room');    //////////////////////////
        Route::post('setting/room-layout/room/all', 'room_layout_room_update')->name('setting.room_layout_room_update');    //////////////////////////
        Route::post('setting/room-layout/room', 'room_layout_room_insert')->name('setting.room_layout_room_insert');    //////////////////////////
        Route::delete('setting/room-layout/room/{id}', 'room_layout_room_delete')->name('setting.room_layout_room_delete');    //////////////////////////
        ////////////////
        Route::get('setting/water-electric-bill', 'water_electric_bill')->name('setting.water_electric_bill');    //////////////////////////
        Route::get('setting/water-electric-bill/datatable', 'water_electric_bill_datatable')->name('setting.water_electric_bill-datatable');    //////////////////////////
        Route::get('setting/water-bill/{id}', 'water_bill_edit')->name('setting.water-bill-edit');    //////////////////////////
        Route::post('setting/water-bill/update/{id}', 'water_bill_update')->name('setting.water-bill-update');    //////////////////////////
        Route::get('setting/electric-bill/{id}', 'electric_bill_edit')->name('setting.electric-bill-edit');    //////////////////////////
        Route::post('setting/electric-bill/update/{id}', 'electric_bill_update')->name('setting.water-electric-bill-update');    //////////////////////////

        Route::get('setting/service-discount', 'service_discount')->name('setting.service_discount');    //////////////////////////
        
        Route::get('setting/service/datatable', 'service_datatable')->name('setting.service-datatable');    //////////////////////////
        Route::get('setting/service/get_service', 'get_service')->name('setting.service-get_service');    //////////////////////////
        Route::get('setting/service/delete_service', 'delete_service')->name('setting.service-delete_service');    //////////////////////////
        Route::post('setting/service/insert', 'service_insert')->name('setting.service-insert');    //////////////////////////
        Route::post('setting/service/update/{id}', 'service_update')->name('setting.service-update');    //////////////////////////
        Route::get('setting/service/room/{id}', 'service_room')->name('setting.service-room');    //////////////////////////
        Route::post('setting/service/room/update', 'service_room_update')->name('setting.service-room-update');    //////////////////////////
        Route::delete('setting/service/delete_on_room', 'service_on_room_delete')->name('setting.service-on-room-delete');    //////////////////////////
        Route::delete('setting/service/delete/{id}', 'service_delete')->name('setting.service-delete');    //////////////////////////

        Route::get('setting/discount/datatable', 'discount_datatable')->name('setting.discount-datatable');    //////////////////////////
        Route::get('setting/discount/get_discount', 'get_discount')->name('setting.discount-get_discount');    //////////////////////////
        Route::get('setting/discount/delete_discount', 'delete_discount')->name('setting.discount-delete_discount');    //////////////////////////
        Route::post('setting/discount/insert', 'discount_insert')->name('setting.discount-insert');    //////////////////////////
        Route::post('setting/discount/update/{id}', 'discount_update')->name('setting.discount-update');    //////////////////////////
        Route::get('setting/discount/room/{id}', 'discount_room')->name('setting.discount-room');    //////////////////////////
        Route::post('setting/discount/room/update', 'discount_room_update')->name('setting.discount-room-update');    //////////////////////////
        Route::delete('setting/discount/delete_on_room', 'discount_on_room_delete')->name('setting.discount-on-room-delete');    //////////////////////////
        Route::delete('setting/discount/delete/{id}', 'discount_delete')->name('setting.discount-delete');    //////////////////////////
        ////////////////
        Route::get('setting/bank', 'bank')->name('setting.bank');    //////////////////////////
        Route::get('setting/bank/datatable', 'bank_datatable')->name('setting.bank-datatable');    //////////////////////////
        Route::post('setting/bank/insert', 'bank_insert')->name('setting.bank-insert');    //////////////////////////
        Route::get('setting/bank/{id}', 'bank_edit')->name('setting.bank-edit');    //////////////////////////
        Route::post('setting/bank/update/{id}', 'bank_update')->name('setting.bank-update');    //////////////////////////
        Route::delete('setting/bank/{id}', 'bank_delete')->name('setting.bank-delete');    //////////////////////////
        ////////////////
        Route::get('setting/company', 'company')->name('setting.company');    //////////////////////////
        Route::get('setting/company/datatable', 'company_datatable')->name('setting.company-datatable');    //////////////////////////
        Route::post('setting/company/insert', 'company_insert')->name('setting.company-insert');    //////////////////////////
        Route::get('setting/company/{id}', 'company_edit')->name('setting.company-edit');    //////////////////////////
        Route::post('setting/company/update/{id}', 'company_update')->name('setting.company-update');    //////////////////////////
        Route::delete('setting/company/{id}', 'company_delete')->name('setting.company-delete');    //////////////////////////
        ////////////////
        // Route::get('setting/company', 'company')->name('setting.company');    //////////////////////////
        // Route::get('setting/company/datatable', 'company_datatable')->name('setting.company-datatable');    //////////////////////////
        // Route::post('setting/company/insert', 'company_insert')->name('setting.company-insert');    //////////////////////////
        // Route::get('setting/company/{id}', 'company_edit')->name('setting.company-edit');    //////////////////////////
        // Route::post('setting/company/update/{id}', 'company_update')->name('setting.company-update');    //////////////////////////
        // Route::delete('setting/company/{id}', 'company_delete')->name('setting.company-delete');    //////////////////////////
        // ////////////////
    });
    Route::controller(DashboardController::class)->group(function() {                    //////////////////////////
        Route::get('dashboard', 'index')->name('dashboard');    //////////////////////////
        Route::get('dashboard/datatable', 'datatable')->name('room.datatable');    //////////////////////////
        Route::get('dashboard/overdue', 'overdue')->name('dashboard.overdue');    //////////////////////////
        Route::get('dashboard/overdue/{id}', 'invoice')->name('dashboard.invoice');    //////////////////////////
        Route::get('dashboard/{branch_id}', 'index')->name('dashboard');    //////////////////////////
    });
    Route::controller(PDFController::class)->group(function() {                    //////////////////////////
        // Route::get('pdf', 'index')->name('dashboard');    //////////////////////////
        Route::get('pdf/receipt/{receipt_id}', 'receipt')->name('pdf.receipt');    //////////////////////////
        Route::get('pdf/invoice/{invoice_id}', 'invoice')->name('pdf.invoice');    //////////////////////////
        Route::get('pdf/invoice-many/{invoice_id}', 'invoice_many')->name('pdf.invoice_many');    //////////////////////////
        Route::get('pdf/invoice-bill-all/{invoice_id}', 'invoice_bill')->name('pdf.invoice_bill');    //////////////////////////
    });
    Route::controller(RoomController::class)->group(function() {                    //////////////////////////
        Route::get('room', 'index')->name('room');    //////////////////////////
        Route::get('room/reserve', 'reserve_form')->name('reserve');    //////////////////////////
        Route::get('room/get-deposit', 'get_deposit')->name('get-deposit');    //////////////////////////
        Route::get('room/get-reservation', 'get_reservation')->name('get-reservation');    //////////////////////////
        Route::post('room', 'store')->name('insert');    //////////////////////////
        Route::post('room/insert_contract', 'insert_contract')->name('insert_contract');    //////////////////////////
        Route::post('room/receipt', 'insert_receipt')->name('insert_receipt');    //////////////////////////
        Route::post('room/update_contract/{id}', 'update_contract')->name('update_contract');    //////////////////////////
        Route::get('room/get-room-rental-contract/{id}', 'get_room_rental_contract')->name('get_room_rental_contract');    //////////////////////////
        Route::get('room/get-room-rental-reservation/{id}', 'get_room_rental_reservation')->name('get_room_rental_reservation');    //////////////////////////
        Route::get('room/get-room-detail-contract/{id}', 'get_room_detail_contract')->name('get_room_detail_contract');    //////////////////////////
        Route::get('room/get-room-form-contract/{id}', 'get_room_form_contract')->name('get_room_form_contract');    //////////////////////////
        Route::get('room/get-bill/{id}/{month}', 'get_bill')->name('get_bill');    //////////////////////////
        Route::get('get-districts/{id}', 'get_districts')->name('get-districts');    //////////////////////////
        Route::get('get-subdistricts/{id}', 'get_subdistricts')->name('get-subdistricts');    //////////////////////////
        Route::get('get-zipcode/{id}', 'get_zipcode')->name('get-zipcode');    //////////////////////////
        Route::get('get-floors/{id}', 'get_floors')->name('get-floors');    //////////////////////////
        Route::get('room/selected', 'selected')->name('room-selected');    //////////////////////////
        Route::get('room/datatable', 'datatable')->name('room.datatable');    //////////////////////////
        Route::get('room/summary', 'room_summary')->name('room.summary');    //////////////////////////
        Route::post('room/change_room/{old}/{new}', 'change_room')->name('room.change_room');    //////////////////////////
        Route::post('room/insert_renter', 'insert_renter')->name('room.insert_renter');    //////////////////////////
        Route::get('room/{id}', 'show')->name('show');    //////////////////////////
        Route::get('room/asset/{room_id}/{asset_id}', 'get_asset')->name('room.get_asset');    //////////////////////////
        Route::post('room/asset/update_asset', 'update_asset')->name('room.update_asset');    //////////////////////////

    });
    Route::controller(MeterController::class)->group(function() {                   //////////////////////////
        Route::get('meter', 'index')->name('meter');    //////////////////////////
        Route::get('meter/water/datatable', 'water_datatable')->name('meter.water-datatable');    //////////////////////////
        Route::post('meter/water_unit', 'water_unit_update')->name('meter.water-unit_update');    //////////////////////////
        Route::get('meter/electricity/datatable', 'electricity_datatable')->name('meter.electricity-datatable');    //////////////////////////
    });
    Route::controller(RenterController::class)->group(function() {                   //////////////////////////
        Route::get('renter', 'index')->name('renter');    //////////////////////////
    });
    Route::controller(VehicleController::class)->group(function() {                   //////////////////////////
        Route::get('vehicle', 'index')->name('vehicle');    //////////////////////////
        Route::get('vehicle/current/datatable', 'current_datatable')->name('vehicle.current-datatable');    //////////////////////////
        Route::get('vehicle/old/datatable', 'old_datatable')->name('vehicle.old-datatable');    //////////////////////////
        Route::get('vehicle/current/export/excel', 'current_export_excel')->name('vehicle.current-export-excel');    //////////////////////////
        Route::get('vehicle/old/export/excel', 'old_export_excel')->name('vehicle.old-export-excel');    //////////////////////////
        
    });
    Route::controller(UserController::class)->group(function() {                   //////////////////////////
        Route::get('user', 'index')->name('user');    //////////////////////////
        Route::get('user/datatable', 'datatable')->name('user.datatable');    //////////////////////////
        Route::get('user/check-user/{email}', 'check_user')->name('user.check-user');    //////////////////////////
        Route::get('user/{id}', 'edit')->name('user');    //////////////////////////
        Route::post('user', 'store')->name('user.insert');    //////////////////////////
        Route::post('user/{id}', 'update')->name('user.update');    //////////////////////////
    });
    Route::controller(AuditController::class)->group(function() {                   //////////////////////////
        Route::get('audit', 'index')->name('audit');    //////////////////////////
        
        Route::get('audit/invoice/datatable', 'invoice_datatable')->name('audit.invoice-datatable');    //////////////////////////
        Route::get('audit/receipt/datatable', 'receipt_datatable')->name('audit.receipt-datatable');    //////////////////////////
        Route::get('audit/invoice/export/excel', 'invoice_export_excel')->name('audit.current-export-excel');    //////////////////////////
        Route::get('audit/receipt/export/excel', 'receipt_export_excel')->name('audit.receipt-export-excel');    //////////////////////////
        
        ////////////////
    });
    Route::controller(BillController::class)->group(function() {                    //////////////////////////
        Route::get('bill', 'index')->name('bill');    //////////////////////////
        Route::get('bill/summary', 'bill_summary')->name('bill.summary');    //////////////////////////
        Route::get('bill/datatable', 'datatable')->name('bill.datatable');    //////////////////////////
        Route::get('bill/waiting-for-confirmation', 'waiting_for_confirmation')->name('bill.waiting-for-confirmation');    //////////////////////////
        Route::get('bill/export/excel', 'export_excel')->name('bill.export-excel');    //////////////////////////
        Route::post('bill/incomplete_update', 'incomplete_update')->name('bill.incomplete_update');    //////////////////////////
        Route::post('bill/payment_bill', 'payment_bill')->name('bill.payment_bill');    //////////////////////////
        Route::get('bill/{id}', 'invoice')->name('bill.invoice');    //////////////////////////
        Route::post('bill/change_status_bill/{id}', 'change_status_bill')->name('bill.change-status-bill');    //////////////////////////
    });
    Route::controller(ApartmentController::class)->group(function() {                    //////////////////////////
        Route::get('apartment', 'index')->name('apartment');    //////////////////////////
        Route::get('apartment/add', 'add')->name('apartment.add');    //////////////////////////
        Route::post('apartment/add', 'store')->name('apartment.insert');    //////////////////////////
        Route::get('apartment/manage', 'manage')->name('apartment.manage');    //////////////////////////
    });
    Route::controller(BuildingController::class)->group(function() {                    //////////////////////////
        Route::get('building', 'index')->name('building');    //////////////////////////
        Route::get('building/add', 'add')->name('building.add');    //////////////////////////
        Route::post('building/add', 'store')->name('building.insert');    //////////////////////////
        Route::get('building/manage', 'manage')->name('building.manage');    //////////////////////////
    });
    Route::controller(BranchController::class)->group(function() {                    //////////////////////////
        Route::get('branch', 'index')->name('branch');    //////////////////////////
        Route::get('branch/add', 'add')->name('branch.add');    //////////////////////////
        Route::post('branch/add', 'store')->name('branch.insert');    //////////////////////////
        Route::get('branch/manage', 'manage')->name('branch.manage');    //////////////////////////
    });
    Route::controller(IncomeExpensesController::class)->group(function() {                    //////////////////////////
        Route::get('income-expenses', 'index')->name('income-expenses');    //////////////////////////
        Route::get('income-expenses/summary', 'summary_IE')->name('income-expenses.summary');    //////////////////////////
        Route::post('income-expenses', 'store')->name('income-expenses.insert');    //////////////////////////
        Route::get('income-expenses/datatable', 'datatable')->name('income-expenses.datatable');    //////////////////////////
        Route::get('income-expenses/{id}', 'show')->name('income-expenses.show');    //////////////////////////
    });
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');

    // Route::post('dashboard/edit_news', [DashboardController::class, 'edit_news'])->name('dashboard.edit_news');    //////////////////////////
    // Route::controller(DashboardController::class)->group(function() {                    //////////////////////////
    //     Route::get('dashboard', 'index')->name('dashboard');    //////////////////////////
    //     Route::get('change_password', 'change_password_form')->name('change_password_form');    //////////////////////////
    // });

    // Route::get('profile',  [UserController::class, 'profile'])->name('profile');    //////////////////////////
    // Route::get('profile/{id}',  [UserController::class, 'profile'])->name('profile.id');    //////////////////////////
    // // Route::get('profile',  [UserController::class, 'profile'])->name('profile');    //////////////////////////

    // Route::get('user/datatable', [UserController::class, 'datatable'])->name('user.datatable');    //////////////////////////
    // Route::resource('user', UserController::class);                         //////////////////////////
    // Route::controller(UserController::class)->group(function() {     
    //     Route::post('edit_news/{id}', 'edit_news')->name('edit_news');    //////////////////////////
    //     Route::post('edit_news', 'edit_news')->name('edit_news');    //////////////////////////               //////////////////////////
    //     Route::post('change_password', 'change_password')->name('change_password');    //////////////////////////
    //     Route::get('check_password', 'check_password')->name('check_password');    //////////////////////////
    //     Route::post('user/import_excel_user', 'import_excel_user')->name('user.import_excel_user');    //////////////////////////
    //     Route::post('user/{id}', 'update')->name('user.update');    //////////////////////////
    //     Route::get('user-page', 'index')->name('user');    //////////////////////////
    // });

    Route::controller(ExportExcelController::class)->group(function() {                    //////////////////////////
        Route::get('export-excel/import-excel-user', 'user_detail')->name('export-excel.import_excel_user');    //////////////////////////
        Route::get('export-excel/import-excel-user-leave', 'user_leave')->name('export-excel.import_excel_user_leave');    //////////////////////////
        Route::get('export-excel-page', 'index')->name('export-excel');    //////////////////////////
    });


/////////////// Ajax ////////////////
Route::get('change_date_format/{date}', [UserController::class, 'ChangeDateFormat'])->name('change_date_format');    //////////////////////////

});
