<?php

use App\Http\Controllers\Kyc\AdminKycController;
use App\Http\Controllers\Kyc\KycController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/kyc', [KycController::class, 'index'])->name('kyc.index');
    Route::get('/kyc/create', [KycController::class, 'create'])->name('kyc.create');
    Route::post('/kyc', [KycController::class, 'store'])->name('kyc.store');
    Route::get('/kyc/{kyc}', [KycController::class, 'show'])->name('kyc.show');
    Route::get('/kyc/{kyc}/document', [KycController::class, 'document'])->name('kyc.seller.document');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/kyc', [AdminKycController::class, 'index'])->name('kyc.index');
    Route::get('/kyc/{kyc}', [AdminKycController::class, 'show'])->name('kyc.show');
    Route::get('/kyc/{kyc}/document', [AdminKycController::class, 'document'])->name('kyc.document');
    Route::patch('/kyc/{kyc}/verify', [AdminKycController::class, 'verify'])->name('kyc.verify');
    Route::patch('/kyc/{kyc}/reject', [AdminKycController::class, 'reject'])->name('kyc.reject');
});
