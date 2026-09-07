<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

// Home route
Route::get('/', [HomeController::class, 'index'])->name('home');

// About page
Route::view('/about', 'about')->name('about');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public product routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/category/{category}', [ProductController::class, 'category'])->name('products.category');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// Cart routes (accessible to guests and authenticated users)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::put('/update/{id}', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{id}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
    Route::get('/count', [CartController::class, 'count'])->name('count');
});

// Authenticated user routes
Route::middleware('auth')->group(function () {
    // Checkout routes
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
    });
    
    // Order routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::get('/{order}/track', [OrderController::class, 'track'])->name('track');
        Route::get('/success/{order}', [OrderController::class, 'success'])->name('success');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::post('/{order}/reorder', [OrderController::class, 'reorder'])->name('reorder');
        Route::post('/{order}/upload-proof', [OrderController::class, 'uploadPaymentProof'])->name('upload-proof');
    });
    
    // Profile routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
        Route::get('/addresses', [ProfileController::class, 'addresses'])->name('addresses');
        Route::post('/addresses', [ProfileController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/addresses/{address}', [ProfileController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/addresses/{address}', [ProfileController::class, 'deleteAddress'])->name('addresses.delete');
        Route::post('/addresses/{address}/default', [ProfileController::class, 'setDefaultAddress'])->name('addresses.default');
    });
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Product management
    Route::resource('products', AdminProductController::class);
    Route::post('/products/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('products.toggle-status');
    
    // Category management
    Route::resource('categories', AdminCategoryController::class);
    
    // Order management
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
        Route::put('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('update-status');
        Route::post('/{order}/approve-payment', [AdminOrderController::class, 'approvePayment'])->name('approve-payment');
        Route::post('/{order}/reject-payment', [AdminOrderController::class, 'rejectPayment'])->name('reject-payment');
        Route::get('/export/excel', [AdminOrderController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [AdminOrderController::class, 'exportPdf'])->name('export.pdf');
    });
    
    // User management
    Route::resource('users', AdminUserController::class);
    Route::post('/users/{user}/toggle-role', [AdminUserController::class, 'toggleRole'])->name('users.toggle-role');
    Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/bulk-action', [AdminUserController::class, 'bulkAction'])->name('users.bulk-action');
    Route::get('/users/export', [AdminUserController::class, 'export'])->name('users.export');
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [AdminController::class, 'salesReport'])->name('sales');
        Route::get('/inventory', [AdminController::class, 'inventoryReport'])->name('inventory');
        Route::get('/customers', [AdminController::class, 'customerReport'])->name('customers');
        Route::get('/export-sales', [AdminController::class, 'exportSalesReport'])->name('export-sales');
    });
});
