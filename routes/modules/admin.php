<?php

use App\Http\Controllers\Admin\AdminContentController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFolderController;
use App\Http\Controllers\Admin\AdminShowcaseController;
use App\Http\Controllers\Admin\AdminTransactionController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('user.index');
    Route::post('/users/store', [UserController::class, 'storeUser'])->name('user.store');
    Route::get('/users/create', [UserController::class, 'createUser'])->name('user.create');
    Route::get('/users/{user}/edit', [UserController::class, 'editUser'])->name('user.edit');
    Route::put('/users/{user}', [UserController::class, 'updateUser'])->name('user.update');
    Route::delete('/users/{user}', [UserController::class, 'destroyUser'])->name('user.destroy');

    Route::get('/contents', [AdminContentController::class, 'index'])->name('content.index');
    Route::get('/contents/{content}/edit', [AdminContentController::class, 'editStatusContent'])->name('content.status.edit');
    Route::put('/contents/{content}', [AdminContentController::class, 'updateStatusContent'])->name('content.status.update');

    Route::get('/folders', [AdminFolderController::class, 'index'])->name('folder.index');
    Route::get('/folders/{folder}/edit', [AdminFolderController::class, 'editStatusFolder'])->name('folder.status.edit');
    Route::put('/folders/{folder}', [AdminFolderController::class, 'updateStatusFolder'])->name('folder.status.update');

    Route::get('/showcases', [AdminShowcaseController::class, 'index'])->name('showcase.index');
    Route::get('/showcases/{showcaseItem}/edit', [AdminShowcaseController::class, 'editStatusShowcase'])->name('showcase.status.edit');
    Route::put('/showcases/{showcaseItem}', [AdminShowcaseController::class, 'updateStatusShowcase'])->name('showcase.status.update');

    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transaction.index');
    Route::get('/transactions/{transaction}', [AdminTransactionController::class, 'show'])->name('transaction.show');
    Route::post('/transactions/{transaction}/sync-status', [AdminTransactionController::class, 'syncStatus'])->name('transaction.sync-status');
});
