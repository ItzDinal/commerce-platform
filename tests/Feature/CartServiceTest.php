<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createCart(): Cart
    {
        $user = \App\Models\User::create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        return Cart::create([
            'user_id' => $user->id,
        ]);
    }

    private function createVariant(
        int $quantity = 10,
        int $reserved = 0
    ): ProductVariant {
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
            'quantity' => $quantity,
            'reserved' => $reserved,
        ]);

        return $variant;
    }

    public function test_add_to_cart_creates_cart_item(): void
    {
        $cart = $this->createCart();
        $variant = $this->createVariant(10);

        $item = app(CartService::class)->addToCart(
            $cart,
            $variant,
            2
        );

        $this->assertSame($variant->id, $item->product_variant_id);
        $this->assertSame(2, $item->quantity);

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
    }

    public function test_adding_same_variant_increases_existing_quantity(): void
    {
        $cart = $this->createCart();
        $variant = $this->createVariant(10);

        $service = app(CartService::class);

        $firstItem = $service->addToCart(
            $cart,
            $variant,
            2
        );

        $secondItem = $service->addToCart(
            $cart,
            $variant,
            3
        );

        $this->assertSame(
            $firstItem->id,
            $secondItem->id
        );

        $this->assertSame(5, $secondItem->quantity);

        $this->assertCount(1, $cart->fresh()->items);
    }

    public function test_add_to_cart_rejects_quantity_above_available_inventory(): void
    {
        $cart = $this->createCart();
        $variant = $this->createVariant(
            quantity: 5,
            reserved: 2
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Insufficient available inventory.'
        );

        app(CartService::class)->addToCart(
            $cart,
            $variant,
            4
        );
    }

    public function test_add_to_cart_considers_existing_cart_quantity(): void
    {
        $cart = $this->createCart();
        $variant = $this->createVariant(5);

        $service = app(CartService::class);

        $service->addToCart(
            $cart,
            $variant,
            3
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Insufficient available inventory.'
        );

        $service->addToCart(
            $cart,
            $variant,
            3
        );
    }

    public function test_add_to_cart_rejects_zero_or_negative_quantity(): void
    {
        $cart = $this->createCart();
        $variant = $this->createVariant();

        $this->expectException(RuntimeException::class);

        app(CartService::class)->addToCart(
            $cart,
            $variant,
            0
        );
    }

    public function test_update_quantity_changes_cart_item_quantity(): void
    {
        $cart = $this->createCart();
        $variant = $this->createVariant(10);

        $service = app(CartService::class);

        $item = $service->addToCart(
            $cart,
            $variant,
            2
        );

        $updatedItem = $service->updateQuantity(
            $item,
            5
        );

        $this->assertSame(5, $updatedItem->quantity);
    }

    public function test_update_quantity_cannot_exceed_available_inventory(): void
    {
        $cart = $this->createCart();
        $variant = $this->createVariant(
            quantity: 10,
            reserved: 4
        );

        $item = app(CartService::class)->addToCart(
            $cart,
            $variant,
            2
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Insufficient available inventory.'
        );

        app(CartService::class)->updateQuantity(
            $item,
            7
        );
    }

    public function test_remove_item_deletes_cart_item(): void
    {
        $cart = $this->createCart();
        $variant = $this->createVariant();

        $item = app(CartService::class)->addToCart(
            $cart,
            $variant,
            2
        );

        app(CartService::class)->removeItem($item);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $item->id,
        ]);
    }
    public function test_get_cart_loads_items_with_product_details(): void
    {
        $cart = $this->createCart();
        $variant = $this->createVariant();

        $item = app(CartService::class)->addToCart(
            $cart,
            $variant,
            2
        );

        $loadedCart = app(CartService::class)->getCart($cart);

        $this->assertCount(1, $loadedCart->items);

        $loadedItem = $loadedCart->items->first();

        $this->assertTrue(
            $loadedItem->is($item)
        );

        $this->assertTrue(
            $loadedItem->relationLoaded('productVariant')
        );

        $this->assertTrue(
            $loadedItem->productVariant->relationLoaded('product')
        );

        $this->assertTrue(
            $loadedItem->productVariant->relationLoaded('attributeValues')
        );
    }
    public function test_cart_item_calculates_line_total(): void
    {
        $cart = $this->createCart();
        $variant = $this->createVariant();

        $item = app(CartService::class)->addToCart(
            $cart,
            $variant,
            3
        );

        $this->assertSame(
            45000,
            $item->lineTotal()
        );
    }

    public function test_cart_calculates_total_items(): void
    {
        $cart = $this->createCart();

        $variantOne = $this->createVariant();

        $item = app(CartService::class)->addToCart(
            $cart,
            $variantOne,
            2
        );

        $this->assertSame(
            2,
            app(CartService::class)->totalItems(
                $cart->fresh()
            )
        );
    }

    public function test_cart_calculates_subtotal(): void
    {
        $cart = $this->createCart();

        $variantOne = $this->createVariant();

        $itemOne = app(CartService::class)->addToCart(
            $cart,
            $variantOne,
            2
        );

        $this->assertSame(
            30000,
            app(CartService::class)->subtotal(
                $cart->fresh()->load('items.productVariant')
            )
        );
    }
    public function test_get_cart_data_returns_view_cart_structure(): void
    {
        $cart = $this->createCart();
        $variant = $this->createVariant();

        $item = app(CartService::class)->addToCart(
            $cart,
            $variant,
            2
        );

        $cartData = app(CartService::class)->getCartData($cart);

        $this->assertSame(
            $cart->id,
            $cartData->id
        );

        $this->assertCount(
            1,
            $cartData->items
        );

        $cartItem = $cartData->items[0];

        $this->assertSame(
            $item->id,
            $cartItem->id
        );

        $this->assertSame(
            'Premium Silk Saree',
            $cartItem->productName
        );

        $this->assertSame(
            'SAR-RED-FS-001',
            $cartItem->sku
        );

        $this->assertSame(
            2,
            $cartItem->quantity
        );

        $this->assertSame(
            15000,
            $cartItem->unitPrice
        );

        $this->assertSame(
            30000,
            $cartItem->lineTotal
        );

        $this->assertSame(
            2,
            $cartData->totalItems
        );

        $this->assertSame(
            30000,
            $cartData->subtotal
        );
    }
}