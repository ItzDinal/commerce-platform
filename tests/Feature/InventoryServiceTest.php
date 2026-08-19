<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createInventory(
        int $quantity = 0,
        int $reserved = 0
    ): Inventory {
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

        return Inventory::create([
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
            'reserved' => $reserved,
        ]);
    }

    public function test_receive_increases_inventory_and_creates_movement(): void
    {
        $inventory = $this->createInventory();

        $movement = app(InventoryService::class)->receive(
            $inventory,
            20,
            'Initial stock'
        );

        $inventory->refresh();

        $this->assertSame(20, $inventory->quantity);
        $this->assertSame('received', $movement->type);
        $this->assertSame(20, $movement->quantity);
        $this->assertSame('Initial stock', $movement->reason);

        $this->assertDatabaseHas('inventory_movements', [
            'id' => $movement->id,
            'inventory_id' => $inventory->id,
            'type' => 'received',
            'quantity' => 20,
        ]);
    }

    public function test_remove_decreases_inventory_and_creates_movement(): void
    {
        $inventory = $this->createInventory(20);

        $movement = app(InventoryService::class)->remove(
            $inventory,
            5,
            'Manual adjustment'
        );

        $inventory->refresh();

        $this->assertSame(15, $inventory->quantity);
        $this->assertSame('adjustment', $movement->type);
        $this->assertSame(-5, $movement->quantity);
    }

    public function test_remove_cannot_exceed_available_inventory(): void
    {
        $inventory = $this->createInventory(5);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Insufficient available inventory.'
        );

        app(InventoryService::class)->remove(
            $inventory,
            6
        );
    }

    public function test_remove_considers_reserved_inventory(): void
    {
        $inventory = $this->createInventory(
            quantity: 10,
            reserved: 4
        );

        $this->assertSame(6, $inventory->available);

        $this->expectException(RuntimeException::class);

        app(InventoryService::class)->remove(
            $inventory,
            7
        );
    }

    public function test_receive_rejects_zero_or_negative_quantity(): void
    {
        $inventory = $this->createInventory();

        $this->expectException(RuntimeException::class);

        app(InventoryService::class)->receive(
            $inventory,
            0
        );
    }

    public function test_remove_rejects_zero_or_negative_quantity(): void
    {
        $inventory = $this->createInventory(10);

        $this->expectException(RuntimeException::class);

        app(InventoryService::class)->remove(
            $inventory,
            0
        );
    }
    public function test_reserve_increases_reserved_quantity(): void
    {
        $inventory = $this->createInventory(
            quantity: 20,
            reserved: 0
        );

        app(InventoryService::class)->reserve(
            $inventory,
            3
        );

        $inventory->refresh();

        $this->assertSame(20, $inventory->quantity);
        $this->assertSame(3, $inventory->reserved);
        $this->assertSame(17, $inventory->available);
    }

    public function test_release_decreases_reserved_quantity(): void
    {
        $inventory = $this->createInventory(
            quantity: 20,
            reserved: 5
        );

        app(InventoryService::class)->release(
            $inventory,
            2
        );

        $inventory->refresh();

        $this->assertSame(20, $inventory->quantity);
        $this->assertSame(3, $inventory->reserved);
        $this->assertSame(17, $inventory->available);
    }

    public function test_cannot_reserve_more_than_available_inventory(): void
    {
        $inventory = $this->createInventory(
            quantity: 10,
            reserved: 7
        );

        $this->assertSame(3, $inventory->available);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Insufficient available inventory.'
        );

        app(InventoryService::class)->reserve(
            $inventory,
            4
        );
    }

    public function test_cannot_release_more_than_reserved_inventory(): void
    {
        $inventory = $this->createInventory(
            quantity: 10,
            reserved: 3
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Cannot release more inventory than currently reserved.'
        );

        app(InventoryService::class)->release(
            $inventory,
            4
        );
    }

    public function test_reserve_rejects_zero_or_negative_quantity(): void
    {
        $inventory = $this->createInventory(
            quantity: 10
        );

        $this->expectException(RuntimeException::class);

        app(InventoryService::class)->reserve(
            $inventory,
            0
        );
    }

    public function test_release_rejects_zero_or_negative_quantity(): void
    {
        $inventory = $this->createInventory(
            quantity: 10,
            reserved: 5
        );

        $this->expectException(RuntimeException::class);

        app(InventoryService::class)->release(
            $inventory,
            0
        );
    }
}