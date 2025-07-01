<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupermarketController;
use App\Http\Controllers\MainCategoryController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Advertisement_SupermarketController;
use App\Http\Controllers\Customer_FavoritesController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InvoiceController;

//! هون للمتاجر
Route::get('/supermarkets', [SupermarketController::class, 'index']);
Route::get('/supermarkets/{id}', [SupermarketController::class, 'show']);


Route::prefix('main-categories')->group(function () {
    Route::get('/', [MainCategoryController::class, 'index']);
    Route::get('/{id}', [MainCategoryController::class, 'show']);
});

Route::prefix('subcategories')->group(function () {
    Route::get('/', [SubcategoryController::class, 'index']);
    Route::get('/{id}', [SubcategoryController::class, 'show']);
});

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
});

//! هون للاعلانات يامعلم
Route::prefix('advertisements')->group(function () {
    Route::get('/', [Advertisement_SupermarketController::class, 'index']);
    Route::get('/supermarket/{supermarketId}', [Advertisement_SupermarketController::class, 'showBySupermarket']);
    Route::get('/{id}', [Advertisement_SupermarketController::class, 'showByAdvertisement']);
});

//! مسارات تسجيل وتسجيل دخول
Route::prefix('customer')->group(function () {
    Route::post('/signup', [CustomerController::class, 'signup'])->middleware('throttle:10,1')->name('customer.signup');
    Route::post('/login', [CustomerController::class, 'login'])->middleware('throttle:10,1')->name('customer.login');

});

//! مسارات محمية (فواتير + طلبات + مفضلات + تسجيل خروج)
Route::prefix('customer')->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [CustomerController::class, 'logout'])->middleware('throttle:10,1')->name('customer.logout');
    Route::get('/profile', [CustomerController::class, 'profile'])->name('customer.profile'); // المسار الجديد
    Route::get('/favorites', [Customer_FavoritesController::class, 'index']);
    Route::post('/favorites', [Customer_FavoritesController::class, 'store']);
    Route::delete('/favorites/{favoriteId}', [Customer_FavoritesController::class, 'destroy']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{orderId}', [OrderController::class, 'update']);
    Route::delete('/orders/{orderId}', [OrderController::class, 'destroy']);
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('customer.invoices.index');
    Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('customer.invoices.show');
});

//? create by abd fattah

