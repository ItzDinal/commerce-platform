<?php

namespace Database\Factories;

use App\Models\ProductAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductAttribute>
 */
class ProductAttributeFactory extends Factory
{
    protected $model = ProductAttribute::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Color',
            'Size',
            'Material',
            'Pattern',
            'Fabric',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => 'select',
        ];
    }
}