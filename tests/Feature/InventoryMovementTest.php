<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_can_have_multiple_movements(): void
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
            'reserved' => 0,
        ]);

        $received = InventoryMovement::create([
            'inventory_id' => $inventory->id,
            'type' => 'received',
            'quantity' => 20,
            'reason' => 'Initial stock',
        ]);

        $sold = InventoryMovement::create([
            'inventory_id' => $inventory->id,
            'type' => 'sold',
            'quantity' => -2,
            'reason' => 'Customer order',
        ]);

        $this->assertCount(2, $inventory->movements);

        $this->assertTrue(
            $inventory->movements->contains($received)
        );

        $this->assertTrue(
            $inventory->movements->contains($sold)
        );
    }

    public function test_inventory_movement_belongs_to_inventory(): void
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
            'reserved' => 0,
        ]);

        $movement = InventoryMovement::create([
            'inventory_id' => $inventory->id,
            'type' => 'received',
            'quantity' => 20,
            'reason' => 'Initial stock',
        ]);

        $this->assertTrue(
            $movement->inventory->is($inventory)
        );
    }

    public function test_inventory_movement_uses_ulid(): void
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
            'reserved' => 0,
        ]);

        $movement = InventoryMovement::create([
            'inventory_id' => $inventory->id,
            'type' => 'received',
            'quantity' => 20,
        ]);

        $this->assertNotEmpty($movement->id);
        $this->assertSame(26, strlen($movement->id));
        $this->assertFalse($movement->getIncrementing());
        $this->assertSame('string', $movement->getKeyType());
    }

    public function test_movement_quantity_can_be_positive_or_negative(): void
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
            'reserved' => 0,
        ]);

        $received = InventoryMovement::create([
            'inventory_id' => $inventory->id,
            'type' => 'received',
            'quantity' => 20,
        ]);

        $sold = InventoryMovement::create([
            'inventory_id' => $inventory->id,
            'type' => 'sold',
            'quantity' => -2,
        ]);

        $this->assertSame(20, $received->quantity);
        $this->assertSame(-2, $sold->quantity);
    }
}