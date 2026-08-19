<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_variant_can_have_inventory(): void
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

        $inventory = Inventory::create([
            'product_variant_id' => $variant->id,
            'quantity' => 20,
            'reserved' => 3,
        ]);

        $this->assertTrue(
            $variant->inventory->is($inventory)
        );
    }

    public function test_inventory_belongs_to_variant(): void
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

        $inventory = Inventory::create([
            'product_variant_id' => $variant->id,
            'quantity' => 20,
            'reserved' => 3,
        ]);

        $this->assertTrue(
            $inventory->productVariant->is($variant)
        );
    }

    public function test_inventory_uses_ulid(): void
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

        $inventory = Inventory::create([
            'product_variant_id' => $variant->id,
            'quantity' => 20,
            'reserved' => 3,
        ]);

        $this->assertNotEmpty($inventory->id);
        $this->assertSame(26, strlen($inventory->id));
        $this->assertFalse($inventory->getIncrementing());
        $this->assertSame('string', $inventory->getKeyType());
    }

    public function test_available_quantity_is_calculated_from_quantity_and_reserved(): void
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

        $inventory = Inventory::create([
            'product_variant_id' => $variant->id,
            'quantity' => 20,
            'reserved' => 3,
        ]);

        $this->assertSame(17, $inventory->available);
    }

    public function test_variant_can_have_only_one_inventory_record(): void
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

        Inventory::create([
            'product_variant_id' => $variant->id,
            'quantity' => 20,
            'reserved' => 3,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Inventory::create([
            'product_variant_id' => $variant->id,
            'quantity' => 10,
            'reserved' => 1,
        ]);
    }
}