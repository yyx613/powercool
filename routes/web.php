<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\CreditTermController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtorTypeController;
use App\Http\Controllers\EInvoiceController;
use App\Http\Controllers\GRNController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryServiceHistoryController;
use App\Http\Controllers\MaterialUseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProjectTypeController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UOMController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarrantyPeriodController;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Platforms\LazadaController;
use App\Http\Controllers\Platforms\ShopeeController;
use App\Http\Controllers\Platforms\TiktokController;
use App\Http\Controllers\Platforms\WooCommerceController;
use App\Http\Controllers\PriorityController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\WarrantyController;

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

Route::get('/', function () {
    return redirect(route('login'));
});

Route::controller(CustomerController::class)->name('customer.')->group(function () {
    Route::get('/create-customer-link', 'createLink')->name('create_link');
});

// Change language
Route::get('/change-language/{lang}', function($locale) {
    Session::put('selected_lang', $locale);

    App::setLocale($locale);

    return back();
})->name('change_language');

Route::middleware('auth', 'select_lang', 'notification')->group(function () { 
    // View activty log data
    Route::get('/view-log/{log}', function (ActivityLog $log) {
        return $log->data ?? 'No Data Found';
    })->name('view_log');
    // Notification 
    Route::controller(NotificationController::class)->prefix('/notification')->name('notification.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/read/{id}', 'read')->name('read');
    });
    // Dashboard
    Route::controller(DashboardController::class)->prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', 'index')->name('index');
    });
    // Inventory
    Route::controller(InventoryController::class)->prefix('inventory-summary')->name('inventory_summary.')->middleware(['can:inventory.summary.view'])->group(function () { // Inventory Category
        Route::get('/', 'indexSummary')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
    });
    Route::controller(InventoryController::class)->prefix('inventory-category')->name('inventory_category.')->middleware(['can:inventory.category.view'])->group(function () { // Inventory Category
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create')->middleware(['can:inventory.category.create']);
        Route::get('/edit/{cat}', 'edit')->name('edit')->middleware(['can:inventory.category.edit']);
        Route::post('/upsert', 'upsert')->name('upsert');
        Route::get('/delete/{cat}', 'delete')->name('delete')->middleware(['can:inventory.category.delete']);

        Route::get('/stock-in/{product_child}', 'stockIn')->name('stock_in');
        Route::get('/stock-out/{product_child}', 'stockOut')->name('stock_out');
        Route::get('/transfer/{product_child}', 'transfer')->name('transfer');
    });
    // Warranty
    Route::controller(WarrantyController::class)->prefix('warranty')->name('warranty.')->middleware(['can:warranty.view'])->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/view/{sale}', 'view')->name('view');
        Route::get('/view-get-data', 'viewGetData')->name('view_get_data');
    });
    // Service History
    Route::controller(InventoryServiceHistoryController::class)->prefix('service-history')->name('service_history.')->middleware(['can:service_history.view'])->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create')->middleware(['can:service_history.create']);
        // Route::get('/edit/{sh}', 'edit')->name('edit')->middleware(['can:service_history.edit']);
        Route::get('/view/{sh}', 'view')->name('view');
        Route::get('/view-get-data', 'viewGetData')->name('view_get_data');
        Route::post('/upsert/{sh?}', 'upsert')->name('upsert');
    });
    // GRN
    Route::controller(GRNController::class)->prefix('grn')->name('grn.')->middleware(['can:grn.view'])->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create')->middleware(['can:grn.create']);
        Route::get('/edit/{sku}', 'edit')->name('edit')->middleware(['can:grn.create']);
        Route::post('/upsert', 'upsert')->name('upsert');
        Route::get('/pdf/{sku}', 'pdf')->name('pdf');
        Route::post('/stock-in', 'stockIn')->name('stock_in');
    });
    // Products
    Route::controller(ProductController::class)->prefix('product')->name('product.')->middleware(['can:inventory.product.view'])->group(function () { // Product
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create')->middleware(['can:inventory.product.create']);
        Route::get('/edit/{product}', 'edit')->name('edit')->middleware(['can:inventory.product.edit']);
        Route::post('/upsert', 'upsert')->name('upsert');
        Route::get('/delete/{product}', 'delete')->name('delete')->middleware(['can:inventory.product.delete']);
        Route::get('/view/{product}', 'view')->name('view');
        Route::get('/view-get-data', 'viewGetData')->name('view_get_data');
        Route::get('/view-get-data-raw-material', 'viewGetDataRawMaterial')->name('view_get_data_raw_material');
        Route::get('/view-get-data-cost', 'viewGetDataCost')->name('view_get_data_cost');
        Route::get('/generate-barcode', 'generateBarcode')->name('generate_barcode');
    });
    Route::controller(ProductController::class)->prefix('raw-material')->name('raw_material.')->middleware(['can:inventory.raw_material.view'])->group(function () { // Raw Material
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create')->middleware(['can:inventory.raw_material.create']);
        Route::get('/edit/{product}', 'edit')->name('edit')->middleware(['can:inventory.raw_material.edit']);
        Route::post('/upsert', 'upsert')->name('upsert');
        Route::get('/delete/{product}', 'delete')->name('delete')->middleware(['can:inventory.raw_material.delete']);
        Route::get('/view/{product}', 'view')->name('view');
        Route::get('/view-get-data', 'viewGetData')->name('view_get_data');
        Route::get('/view-get-data-raw-material', 'viewGetDataRawMaterial')->name('view_get_data_raw_material');
        Route::get('/view-get-data-cost', 'viewGetDataCost')->name('view_get_data_cost');
        Route::get('/generate-barcode', 'generateBarcode')->name('generate_barcode');
    });
    // Sale - Quotation/Sale Order
    Route::controller(SaleController::class)->group(function () {
        // Quotation
        Route::prefix('quotation')->name('quotation.')->middleware(['can:sale.quotation.view'])->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/create', 'create')->name('create')->middleware(['can:sale.quotation.create']);
            Route::get('/edit/{sale}', 'edit')->name('edit')->middleware(['can:sale.quotation.edit']);
            Route::get('/delete/{sale}', 'delete')->name('delete')->middleware(['can:sale.quotation.delete']);
            Route::get('/pdf/{sale}', 'pdf')->name('pdf');
            Route::get('/to-sale-order', 'toSaleOrder')->name('to_sale_order')->middleware(['can:sale.quotation.convert']);
            Route::get('/convert-to-sale-order', 'converToSaleOrder')->name('convert_to_sale_order');
        });

        //Pending
        Route::prefix('pending-order')->name('pending_order.')->group(function () {
            Route::get('/', 'indexPendingOrder')->name('index');
            Route::get('/get-data', 'getDataPendingOrder')->name('get_data');
            Route::get('/edit/{sale}', 'editSaleOrder')->name('edit');
            Route::get('/delete/{sale}', 'delete')->name('delete');
            Route::get('/count','getPendingOrdersCount')->name('count');
            Route::get('/get-sale-person', 'getSalePerson')->name('get_sale_person');
            Route::post('/asssign-to-sale-person', 'assignSalePerson')->name('assign_sale_person');
        });

        // Sale Order
        Route::prefix('sale-order')->name('sale_order.')->middleware(['can:sale.sale_order.view'])->group(function () {
            Route::get('/', 'indexSaleOrder')->name('index');
            Route::get('/get-data', 'getDataSaleOrder')->name('get_data');
            Route::get('/create', 'createSaleOrder')->name('create')->middleware(['can:sale.sale_order.create']);
            Route::get('/edit/{sale}', 'editSaleOrder')->name('edit')->middleware(['can:sale.sale_order.edit']);
            Route::get('/cancel/{sale}', 'cancelSaleOrder')->name('cancel')->middleware(['can:sale.sale_order.cancel']);
            Route::get('/delete/{sale}', 'delete')->name('delete')->middleware(['can:sale.sale_order.delete']);
            Route::get('/pdf/{sale}', 'pdfSaleOrder')->name('pdf');
            Route::get('/to-delivery-order', 'toDeliveryOrder')->name('to_delivery_order')->middleware(['can:sale.sale_order.convert']);
            Route::get('/convert-to-delivery-order', 'converToDeliveryOrder')->name('convert_to_delivery_order');
        });

        Route::prefix('sale')->name('sale.')->group(function () {
            Route::post('/upsert-quotation-details', 'upsertQuoDetails')->name('upsert_quo_details');
            Route::post('/upsert-product-details', 'upsertProDetails')->name('upsert_pro_details');
            Route::post('/upsert-remark', 'upsertRemark')->name('upsert_remark');
            Route::post('/upsert-payment-details', 'upsertPayDetails')->name('upsert_pay_details');
            Route::post('/upsert-delivery-schedule', 'upsertDelSchedule')->name('upsert_delivery_schedule');
            Route::get('/get-products/{sale}', 'getProducts')->name('get_products');
            Route::get('/to-production/{sale}', 'toProduction')->name('to_production');
        });

        // Delivery Order
        Route::prefix('delivery-order')->name('delivery_order.')->middleware(['can:sale.delivery_order.view'])->group(function () {
            Route::get('/', 'indexDeliveryOrder')->name('index');
            Route::get('/get-data', 'getDataDeliveryOrder')->name('get_data');
            Route::get('/to-invoice', 'toInvoice')->name('to_invoice')->middleware(['can:sale.delivery_order.convert']);
            Route::get('/convert-to-invoice', 'convertToInvoice')->name('convert_to_invoice');
        });
        // Invoice
        Route::prefix('invoice')->name('invoice.')->middleware(['can:sale.invoice.view'])->group(function () {
            Route::get('/', 'indexInvoice')->name('index');
            Route::get('/get-data', 'getDataInvoice')->name('get_data');
            Route::get('/e-invoice', 'indexEInvoice')->name('e-invoice.index');
            Route::get('/get-data-e-invoice', 'getDataEInvoice')->name('get_data_e-invoice');
            Route::get('/consolidated-e-invoice', 'indexConsolidatedEInvoice')->name('consolidated-e-invoice.index');
            Route::get('/get-data-consolidated-e-invoice', 'getDataConsolidatedEInvoice')->name('get_data_consolidated-e-invoice');
            Route::get('/credit-note', 'indexCreditNote')->name('credit-note.index');
            Route::get('/get-data-credit-note', 'getDataCreditNote')->name('get_data_credit-note');
            Route::get('/debit-note', 'indexDebitNote')->name('debit-note.index');
            Route::get('/get-data-debit-note', 'getDataDebitNote')->name('get_data_debit-note');
        });

        Route::get('/download', 'download')->name('download');

        // Target
        Route::prefix('target')->name('target.')->middleware(['can:sale.target.view'])->group(function () {
            Route::get('/', 'indexTarget')->name('index');
            Route::get('/get-data', 'getDataTarget')->name('get_data');
            Route::get('/create', 'createTarget')->name('create');
            Route::post('/store', 'storeTarget')->name('store');
            Route::get('/edit/{target}', 'editTarget')->name('edit');
            Route::post('/update/{target}', 'updateTarget')->name('update');
        });
        // Billing
        Route::prefix('billing')->name('billing.')->middleware(['can:sale.billing.view'])->group(function () {
            Route::get('/', 'indexBilling')->name('index');
            Route::get('/get-data', 'getDataBilling')->name('get_data');
            Route::get('/to-invoice-billing', 'toBilling')->name('to_billing');
            Route::get('/convert-to-invoice-billing', 'convertToBilling')->name('convert_to_billing');
        });
    });
    // Task
    Route::controller(TaskController::class)->prefix('task')->name('task.')->middleware(['can:task.view'])->group(function () {
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/delete/{task}', 'delete')->name('delete')->middleware(['can:task.delete']);

        Route::prefix('driver')->name('driver.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create')->middleware(['can:task.create']);
            Route::post('/store', 'driverStore')->name('store');
            Route::get('/view/{task}', 'view')->name('view');
            Route::get('/edit/{task}', 'edit')->name('edit')->middleware(['can:task.edit']);
            Route::post('/update/{task}', 'driverUpdate')->name('update');
        });
        Route::prefix('technician')->name('technician.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create')->middleware(['can:task.create']);
            Route::post('/store', 'technicianStore')->name('store');
            Route::get('/view/{task}', 'view')->name('view');
            Route::get('/edit/{task}', 'edit')->name('edit')->middleware(['can:task.edit']);
            Route::post('/update/{task}', 'technicianUpdate')->name('update');
            Route::get('/generate-report', 'generate99ServiceReport')->name('generate_99_servie_report');
        });
        Route::prefix('sale')->name('sale.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create')->middleware(['can:task.create']);
            Route::post('/store', 'saleStore')->name('store');
            Route::get('/view/{task}', 'view')->name('view');
            Route::get('/edit/{task}', 'edit')->name('edit')->middleware(['can:task.edit']);
            Route::post('/update/{task}', 'saleUpdate')->name('update');
        });
    });
    // Production
    Route::controller(ProductionController::class)->prefix('production')->name('production.')->middleware(['can:production.view'])->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create')->middleware(['can:production.create']);
        Route::get('/edit/{production}', 'edit')->name('edit')->middleware(['can:production.edit']);
        Route::get('/view/{production}', 'view')->name('view');
        Route::get('/delete/{production}', 'delete')->name('delete')->middleware(['can:production.delete']);
        Route::post('/upsert/{production?}', 'upsert')->name('upsert');
        Route::post('/check-in-milestone', 'checkInMilestone')->name('check_in_milestone');
    });
    // Ticket
    Route::controller(TicketController::class)->prefix('ticket')->name('ticket.')->middleware(['can:ticket.view'])->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create')->middleware(['can:ticket.create']);
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{ticket}', 'edit')->name('edit')->middleware(['can:ticket.edit']);
        Route::post('/update/{ticket}', 'update')->name('update');
        Route::get('/delete/{ticket}', 'delete')->name('delete')->middleware(['can:ticket.delete']);
    });
    // Report
    Route::controller(ReportController::class)->prefix('report')->name('report.')->group(function () {
        Route::prefix('production-report')->name('production_report.')->group(function() {
            Route::get('/', 'indexProduction')->name('index');
            Route::get('/get-data', 'getDataProduction')->name('get_data');
            Route::get('/export-in-excel', 'exportInExcelProduction')->name('export_in_excel');
            Route::get('/export-in-pdf', 'exportInPdfProduction')->name('export_in_pdf');
        });
        Route::prefix('sales-report')->name('sales_report.')->group(function() {
            Route::get('/', 'indexSales')->name('index');
            Route::get('/get-data', 'getDataSales')->name('get_data');
            Route::get('/export-in-excel', 'exportInExcelSales')->name('export_in_excel');
            Route::get('/export-in-pdf', 'exportInPdfSales')->name('export_in_pdf');
        });
        Route::prefix('stock-report')->name('stock_report.')->group(function() {
            Route::get('/', 'indexStock')->name('index');
            Route::get('/get-data', 'getDataStock')->name('get_data');
            Route::get('/export', 'exportStock')->name('export');
            Route::get('/export-in-excel', 'exportInExcelStock')->name('export_in_excel');
            Route::get('/export-in-pdf', 'exportInPdfStock')->name('export_in_pdf');
        });
        Route::prefix('earning-report')->name('earning_report.')->group(function() {
            Route::get('/', 'indexEarning')->name('index');
            Route::get('/get-data', 'getDataEarning')->name('get_data');
            Route::get('/export', 'exportEarning')->name('export');
            Route::get('/export-in-excel', 'exportInExcelEarning')->name('export_in_excel');
            Route::get('/export-in-pdf', 'exportInPdfEarning')->name('export_in_pdf');
        });
        Route::prefix('service-report')->name('service_report.')->group(function() {
            Route::get('/', 'indexService')->name('index');
            Route::get('/get-data', 'getDataService')->name('get_data');
            Route::get('/export', 'exportService')->name('export');
            Route::get('/export-in-excel', 'exportInExcelService')->name('export_in_excel');
            Route::get('/export-in-pdf', 'exportInPdfService')->name('export_in_pdf');
        });
        Route::prefix('technician-stock-report')->name('technician_stock_report.')->group(function() {
            Route::get('/', 'indexTechnicianStock')->name('index');
            Route::get('/get-data', 'getDataTechnicianStock')->name('get_data');
            Route::get('/export', 'exportTechnicianStock')->name('export');
            Route::get('/export-in-excel', 'exportInExcelTechnicianStock')->name('export_in_excel');
            Route::get('/export-in-pdf', 'exportInPdfTechnicianStock')->name('export_in_pdf');
        });
    });
    // Customer
    Route::controller(CustomerController::class)->prefix('customer')->name('customer.')->middleware(['can:customer.view'])->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create')->middleware(['can:customer.create']);
        Route::get('/edit/{customer}', 'edit')->name('edit')->middleware(['can:customer.edit']);
        Route::get('/delete/{customer}', 'delete')->name('delete')->middleware(['can:customer.delete']);
        Route::post('/upsert-info', 'upsertInfo')->name('upsert_info')->withoutMiddleware(['can:customer.view', 'auth', 'select_lang']);
        Route::post('/upsert-location', 'upsertLocation')->name('upsert_location')->withoutMiddleware(['can:customer.view', 'auth', 'select_lang']);
        Route::get('/get-location', 'getLocation')->name('get_location');
    });
    // Supplier
    Route::controller(SupplierController::class)->prefix('supplier')->name('supplier.')->middleware(['can:supplier.view'])->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create')->middleware(['can:supplier.create']);
        Route::get('/edit/{supplier}', 'edit')->name('edit')->middleware(['can:supplier.edit']);
        Route::get('/delete/{supplier}', 'delete')->name('delete')->middleware(['can:supplier.delete']);
        Route::post('/upsert/{supplier?}', 'upsert')->name('upsert');
    });
    // Setting
    Route::middleware(['can:setting.view'])->group(function() {
        // Material Use
        Route::controller(MaterialUseController::class)->prefix('material-use')->name('material_use.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::get('/edit/{material}', 'edit')->name('edit');
            Route::get('/delete/{material}', 'delete')->name('delete');
            Route::post('/upsert', 'upsert')->name('upsert');
        });
        // Warranty Period
        Route::controller(WarrantyPeriodController::class)->prefix('warranty-period')->name('warranty_period.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{warranty}', 'edit')->name('edit');
            Route::post('/update/{warranty}', 'update')->name('update');
            Route::get('/delete/{warranty}', 'delete')->name('delete');
        });
        // Promotion
        Route::controller(PromotionController::class)->prefix('promotion')->name('promotion.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{promotion}', 'edit')->name('edit');
            Route::post('/update/{promotion}', 'update')->name('update');
            Route::get('/delete/{promotion}', 'delete')->name('delete');
        });
        // Project Type
        Route::controller(ProjectTypeController::class)->prefix('project-type')->name('project_type.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{type}', 'edit')->name('edit');
            Route::post('/update/{type}', 'update')->name('update');
            Route::get('/delete/{type}', 'delete')->name('delete');
        });
        // Service
        Route::controller(ServiceController::class)->prefix('service')->name('service.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{service}', 'edit')->name('edit');
            Route::post('/update/{service}', 'update')->name('update');
            Route::get('/delete/{service}', 'delete')->name('delete');
        });
        // Currency
        Route::controller(CurrencyController::class)->prefix('currency')->name('currency.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{currency}', 'edit')->name('edit');
            Route::post('/update/{currency}', 'update')->name('update');
            Route::get('/delete/{currency}', 'delete')->name('delete');
        });
        // Credit Term
        Route::controller(CreditTermController::class)->prefix('credit-term')->name('credit_term.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{credit}', 'edit')->name('edit');
            Route::post('/update/{credit}', 'update')->name('update');
            Route::get('/delete/{credit}', 'delete')->name('delete');
        });
        // Area 
        Route::controller(AreaController::class)->prefix('area')->name('area.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{area}', 'edit')->name('edit');
            Route::post('/update/{area}', 'update')->name('update');
            Route::get('/delete/{area}', 'delete')->name('delete');
        });
        // Debtor Type 
        Route::controller(DebtorTypeController::class)->prefix('debtor-type')->name('debtor_type.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{debtor}', 'edit')->name('edit');
            Route::post('/update/{debtor}', 'update')->name('update');
            Route::get('/delete/{debtor}', 'delete')->name('delete');
        });
        // UOM
        Route::controller(UOMController::class)->prefix('uom')->name('uom.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{uom}', 'edit')->name('edit');
            Route::post('/update/{uom}', 'update')->name('update');
        });
        // Platform
        Route::controller(PlatformController::class)->prefix('platform')->name('platform.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{platform}', 'edit')->name('edit');
            Route::post('/update/{platform}', 'update')->name('update');
        });
        // Priorities
        Route::controller(PriorityController::class)->prefix('priority')->name('priority.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{priority}', 'edit')->name('edit');
            Route::post('/update/{priority}', 'update')->name('update');
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

            Route::get('as-branch', 'asBranch')->name('as_branch');
        });
        // Role Management
        Route::controller(RoleController::class)->prefix('role-management')->name('role_management.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{role}', 'edit')->name('edit');
            Route::post('/update/{role}', 'update')->name('update');
        });
    });
});

