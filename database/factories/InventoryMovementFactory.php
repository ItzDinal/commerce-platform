<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    protected $model = InventoryMovement::class;

    public function definition(): array
    {
        $type = fake()->randomElement([
            'receive',
            'remove',
        ]);

        return [
            'inventory_id' => Inventory::factory(),
            'type' => $type,
            'quantity' => $type === 'receive'
                ? fake()->numberBetween(1, 100)
                : -fake()->numberBetween(1, 100),
            'reason' => fake()->randomElement([
                'Initial stock',
                'Stock adjustment',
                'Purchase received',
                'Damaged item',
                'Manual correction',
            ]),
        ];
    }
}