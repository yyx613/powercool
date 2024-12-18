<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\InventoryController;
use App\Http\Controllers\Api\v1\NotificationController;
use App\Http\Controllers\Api\v1\SaleController;
use App\Http\Controllers\Api\v1\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\GRNController;

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

Route::prefix('v1')->group(function () {
    // Auth
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::middleware('auth:sanctum')->group(function () {
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
    Route::middleware('auth:sanctum')->group(function () {
        // Task
        Route::controller(TaskController::class)->prefix('task')->group(function () {
            Route::get('get-statistic', 'getStatistic');
            Route::get('get-all', 'getAll');
            Route::get('get-detail/{task}', 'getDetail');
            Route::post('update-milestone/{task_ms}', 'updateMilestone');
        });
        // Notification
        Route::controller(NotificationController::class)->prefix('notification')->group(function () {
            Route::get('get-all', 'getAll');
            Route::get('read/{noti}', 'read');
        });
        // Target
        Route::controller(SaleController::class)->prefix('sales-target')->group(function () {
            Route::get('get-all', 'getAllSalesTarget');
        });
        // Inventory
        Route::controller(InventoryController::class)->prefix('inventory')->group(function () {
            Route::get('/get-raw-material-and-sparepart', 'getRawMaterialAndSparepart');
            Route::get('/get-sale-person-cancelled-products', 'getSalePersonCancelledProducts');
        });
        // Customer
        Route::controller(CustomerController::class)->prefix('customer')->group(function () {
            Route::get(uri: '/sync/{customer}/{company}', action: 'sync')->name('sync');
        });
        // Supplier
        Route::controller(SupplierController::class)->prefix('supplier')->group(function () {
            Route::get(uri: '/sync/{supplier}/{company}', action: 'sync')->name('sync');
        });
        // Grn
        Route::controller(GRNController::class)->prefix('grn')->group(function () {
            Route::get('/sync/{company}', 'sync')->name('sync');
        });
        // Invoice
        Route::controller(SaleController::class)->prefix('invoice')->group(function () {
            Route::get('/sync/{company}', 'sync')->name('sync');
        });
    });

});
