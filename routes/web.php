<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\ShowcaseController;
use Illuminate\Support\Facades\Route;
use PharIo\Manifest\Author;

Route::get('/', [HomeController::class, 'index'])->name('dashboard');
Route::get('/content/{content}', [HomeController::class, 'showDetailContent'])->name('content.detail');
Route::get('/content/{user}/showcase', [HomeController::class, 'showShowcase'])->name('authors.show');
Route::get('/showcase/{showcaseItem}', [HomeController::class, 'showDetailShowcase'])->name('authors.show.detail');
Route::get('/showcase/{user}/published', [HomeController::class, 'showPublishedContent'])->name('authors.show.published');
Route::get('/folder/{folder}', [HomeController::class, 'showPublishedFolder'])->name('folder.show');

Route::get('/about', function () {
    return view('about');
});

// Route::get('/', function () {
//     return view('welcome');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard/content', [ContentController::class, 'index'])->name('content.index');
    Route::post('/dashboard/content/store', [ContentController::class, 'store'])->name('content.store');
    Route::get('/dashboard/content/create', [ContentController::class, 'create'])->name('content.create');
    Route::get('/dashboard/content/{content}/edit', [ContentController::class, 'edit'])->name('content.edit');
    Route::put('/dashboard/content/{content}', [ContentController::class, 'update'])->name('content.update');
    Route::delete('/dashboard/content/{content}', [ContentController::class, 'destroy'])->name('content.destroy');

    Route::get('/dashboard/folder/', [ContentController::class, 'folderIndex'])->name('folder.index');
    Route::post('/dashboard/folder/store', [ContentController::class, 'storeFolder'])->name('folder.store');
    Route::get('/dashboard/folder/create', [ContentController::class, 'createFolder'])->name('folder.create');
    Route::get('/dashboard/folder/{folder}/edit', [ContentController::class, 'editFolder'])->name('folder.edit');
    Route::put('/dashboard/folder/{folder}', [ContentController::class, 'updateFolder'])->name('folder.update');
    Route::delete('/dashboard/folder/{folder}', [ContentController::class, 'destroyFolder'])->name('folder.destroy');

    // Route::get('/dashboard/folder/detail/', [ContentController::class, 'detailFolderIndex'])->name('detail.folder.index');
    Route::get('/dashboard/folder/detail/{folder}', [ContentController::class, 'detailFolderIndex'])->whereNumber('folder')->name('detail.folder.show');
    Route::post('/dashboard/folder/detail/store', [ContentController::class, 'storeDetailFolder'])->name('detail.folder.store');
    Route::get('/dashboard/folder/detail/create', [ContentController::class, 'createDetailFolder'])->name('detail.folder.create');

    Route::get('/dashboard/folder/detail/content/create', [ContentController::class, 'createContentDetailFolder'])->name('content.detail.folder.create');
    Route::post('/dashboard/folder/detail/content/store', [ContentController::class, 'storeContentDetailFolder'])->name('content.detail.folder.store');

    // Route::get('/dashboard/content/{content}/move', [ContentController::class, 'contentMoveForm'])->name('content.move.form');
    Route::put('/dashboard/content/{content}/move', [ContentController::class, 'contentMove'])->name('content.move');
    Route::put('/dashboard/folder/{folder}/move', [ContentController::class, 'folderMove'])->name('folder.move');

    Route::get('/dashboard/showcase/', [ShowcaseController::class, 'showcaseIndex'])->name('showcase.index');
    Route::post('/dashboard/showcase/store', [ShowcaseController::class, 'showcaseStore'])->name('showcase.store');
    Route::get('/dashboard/showcase/create', [ShowcaseController::class, 'showcaseCreate'])->name('showcase.create');
    Route::get('/dashboard/showcase/{showcaseItem}/edit', [ShowcaseController::class, 'showcaseEdit'])->name('showcase.edit');
    Route::put('/dashboard/showcase/{showcaseItem}', [ShowcaseController::class, 'showcaseUpdate'])->name('showcase.update');
    Route::delete('/dashboard/showcase/{showcaseItem}', [ShowcaseController::class, 'showcaseDestroy'])->name('showcase.destroy');
    Route::post('/dashboard/showcase/store-from-content', [ShowcaseController::class, 'showcaseFromContentStore'])->name('showcase.from.content.store');
    Route::get('/dashboard/showcase/create-from-content', [ShowcaseController::class, 'showcaseFromContentCreate'])->name('showcase.from.content.create');
});

require __DIR__ . '/auth.php';
