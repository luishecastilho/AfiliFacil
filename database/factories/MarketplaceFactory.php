<?php

namespace Database\Factories;

use App\Marketplace\Importers\ShopeeImporter;
use App\Models\Marketplace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Marketplace>
 */
class MarketplaceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
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
        ];
    }
}
