<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\ProductVariantStatus;

class ProductVariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_variant_status_is_cast_to_enum(): void
    {
        $product = Product::create([
            'name' => 'Premium Silk Saree',
            'slug' => 'premium-silk-saree',
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SAR-RED-FS-001',
            'price' => 15000,
            'status' => 'active',
        ]);

        $variant->refresh();

        $this->assertInstanceOf(
            \App\Enums\ProductVariantStatus::class,
            $variant->status
        );

        $this->assertSame(
            ProductVariantStatus::ACTIVE,
            $variant->status
        );
    }

    public function test_variant_belongs_to_product(): void
    {
        $product = Product::create([
            'name' => 'Premium Silk Saree',
            'slug' => 'premium-silk-saree',
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SAR-RED-FS-001',
            'price' => 15000,
            'status' => 'active',
        ]);

        $this->assertTrue(
            $variant->product->is($product)
        );
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
            'sku' => 'SAR-RED-FS-001',
            'price' => 15000,
            'status' => 'active',
        ]);

        $this->assertNotEmpty($variant->id);
        $this->assertSame(26, strlen($variant->id));
        $this->assertFalse($variant->getIncrementing());
        $this->assertSame('string', $variant->getKeyType());
    }

    public function test_sku_must_be_unique(): void
    {
        $product = Product::create([
            'name' => 'Premium Silk Saree',
            'slug' => 'premium-silk-saree',
            'status' => 'active',
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SAR-RED-FS-001',
            'price' => 15000,
            'status' => 'active',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SAR-RED-FS-001',
            'price' => 16000,
            'status' => 'active',
        ]);
    }
}