<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ImportRowController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceDownloadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SellerController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('imports', ImportController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::get('/imports/{import}/rows', [ImportRowController::class, 'index'])->name('imports.rows.index');

    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/imports/{import}/invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::get('/imports/{import}/invoices/progress', [InvoiceController::class, 'progress'])->name('invoices.progress');
    Route::post('/invoices/{invoice}/retry', [InvoiceController::class, 'retry'])->name('invoices.retry');

    Route::get('/invoices/{invoice}/download/{type}', [InvoiceDownloadController::class, 'show'])->name('invoices.download');
    Route::get('/imports/{import}/download/zip', [InvoiceDownloadController::class, 'zip'])->name('imports.download.zip');

    Route::get('/sellers', [SellerController::class, 'index'])->name('sellers.index');
    Route::get('/sellers/{seller}/edit', [SellerController::class, 'edit'])->name('sellers.edit');
    Route::patch('/sellers/{seller}', [SellerController::class, 'update'])->name('sellers.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
