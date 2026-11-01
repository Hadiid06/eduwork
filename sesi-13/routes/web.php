<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopController;

Route::get('/', [ShopController::class, 'index'])->name('home');
Route::get('/keranjang', [ShopController::class, 'cart'])->name('cart.index');
Route::post('/keranjang/{id}', [ShopController::class, 'add'])->name('cart.add');
Route::patch('/keranjang/{id}', [ShopController::class, 'update'])->name('cart.update');
Route::delete('/keranjang/{id}', [ShopController::class, 'remove'])->name('cart.remove');
