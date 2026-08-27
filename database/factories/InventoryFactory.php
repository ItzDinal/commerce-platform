<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventory>
 */
class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(0, 100);

        return [
            'product_variant_id' => ProductVariant::factory(),
            'quantity' => $quantity,
            'reserved' => 0,
        ];
    }
}