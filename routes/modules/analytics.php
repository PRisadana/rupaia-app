<?php

use App\Http\Controllers\Analytics\SellerSalesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'seller'])->group(function () {
    Route::get('/dashboard/sales', [SellerSalesController::class, 'index'])->name('seller.sales.index');
});
