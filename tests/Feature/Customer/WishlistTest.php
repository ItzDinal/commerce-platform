<?php

namespace Tests\Feature\Customer;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\WishlistItem;
use App\Models\Inventory;
use App\Models\ProductVariant;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_add_product_to_wishlist(): void
    {
        $product = Product::factory()->create();

        $response = $this->post(
            route('customer.wishlist.store'),
            ['product_id' => $product->id]
        );

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('wishlist_items', [
            'product_id' => $product->id,
        ]);
    }

    public function test_authenticated_customer_can_add_product_to_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->post(
            route('customer.wishlist.store'),
            ['product_id' => $product->id]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_customer_cannot_add_same_product_to_wishlist_twice(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->post(
            route('customer.wishlist.store'),
            ['product_id' => $product->id]
        );

        $this->actingAs($user)->post(
            route('customer.wishlist.store'),
            ['product_id' => $product->id]
        );

        $this->assertDatabaseCount('wishlist_items', 1);
    }

    public function test_customer_cannot_add_nonexistent_product_to_wishlist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            route('customer.wishlist.store'),
            ['product_id' => '01INVALIDPRODUCT']
        );

        $response->assertSessionHasErrors('product_id');
    }
    public function test_authenticated_customer_can_remove_product_from_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)->delete(
            route('customer.wishlist.destroy', $wishlistItem)
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('wishlist_items', [
            'id' => $wishlistItem->id,
        ]);
    }

    public function test_customer_cannot_remove_another_customers_wishlist_item(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = Product::factory()->create();

        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $otherUser->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)->delete(
            route('customer.wishlist.destroy', $wishlistItem)
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('wishlist_items', [
            'id' => $wishlistItem->id,
            'user_id' => $otherUser->id,
        ]);
    }

    public function test_guest_cannot_remove_product_from_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->delete(
            route('customer.wishlist.destroy', $wishlistItem)
        );

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('wishlist_items', [
            'id' => $wishlistItem->id,
        ]);
    }
    public function test_guest_cannot_view_wishlist(): void
    {
        $response = $this->get(
            route('customer.wishlist.index')
        );

        $response->assertRedirect('/login');
    }

    public function test_authenticated_customer_can_view_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Test Product',
        ]);

        $user->wishlistItems()->create([
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)->get(
            route('customer.wishlist.index')
        );

        $response->assertSuccessful();
        $response->assertSee('Test Product');
    }

    public function test_customer_sees_only_their_wishlist_items(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userProduct = Product::factory()->create([
            'name' => 'My Wishlist Product',
        ]);

        $otherProduct = Product::factory()->create([
            'name' => 'Other Customer Product',
        ]);

        $user->wishlistItems()->create([
            'product_id' => $userProduct->id,
        ]);

        $otherUser->wishlistItems()->create([
            'product_id' => $otherProduct->id,
        ]);

        $response = $this->actingAs($user)->get(
            route('customer.wishlist.index')
        );

        $response->assertSuccessful();
        $response->assertSee('My Wishlist Product');
        $response->assertDontSee('Other Customer Product');
    }

    public function test_wishlist_can_contain_multiple_products(): void
    {
        $user = User::factory()->create();

        $products = Product::factory()->count(3)->create();

        foreach ($products as $product) {
            $user->wishlistItems()->create([
                'product_id' => $product->id,
            ]);
        }

        $response = $this->actingAs($user)->get(
            route('customer.wishlist.index')
        );

        $response->assertSuccessful();

        foreach ($products as $product) {
            $response->assertSee($product->name);
        }
    }

    public function test_empty_wishlist_shows_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(
            route('customer.wishlist.index')
        );

        $response->assertSuccessful();
        $response->assertSee('Your wishlist is empty.');
    }
    public function test_wishlist_persists_after_customer_logs_out_and_back_in(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'name' => 'Persistent Wishlist Product',
        ]);

        // Customer adds product to wishlist
        $this->actingAs($user)->post(
            route('customer.wishlist.store'),
            [
                'product_id' => $product->id,
            ]
        );

        // Customer logs out
        $this->post('/logout');

        // Customer logs back in
        $this->actingAs($user);

        // Wishlist should still contain the product
        $response = $this->get(
            route('customer.wishlist.index')
        );

        $response->assertSuccessful();
        $response->assertSee('Persistent Wishlist Product');

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }
    public function test_customer_can_move_wishlist_item_to_cart(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'name' => 'Premium Silk Saree',
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

        $wishlistItem = $user->wishlistItems()->create([
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)->post(
            route('customer.wishlist.move-to-cart', $wishlistItem),
            [
                'product_variant_id' => $variant->id,
                'quantity' => 1,
            ]
        );

        $response->assertRedirect();

        $cart = $user->cart;

        $this->assertNotNull($cart);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->assertDatabaseMissing('wishlist_items', [
            'id' => $wishlistItem->id,
        ]);
    }
    public function test_customer_cannot_move_another_customers_wishlist_item_to_cart(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $product = Product::factory()->create();

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 10,
            'reserved' => 0,
        ]);

        $wishlistItem = $otherUser->wishlistItems()->create([
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)->post(
            route('customer.wishlist.move-to-cart', $wishlistItem),
            [
                'product_variant_id' => $variant->id,
                'quantity' => 1,
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseMissing('cart_items', [
            'product_variant_id' => $variant->id,
        ]);

        $this->assertDatabaseHas('wishlist_items', [
            'id' => $wishlistItem->id,
        ]);
    }

    public function test_customer_cannot_move_wrong_product_variant_to_cart(): void
    {
        $user = User::factory()->create();

        $wishlistProduct = Product::factory()->create([
            'name' => 'Wishlist Product',
        ]);

        $otherProduct = Product::factory()->create([
            'name' => 'Other Product',
        ]);

        $wrongVariant = ProductVariant::factory()->create([
            'product_id' => $otherProduct->id,
        ]);

        Inventory::factory()->create([
            'product_variant_id' => $wrongVariant->id,
            'quantity' => 10,
            'reserved' => 0,
        ]);

        $wishlistItem = $user->wishlistItems()->create([
            'product_id' => $wishlistProduct->id,
        ]);

        $response = $this->actingAs($user)->post(
            route('customer.wishlist.move-to-cart', $wishlistItem),
            [
                'product_variant_id' => $wrongVariant->id,
                'quantity' => 1,
            ]
        );

        $response->assertSessionHasErrors('product_variant_id');

        $this->assertDatabaseMissing('cart_items', [
            'product_variant_id' => $wrongVariant->id,
        ]);

        $this->assertDatabaseHas('wishlist_items', [
            'id' => $wishlistItem->id,
        ]);
    }
}