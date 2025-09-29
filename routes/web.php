<?php

use App\Http\Controllers\ProductListController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
    // Route::post('/list/products', [ProductListController::class, 'oilAddProduct'])->name('admin.OilAddProduct'); // This will be replaced

});

// Route::get('/login', [App\Http\Controllers\UserController::class, 'showLoginForm'])->name('login.form');
// Route::post('/login', [App\Http\Controllers\UserController::class, 'loginstore'])->name('login.store');
