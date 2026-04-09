<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CabinetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiningTableController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});



Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/cabinet', CabinetController::class)->name('cabinet');

    Route::get('/dashboard', DashboardController::class)
        ->middleware('can:dashboard.view')
        ->name('dashboard');

    Route::view('/waiter', 'waiter.index')
        ->middleware('can:waiter.panel')
        ->name('waiter.index');

    Route::view('/kitchen', 'stations.kitchen')
        ->middleware('can:kitchen.view')
        ->name('kitchen.index');

    Route::view('/bar', 'stations.bar')
        ->middleware('can:bar.view')
        ->name('bar.index');

    Route::view('/pos', 'pos.index')
        ->middleware('can:orders.create')
        ->name('pos.index');

    Route::get('/staff', [StaffController::class, 'index'])
        ->middleware('can:staff.manage')
        ->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])
        ->middleware('can:staff.manage')
        ->name('staff.store');
    Route::put('/staff/{user}', [StaffController::class, 'update'])
        ->middleware('can:staff.manage')
        ->name('staff.update');
    Route::delete('/staff/{user}', [StaffController::class, 'destroy'])
        ->middleware('can:staff.manage')
        ->name('staff.destroy');

    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('can:roles.manage')
        ->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('can:roles.manage')
        ->name('roles.store');
    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('can:roles.manage')
        ->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('can:roles.manage')
        ->name('roles.destroy');

    Route::get('/branches', [BranchController::class, 'index'])
        ->middleware('can:branches.manage')
        ->name('branches.index');
    Route::post('/branches', [BranchController::class, 'store'])
        ->middleware('can:branches.manage')
        ->name('branches.store');
    Route::put('/branches/{branch}', [BranchController::class, 'update'])
        ->middleware('can:branches.manage')
        ->name('branches.update');
    Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])
        ->middleware('can:branches.manage')
        ->name('branches.destroy');

    Route::get('/tables', [DiningTableController::class, 'index'])
        ->middleware('can:tables.manage')
        ->name('tables.index');
    Route::post('/tables', [DiningTableController::class, 'store'])
        ->middleware('can:tables.manage')
        ->name('tables.store');
    Route::put('/tables/{table}', [DiningTableController::class, 'update'])
        ->middleware('can:tables.manage')
        ->name('tables.update');
    Route::delete('/tables/{table}', [DiningTableController::class, 'destroy'])
        ->middleware('can:tables.manage')
        ->name('tables.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])
        ->middleware('can:categories.manage')
        ->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])
        ->middleware('can:categories.manage')
        ->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])
        ->middleware('can:categories.manage')
        ->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
        ->middleware('can:categories.manage')
        ->name('categories.destroy');

    Route::get('/products', [ProductController::class, 'index'])
        ->middleware('can:products.manage')
        ->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])
        ->middleware('can:products.manage')
        ->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])
        ->middleware('can:products.manage')
        ->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->middleware('can:products.manage')
        ->name('products.destroy');

    Route::get('/orders/{order}/receipt', [ReceiptController::class, 'show'])
        ->middleware('can:orders.view')
        ->name('orders.receipt');

    Route::get('/reports', ReportController::class)
        ->middleware('can:reports.view')
        ->name('reports.index');
});
