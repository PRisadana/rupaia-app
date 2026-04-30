<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminContentController;
use App\Http\Controllers\Admin\AdminShowcaseController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\PresetController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\ShowcaseController;
use App\Http\Controllers\EditingController;
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

    Route::get('/contents/{content}/edit-preview', [EditingController::class, 'showPreviewPage'])->name('editing.preview');
    Route::get('/contents/{content}/image-preview', [EditingController::class, 'imagePreview'])->name('content.image-preview');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/presets', [PresetController::class, 'index'])->name('preset.index');
    Route::post('/presets/store', [PresetController::class, 'storePreset'])->name('preset.store');
    Route::get('/presets/create', [PresetController::class, 'createPreset'])->name('preset.create');
    Route::get('/presets/{preset}/edit', [PresetController::class, 'editPreset'])->name('preset.edit');
    Route::put('/presets/{preset}', [PresetController::class, 'updatePreset'])->name('preset.update');
    Route::delete('/presets/{preset}', [PresetController::class, 'destroyPreset'])->name('preset.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('user.index');
    Route::post('/users/store', [UserController::class, 'storeUser'])->name('user.store');
    Route::get('/users/create', [UserController::class, 'createUser'])->name('user.create');
    Route::get('/users/{user}/edit', [UserController::class, 'editUser'])->name('user.edit');
    Route::put('/users/{user}', [UserController::class, 'updateUser'])->name('user.update');
    Route::delete('/users/{user}', [UserController::class, 'destroyUser'])->name('user.destroy');

    Route::get('/contents', [AdminContentController::class, 'index'])->name('content.index');
    Route::get('/contents/{content}/edit', [AdminContentController::class, 'editStatusContent'])->name('content.status.edit');
    Route::put('/contents/{content}', [AdminContentController::class, 'updateStatusContent'])->name('content.status.update');

    Route::get('/showcases', [AdminShowcaseController::class, 'index'])->name('showcase.index');
    Route::get('/showcases/{showcaseItem}/edit', [AdminShowcaseController::class, 'editStatusShowcase'])->name('showcase.status.edit');
    Route::put('/showcases/{showcaseItem}', [AdminShowcaseController::class, 'updateStatusShowcase'])->name('showcase.status.update');

    Route::get('/tags', [TagController::class, 'index'])->name('tag.index');
    Route::post('/tags/store', [TagController::class, 'storeTag'])->name('tag.store');
    Route::get('/tags/create', [TagController::class, 'createTag'])->name('tag.create');
    Route::get('/tags/{tag}/edit', [TagController::class, 'editTag'])->name('tag.edit');
    Route::put('/tags/{tag}', [TagController::class, 'updateTag'])->name('tag.update');
    Route::delete('/tags/{tag}', [TagController::class, 'destroyTag'])->name('tag.destroy');
});

require __DIR__ . '/auth.php';
