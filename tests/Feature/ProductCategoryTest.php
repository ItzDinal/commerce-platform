<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_belong_to_multiple_categories(): void
    {
        $sarees = Category::create([
            'name' => 'Sarees',
            'slug' => 'sarees',
        ]);

        $newArrivals = Category::create([
            'name' => 'New Arrivals',
            'slug' => 'new-arrivals',
        ]);

        $product = Product::create([
            'name' => 'Red Silk Saree',
            'slug' => 'red-silk-saree',
            'status' => 'active',
        ]);

        $product->categories()->attach([
            $sarees->id,
            $newArrivals->id,
        ]);

        $this->assertCount(2, $product->categories);

        $this->assertTrue(
            $product->categories->contains($sarees)
        );

        $this->assertTrue(
            $product->categories->contains($newArrivals)
        );
    }

    public function test_category_can_have_multiple_products(): void
    {
        $category = Category::create([
            'name' => 'Sarees',
            'slug' => 'sarees',
        ]);

        $redSaree = Product::create([
            'name' => 'Red Silk Saree',
            'slug' => 'red-silk-saree',
            'status' => 'active',
        ]);

        $blueSaree = Product::create([
            'name' => 'Blue Cotton Saree',
            'slug' => 'blue-cotton-saree',
            'status' => 'active',
        ]);

        $category->products()->attach([
            $redSaree->id,
            $blueSaree->id,
        ]);

        $this->assertCount(2, $category->products);

        $this->assertTrue(
            $category->products->contains($redSaree)
        );

        $this->assertTrue(
            $category->products->contains($blueSaree)
        );
    }
}