<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleOrderController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('auth')->group(function() {
    // Dashboard
    Route::controller(HomeController::class)->group(function() {
        Route::get('/', 'index')->name('dashboard');
    });
    // View activty log data    
    Route::get('/view-log/{log}', function(ActivityLog $log) {
        return $log->data ?? 'No Data Found';
    })->name('view_log');
    // Sale - Quotation/Sale Order
    Route::controller(SaleController::class)->group(function() {
        // Quotation
        Route::prefix('quotation')->name('quotation.')->group(function() {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::get('/edit/{sale}', 'edit')->name('edit');
            Route::get('/delete/{sale}', 'delete')->name('delete');
            Route::get('/pdf/{sale}', 'pdf')->name('pdf');
            Route::get('/to-sale-order', 'toSaleOrder')->name('to_sale_order');
            Route::get('/convert-to-sale-order', 'converToSaleOrder')->name('convert_to_sale_order');
        });
        // Sale Order
        Route::prefix('sale-order')->name('sale_order.')->group(function() {
            Route::get('/', 'indexSaleOrder')->name('index');
            Route::get('/get-data', 'getDataSaleOrder')->name('get_data');
            Route::get('/create', 'createSaleOrder')->name('create');
            Route::get('/edit/{sale}', 'editSaleOrder')->name('edit');
            Route::get('/delete/{sale}', 'delete')->name('delete');
            Route::get('/pdf/{sale}', 'pdfSaleOrder')->name('pdf');
            Route::get('/to-delivery-order', 'toDeliveryOrder')->name('to_delivery_order');
            Route::get('/convert-to-delivery-order', 'converToDeliveryOrder')->name('convert_to_delivery_order');
        });

        Route::prefix('sale')->name('sale.')->group(function() {
            Route::post('/upsert-quotation-details', 'upsertQuoDetails')->name('upsert_quo_details');
            Route::post('/upsert-product-details', 'upsertProDetails')->name('upsert_pro_details');
            Route::post('/upsert-remark', 'upsertRemark')->name('upsert_remark');
            Route::post('/upsert-payment-details', 'upsertPayDetails')->name('upsert_pay_details');
            Route::post('/upsert-delivery-schedule', 'upsertDelSchedule')->name('upsert_delivery_schedule'); 
        });

        // Delivery Order
        Route::prefix('delivery-order')->name('delivery_order.')->group(function() {
            Route::get('/', 'indexDeliveryOrder')->name('index');
            Route::get('/get-data', 'getDataDeliveryOrder')->name('get_data');
            Route::get('/to-invoice', 'toInvoice')->name('to_invoice');
            Route::get('/convert-to-invoice', 'convertToInvoice')->name('convert_to_invoice');
        });
        // Invoice
        Route::prefix('invoice')->name('invoice.')->group(function() {
            Route::get('/', 'indexInvoice')->name('index');
            Route::get('/get-data', 'getDataInvoice')->name('get_data');
        });
        
        Route::get('/download', 'download')->name('download');

        // Target
        Route::prefix('target')->name('target.')->group(function() {
            Route::get('/', 'indexTarget')->name('index');
            Route::get('/get-data', 'getDataTarget')->name('get_data');
            Route::get('/create', 'createTarget')->name('create');
            Route::post('/store', 'storeTarget')->name('store');
            Route::get('/edit/{target}', 'editTarget')->name('edit');
            Route::post('/update/{target}', 'updateTarget')->name('update');
        });
    });
    // Task
    Route::controller(TaskController::class)->prefix('task')->name('task.')->group(function() {
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/delete/{task}', 'delete')->name('delete');

        Route::prefix('driver')->name('driver.')->group(function() {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'driverStore')->name('store');
            Route::get('/view/{task}', 'view')->name('view');
            Route::get('/edit/{task}', 'edit')->name('edit');
            Route::post('/update/{task}', 'driverUpdate')->name('update');
        });
        Route::prefix('technician')->name('technician.')->group(function() {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'technicianStore')->name('store');
            Route::get('/view/{task}', 'view')->name('view');
            Route::get('/edit/{task}', 'edit')->name('edit');
            Route::post('/update/{task}', 'technicianUpdate')->name('update');
        });
        Route::prefix('sale')->name('sale.')->group(function() {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'saleStore')->name('store');
            Route::get('/view/{task}', 'view')->name('view');
            Route::get('/edit/{task}', 'edit')->name('edit');
            Route::post('/update/{task}', 'saleUpdate')->name('update');
        });
    });
    // Ticket
    Route::controller(TicketController::class)->prefix('ticket')->name('ticket.')->group(function() {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{ticket}', 'edit')->name('edit');
        Route::post('/update/{ticket}', 'update')->name('update');
        Route::get('/delete/{ticket}', 'delete')->name('delete');
    });
    // Customer
    Route::controller(CustomerController::class)->prefix('customer')->name('customer.')->group(function() {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{customer}', 'edit')->name('edit');
        Route::post('/update/{customer}', 'update')->name('update');
        Route::get('/delete/{customer}', 'delete')->name('delete');
    });
    // User Management
    Route::controller(UserController::class)->prefix('user-management')->name('user_management.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{user}', 'edit')->name('edit');
        Route::post('/update/{user}', 'update')->name('update');
        Route::get('/delete/{user}', 'delete')->name('delete');
    });
    // Role Management
    Route::controller(RoleController::class)->prefix('role-management')->name('role_management.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{role}', 'edit')->name('edit');
        Route::post('/update/{role}', 'update')->name('update');
        Route::get('/delete/{role}', 'delete')->name('delete');
    });
});


// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__.'/auth.php';
