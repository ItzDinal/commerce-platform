<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue($order->user->is($user));
        $this->assertTrue($user->orders->contains($order));
    }

    public function test_order_uses_ulid(): void
    {
        $order = Order::factory()->create();

        $this->assertMatchesRegularExpression(
            '/^[0-9A-HJKMNP-TV-Z]{26}$/i',
            $order->id
        );

        $this->assertFalse($order->getIncrementing());
        $this->assertSame('string', $order->getKeyType());
    }

    public function test_order_status_is_stored(): void
    {
        $order = Order::factory()->create([
            'status' => 'pending',
        ]);

        $this->assertSame('pending', $order->status);
    }

    public function test_user_can_have_multiple_orders(): void
    {
        $user = User::factory()->create();

        Order::factory()
            ->count(3)
            ->create([
                'user_id' => $user->id,
            ]);

        $this->assertCount(3, $user->fresh()->orders);
    }
}