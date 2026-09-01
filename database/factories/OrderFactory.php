<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-'.$this->faker->unique()->numerify('######'),
            'shipping_address_id' => null,
            'billing_address_id' => null,
            'shipping_method' => 'standard',
            'status' => 'pending',
            'subtotal' => 0,
            'shipping_fee' => 0,
            'total' => 0,
        ];
    }
}
