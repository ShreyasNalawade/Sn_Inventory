<?php

use App\Http\Controllers\ProductListController;
use App\Http\Controllers\VashiMarketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('admin.login');
});

// Route::get('/list/products', function () {
//     return view('admin.listofPrice');
// });

// Route::get('/login', function () {
//     return view('admin.login');
// });

Route::group(['middleware' => ['web']], function () {
    // Your web routes here
    Route::controller(App\Http\Controllers\UserController::class)->group(function () {
        Route::get('/login', 'showLoginForm')->name('login.form');
        Route::post('/login/store', 'loginStore')->name('login.store');
        Route::get('/list/products', 'showlistofPrice')->name('admin.listofPrice');
    });

    Route::get('/products/create', [ProductListController::class, 'create'])->name('admin.product.create');
    Route::post('/products', [ProductListController::class, 'store'])->name('admin.product.store');
    Route::get('/products/{product}/edit', [ProductListController::class, 'edit'])->name('admin.product.edit');
    Route::put('/products/{product}', [ProductListController::class, 'update'])->name('admin.product.update');
    Route::get('/vashi-market/bills', [\App\Http\Controllers\VashiMarketController::class, 'index'])->name('vashi-market.index');
    Route::get('/vashi-market/bills/create', [\App\Http\Controllers\VashiMarketController::class, 'create'])->name('vashi-market.create');
    Route::post('/vashi-market/bills', [\App\Http\Controllers\VashiMarketController::class, 'store'])->name('vashi-market.store');
    Route::get('/vashi-market/{id}/edit', [VashiMarketController::class, 'editVashiBill'])->name('vashi-market.edit');
    Route::put('/vashi-market/{id}', [VashiMarketController::class, 'updateVashiBill'])->name('vashi-market.update');

    Route::get('vashi-market/{vashiMarketBill}/payment', [VashiMarketController::class, 'showPaymentForm'])->name('vashi-market.payment.form');
    Route::get('vashi-market/details/{vashiMarketBill}', [VashiMarketController::class, 'showBillDetails'])->name('vashi-market.showBillDetails');


    // Route::post('/list/products', [ProductListController::class, 'oilAddProduct'])->name('admin.OilAddProduct'); // This will be replaced

});

// Route::get('/login', [App\Http\Controllers\UserController::class, 'showLoginForm'])->name('login.form');
// Route::post('/login', [App\Http\Controllers\UserController::class, 'loginstore'])->name('login.store');
