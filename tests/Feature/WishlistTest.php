<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_item_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $wishlistItem = $user->wishlistItems()->create([
            'product_id' => $product->id,
        ]);

        $this->assertTrue($wishlistItem->user->is($user));
        $this->assertTrue($user->fresh()->wishlistItems->contains($wishlistItem));
    }

    public function test_wishlist_item_belongs_to_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $wishlistItem = $user->wishlistItems()->create([
            'product_id' => $product->id,
        ]);

        $this->assertTrue($wishlistItem->product->is($product));
    }

    public function test_user_can_have_multiple_wishlist_items(): void
    {
        $user = User::factory()->create();

        $products = Product::factory()->count(3)->create();

        foreach ($products as $product) {
            $user->wishlistItems()->create([
                'product_id' => $product->id,
            ]);
        }

        $this->assertCount(3, $user->fresh()->wishlistItems);
    }

    public function test_wishlist_item_uses_ulid(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $wishlistItem = $user->wishlistItems()->create([
            'product_id' => $product->id,
        ]);

        $this->assertMatchesRegularExpression(
            '/^[0-9A-HJKMNP-TV-Z]{26}$/i',
            $wishlistItem->id
        );
    }

    public function test_same_product_cannot_be_added_to_wishlist_twice(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $user->wishlistItems()->create([
            'product_id' => $product->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $user->wishlistItems()->create([
            'product_id' => $product->id,
        ]);
    }

    public function test_different_users_can_wishlist_the_same_product(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = Product::factory()->create();

        $user->wishlistItems()->create([
            'product_id' => $product->id,
        ]);

        $otherUser->wishlistItems()->create([
            'product_id' => $product->id,
        ]);

        $this->assertCount(1, $user->fresh()->wishlistItems);
        $this->assertCount(1, $otherUser->fresh()->wishlistItems);
    }
}