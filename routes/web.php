<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContentController;
use App\Models\Content;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('dashboard');

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

    Route::get('/dashboard/content/create', [ContentController::class, 'create'])->name('content.create');
    Route::post('/dashboard/content', [ContentController::class, 'store'])->name('content.store');
    Route::get('/dashboard/content/{content}/edit', [ContentController::class, 'edit'])->name('content.edit');
    Route::put('/dashboard/content/{content}', [ContentController::class, 'update'])->name('content.update');
    Route::delete('/dashboard/content/{content}', [ContentController::class, 'destroy'])->name('content.destroy');

    Route::get('/dashboard/folder', [ContentController::class, 'storeFolder'])->name('folder.store');
});

require __DIR__ . '/auth.php';
