<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case PACKED = 'packed';
    case SHIPPED = 'shipped';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Order Placed',
            self::CONFIRMED => 'Confirmed',
            self::PROCESSING => 'Processing',
            self::PACKED => 'Packed',
            self::SHIPPED => 'Shipped',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Delivered',
        };
    }

    /**
     * @return self[]
     */
    public static function lifecycle(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::PROCESSING,
            self::PACKED,
            self::SHIPPED,
            self::OUT_FOR_DELIVERY,
            self::DELIVERED,
        ];
    }

    public function canTransitionTo(self $nextStatus): bool
    {
        $currentPosition = array_search($this, self::lifecycle(), true);
        $nextPosition = array_search($nextStatus, self::lifecycle(), true);

        return $currentPosition !== false
            && $nextPosition === $currentPosition + 1;
    }
}
