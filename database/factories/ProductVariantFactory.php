<?php

namespace Database\Factories;

use App\Enums\ProductVariantStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-????')),
            'price' => fake()->randomFloat(2, 500, 5000),
            'compare_at_price' => null,
            'status' => ProductVariantStatus::DRAFT,
        ];
    }
}