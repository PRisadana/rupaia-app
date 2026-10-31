<?php

use App\Http\Controllers\License\LicenseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/licenses', [LicenseController::class, 'index'])->name('license.index');
    Route::post('/licenses/store', [LicenseController::class, 'store'])->name('license.store');
    Route::get('/licenses/create', [LicenseController::class, 'create'])->name('license.create');
    Route::get('/licenses/{license}/edit', [LicenseController::class, 'edit'])->name('license.edit');
    Route::put('/licenses/{license}', [LicenseController::class, 'update'])->name('license.update');
    Route::delete('/licenses/{license}', [LicenseController::class, 'destroy'])->name('license.destroy');
});
