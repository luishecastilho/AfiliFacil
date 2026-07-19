<?php

namespace Database\Seeders;

use App\Marketplace\Importers\ShopeeImporter;
use App\Models\Marketplace;
use Illuminate\Database\Seeder;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        Marketplace::updateOrCreate(
            ['slug' => 'shopee'],
            [
                'name' => 'Shopee',
                'importer_class' => ShopeeImporter::class,
                'active' => true,
                'config' => [
                    'column_map' => [
                        'Row' => 'row_number',
                        'Seller Name' => 'seller_name',
                        'Seller Document' => 'seller_document',
                        'Seller Email' => 'seller_email',
                        'Commission Amount' => 'amount',
                        'Reference Month' => 'reference_month',
                    ],
                ],
            ],
        );
    }
}
