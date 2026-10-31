<?php

use App\Http\Controllers\Content\ContentController;
use App\Http\Controllers\Content\TagController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'seller'])->group(function () {
    Route::get('/dashboard/content', [ContentController::class, 'index'])->name('content.index');
    Route::post('/dashboard/content/store', [ContentController::class, 'store'])->name('content.store');
    Route::get('/dashboard/content/create', [ContentController::class, 'create'])->name('content.create');
    Route::get('/dashboard/content/{content}/edit', [ContentController::class, 'edit'])->name('content.edit');
    Route::put('/dashboard/content/{content}', [ContentController::class, 'update'])->name('content.update');
    Route::delete('/dashboard/content/{content}', [ContentController::class, 'destroy'])->name('content.destroy');

    Route::get('/dashboard/content/batch/create', [ContentController::class, 'createBatch'])->name('content.batch.create');
    Route::post('/dashboard/content/batch/store', [ContentController::class, 'storeBatch'])->name('content.batch.store');

    Route::get('/dashboard/folder/detail/content/create', [ContentController::class, 'createContentDetailFolder'])->name('content.detail.folder.create');
    Route::post('/dashboard/folder/detail/content/store', [ContentController::class, 'storeContentDetailFolder'])->name('content.detail.folder.store');

    Route::get('/dashboard/folder/{folder}/content/batch/create', [ContentController::class, 'createBatchFromFolder'])->name('content.folder.batch.create');
    Route::post('/dashboard/folder/{folder}/content/batch/store', [ContentController::class, 'storeBatchFromFolder'])->name('content.folder.batch.store');

    Route::put('/dashboard/content/{content}/move', [ContentController::class, 'contentMove'])->name('content.move');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/tags', [TagController::class, 'index'])->name('tag.index');
    Route::post('/tags/store', [TagController::class, 'storeTag'])->name('tag.store');
    Route::get('/tags/create', [TagController::class, 'createTag'])->name('tag.create');
    Route::get('/tags/{tag}/edit', [TagController::class, 'editTag'])->name('tag.edit');
    Route::put('/tags/{tag}', [TagController::class, 'updateTag'])->name('tag.update');
    Route::delete('/tags/{tag}', [TagController::class, 'destroyTag'])->name('tag.destroy');
});
