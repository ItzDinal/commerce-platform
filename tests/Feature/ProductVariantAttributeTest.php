<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantAttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_variant_can_have_multiple_attribute_values(): void
    {
        $product = Product::create([
            'name' => 'Premium Silk Saree',
            'slug' => 'premium-silk-saree',
            'status' => 'active',
        ]);

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

        $red = ProductAttributeValue::create([
            'product_attribute_id' => $color->id,
            'value' => 'Red',
            'slug' => 'red',
        ]);

        $freeSize = ProductAttributeValue::create([
            'product_attribute_id' => $size->id,
            'value' => 'Free Size',
            'slug' => 'free-size',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SAR-RED-FS-001',
            'price' => 15000,
            'status' => 'active',
        ]);

        $variant->attributeValues()->attach([
            $red->id,
            $freeSize->id,
        ]);

        $this->assertCount(2, $variant->attributeValues);

        $this->assertTrue(
            $variant->attributeValues->contains($red)
        );

        $this->assertTrue(
            $variant->attributeValues->contains($freeSize)
        );
    }

    public function test_attribute_value_can_belong_to_multiple_variants(): void
    {
        $product = Product::create([
            'name' => 'Premium Silk Saree',
            'slug' => 'premium-silk-saree',
            'status' => 'active',
        ]);

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

        $variantOne = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SAR-RED-001',
            'price' => 15000,
            'status' => 'active',
        ]);

        $variantTwo = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SAR-RED-002',
            'price' => 16000,
            'status' => 'active',
        ]);

        $red->variants()->attach([
            $variantOne->id,
            $variantTwo->id,
        ]);

        $this->assertCount(2, $red->variants);
    }

    public function test_variant_uses_ulid(): void
    {
        $product = Product::create([
            'name' => 'Premium Silk Saree',
            'slug' => 'premium-silk-saree',
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SAR-RED-001',
            'price' => 15000,
            'status' => 'active',
        ]);

        $this->assertNotEmpty($variant->id);
        $this->assertSame(26, strlen($variant->id));
        $this->assertFalse($variant->getIncrementing());
        $this->assertSame('string', $variant->getKeyType());
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

    public function test_same_variant_and_attribute_value_cannot_be_attached_twice(): void
    {
        $product = Product::create([
            'name' => 'Premium Silk Saree',
            'slug' => 'premium-silk-saree',
            'status' => 'active',
        ]);

        $attribute = ProductAttribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
        ]);

        $red = ProductAttributeValue::create([
            'product_attribute_id' => $attribute->id,
            'value' => 'Red',
            'slug' => 'red',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SAR-RED-001',
            'price' => 15000,
            'status' => 'active',
        ]);

        $variant->attributeValues()->attach($red->id);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $variant->attributeValues()->attach($red->id);
    }
}