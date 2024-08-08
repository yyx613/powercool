<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\NotificationController;
use App\Http\Controllers\Api\v1\SaleController;
use App\Http\Controllers\Api\v1\TaskController;
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
    // Task
    Route::controller(TaskController::class)->middleware('auth:sanctum')->prefix('task')->group(function() {
        Route::get('get-statistic', 'getStatistic');
        Route::get('get-all', 'getAll');
        Route::get('get-detail/{task}', 'getDetail');
        Route::post('update-milestone/{task_ms}', 'updateMilestone');
    });
    // Notification
    Route::controller(NotificationController::class)->middleware('auth:sanctum')->prefix('notification')->group(function() {
        Route::get('get-all', 'getAll');
        Route::get('read/{noti}', 'read');
    });
    // Target
    Route::controller(SaleController::class)->middleware('auth:sanctum')->group(function() {
        Route::prefix('sales-target')->group(function() {
            Route::get('get-all', 'getAllSalesTarget');
        });
    });
});
