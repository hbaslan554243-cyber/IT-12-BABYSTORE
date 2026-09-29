<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalesLogController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Routes are grouped by the problem/module they solve, per the feasibility
| study in Chapter 2:
|   Problem 1 -> Product / Inventory routes
|   Problem 2 -> Sales / POS routes
|   Problem 3 -> Itemized Sales / Receipt Log routes
|   (Purchase Orders / Suppliers kept as a supporting module for restocking)
|
| Access is role-based:
|   'role:owner'         -> Owner only (dashboard-wide + user management)
|   'role:owner,staff'   -> Both roles, per the Owner/Staff permission table
*/

// --- Authentication (unauthenticated only) ---
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// --- Everything below requires a logged-in, active account ---
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profile & account (both roles)
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Problem 1: Inventory
    // Staff can view products; only the Owner can add/edit/delete.
    Route::get('products', [ProductController::class, 'index'])->name('products.index')->middleware('role:owner,staff');
    Route::middleware('role:owner')->group(function () {
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    // Problem 2: Sales / POS (both roles can process sales)
    Route::middleware('role:owner,staff')->group(function () {
        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
        Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    });

    // Problem 3: Itemized Sales / Receipt Log (Owner: full reports; Staff: relevant history)
    Route::get('sales-log', [SalesLogController::class, 'index'])->name('sales.log')->middleware('role:owner,staff');

    // Supporting module: Restocking / Purchase Orders (both can record restocking)
    Route::middleware('role:owner,staff')->group(function () {
        Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::post('purchase-orders/{purchaseOrder}/deliver', [PurchaseOrderController::class, 'markDelivered'])->name('purchase-orders.deliver');
    });
    // Only the Owner creates suppliers and places new purchase orders.
    Route::middleware('role:owner')->group(function () {
        Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    });

    // Revenue & Summary Reports — Owner only
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index')->middleware('role:owner');

    // Manage Users/Accounts — Owner only
    Route::middleware('role:owner')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });
});
