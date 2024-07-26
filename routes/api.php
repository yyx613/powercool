<?php

use App\Http\Controllers\Api\v1\AuthController;
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
            Route::get('auto-login', 'autoLogin');
            Route::post('update', 'update');
        });
        Route::post('login', 'login');
        Route::post('login/third-party', 'loginThirdParty');
        Route::post('forget-password', 'forgetPassword');
        Route::post('verify-forget-password-code', 'verifyForgetPasswordCode');
        Route::post('reset-password', 'resetPassword');
        Route::post('get-verification-mail', 'getVerificationMail');
    });
    // Task
    Route::controller(TaskController::class)->middleware('auth:sanctum')->prefix('task')->group(function() {
        Route::get('get-all', 'getAll');
        Route::post('update-milestone/{task_ms}', 'updateMilestone');
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
