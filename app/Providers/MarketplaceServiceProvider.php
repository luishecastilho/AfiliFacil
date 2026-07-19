<?php

namespace App\Providers;

use App\Marketplace\Contracts\MarketplaceImporterInterface;
use App\Marketplace\Importers\ShopeeImporter;
use Illuminate\Support\ServiceProvider;

/**
 * Binds the default MarketplaceImporterInterface implementation.
 *
 * Per-import resolution actually happens by resolving the concrete class named in
 * marketplace.importer_class (e.g. app($marketplace->importer_class)) rather than
 * through this fixed binding — this provider only supplies the fallback/default.
 */
class MarketplaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MarketplaceImporterInterface::class, ShopeeImporter::class);
    }
}
