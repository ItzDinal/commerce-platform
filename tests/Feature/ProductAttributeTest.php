<?php

namespace Tests\Feature;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class ProductAttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribute_can_have_multiple_values(): void
    {
        $color = ProductAttribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
        ]);

        $red = ProductAttributeValue::create([
            'product_attribute_id' => $color->id,
            'value' => 'Red',
            'slug' => 'red',
        ]);

        $blue = ProductAttributeValue::create([
            'product_attribute_id' => $color->id,
            'value' => 'Blue',
            'slug' => 'blue',
        ]);

        $this->assertCount(2, $color->values);

        $this->assertTrue(
            $color->values->contains($red)
        );

        $this->assertTrue(
            $color->values->contains($blue)
        );
    }

    public function test_attribute_value_belongs_to_attribute(): void
    {
        $color = ProductAttribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
        ]);

        $red = ProductAttributeValue::create([
            'product_attribute_id' => $color->id,
            'value' => 'Red',
            'slug' => 'red',
        ]);

        $this->assertTrue(
            $red->attribute->is($color)
        );
    }

    public function test_attribute_uses_ulid(): void
    {
        $attribute = ProductAttribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
        ]);

        $this->assertNotEmpty($attribute->id);
        $this->assertSame(26, strlen($attribute->id));
        $this->assertFalse($attribute->getIncrementing());
        $this->assertSame('string', $attribute->getKeyType());
    }

    public function test_attribute_value_uses_ulid(): void
    {
        $attribute = ProductAttribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
        ]);

        $value = ProductAttributeValue::create([
            'product_attribute_id' => $attribute->id,
            'value' => 'Red',
            'slug' => 'red',
        ]);

        $this->assertNotEmpty($value->id);
        $this->assertSame(26, strlen($value->id));
        $this->assertFalse($value->getIncrementing());
        $this->assertSame('string', $value->getKeyType());
    }

    public function test_attribute_value_slug_must_be_unique_within_attribute(): void
    {
        $color = ProductAttribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
        ]);

        ProductAttributeValue::create([
            'product_attribute_id' => $color->id,
            'value' => 'Red',
            'slug' => 'red',
        ]);

        $this->expectException(QueryException::class);

        ProductAttributeValue::create([
            'product_attribute_id' => $color->id,
            'value' => 'Another Red',
            'slug' => 'red',
        ]);
    }

    public function test_same_value_slug_can_exist_under_different_attributes(): void
    {
        $color = ProductAttribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
        ]);

        $size = ProductAttribute::create([
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'select',
        ]);

        ProductAttributeValue::create([
            'product_attribute_id' => $color->id,
            'value' => 'Red',
            'slug' => 'red',
        ]);

        $sizeValue = ProductAttributeValue::create([
            'product_attribute_id' => $size->id,
            'value' => 'Red',
            'slug' => 'red',
        ]);

        $this->assertDatabaseHas('product_attribute_values', [
            'id' => $sizeValue->id,
            'product_attribute_id' => $size->id,
            'slug' => 'red',
        ]);
    }
}