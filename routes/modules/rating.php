<?php

use App\Http\Controllers\Rating\AdminRatingController;
use App\Http\Controllers\Rating\RatingController;
use App\Http\Controllers\Rating\SellerRatingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'buyer'])->group(function () {
    Route::post('/transactions/{transaction}/items/{transactionItem}/rating', [RatingController::class, 'store'])->name('transactions.items.rating.store');
});

Route::middleware(['auth', 'seller'])->group(function () {
    Route::get('/dashboard/ratings', [SellerRatingController::class, 'index'])->name('seller.rating.index');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/ratings', [AdminRatingController::class, 'index'])->name('rating.index');
    Route::patch('/ratings/{rating}/status', [AdminRatingController::class, 'updateStatus'])->name('rating.status');
});
