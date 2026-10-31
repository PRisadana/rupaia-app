<?php

use App\Http\Controllers\Policy\PolicyController;
use Illuminate\Support\Facades\Route;

Route::get('/policies', [PolicyController::class, 'index'])->name('policies.index');
