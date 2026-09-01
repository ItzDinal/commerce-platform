<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_order_status_contains_the_expected_values(): void
    {
        $this->assertSame([
            'pending',
            'confirmed',
            'processing',
            'packed',
            'shipped',
            'out_for_delivery',
            'delivered',
        ], array_map(
            static fn (OrderStatus $status): string => $status->value,
            OrderStatus::cases()
        ));
    }

    public function test_order_status_has_the_expected_customer_labels(): void
    {
        $this->assertSame('Order Placed', OrderStatus::PENDING->label());
        $this->assertSame('Confirmed', OrderStatus::CONFIRMED->label());
        $this->assertSame('Processing', OrderStatus::PROCESSING->label());
        $this->assertSame('Packed', OrderStatus::PACKED->label());
        $this->assertSame('Shipped', OrderStatus::SHIPPED->label());
        $this->assertSame(
            'Out for Delivery',
            OrderStatus::OUT_FOR_DELIVERY->label()
        );
        $this->assertSame('Delivered', OrderStatus::DELIVERED->label());
    }

    public function test_order_status_exposes_the_authoritative_lifecycle(): void
    {
        $this->assertSame([
            OrderStatus::PENDING,
            OrderStatus::CONFIRMED,
            OrderStatus::PROCESSING,
            OrderStatus::PACKED,
            OrderStatus::SHIPPED,
            OrderStatus::OUT_FOR_DELIVERY,
            OrderStatus::DELIVERED,
        ], OrderStatus::lifecycle());
    }

    public function test_order_status_allows_only_the_next_lifecycle_transition(): void
    {
        $this->assertTrue(
            OrderStatus::PENDING->canTransitionTo(OrderStatus::CONFIRMED)
        );
        $this->assertFalse(
            OrderStatus::PENDING->canTransitionTo(OrderStatus::SHIPPED)
        );
        $this->assertFalse(
            OrderStatus::DELIVERED->canTransitionTo(OrderStatus::PENDING)
        );
    }
}
