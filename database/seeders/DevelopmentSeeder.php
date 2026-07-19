<?php

namespace Database\Seeders;

use App\Enums\ImportRowStatus;
use App\Enums\ImportStatus;
use App\Enums\InvoiceStatus;
use App\Models\Import;
use App\Models\ImportRow;
use App\Models\Invoice;
use App\Models\Marketplace;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates a test user with imports at various pipeline states, for local
 * exploration of the UI without running the full upload/parse/issue flow.
 */
class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'dev@nf-facilitator.test'],
            ['name' => 'Dev User', 'password' => bcrypt('password')],
        );

        $marketplace = Marketplace::firstOrCreate(
            ['slug' => 'shopee'],
            ['name' => 'Shopee', 'importer_class' => \App\Marketplace\Importers\ShopeeImporter::class],
        );

        // Import #1: parsed but not yet validated.
        Import::factory()->for($user)->for($marketplace)->create(['status' => ImportStatus::Parsed]);

        // Import #2: validated, ready for invoice generation.
        $readyImport = Import::factory()->for($user)->for($marketplace)->validated()->create();
        ImportRow::factory()
            ->for($readyImport)
            ->count(10)
            ->state(['status' => ImportRowStatus::Valid])
            ->create(['seller_id' => Seller::factory()->for($user)]);

        // Import #3: fully invoiced.
        $invoicedImport = Import::factory()->for($user)->for($marketplace)->validated()->create();
        $seller = Seller::factory()->for($user)->create();
        Invoice::factory()
            ->for($invoicedImport)
            ->for($seller)
            ->generated()
            ->create();
    }
}
