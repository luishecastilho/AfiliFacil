<?php

namespace App\Providers;

use App\InvoiceProvider\Contracts\InvoiceProviderInterface;
use App\InvoiceProvider\Providers\NacionalNfseProvider;
use App\InvoiceProvider\Providers\NullInvoiceProvider;
use App\Nfse\Dps\DpsBuilder;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DpsBuilder::class, fn () => new DpsBuilder(config('afilifacil.nfse.ver_aplic')));

        $this->app->bind(InvoiceProviderInterface::class, function ($app) {
            return match (config('afilifacil.invoice.driver')) {
                'nacional' => $app->make(NacionalNfseProvider::class),
                default => $app->make(NullInvoiceProvider::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
