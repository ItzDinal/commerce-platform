<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_a_cart(): void
    {
        $user = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $cart = Cart::create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue(
            $user->cart->is($cart)
        );

        $this->assertTrue(
            $cart->user->is($user)
        );
    }

    public function test_cart_can_have_multiple_items(): void
    {
        $user = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $product = Product::create([
            'name' => 'Premium Silk Saree',
            'slug' => 'premium-silk-saree',
            'status' => 'active',
        ]);

        $variantOne = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SAR-RED-001',
            'price' => 15000,
            'status' => 'active',
        ]);

        $variantTwo = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SAR-BLUE-001',
            'price' => 16000,
            'status' => 'active',
        ]);

        $cart = Cart::create([
            'user_id' => $user->id,
        ]);

        $itemOne = CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variantOne->id,
            'quantity' => 2,
        ]);

        $itemTwo = CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variantTwo->id,
            'quantity' => 1,
        ]);

        $this->assertCount(2, $cart->items);

        $this->assertTrue(
            $cart->items->contains($itemOne)
        );

        $this->assertTrue(
            $cart->items->contains($itemTwo)
        );
    }

    public function test_cart_item_belongs_to_product_variant(): void
    {
        $user = User::create([
            'name' => 'Test Customer',
            'email' => 'customer2@example.com',
            'password' => 'password',
        ]);

        $product = Product::create([
            'name' => 'Premium Silk Saree',
            'slug' => 'premium-silk-saree-2',
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SAR-RED-002',
            'price' => 15000,
            'status' => 'active',
        ]);

        $cart = Cart::create([
            'user_id' => $user->id,
        ]);

        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->assertTrue(
            $item->productVariant->is($variant)
        );

        $this->assertTrue(
            $variant->cartItems->contains($item)
        );
    }

    public function test_cart_item_uses_ulid(): void
    {
        $user = User::create([
            'name' => 'Test Customer',
            'email' => 'customer3@example.com',
            'password' => 'password',
        ]);

        $cart = Cart::create([
            'user_id' => $user->id,
        ]);

        $this->assertNotEmpty($cart->id);
        $this->assertSame(26, strlen($cart->id));
        $this->assertFalse($cart->getIncrementing());
        $this->assertSame('string', $cart->getKeyType());
    }
}