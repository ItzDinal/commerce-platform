<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Cart;
use App\Models\CartItem;

class DemoDataFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_factory_creates_category(): void
    {
        $category = Category::factory()->create();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
        ]);
    }

    public function test_product_factory_creates_draft_product(): void
    {
        $product = Product::factory()->create();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'status' => ProductStatus::DRAFT->value,
        ]);
    }

    public function test_factories_can_create_multiple_records(): void
    {
        Category::factory()->count(3)->create();
        Product::factory()->count(5)->create();

        $this->assertDatabaseCount('categories', 3);
        $this->assertDatabaseCount('products', 5);
    }
    public function test_product_attribute_factory_creates_attribute(): void
    {
        $attribute = ProductAttribute::factory()->create();

        $this->assertDatabaseHas('product_attributes', [
            'id' => $attribute->id,
            'name' => $attribute->name,
            'slug' => $attribute->slug,
            'type' => 'select',
        ]);
    }

    public function test_product_attribute_value_factory_creates_value(): void
    {
        $attribute = ProductAttribute::factory()->create();

        $value = ProductAttributeValue::factory()->create([
            'product_attribute_id' => $attribute->id,
        ]);

        $this->assertDatabaseHas('product_attribute_values', [
            'id' => $value->id,
            'product_attribute_id' => $attribute->id,
            'value' => $value->value,
            'slug' => $value->slug,
        ]);
    }
    public function test_product_variant_factory_creates_variant(): void
    {
        $variant = ProductVariant::factory()->create();

        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'product_id' => $variant->product_id,
            'sku' => $variant->sku,
        ]);

        $this->assertNotNull($variant->product);
    }
    public function test_inventory_factory_creates_inventory(): void
    {
        $inventory = Inventory::factory()->create();

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'product_variant_id' => $inventory->product_variant_id,
            'quantity' => $inventory->quantity,
            'reserved' => $inventory->reserved,
        ]);

        $this->assertNotNull($inventory->productVariant);
    }
    public function test_inventory_movement_factory_creates_movement(): void
    {
        $movement = InventoryMovement::factory()->create();

        $this->assertDatabaseHas('inventory_movements', [
            'id' => $movement->id,
            'inventory_id' => $movement->inventory_id,
            'type' => $movement->type,
            'quantity' => $movement->quantity,
            'reason' => $movement->reason,
        ]);

        $this->assertNotNull($movement->inventory);
    }
    public function test_cart_factory_creates_cart(): void
    {
        $cart = Cart::factory()->create();

        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'user_id' => $cart->user_id,
        ]);

        $this->assertNotNull($cart->user);
    }
    public function test_cart_item_factory_creates_cart_item(): void
    {
        $cartItem = CartItem::factory()->create();

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'cart_id' => $cartItem->cart_id,
            'product_variant_id' => $cartItem->product_variant_id,
            'quantity' => $cartItem->quantity,
        ]);

        $this->assertNotNull($cartItem->cart);
        $this->assertNotNull($cartItem->productVariant);
    }

}
