<?php

use App\Http\Controllers\Cart\CartController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'buyer'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/content/{content}', [CartController::class, 'storeContent'])->name('cart.content.store');
    Route::post('/cart/bundle/{folder}', [CartController::class, 'storeBundle'])->name('cart.bundle.store');
    Route::patch('/cart/items/{cartItem}/refresh-price', [CartController::class, 'refreshPrice'])->name('cart.items.refresh-price');
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');
});
