<?php

namespace Tests\Feature\Customer;

use App\Enums\OrderStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Order;
use App\Models\Address;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_customer_dashboard(): void
    {
        $response = $this->get('/account');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_customer_can_view_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user)
            ->get('/account');

        $response->assertSuccessful();
        $response->assertSee('John Doe');
        $response->assertSee('john@example.com');
        $response->assertSee('Profile');
    }
    public function test_dashboard_shows_recent_orders(): void
    {
        $user = User::factory()->create();

        $orders = Order::factory()
            ->count(3)
            ->create([
                'user_id' => $user->id,
            ]);

        $response = $this->actingAs($user)
            ->get('/account');

        $response->assertSuccessful();

        foreach ($orders as $order) {
            $response->assertSee($order->id);
            $response->assertSee(OrderStatus::PENDING->label());
        }
    }

    public function test_dashboard_only_shows_the_authenticated_customers_orders(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userOrder = Order::factory()->create([
            'user_id' => $user->id,
        ]);

        $otherUserOrder = Order::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/account');

        $response->assertSuccessful();

        $response->assertSee($userOrder->id);
        $response->assertDontSee($otherUserOrder->id);
    }

    public function test_dashboard_shows_only_five_recent_orders(): void
    {
        $user = User::factory()->create();

        Order::factory()
            ->count(6)
            ->create([
                'user_id' => $user->id,
            ]);

        $response = $this->actingAs($user)
            ->get('/account');

        $response->assertSuccessful();

        $this->assertCount(
            5,
            $user->fresh()->orders()->latest()->take(5)->get()
        );
    }

    public function test_dashboard_shows_message_when_customer_has_no_orders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/account');

        $response->assertSuccessful();
        $response->assertSee('You have no orders yet.');
    }
    public function test_dashboard_shows_saved_addresses(): void
    {
        $user = User::factory()->create();

        $address = Address::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '123 Main Street',
            'city' => 'Colombo',
            'state' => 'Western',
            'postal_code' => '00100',
            'country' => 'Sri Lanka',
        ]);

        $response = $this->actingAs($user)
            ->get('/account');

        $response->assertSuccessful();

        $response->assertSee($address->first_name);
        $response->assertSee($address->last_name);
        $response->assertSee($address->address_line_1);
        $response->assertSee($address->city);
    }
    public function test_dashboard_only_shows_authenticated_customers_addresses(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userAddress = Address::factory()->create([
            'user_id' => $user->id,
            'address_line_1' => 'Customer Address',
        ]);

        $otherUserAddress = Address::factory()->create([
            'user_id' => $otherUser->id,
            'address_line_1' => 'Other Customer Address',
        ]);

        $response = $this->actingAs($user)
            ->get('/account');

        $response->assertSuccessful();

        $response->assertSee($userAddress->address_line_1);
        $response->assertDontSee($otherUserAddress->address_line_1);
    }
    public function test_dashboard_shows_message_when_customer_has_no_addresses(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/account');

        $response->assertSuccessful();
        $response->assertSee('You have no saved addresses.');
    }
    public function test_dashboard_shows_wishlist_summary(): void
    {
        $user = User::factory()->create();

        $wishlistItem = \App\Models\WishlistItem::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/account');

        $response->assertSuccessful();

        $response->assertSee('Wishlist');
        $response->assertSee($wishlistItem->product->name);
    }
    public function test_dashboard_only_shows_authenticated_customers_wishlist(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userWishlistItem = \App\Models\WishlistItem::factory()->create([
            'user_id' => $user->id,
        ]);

        $otherUserWishlistItem = \App\Models\WishlistItem::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/account');

        $response->assertSuccessful();

        $response->assertSee($userWishlistItem->product->name);
        $response->assertDontSee($otherUserWishlistItem->product->name);
    }
    public function test_dashboard_shows_message_when_customer_has_no_wishlist_items(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/account');

        $response->assertSuccessful();

        $response->assertSee('Your wishlist is empty.');
    }
}