//Lazada
Route::prefix('lazada')->group(function () {
    Route::post('/webhook', [LazadaController::class, 'handleLazadaWebhook']);
    Route::get('/get-access-token/{code}', [LazadaController::class, 'getAccessTokenLazada']);
    Route::get('/refresh-access-token', [LazadaController::class, 'refreshAccessTokenLazada']);
});

//Shopee
Route::prefix('shopee')->group(function () {
    Route::post('/webhook', [ShopeeController::class, 'handleShopeeWebhook']);
    Route::get('/generate-auth-link', [ShopeeController::class, 'generateAuthLinkShopee']);
    Route::get('/get-access-token/{code}', [ShopeeController::class, 'getAccessTokenShopee']);
    Route::get('/refresh-access-token', [ShopeeController::class, 'refreshAccessTokenShopee']);
});

//Tiktok
Route::prefix('tiktok')->group(function () {
    Route::post('/webhook', [TiktokController::class, 'handleTiktokWebhook']);
    Route::get('/get-access-token/{code}', [TiktokController::class, 'getAccessTokenTiktok']);
    Route::get('/refresh-access-token', [TiktokController::class, 'refreshAccessTokenTiktok']);
});

//WooCommerce
Route::prefix('woo-commerce')->group(function () {
    Route::post('/order-created/webhook', [WooCommerceController::class, 'handleWooCommerceOrderCreated']);
    Route::post('/order-updated/webhook', [WooCommerceController::class, 'handleWooCommerceOrderUpdated']);
    Route::post('/order-deleted/webhook', [WooCommerceController::class, 'handleWooCommerceOrderDeleted']);
    Route::post('/order-restored/webhook', [WooCommerceController::class, 'handleWooCommerceOrderRestored']);
});

