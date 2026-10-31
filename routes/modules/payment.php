<?php

use App\Http\Controllers\Payment\MidtransWebhookController;
use App\Http\Controllers\Payment\PaymentController;
use Illuminate\Support\Facades\Route;

Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handle'])->name('midtrans.notification');

Route::middleware(['auth', 'buyer'])->group(function () {
    Route::post('/transactions/{transaction}/payment', [PaymentController::class, 'create'])->name('transactions.payment.create');
});
