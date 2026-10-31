<?php

use App\Http\Controllers\Catalog\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('dashboard');
Route::get('/content/{content}', [HomeController::class, 'showDetailContent'])->name('content.detail');
Route::get('/content/{user}/showcase', [HomeController::class, 'showShowcase'])->name('authors.show');
Route::get('/showcase/{showcaseItem}', [HomeController::class, 'showDetailShowcase'])->name('authors.show.detail');
Route::get('/showcase/{user}/published', [HomeController::class, 'showPublishedContent'])->name('authors.show.published');
Route::get('/folder/{folder}', [HomeController::class, 'showPublishedFolder'])->name('folder.show');
