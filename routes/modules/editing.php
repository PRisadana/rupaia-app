<?php

use App\Http\Controllers\Editing\EditingController;
use App\Http\Controllers\Editing\PresetController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/contents/{content}/edit-preview', [EditingController::class, 'showPreviewPage'])->name('editing.preview');
    Route::get('/contents/{content}/image-preview', [EditingController::class, 'imagePreview'])->name('content.image-preview');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/presets', [PresetController::class, 'index'])->name('preset.index');
    Route::post('/presets/store', [PresetController::class, 'storePreset'])->name('preset.store');
    Route::get('/presets/create', [PresetController::class, 'createPreset'])->name('preset.create');
    Route::get('/presets/{preset}/edit', [PresetController::class, 'editPreset'])->name('preset.edit');
    Route::put('/presets/{preset}', [PresetController::class, 'updatePreset'])->name('preset.update');
    Route::delete('/presets/{preset}', [PresetController::class, 'destroyPreset'])->name('preset.destroy');
});
