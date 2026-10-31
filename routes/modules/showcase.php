<?php

use App\Http\Controllers\Showcase\ShowcaseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'seller'])->group(function () {
    Route::get('/dashboard/showcase/', [ShowcaseController::class, 'showcaseIndex'])->name('showcase.index');
    Route::post('/dashboard/showcase/store', [ShowcaseController::class, 'showcaseStore'])->name('showcase.store');
    Route::get('/dashboard/showcase/create', [ShowcaseController::class, 'showcaseCreate'])->name('showcase.create');
    Route::get('/dashboard/showcase/{showcaseItem}/edit', [ShowcaseController::class, 'showcaseEdit'])->name('showcase.edit');
    Route::put('/dashboard/showcase/{showcaseItem}', [ShowcaseController::class, 'showcaseUpdate'])->name('showcase.update');
    Route::delete('/dashboard/showcase/{showcaseItem}', [ShowcaseController::class, 'showcaseDestroy'])->name('showcase.destroy');
    Route::post('/dashboard/showcase/store-from-content', [ShowcaseController::class, 'showcaseFromContentStore'])->name('showcase.from.content.store');
    Route::get('/dashboard/showcase/create-from-content', [ShowcaseController::class, 'showcaseFromContentCreate'])->name('showcase.from.content.create');
});
