<?php

use App\Http\Controllers\Report\AdminReportController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Report\SellerReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/contents/{content}/report', [ReportController::class, 'storeContentReport'])->name('content.report');
    Route::post('/showcases/{showcaseItem}/report', [ReportController::class, 'storeShowcaseReport'])->name('showcase.report');
});

Route::middleware(['auth', 'seller'])->group(function () {
    Route::get('/dashboard/reports', [SellerReportController::class, 'index'])->name('seller.report.index');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reports', [AdminReportController::class, 'index'])->name('report.index');
    Route::get('/reports/contents/{content}', [AdminReportController::class, 'show'])->name('report.show');
    Route::patch('/reports/contents/{content}/process', [AdminReportController::class, 'processContentReports'])->name('report.process');

    Route::get('/reports/showcases', [AdminReportController::class, 'indexShowcase'])->name('report.showcase.index');
    Route::get('/reports/showcases/{showcaseItem}', [AdminReportController::class, 'showcaseShow'])->name('report.showcase.show');
    Route::patch('/reports/showcases/{showcaseItem}/process', [AdminReportController::class, 'processShowcaseReports'])->name('report.showcase.process');
});
