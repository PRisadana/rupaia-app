<?php

use App\Http\Controllers\Folder\FolderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'seller'])->group(function () {
    Route::get('/dashboard/folder/', [FolderController::class, 'index'])->name('folder.index');
    Route::post('/dashboard/folder/store', [FolderController::class, 'store'])->name('folder.store');
    Route::get('/dashboard/folder/create', [FolderController::class, 'create'])->name('folder.create');
    Route::get('/dashboard/folder/{folder}/edit', [FolderController::class, 'edit'])->name('folder.edit');
    Route::put('/dashboard/folder/{folder}', [FolderController::class, 'update'])->name('folder.update');
    Route::delete('/dashboard/folder/{folder}', [FolderController::class, 'destroy'])->name('folder.destroy');

    Route::get('/dashboard/folder/detail/{folder}', [FolderController::class, 'detailFolderIndex'])->whereNumber('folder')->name('detail.folder.show');
    Route::post('/dashboard/folder/detail/store', [FolderController::class, 'storeDetailFolder'])->name('detail.folder.store');
    Route::get('/dashboard/folder/detail/create', [FolderController::class, 'createDetailFolder'])->name('detail.folder.create');

    Route::put('/dashboard/folder/{folder}/move', [FolderController::class, 'folderMove'])->name('folder.move');
});
