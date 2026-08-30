<?php

namespace Tests\Feature\Customer;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_cart(): void
    {
        $response = $this->get(
            route('customer.cart.index')
        );

        $response->assertRedirect('/login');
    }

    public function test_authenticated_customer_can_view_cart(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(
            route('customer.cart.index')
        );

        $response->assertSuccessful();
    }

    public function test_authenticated_customer_can_add_variant_to_cart(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create();

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 15000,
        ]);

        Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 10,
            'reserved' => 0,
        ]);

        $response = $this->actingAs($user)->post(
            route('customer.cart.store'),
            [
                'product_variant_id' => $variant->id,
                'quantity' => 2,
            ]
        );

        $response->assertRedirect();

        $cart = $user->cart;

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
    }

    public function test_customer_cannot_add_nonexistent_variant_to_cart(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            route('customer.cart.store'),
            [
                'product_variant_id' => '01INVALIDVARIANT',
                'quantity' => 1,
            ]
        );

        $response->assertSessionHasErrors('product_variant_id');
    }

    public function test_adding_same_variant_increases_quantity(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create();

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 15000,
        ]);

        Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 10,
            'reserved' => 0,
        ]);

        $this->actingAs($user)->post(
            route('customer.cart.store'),
            [
                'product_variant_id' => $variant->id,
                'quantity' => 2,
            ]
        );

        $this->actingAs($user)->post(
            route('customer.cart.store'),
            [
                'product_variant_id' => $variant->id,
                'quantity' => 3,
            ]
        );

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $user->cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 5,
        ]);

        $this->assertDatabaseCount('cart_items', 1);
    }
}