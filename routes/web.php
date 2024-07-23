<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
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
    // Task
    Route::controller(TaskController::class)->prefix('task')->name('task.')->group(function() {
        Route::get('/delete/{task}', 'delete')->name('delete');

        Route::prefix('driver')->name('driver.')->group(function() {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'driverGetData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'driverStore')->name('store');
            Route::get('/edit/{task}', 'edit')->name('edit');
            Route::post('/update/{task}', 'driverUpdate')->name('update');
        });
        Route::prefix('technician')->name('technician.')->group(function() {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'technicianGetData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'technicianStore')->name('store');
            Route::get('/edit/{task}', 'edit')->name('edit');
            Route::post('/update/{task}', 'technicianUpdate')->name('update');
        });
        Route::prefix('sale')->name('sale.')->group(function() {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'saleGetData')->name('get_data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'saleStore')->name('store');
            Route::get('/edit/{task}', 'edit')->name('edit');
            Route::post('/update/{task}', 'saleUpdate')->name('update');
        });
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
    Route::controller(UserController::class)->prefix('/user-management')->name('user_management.')->middleware(['can:user_management'])->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{user}', 'edit')->name('edit');
        Route::post('/update/{user}', 'update')->name('update');
        Route::get('/delete/{user}', 'delete')->name('delete');
        Route::get('/export', 'export')->name('export');
    });
    // Role Management
    Route::controller(RoleController::class)->prefix('/role-management')->name('role_management.')->middleware(['can:role_management'])->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('get_data');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{role}', 'edit')->name('edit');
        Route::post('/update/{role}', 'update')->name('update');
        Route::get('/delete/{role}', 'delete')->name('delete');
        Route::get('/export', 'export')->name('export');
    });
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
