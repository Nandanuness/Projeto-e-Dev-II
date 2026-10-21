<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : view('auth.login'));
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/estoque', DashboardController::class)->name('stock.index');
    Route::post('/movimentacoes', [StockMovementController::class, 'store'])->name('movements.store');
    Route::get('/historico', [ManagementController::class, 'movements'])->name('movements.index');
    Route::get('/relatorios', [ReportController::class, 'index'])->middleware('role:gerente')->name('reports.index');
    Route::get('/relatorios/estoque.xlsx', [ReportController::class, 'stockXlsx'])->middleware('role:gerente')->name('reports.stock.xlsx');
    Route::middleware('role:gerente')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/categorias', [ManagementController::class, 'categories'])->name('categories');
        Route::post('/categorias', [ManagementController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categorias/{category}', [ManagementController::class, 'updateCategory'])->name('categories.update');
        Route::patch('/categorias/{category}/situacao', [ManagementController::class, 'toggleCategory'])->name('categories.toggle');
        Route::delete('/categorias/{category}', [ManagementController::class, 'destroyCategory'])->name('categories.destroy');
        Route::get('/fornecedores', [ManagementController::class, 'suppliers'])->name('suppliers');
        Route::post('/fornecedores', [ManagementController::class, 'storeSupplier'])->name('suppliers.store');
        Route::put('/fornecedores/{supplier}', [ManagementController::class, 'updateSupplier'])->name('suppliers.update');
        Route::patch('/fornecedores/{supplier}/situacao', [ManagementController::class, 'toggleSupplier'])->name('suppliers.toggle');
        Route::delete('/fornecedores/{supplier}', [ManagementController::class, 'destroySupplier'])->name('suppliers.destroy');
        Route::get('/produtos', [ManagementController::class, 'products'])->name('products');
        Route::post('/produtos', [ManagementController::class, 'storeProduct'])->name('products.store');
        Route::put('/produtos/{product}', [ManagementController::class, 'updateProduct'])->name('products.update');
        Route::patch('/produtos/{product}/situacao', [ManagementController::class, 'toggleProduct'])->name('products.toggle');
        Route::delete('/produtos/{product}', [ManagementController::class, 'destroyProduct'])->name('products.destroy');
        Route::get('/usuarios', [ManagementController::class, 'users'])->name('users');
        Route::post('/usuarios', [ManagementController::class, 'storeUser'])->name('users.store');
        Route::put('/usuarios/{user}', [ManagementController::class, 'updateUser'])->name('users.update');
        Route::patch('/usuarios/{user}/situacao', [ManagementController::class, 'toggleUser'])->name('users.toggle');
    });
});
