<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarrantyPeriodController;
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

Route::get('/', function() {
    return redirect(route('login'));
});

Route::middleware('auth')->group(function() {
    // View activty log data    
    Route::get('/view-log/{log}', function(ActivityLog $log) {
        return $log->data ?? 'No Data Found';
    })->name('view_log');
    // Inventory
    Route::controller(InventoryController::class)->prefix('inventory-summary')->name('inventory_summary.')->group(function() { // Inventory Category
        Route::get('/', 'indexSummary')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
    });
    Route::controller(InventoryController::class)->prefix('inventory-category')->name('inventory_category.')->group(function() { // Inventory Category
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create');
        Route::get('/edit/{cat}', 'edit')->name('edit');
        Route::post('/upsert', 'upsert')->name('upsert');
        Route::get('/delete/{cat}', 'delete')->name('delete');
    });
    Route::controller(ProductController::class)->prefix('product')->name('product.')->group(function() { // Product
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create');
        Route::get('/edit/{product}', 'edit')->name('edit');
        Route::post('/upsert', 'upsert')->name('upsert');
        Route::post('/upsert-serial-no', 'upsertSerialNo')->name('upsert_serial_no');
        Route::get('/delete/{product}', 'delete')->name('delete');
        Route::get('/view/{product}', 'view')->name('view');
        Route::get('/view-get-data', 'viewGetData')->name('view_get_data');
    });
    Route::controller(ProductController::class)->prefix('raw-material')->name('raw_material.')->group(function() { // Raw Material
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create');
        Route::get('/edit/{product}', 'edit')->name('edit');
        Route::get('/delete/{product}', 'delete')->name('delete');
        Route::get('/view/{product}', 'view')->name('view');
        Route::get('/view-get-data', 'viewGetData')->name('view_get_data');
    });
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
    // Production
    Route::controller(ProductionController::class)->prefix('production')->name('production.')->group(function() {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create');
        Route::get('/edit/{production}', 'edit')->name('edit');
        Route::get('/view/{production}', 'view')->name('view');
        Route::get('/delete/{production}', 'delete')->name('delete');
        Route::post('/upsert/{production?}', 'upsert')->name('upsert');
        Route::post('/check-in-milestone', 'checkInMilestone')->name('check_in_milestone');
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
        Route::get('/edit/{customer}', 'edit')->name('edit');
        Route::get('/delete/{customer}', 'delete')->name('delete');
        Route::post('/upsert-info', 'upsertInfo')->name('upsert_info');
        Route::post('/upsert-location', 'upsertLocation')->name('upsert_location');
        Route::get('/get-location', 'getLocation')->name('get_location');
    });
    // Supplier
    Route::controller(SupplierController::class)->prefix('supplier')->name('supplier.')->group(function() {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create');
        Route::get('/edit/{supplier}', 'edit')->name('edit');
        Route::get('/delete/{supplier}', 'delete')->name('delete');
        Route::post('/upsert/{supplier?}', 'upsert')->name('upsert');
    });
    // Warranty Period
    Route::controller(WarrantyPeriodController::class)->prefix('warranty-period')->name('warranty_period.')->group(function() {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{warranty}', 'edit')->name('edit');
        Route::post('/update/{warranty}', 'update')->name('update');
        Route::get('/delete/{warranty}', 'delete')->name('delete');
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
