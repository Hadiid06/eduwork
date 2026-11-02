<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShopController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/keranjang', [ShopController::class, 'cart'])->name('cart.index');
Route::post('/keranjang/{id}', [ShopController::class, 'add'])->name('cart.add');
Route::patch('/keranjang/{id}', [ShopController::class, 'update'])->name('cart.update');
Route::delete('/keranjang/{id}', [ShopController::class, 'remove'])->name('cart.remove');
