<?php

use App\Http\Controllers\Payout\AdminPayoutController;
use App\Http\Controllers\Payout\SellerPayoutController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'seller'])->group(function () {
    Route::get('/dashboard/payouts', [SellerPayoutController::class, 'index'])->name('seller.payout.index');
    Route::post('/dashboard/payouts', [SellerPayoutController::class, 'store'])->name('seller.payout.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/payouts', [AdminPayoutController::class, 'index'])->name('payout.index');
    Route::get('/payouts/{payout}', [AdminPayoutController::class, 'show'])->name('payout.show');
    Route::patch('/payouts/{payout}/paid', [AdminPayoutController::class, 'markPaid'])->name('payout.paid');
    Route::patch('/payouts/{payout}/reject', [AdminPayoutController::class, 'reject'])->name('payout.reject');
});
