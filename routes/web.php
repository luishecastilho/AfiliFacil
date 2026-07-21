<?php

use App\Http\Controllers\AddressLookupController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ImportRowController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceDownloadController;
use App\Http\Controllers\IssuerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Landing'))->name('landing');
Route::get('/termos', fn () => Inertia::render('Legal/Terms'))->name('terms');
Route::get('/privacidade', fn () => Inertia::render('Legal/Privacy'))->name('privacy');

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('cashier.webhook');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->middleware('fiscal.ready')->name('billing.checkout');
    Route::get('/billing/success', [BillingController::class, 'success'])->name('billing.success');
    Route::get('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::post('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');

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

    Route::get('/settings/fiscal', [IssuerController::class, 'edit'])->name('issuer.edit');
    Route::post('/settings/fiscal', [IssuerController::class, 'update'])->name('issuer.update');
    Route::post('/settings/fiscal/certificate', [IssuerController::class, 'uploadCertificate'])->name('issuer.certificate');
    Route::post('/settings/fiscal/validate', [IssuerController::class, 'validatePortal'])->name('issuer.validate');
    Route::get('/cep/{cep}', [AddressLookupController::class, 'show'])->name('cep.lookup');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
