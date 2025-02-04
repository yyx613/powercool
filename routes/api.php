<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\InventoryController;
use App\Http\Controllers\Api\v1\NotificationController;
use App\Http\Controllers\Api\v1\SaleController;
use App\Http\Controllers\Api\v1\TaskController;
use App\Http\Controllers\Api\sync\SyncAutoCountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function() {
    Route::get('/test', function () {
        return response()->json(['message' => 'Hello, API!']);
    });
    // Auth
    Route::controller(AuthController::class)->prefix('auth')->group(function() {
        Route::middleware('auth:sanctum')->group(function() {
            Route::post('update', 'update');
        });
        Route::get('auto-login', 'autoLogin');
        Route::post('login', 'login');
        Route::post('login/third-party', 'loginThirdParty');
        Route::post('forget-password', 'forgetPassword');
        Route::post('verify-forget-password-code', 'verifyForgetPasswordCode');
        Route::post('reset-password', 'resetPassword');
        Route::post('get-verification-mail', 'getVerificationMail');
    });
    Route::middleware('auth:sanctum')->group(function() {
        // Task
        Route::controller(TaskController::class)->prefix('task')->group(function() {
            Route::get('get-statistic', 'getStatistic');
            Route::get('get-all', 'getAll');
            Route::get('get-detail/{task}', 'getDetail');
            Route::post('update-milestone/{task_ms}', 'updateMilestone');
        });
        // Notification
        Route::controller(NotificationController::class)->prefix('notification')->group(function() {
            Route::get('get-all', 'getAll');
            Route::get('read/{noti}', 'read');
        });
        // Target
        Route::controller(SaleController::class)->prefix('sales-target')->group(function() {
            Route::get('get-all', 'getAllSalesTarget');
        });
        // Inventory
        Route::controller(InventoryController::class)->prefix('inventory')->group(function() {
            Route::get('/get-raw-material-and-sparepart', 'getRawMaterialAndSparepart');
            Route::get('/get-sale-person-cancelled-products', 'getSalePersonCancelledProducts');
        });
    });
});

Route::prefix('sync')->controller(SyncAutoCountController::class)->group(function () {
    Route::get('/test', function () {
        return response()->json(['message' => 'Hello, API!']);
    });
    Route::post('/syncCreditor',[SyncAutoCountController::class,'syncCreditor']);
    Route::post('/syncDebtor',[SyncAutoCountController::class,'syncDebtor']);
    //Suppliers
    Route::get('/suppliers/unsynced', [SyncAutoCountController::class, 'getUnsyncedSuppliers']);
    Route::post('/suppliers/updateSupplierSyncStatus', [SyncAutoCountController::class, 'updateSupplierSyncStatus']);
    //Customers
    Route::get('/customers/unsynced', [SyncAutoCountController::class, 'getUnsyncedCustomers']);
    Route::post('/customers/updateCustomerSyncStatus', [SyncAutoCountController::class, 'updateCustomerSyncStatus']);
    //Products
    Route::get('/products/unsynced', [SyncAutoCountController::class, 'getUnsyncedProducts']);
    Route::post('/products/updateProductSyncStatus', [SyncAutoCountController::class, 'updateProductSyncStatus']);
    //Invoices
    Route::get('/invoices/unsynced', [SyncAutoCountController::class, 'getUnsyncedInvoices']);
    Route::post('/invoices/updateInvoiceSyncStatus', [SyncAutoCountController::class, 'updateInvoiceSyncStatus']);
    //GRN
    Route::get('/grns/unsynced', [SyncAutoCountController::class, 'getUnsyncedGrns']);
    Route::post('/grns/updateGrnsSyncStatus', [SyncAutoCountController::class, 'updateGrnsSyncStatus']);
});