Route::prefix('e-invoice')->group(function () {
    Route::get('/login', [EInvoiceController::class, 'login']);
    Route::get('/generate', [EInvoiceController::class, 'generateXmlInvoice']);
    Route::post('/submit', [EInvoiceController::class, 'submit']);
    Route::post('/submit-consolidated', [EInvoiceController::class, 'submitConsolidated']);
    Route::get('/send-to-customer', [EInvoiceController::class, 'sendEmail'])->name('send.email');
    Route::get('/download', [EInvoiceController::class, 'download'])->name('e-invoice.download');
    Route::post('/get-invoice-item', [EInvoiceController::class, 'getInvoiceItem']);
    Route::post('/submit-note', [EInvoiceController::class, 'submitNote'])->name('submit.note');
    Route::post('/get-cons-invoice-item', [EInvoiceController::class, 'getConsInvoiceItem']);
    Route::get('/to-note',  [EInvoiceController::class, 'toNote'])->name('to_note');
    Route::post('/cancel-e-invoice',  [EInvoiceController::class, 'cancelEInvoice'])->name('cancel_e_invoice');
    Route::post('/resubmit-e-invoice',  [EInvoiceController::class, 'resubmitEInvoice'])->name('resubmit_e_invoice');
    Route::post('/billing-submit', [EInvoiceController::class, 'billingSubmit']);

});

Route::get('/sync-classification-codes', [EInvoiceController::class, 'syncClassificationCodes']);

Route::get('/test1', [EInvoiceController::class, 'test']);
Route::get('/email', function(){
    return view('invoice.email');
});
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__ . '/auth.php';
