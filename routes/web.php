<?php

use App\Http\Controllers\ProductListController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VashiMarketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login.form');
});

// GUEST ROUTES
// These routes are only for users who are NOT logged in.
// The 'guest' middleware will redirect them to 'admin.listofPrice' if they are already logged in.
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [UserController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login/store', [UserController::class, 'loginStore'])->name('login.store');
});

// AUTHENTICATED ROUTES
// These routes are only for users who ARE logged in.
// The 'auth' middleware will redirect them to 'login.form' if they are not logged in.
Route::middleware(['auth'])->group(function () {

    Route::get('/list/products', [UserController::class, 'showlistofPrice'])->name('admin.listofPrice');
    Route::get('/logout', [UserController::class, 'logout'])->name('logout');

    // Product Routes
    Route::get('/products/create', [ProductListController::class, 'create'])->name('admin.product.create');
    Route::post('/products', [ProductListController::class, 'store'])->name('admin.product.store');
    Route::get('/products/{product}/edit', [ProductListController::class, 'edit'])->name('admin.product.edit');
    Route::put('/products/{product}', [ProductListController::class, 'update'])->name('admin.product.update');

    // Vashi Market Routes
    Route::get('/vashi-market/bills', [VashiMarketController::class, 'index'])->name('vashi-market.index');
    Route::get('/vashi-market/bills/create', [VashiMarketController::class, 'create'])->name('vashi-market.create');
    Route::post('/vashi-market/bills', [VashiMarketController::class, 'store'])->name('vashi-market.store');
    Route::get('/vashi-market/{id}/edit', [VashiMarketController::class, 'editVashiBill'])->name('vashi-market.edit');
    Route::put('/vashi-market/{id}', [VashiMarketController::class, 'updateVashiBill'])->name('vashi-market.update');
    Route::get('vashi-market/{vashiMarketBill}/payment', [VashiMarketController::class, 'showPaymentForm'])->name('vashi-market.payment.form');
    Route::get('vashi-market/details/{vashiMarketBill}', [VashiMarketController::class, 'showBillDetails'])->name('vashi-market.showBillDetails');

});