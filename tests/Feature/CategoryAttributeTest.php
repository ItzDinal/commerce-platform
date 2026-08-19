<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ProductAttribute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryAttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_have_multiple_attributes(): void
    {
        $category = Category::create([
            'name' => 'Sarees',
            'slug' => 'sarees',
        ]);

        $color = ProductAttribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
        ]);

        $fabric = ProductAttribute::create([
            'name' => 'Fabric',
            'slug' => 'fabric',
            'type' => 'select',
        ]);

        $category->attributes()->attach($color->id);
        $category->attributes()->attach($fabric->id);

        $this->assertCount(2, $category->attributes);

        $this->assertTrue(
            $category->attributes->contains($color)
        );

        $this->assertTrue(
            $category->attributes->contains($fabric)
        );
    }

    public function test_attribute_can_belong_to_multiple_categories(): void
    {
        $sarees = Category::create([
            'name' => 'Sarees',
            'slug' => 'sarees',
        ]);

        $dresses = Category::create([
            'name' => 'Dresses',
            'slug' => 'dresses',
        ]);

        $color = ProductAttribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
        ]);

        $color->categories()->attach([
            $sarees->id,
            $dresses->id,
        ]);

        $this->assertCount(2, $color->categories);

        $this->assertTrue(
            $color->categories->contains($sarees)
        );

        $this->assertTrue(
            $color->categories->contains($dresses)
        );
    }

    public function test_category_attribute_pivot_stores_configuration(): void
    {
        $category = Category::create([
            'name' => 'Sarees',
            'slug' => 'sarees',
        ]);

        $color = ProductAttribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
        ]);

        $category->attributes()->attach($color->id, [
            'is_required' => true,
            'is_filterable' => true,
            'sort_order' => 1,
        ]);

        $attribute = $category->attributes()->first();

        $this->assertTrue(
            $attribute->pivot->is_required
        );

        $this->assertTrue(
            $attribute->pivot->is_filterable
        );

        $this->assertSame(
            1,
            $attribute->pivot->sort_order
        );
    }

    public function test_category_attribute_relationship_can_be_synced(): void
    {
        $category = Category::create([
            'name' => 'Sarees',
            'slug' => 'sarees',
        ]);

        $color = ProductAttribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
        ]);

        $fabric = ProductAttribute::create([
            'name' => 'Fabric',
            'slug' => 'fabric',
            'type' => 'select',
        ]);

        $pattern = ProductAttribute::create([
            'name' => 'Pattern',
            'slug' => 'pattern',
            'type' => 'select',
        ]);

        $category->attributes()->sync([
            $color->id => [
                'is_required' => true,
                'is_filterable' => true,
                'sort_order' => 1,
            ],
            $fabric->id => [
                'is_required' => true,
                'is_filterable' => true,
                'sort_order' => 2,
            ],
        ]);

        $this->assertCount(2, $category->attributes);

        $this->assertDatabaseMissing('category_attribute', [
            'category_id' => $category->id,
            'product_attribute_id' => $pattern->id,
        ]);
    }
}