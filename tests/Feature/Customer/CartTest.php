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

    public function test_customer_cannot_add_more_than_available_stock(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create();

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 15000,
        ]);

        Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 5,
            'reserved' => 0,
        ]);

        $response = $this->actingAs($user)->post(
            route('customer.cart.store'),
            [
                'product_variant_id' => $variant->id,
                'quantity' => 6,
            ]
        );

        $response->assertSessionHasErrors();

        $this->assertDatabaseMissing('cart_items', [
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_authenticated_customer_gets_a_persistent_cart(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(
            route('customer.cart.index')
        );

        $response->assertSuccessful();

        $cart = $user->fresh()->cart;

        $this->assertNotNull($cart);
        $this->assertSame($user->id, $cart->user_id);
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_accessing_cart_multiple_times_returns_same_cart(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('customer.cart.index'))->assertSuccessful();

        $firstCartId = $user->fresh()->cart->id;

        $this->actingAs($user)->get(route('customer.cart.index'))->assertSuccessful();

        $secondCartId = $user->fresh()->cart->id;

        $this->assertSame($firstCartId, $secondCartId);
        $this->assertDatabaseCount('carts', 1);
    }

    public function test_adding_an_item_persists_in_the_database(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'name' => 'Persistent Cart Product',
        ]);

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

        $cart = $user->fresh()->cart;

        $this->assertNotNull($cart);
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseCount('carts', 1);
    }

    public function test_customer_can_log_out_and_log_back_in_and_keep_cart_items(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'name' => 'Session Proof Saree',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 17500,
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
                'quantity' => 1,
            ]
        )->assertRedirect();

        $cartIdBeforeLogout = $user->fresh()->cart->id;

        $this->actingAs($user)->post('/logout')->assertRedirect();

        $loginResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect();
        $this->assertAuthenticatedAs($user);

        $response = $this->get(route('customer.cart.index'));

        $response->assertSuccessful();
        $response->assertSee('Session Proof Saree');

        $cartIdAfterLogin = $user->fresh()->cart->id;

        $this->assertSame($cartIdBeforeLogout, $cartIdAfterLogin);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cartIdAfterLogin,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);
        $this->assertDatabaseCount('carts', 1);
    }

    public function test_customer_cannot_have_multiple_carts(): void
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

        $this->actingAs($user)->get(route('customer.cart.index'))->assertSuccessful();
        $this->actingAs($user)->post(
            route('customer.cart.store'),
            [
                'product_variant_id' => $variant->id,
                'quantity' => 1,
            ]
        )->assertRedirect();
        $this->actingAs($user)->get(route('customer.cart.index'))->assertSuccessful();

        $this->assertDatabaseCount('carts', 1);
        $this->assertSame(1, $user->fresh()->cart()->count());
    }

    public function test_guest_cannot_create_or_access_a_cart(): void
    {
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

        $viewResponse = $this->get(route('customer.cart.index'));
        $addResponse = $this->post(route('customer.cart.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $viewResponse->assertRedirect('/login');
        $addResponse->assertRedirect('/login');
        $this->assertDatabaseCount('carts', 0);
        $this->assertDatabaseMissing('cart_items', [
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_customer_can_remove_cart_item(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create();

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 15000,
        ]);

        $cart = $user->cart()->create();

        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->delete(
            route('customer.cart.destroy', $item)
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('cart_items', [
            'id' => $item->id,
        ]);
    }

    public function test_customer_cannot_remove_another_customers_cart_item(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $product = Product::factory()->create();

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 15000,
        ]);

        $otherCart = $otherUser->cart()->create();

        $item = CartItem::create([
            'cart_id' => $otherCart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->delete(
            route('customer.cart.destroy', $item)
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
        ]);
    }

    public function test_guest_cannot_remove_cart_item(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create();

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 15000,
        ]);

        $cart = $user->cart()->create();

        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response = $this->delete(
            route('customer.cart.destroy', $item)
        );

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
        ]);
    }
}
