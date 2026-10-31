<?php

use App\Http\Controllers\Distribution\TransactionController;
use App\Http\Controllers\Distribution\TransactionDownloadController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'buyer'])->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/transactions/{transaction}/items/{transactionItem}/download', [TransactionDownloadController::class, 'download'])->name('transactions.items.download');
});
