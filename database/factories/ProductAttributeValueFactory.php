<?php

namespace Database\Factories;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductAttributeValue>
 */
class ProductAttributeValueFactory extends Factory
{
    protected $model = ProductAttributeValue::class;

    public function definition(): array
    {
        $value = fake()->unique()->randomElement([
            'Red',
            'Blue',
            'Green',
            'Black',
            'White',
            'Gold',
            'Small',
            'Medium',
            'Large',
            'Silk',
            'Cotton',
            'Georgette',
        ]);

        return [
            'product_attribute_id' => ProductAttribute::factory(),
            'value' => $value,
            'slug' => Str::slug($value),
        ];
    }
}